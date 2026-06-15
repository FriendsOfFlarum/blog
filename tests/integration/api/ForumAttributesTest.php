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
use Flarum\Testing\integration\RetrievesAuthorizedUsers;
use Flarum\Testing\integration\TestCase;

/**
 * Characterizes the blog attributes injected onto the Forum serializer payload
 * by {@see \FoF\Blog\Api\AttachForumSerializerAttributes}. These attributes are
 * read by the frontend to drive routing, redirects and permission-gated UI, so
 * their names, defaults and types are part of the extension's public contract.
 */
class ForumAttributesTest extends TestCase
{
    use RetrievesAuthorizedUsers;

    public function setUp(): void
    {
        parent::setUp();

        $this->extension('fof-blog');

        $this->prepareDatabase([
            'users' => [
                $this->normalUser(),
            ],
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    protected function forumAttributes(?int $authenticatedAs = null): array
    {
        $options = $authenticatedAs !== null ? ['authenticatedAs' => $authenticatedAs] : [];

        $response = $this->send($this->request('GET', '/api', $options));

        $this->assertEquals(200, $response->getStatusCode());

        $body = json_decode($response->getBody()->getContents(), true);

        return $body['data']['attributes'];
    }

    /**
     * @test
     */
    public function forum_payload_exposes_blog_default_attributes_for_a_guest(): void
    {
        $attributes = $this->forumAttributes();

        // Settings-backed defaults (no settings stored, so these are the fallbacks).
        $this->assertSame([''], $attributes['blogTags']);
        $this->assertSame('both', $attributes['blogRedirectsEnabled']);
        $this->assertNull($attributes['blogDefaultImage']);
        $this->assertArrayHasKey('blogCommentsEnabled', $attributes);
        $this->assertArrayHasKey('blogHideTags', $attributes);
        $this->assertArrayHasKey('blogCategoryHierarchy', $attributes);
        $this->assertArrayHasKey('blogAddSidebarNav', $attributes);
        $this->assertArrayHasKey('blogFeaturedCount', $attributes);
        $this->assertArrayHasKey('blogAddHero', $attributes);
    }

    /**
     * @test
     */
    public function blog_tags_setting_is_split_on_pipe(): void
    {
        $this->setting('blog_tags', '1|2|3');

        $attributes = $this->forumAttributes();

        $this->assertSame(['1', '2', '3'], $attributes['blogTags']);
    }

    /**
     * @test
     */
    public function guest_cannot_write_or_approve_blog_posts(): void
    {
        $attributes = $this->forumAttributes();

        $this->assertFalse($attributes['canWriteBlogPosts']);
        $this->assertFalse($attributes['canApproveBlogPosts']);
    }

    /**
     * @test
     */
    public function admin_can_write_and_approve_blog_posts(): void
    {
        $attributes = $this->forumAttributes(1);

        $this->assertTrue($attributes['canWriteBlogPosts']);
        $this->assertTrue($attributes['canApproveBlogPosts']);
    }
}
