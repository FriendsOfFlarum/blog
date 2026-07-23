<?php

/*
 * This file is part of fof/blog.
 *
 * Copyright (c) FriendsOfFlarum.
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace FoF\Blog\Tests\integration\api;

use Carbon\Carbon;
use Flarum\Discussion\Discussion;
use Flarum\Group\Group;
use Flarum\Post\Post;
use Flarum\Tags\Tag;
use Flarum\Testing\integration\RetrievesAuthorizedUsers;
use Flarum\Testing\integration\TestCase;
use Flarum\User\User;
use PHPUnit\Framework\Attributes\Test;

/**
 * Characterizes `POST /api/blogMeta` (the settings-modal path): writers may
 * attach meta to an existing discussion, a discussion only ever gets one meta
 * record (creating again updates it), and the review flag is computed
 * server-side — articles older than 30 seconds are treated as conversions of
 * existing discussions and auto-approved even when review is required.
 */
class CreateBlogMetaTest extends TestCase
{
    use RetrievesAuthorizedUsers;

    const WRITER = 3; // non-admin with blog.writeArticles only

    const OLD_DISCUSSION = 1;   // created long ago -> conversion, auto-approved
    const FRESH_DISCUSSION = 2; // created just now -> subject to review
    const DISCUSSION_WITH_META = 3;
    const RESTRICTED_DISCUSSION = 4; // in a restricted tag the writer cannot see

    public function setUp(): void
    {
        parent::setUp();

        $this->extension('flarum-tags', 'fof-blog');

        $old = Carbon::parse('2025-01-01 00:00:00');
        $now = Carbon::now();

        $this->prepareDatabase([
            User::class => [
                $this->normalUser(),
                ['id' => self::WRITER, 'username' => 'writer', 'email' => 'writer@machine.local', 'is_email_confirmed' => 1],
            ],
            Group::class => [
                ['id' => 100, 'name_singular' => 'Writer', 'name_plural' => 'Writers'],
            ],
            'group_user' => [
                ['user_id' => self::WRITER, 'group_id' => 100],
            ],
            'group_permission' => [
                ['group_id' => 100, 'permission' => 'blog.writeArticles'],
            ],
            Tag::class => [
                // A restricted tag nobody has been granted access to.
                ['id' => 9, 'name' => 'Staff', 'slug' => 'staff', 'position' => 0, 'is_restricted' => true, 'is_hidden' => false],
            ],
            Discussion::class => [
                ['id' => self::OLD_DISCUSSION, 'title' => 'Old discussion', 'slug' => 'old-discussion', 'user_id' => self::WRITER, 'first_post_id' => 1, 'comment_count' => 1, 'created_at' => $old, 'last_posted_at' => $old],
                ['id' => self::FRESH_DISCUSSION, 'title' => 'Fresh discussion', 'slug' => 'fresh-discussion', 'user_id' => self::WRITER, 'first_post_id' => 2, 'comment_count' => 1, 'created_at' => $now, 'last_posted_at' => $now],
                ['id' => self::DISCUSSION_WITH_META, 'title' => 'Existing article', 'slug' => 'existing-article', 'user_id' => self::WRITER, 'first_post_id' => 3, 'comment_count' => 1, 'created_at' => $old, 'last_posted_at' => $old],
                ['id' => self::RESTRICTED_DISCUSSION, 'title' => 'Staff only', 'slug' => 'staff-only', 'user_id' => 1, 'first_post_id' => 4, 'comment_count' => 1, 'created_at' => $old, 'last_posted_at' => $old],
            ],
            Post::class => [
                ['id' => 1, 'discussion_id' => self::OLD_DISCUSSION, 'number' => 1, 'user_id' => self::WRITER, 'type' => 'comment', 'content' => '<t><p>Old.</p></t>', 'created_at' => $old],
                ['id' => 2, 'discussion_id' => self::FRESH_DISCUSSION, 'number' => 1, 'user_id' => self::WRITER, 'type' => 'comment', 'content' => '<t><p>Fresh.</p></t>', 'created_at' => $now],
                ['id' => 3, 'discussion_id' => self::DISCUSSION_WITH_META, 'number' => 1, 'user_id' => self::WRITER, 'type' => 'comment', 'content' => '<t><p>Existing.</p></t>', 'created_at' => $old],
                ['id' => 4, 'discussion_id' => self::RESTRICTED_DISCUSSION, 'number' => 1, 'user_id' => 1, 'type' => 'comment', 'content' => '<t><p>Secret.</p></t>', 'created_at' => $old],
            ],
            'discussion_tag' => [
                ['discussion_id' => self::RESTRICTED_DISCUSSION, 'tag_id' => 9],
            ],
            'blog_meta' => [
                ['id' => 1, 'discussion_id' => self::DISCUSSION_WITH_META, 'summary' => 'Original summary.', 'is_featured' => 0, 'is_sized' => 0, 'is_pending_review' => 0],
            ],
        ]);

        $this->setting('blog_tags', '1');
    }

    /**
     * @param array<string, mixed> $attributes
     */
    protected function postMeta(int $authenticatedAs, int $discussionId, array $attributes = []): \Psr\Http\Message\ResponseInterface
    {
        return $this->send(
            $this->request('POST', '/api/blogMeta', [
                'authenticatedAs' => $authenticatedAs,
                'json'            => [
                    'data' => [
                        'type'          => 'blogMeta',
                        'attributes'    => $attributes,
                        'relationships' => [
                            'discussion' => [
                                'data' => ['type' => 'discussions', 'id' => (string) $discussionId],
                            ],
                        ],
                    ],
                ],
            ])
        );
    }

    #[Test]
    public function writer_can_attach_meta_to_an_existing_discussion(): void
    {
        $response = $this->postMeta(self::WRITER, self::OLD_DISCUSSION, ['summary' => 'A summary.']);

        $this->assertEquals(201, $response->getStatusCode());

        $meta = $this->database()->table('blog_meta')->where('discussion_id', self::OLD_DISCUSSION)->first();

        $this->assertNotNull($meta);
        $this->assertSame('A summary.', $meta->summary);
    }

    #[Test]
    public function converting_an_old_discussion_is_auto_approved_even_when_review_is_required(): void
    {
        $this->setting('blog_requires_review', '1');

        $this->postMeta(self::WRITER, self::OLD_DISCUSSION);

        $this->assertEquals(0, $this->database()->table('blog_meta')->where('discussion_id', self::OLD_DISCUSSION)->value('is_pending_review'));
    }

    #[Test]
    public function meta_for_a_fresh_discussion_is_pending_when_review_is_required(): void
    {
        $this->setting('blog_requires_review', '1');

        $this->postMeta(self::WRITER, self::FRESH_DISCUSSION);

        $this->assertEquals(1, $this->database()->table('blog_meta')->where('discussion_id', self::FRESH_DISCUSSION)->value('is_pending_review'));
    }

    #[Test]
    public function creating_meta_for_a_discussion_that_has_meta_updates_the_existing_record(): void
    {
        $response = $this->postMeta(self::WRITER, self::DISCUSSION_WITH_META, ['summary' => 'Replaced summary.']);

        $this->assertEquals(201, $response->getStatusCode());

        $rows = $this->database()->table('blog_meta')->where('discussion_id', self::DISCUSSION_WITH_META)->get();

        $this->assertCount(1, $rows, 'Expected no duplicate blog_meta rows');
        $this->assertEquals(1, $rows[0]->id, 'Expected the original record to be reused');
        $this->assertSame('Replaced summary.', $rows[0]->summary);
    }

    #[Test]
    public function plain_member_cannot_create_meta(): void
    {
        $response = $this->postMeta(2, self::OLD_DISCUSSION, ['summary' => 'Hijacked.']);

        $this->assertEquals(403, $response->getStatusCode());
        $this->assertNull($this->database()->table('blog_meta')->where('discussion_id', self::OLD_DISCUSSION)->first());
    }

    #[Test]
    public function the_discussion_relationship_is_required(): void
    {
        $response = $this->send(
            $this->request('POST', '/api/blogMeta', [
                'authenticatedAs' => self::WRITER,
                'json'            => [
                    'data' => [
                        'type'       => 'blogMeta',
                        'attributes' => ['summary' => 'Orphan meta.'],
                    ],
                ],
            ])
        );

        $this->assertEquals(422, $response->getStatusCode());
        // Only the fixture row for DISCUSSION_WITH_META exists — no orphan was created.
        $this->assertEquals(1, $this->database()->table('blog_meta')->count());
    }

    #[Test]
    public function writer_cannot_attach_meta_to_a_discussion_they_cannot_see(): void
    {
        $response = $this->postMeta(self::WRITER, self::RESTRICTED_DISCUSSION, ['summary' => 'Sneaky.']);

        // Relationship resolution goes through the discussion resource's
        // visibility scope, so an invisible discussion must not resolve.
        $this->assertGreaterThanOrEqual(400, $response->getStatusCode());
        $this->assertNull($this->database()->table('blog_meta')->where('discussion_id', self::RESTRICTED_DISCUSSION)->first());
    }
}
