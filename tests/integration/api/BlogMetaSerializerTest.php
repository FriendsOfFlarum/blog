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
use Flarum\Testing\integration\RetrievesAuthorizedUsers;
use Flarum\Testing\integration\TestCase;

/**
 * Characterizes {@see \FoF\Blog\Api\Serializer\BlogMetaSerializer}: the shape of
 * the `blogMeta` relationship serialized onto a discussion, and the boolean
 * casting of the nullable tinyint columns. The frontend BlogMeta model mirrors
 * these attribute names, so the contract must stay stable.
 */
class BlogMetaSerializerTest extends TestCase
{
    use RetrievesAuthorizedUsers;

    public function setUp(): void
    {
        parent::setUp();

        $this->extension('flarum-tags', 'fof-blog');

        $now = Carbon::parse('2025-01-01 00:00:00');

        $this->prepareDatabase([
            'users' => [
                $this->normalUser(),
            ],
            'discussions' => [
                ['id' => 1, 'title' => 'Article', 'slug' => 'article', 'user_id' => 1, 'first_post_id' => 1, 'comment_count' => 1, 'created_at' => $now, 'last_posted_at' => $now],
            ],
            'posts' => [
                ['id' => 1, 'discussion_id' => 1, 'number' => 1, 'user_id' => 1, 'type' => 'comment', 'content' => '<t><p>Body.</p></t>', 'created_at' => $now],
            ],
            'blog_meta' => [
                ['id' => 1, 'discussion_id' => 1, 'featured_image' => 'cover.jpg', 'summary' => 'A short summary.', 'is_featured' => 1, 'is_sized' => 0, 'is_pending_review' => 0],
            ],
        ]);
    }

    /**
     * @return array<string, mixed>|null
     */
    protected function blogMetaFromDiscussion(): ?array
    {
        $response = $this->send(
            $this->request('GET', '/api/discussions/1', ['authenticatedAs' => 1])
                ->withQueryParams(['include' => 'blogMeta'])
        );

        $this->assertEquals(200, $response->getStatusCode());

        $body = json_decode($response->getBody()->getContents(), true);

        foreach ($body['included'] ?? [] as $resource) {
            if ($resource['type'] === 'blogMeta') {
                return $resource;
            }
        }

        return null;
    }

    /**
     * @test
     */
    public function discussion_includes_a_blog_meta_resource(): void
    {
        $meta = $this->blogMetaFromDiscussion();

        $this->assertNotNull($meta, 'Expected a blogMeta resource in the included payload');
        $this->assertSame('blogMeta', $meta['type']);
    }

    /**
     * @test
     */
    public function blog_meta_exposes_expected_attributes(): void
    {
        $attributes = $this->blogMetaFromDiscussion()['attributes'];

        $this->assertSame('cover.jpg', $attributes['featuredImage']);
        $this->assertSame('A short summary.', $attributes['summary']);
    }

    /**
     * @test
     */
    public function blog_meta_casts_flags_to_real_booleans(): void
    {
        $attributes = $this->blogMetaFromDiscussion()['attributes'];

        $this->assertTrue($attributes['isFeatured']);
        $this->assertFalse($attributes['isSized']);
        $this->assertFalse($attributes['isPendingReview']);
    }
}
