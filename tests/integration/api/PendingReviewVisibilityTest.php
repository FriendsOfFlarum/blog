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
use PHPUnit\Framework\Attributes\Test;
use Flarum\User\User;
use Flarum\Group\Group;
use Flarum\Discussion\Discussion;
use Flarum\Post\Post;

/**
 * Blog articles pending review must only be visible to a user who can approve
 * posts, or to the article's own author. They must NOT leak to other logged-in
 * users — including other writers (issue #151). Published articles are visible
 * to everyone.
 */
class PendingReviewVisibilityTest extends TestCase
{
    use RetrievesAuthorizedUsers;

    // Discussion ids.
    const PUBLISHED = 1;
    const PENDING_BY_AUTHOR = 2;

    // User ids.
    const APPROVER = 1;     // admin — has blog.canApprovePosts
    const AUTHOR = 3;       // wrote the pending article
    const OTHER_WRITER = 4; // can write articles, but is not the author / approver

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
                ['id' => self::PUBLISHED, 'title' => 'Published', 'slug' => 'published', 'user_id' => self::AUTHOR, 'first_post_id' => 1, 'comment_count' => 1, 'created_at' => $now, 'last_posted_at' => $now, 'is_private' => 0],
                ['id' => self::PENDING_BY_AUTHOR, 'title' => 'Pending', 'slug' => 'pending', 'user_id' => self::AUTHOR, 'first_post_id' => 2, 'comment_count' => 1, 'created_at' => $now, 'last_posted_at' => $now, 'is_private' => 0],
            ],
            Post::class => [
                ['id' => 1, 'discussion_id' => self::PUBLISHED, 'number' => 1, 'user_id' => self::AUTHOR, 'type' => 'comment', 'content' => '<t><p>Published.</p></t>', 'created_at' => $now],
                ['id' => 2, 'discussion_id' => self::PENDING_BY_AUTHOR, 'number' => 1, 'user_id' => self::AUTHOR, 'type' => 'comment', 'content' => '<t><p>Pending.</p></t>', 'created_at' => $now],
            ],
            'blog_meta' => [
                ['id' => 1, 'discussion_id' => self::PUBLISHED, 'is_pending_review' => 0],
                ['id' => 2, 'discussion_id' => self::PENDING_BY_AUTHOR, 'is_pending_review' => 1],
            ],
        ]);
    }

    /**
     * @return int[] discussion ids visible in the listing
     */
    protected function visibleIds(?int $authenticatedAs): array
    {
        $options = $authenticatedAs !== null ? ['authenticatedAs' => $authenticatedAs] : [];

        $response = $this->send($this->request('GET', '/api/discussions', $options));
        $this->assertEquals(200, $response->getStatusCode());

        $body = json_decode($response->getBody()->getContents(), true);

        return array_map(fn ($d) => (int) $d['id'], $body['data'] ?? []);
    }

    #[Test]
    public function approver_sees_pending_articles(): void
    {
        $this->assertContains(self::PENDING_BY_AUTHOR, $this->visibleIds(self::APPROVER));
    }

    #[Test]
    public function author_sees_their_own_pending_article(): void
    {
        $this->assertContains(self::PENDING_BY_AUTHOR, $this->visibleIds(self::AUTHOR));
    }

    #[Test]
    public function other_writer_does_not_see_someone_elses_pending_article(): void
    {
        $visible = $this->visibleIds(self::OTHER_WRITER);

        $this->assertContains(self::PUBLISHED, $visible);
        $this->assertNotContains(self::PENDING_BY_AUTHOR, $visible);
    }

    #[Test]
    public function plain_member_does_not_see_pending_articles(): void
    {
        $visible = $this->visibleIds(2); // normalUser, no blog permissions

        $this->assertContains(self::PUBLISHED, $visible);
        $this->assertNotContains(self::PENDING_BY_AUTHOR, $visible);
    }

    #[Test]
    public function guest_does_not_see_pending_articles(): void
    {
        $visible = $this->visibleIds(null);

        $this->assertNotContains(self::PENDING_BY_AUTHOR, $visible);
    }
}
