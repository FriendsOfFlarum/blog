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

use Flarum\Group\Group;
use Flarum\Tags\Tag;
use Flarum\Testing\integration\RetrievesAuthorizedUsers;
use Flarum\Testing\integration\TestCase;
use Flarum\User\User;
use PHPUnit\Framework\Attributes\Test;

/**
 * Characterizes {@see \FoF\Blog\Listener\CreateBlogMetaOnDiscussionCreate}:
 * when a discussion is created with a configured blog tag, a blog_meta row is
 * created automatically, gated on the `blog.writeArticles` permission. A
 * discussion without a blog tag gets no blog_meta.
 */
class CreateBlogMetaOnDiscussionTest extends TestCase
{
    use RetrievesAuthorizedUsers;

    public function setUp(): void
    {
        parent::setUp();

        // flarum-lock is a hard dependency of fof-blog and provides the
        // `is_locked` column the auto-lock behaviour writes to.
        $this->extension('flarum-tags', 'flarum-lock', 'fof-blog');

        $this->prepareDatabase([
            User::class => [
                $this->normalUser(),
                ['id' => 3, 'username' => 'writer', 'email' => 'writer@machine.local', 'is_email_confirmed' => 1],
                ['id' => 4, 'username' => 'trusted', 'email' => 'trusted@machine.local', 'is_email_confirmed' => 1],
            ],
            Group::class => [
                ['id' => 100, 'name_singular' => 'Writer', 'name_plural' => 'Writers'],
                ['id' => 101, 'name_singular' => 'Trusted writer', 'name_plural' => 'Trusted writers'],
            ],
            'group_user' => [
                ['user_id' => 3, 'group_id' => 100],
                ['user_id' => 4, 'group_id' => 101],
            ],
            'group_permission' => [
                // Writers can write, but their articles may require review.
                ['group_id' => 100, 'permission' => 'blog.writeArticles'],
                // Trusted writers bypass the review queue.
                ['group_id' => 101, 'permission' => 'blog.writeArticles'],
                ['group_id' => 101, 'permission' => 'blog.autoApprovePosts'],
            ],
            Tag::class => [
                ['id' => 1, 'name' => 'Blog', 'slug' => 'blog', 'position' => 0, 'is_restricted' => false, 'is_hidden' => false],
                ['id' => 2, 'name' => 'General', 'slug' => 'general', 'position' => 1, 'is_restricted' => false, 'is_hidden' => false],
            ],
        ]);

        // Tag id 1 is the blog tag.
        $this->setting('blog_tags', '1');
    }

    /**
     * Mirrors the payload the blog composer sends: the blog meta travels as a
     * `newBlogMeta` object (or null) inside the discussion attributes.
     *
     * @param array<int>                $tagIds
     * @param array<string, mixed>|null $blogMeta
     */
    protected function createDiscussion(int $authenticatedAs, array $tagIds, ?array $blogMeta = null): \Psr\Http\Message\ResponseInterface
    {
        return $this->send(
            $this->request('POST', '/api/discussions', [
                'authenticatedAs' => $authenticatedAs,
                'json'            => [
                    'data' => [
                        'attributes'    => [
                            'title'       => 'A new article that is long enough',
                            'content'     => 'This is the body of the article, long enough to pass validation.',
                            'newBlogMeta' => $blogMeta,
                        ],
                        'relationships' => [
                            'tags' => [
                                'data' => array_map(fn ($id) => ['type' => 'tags', 'id' => (string) $id], $tagIds),
                            ],
                        ],
                    ],
                ],
            ])
        );
    }

    protected function blogMetaCount(int $discussionId): int
    {
        return $this->database()->table('blog_meta')->where('discussion_id', $discussionId)->count();
    }

    #[Test]
    public function admin_creating_a_discussion_with_the_blog_tag_creates_blog_meta(): void
    {
        $response = $this->createDiscussion(1, [1]);

        $this->assertEquals(201, $response->getStatusCode());

        $id = json_decode($response->getBody()->getContents(), true)['data']['id'];

        $this->assertSame(1, $this->blogMetaCount((int) $id));
    }

    #[Test]
    public function blog_meta_attributes_from_the_composer_payload_are_persisted(): void
    {
        $response = $this->createDiscussion(1, [1], [
            'featuredImage' => 'https://example.com/cover.jpg',
            'summary'       => 'A short summary.',
            'isSized'       => true,
        ]);

        $this->assertEquals(201, $response->getStatusCode());

        $id = json_decode($response->getBody()->getContents(), true)['data']['id'];

        $meta = $this->database()->table('blog_meta')->where('discussion_id', (int) $id)->first();

        $this->assertNotNull($meta, 'Expected a blog_meta row to be created');
        $this->assertSame('https://example.com/cover.jpg', $meta->featured_image);
        $this->assertSame('A short summary.', $meta->summary);
        $this->assertEquals(1, $meta->is_sized);
    }

    #[Test]
    public function discussion_without_the_blog_tag_does_not_create_blog_meta(): void
    {
        $response = $this->createDiscussion(1, [2]);

        $this->assertEquals(201, $response->getStatusCode());

        $id = json_decode($response->getBody()->getContents(), true)['data']['id'];

        $this->assertSame(0, $this->blogMetaCount((int) $id));
    }

    #[Test]
    public function user_without_write_permission_cannot_create_a_blog_article(): void
    {
        // normalUser() (id 2) is not a moderator, so lacks blog.writeArticles.
        $response = $this->createDiscussion(2, [1]);

        $this->assertEquals(403, $response->getStatusCode());
    }

    #[Test]
    public function non_admin_writer_with_permission_can_create_a_blog_article(): void
    {
        $response = $this->createDiscussion(3, [1]);

        $this->assertEquals(201, $response->getStatusCode());

        $id = json_decode($response->getBody()->getContents(), true)['data']['id'];

        $meta = $this->database()->table('blog_meta')->where('discussion_id', (int) $id)->first();

        $this->assertNotNull($meta, 'Expected a blog_meta row to be created');
        // Review is not required by default, so the article is published immediately.
        $this->assertEquals(0, $meta->is_pending_review);
    }

    #[Test]
    public function article_is_pending_when_review_is_required_and_writer_cannot_auto_approve(): void
    {
        $this->setting('blog_requires_review', '1');

        $response = $this->createDiscussion(3, [1]);

        $this->assertEquals(201, $response->getStatusCode());

        $id = json_decode($response->getBody()->getContents(), true)['data']['id'];

        $this->assertEquals(1, $this->database()->table('blog_meta')->where('discussion_id', (int) $id)->value('is_pending_review'));
    }

    #[Test]
    public function auto_approve_permission_bypasses_the_review_queue(): void
    {
        $this->setting('blog_requires_review', '1');

        $response = $this->createDiscussion(4, [1]);

        $this->assertEquals(201, $response->getStatusCode());

        $id = json_decode($response->getBody()->getContents(), true)['data']['id'];

        $this->assertEquals(0, $this->database()->table('blog_meta')->where('discussion_id', (int) $id)->value('is_pending_review'));
    }

    #[Test]
    public function articles_are_locked_when_comments_are_disabled(): void
    {
        $this->setting('blog_allow_comments', '0');

        $response = $this->createDiscussion(3, [1]);

        $this->assertEquals(201, $response->getStatusCode());

        $id = json_decode($response->getBody()->getContents(), true)['data']['id'];

        $this->assertEquals(1, $this->database()->table('discussions')->where('id', (int) $id)->value('is_locked'));
    }
}
