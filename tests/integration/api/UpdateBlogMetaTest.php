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
use Flarum\Testing\integration\RetrievesAuthorizedUsers;
use Flarum\Testing\integration\TestCase;
use Flarum\User\User;
use PHPUnit\Framework\Attributes\Test;

/**
 * Characterizes `PATCH /api/blogMeta/{id}`: writers may edit article meta, and
 * publishing a pending article (`isPendingReview` -> false) is reserved for
 * users who can approve posts — for anyone else the flag is silently ignored,
 * never an error (the settings modal always sends it).
 */
class UpdateBlogMetaTest extends TestCase
{
    use RetrievesAuthorizedUsers;

    // User ids.
    const APPROVER = 1;     // admin — has blog.canApprovePosts
    const AUTHOR = 3;       // wrote the pending article
    const OTHER_WRITER = 4; // can write articles, but cannot approve

    public function setUp(): void
    {
        parent::setUp();

        $this->extension('flarum-tags', 'fof-blog');

        $now = Carbon::parse('2025-01-01 00:00:00');

        $this->prepareDatabase([
            User::class => [
                $this->normalUser(),
                ['id' => self::AUTHOR, 'username' => 'author', 'email' => 'author@machine.local', 'is_email_confirmed' => 1],
                ['id' => self::OTHER_WRITER, 'username' => 'writer', 'email' => 'writer@machine.local', 'is_email_confirmed' => 1],
            ],
            Group::class => [
                ['id' => 100, 'name_singular' => 'Writer', 'name_plural' => 'Writers'],
            ],
            'group_user' => [
                ['user_id' => self::AUTHOR, 'group_id' => 100],
                ['user_id' => self::OTHER_WRITER, 'group_id' => 100],
            ],
            'group_permission' => [
                // Writers can write, but cannot approve.
                ['group_id' => 100, 'permission' => 'blog.writeArticles'],
            ],
            Discussion::class => [
                ['id' => 1, 'title' => 'Pending article', 'slug' => 'pending-article', 'user_id' => self::AUTHOR, 'first_post_id' => 1, 'comment_count' => 1, 'created_at' => $now, 'last_posted_at' => $now],
            ],
            Post::class => [
                ['id' => 1, 'discussion_id' => 1, 'number' => 1, 'user_id' => self::AUTHOR, 'type' => 'comment', 'content' => '<t><p>Body.</p></t>', 'created_at' => $now],
            ],
            'blog_meta' => [
                ['id' => 1, 'discussion_id' => 1, 'summary' => 'Original summary.', 'is_featured' => 0, 'is_sized' => 0, 'is_pending_review' => 1],
            ],
        ]);

        $this->setting('blog_tags', '1');
    }

    /**
     * @param array<string, mixed> $attributes
     */
    protected function patchMeta(?int $authenticatedAs, array $attributes): \Psr\Http\Message\ResponseInterface
    {
        $options = $authenticatedAs !== null ? ['authenticatedAs' => $authenticatedAs] : [];

        return $this->send(
            $this->request('PATCH', '/api/blogMeta/1', $options + [
                'json' => [
                    'data' => [
                        'type'       => 'blogMeta',
                        'id'         => '1',
                        'attributes' => $attributes,
                    ],
                ],
            ])
        );
    }

    protected function isPendingReview(): bool
    {
        return (bool) $this->database()->table('blog_meta')->where('id', 1)->value('is_pending_review');
    }

    #[Test]
    public function approver_can_publish_a_pending_article(): void
    {
        $response = $this->patchMeta(self::APPROVER, ['isPendingReview' => false]);

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertFalse($this->isPendingReview());
    }

    #[Test]
    public function writer_without_approve_permission_cannot_publish_but_gets_no_error(): void
    {
        $response = $this->patchMeta(self::OTHER_WRITER, ['isPendingReview' => false]);

        // Silently ignored, not rejected — the settings modal always sends the flag.
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertTrue($this->isPendingReview());
    }

    #[Test]
    public function writer_can_update_article_meta(): void
    {
        $response = $this->patchMeta(self::AUTHOR, ['summary' => 'Updated summary.']);

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertSame('Updated summary.', $this->database()->table('blog_meta')->where('id', 1)->value('summary'));
    }

    #[Test]
    public function plain_member_cannot_update_article_meta(): void
    {
        $response = $this->patchMeta(2, ['summary' => 'Hijacked.']);

        $this->assertEquals(403, $response->getStatusCode());
        $this->assertSame('Original summary.', $this->database()->table('blog_meta')->where('id', 1)->value('summary'));
    }

    #[Test]
    public function guest_cannot_update_article_meta(): void
    {
        $response = $this->patchMeta(null, ['summary' => 'Hijacked.']);

        // Tokenless guest writes are rejected by CSRF protection (400) before
        // the endpoint's authentication check would produce a 401.
        $this->assertEquals(400, $response->getStatusCode());
        $this->assertSame('Original summary.', $this->database()->table('blog_meta')->where('id', 1)->value('summary'));
    }
}
