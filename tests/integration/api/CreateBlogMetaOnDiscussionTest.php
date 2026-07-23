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

use Flarum\Tags\Tag;
use Flarum\Testing\integration\RetrievesAuthorizedUsers;
use Flarum\Testing\integration\TestCase;
use Flarum\User\User;
use PHPUnit\Framework\Attributes\Test;

/**
 * Characterizes {@see \FoF\Blog\Listeners\CreateBlogMetaOnDiscussionCreate}:
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

        $this->extension('flarum-tags', 'fof-blog');

        $this->prepareDatabase([
            User::class => [
                $this->normalUser(),
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
}
