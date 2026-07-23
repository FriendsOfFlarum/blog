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
use Flarum\User\User;
use PHPUnit\Framework\Attributes\Test;

/**
 * Characterizes the blog attributes injected onto the forum API payload
 * by {@see \FoF\Blog\Api\ForumResourceFields}. These attributes are
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
            User::class => [
                $this->normalUser(),
                ['id' => 3, 'username' => 'writer', 'email' => 'writer@machine.local', 'is_email_confirmed' => 1],
                ['id' => 4, 'username' => 'approver', 'email' => 'approver@machine.local', 'is_email_confirmed' => 1],
            ],
            Group::class => [
                ['id' => 100, 'name_singular' => 'Writer', 'name_plural' => 'Writers'],
                ['id' => 101, 'name_singular' => 'Approver', 'name_plural' => 'Approvers'],
            ],
            'group_user' => [
                ['user_id' => 3, 'group_id' => 100],
                ['user_id' => 4, 'group_id' => 101],
            ],
            'group_permission' => [
                ['group_id' => 100, 'permission' => 'blog.writeArticles'],
                ['group_id' => 101, 'permission' => 'blog.canApprovePosts'],
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

    #[Test]
    public function forum_payload_exposes_blog_default_attributes_for_a_guest(): void
    {
        $attributes = $this->forumAttributes();

        // Settings-backed defaults (no settings stored, so these are the fallbacks).
        $this->assertSame([''], $attributes['blogTags']);
        $this->assertSame('both', $attributes['blogRedirectsEnabled']);
        $this->assertNull($attributes['blogDefaultImage']);
        $this->assertTrue($attributes['blogCommentsEnabled']);
        $this->assertTrue($attributes['blogHideTags']);
        $this->assertTrue($attributes['blogCategoryHierarchy']);
        $this->assertTrue($attributes['blogAddSidebarNav']);
        $this->assertSame(3, $attributes['blogFeaturedCount']);
        $this->assertTrue($attributes['blogAddHero']);
    }

    #[Test]
    public function blog_tags_setting_is_split_on_pipe(): void
    {
        $this->setting('blog_tags', '1|2|3');

        $attributes = $this->forumAttributes();

        $this->assertSame(['1', '2', '3'], $attributes['blogTags']);
    }

    #[Test]
    public function guest_cannot_write_or_approve_blog_posts(): void
    {
        $attributes = $this->forumAttributes();

        $this->assertFalse($attributes['canWriteBlogPosts']);
        $this->assertFalse($attributes['canApproveBlogPosts']);
    }

    #[Test]
    public function admin_can_write_and_approve_blog_posts(): void
    {
        // Admins are open-gated; the real permission checks are covered by the
        // writer/approver tests below.
        $attributes = $this->forumAttributes(1);

        $this->assertTrue($attributes['canWriteBlogPosts']);
        $this->assertTrue($attributes['canApproveBlogPosts']);
    }

    #[Test]
    public function writer_permission_grants_writing_but_not_approving(): void
    {
        $attributes = $this->forumAttributes(3);

        $this->assertTrue($attributes['canWriteBlogPosts']);
        $this->assertFalse($attributes['canApproveBlogPosts']);
    }

    #[Test]
    public function approve_permission_grants_approving_but_not_writing(): void
    {
        $attributes = $this->forumAttributes(4);

        $this->assertFalse($attributes['canWriteBlogPosts']);
        $this->assertTrue($attributes['canApproveBlogPosts']);
    }

    #[Test]
    public function plain_member_can_neither_write_nor_approve(): void
    {
        $attributes = $this->forumAttributes(2);

        $this->assertFalse($attributes['canWriteBlogPosts']);
        $this->assertFalse($attributes['canApproveBlogPosts']);
    }
}
