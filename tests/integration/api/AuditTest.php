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
 * Characterizes the (optional) flarum/audit integration: creating, approving
 * and editing blog articles produces audit log entries attributed to the
 * acting user.
 */
class AuditTest extends TestCase
{
    use RetrievesAuthorizedUsers;

    const WRITER = 3;    // non-admin with blog.writeArticles
    const MODERATOR = 4; // non-admin with write + approve permissions

    const PENDING_ARTICLE = 1;

    public function setUp(): void
    {
        parent::setUp();

        $this->extension('flarum-tags', 'flarum-lock', 'flarum-audit', 'fof-blog');

        $now = Carbon::parse('2025-01-01 00:00:00');

        $this->prepareDatabase([
            User::class => [
                $this->normalUser(),
                ['id' => self::WRITER, 'username' => 'writer', 'email' => 'writer@machine.local', 'is_email_confirmed' => 1],
                ['id' => self::MODERATOR, 'username' => 'moderator', 'email' => 'moderator@machine.local', 'is_email_confirmed' => 1],
            ],
            Group::class => [
                ['id' => 100, 'name_singular' => 'Writer', 'name_plural' => 'Writers'],
                ['id' => 101, 'name_singular' => 'Moderator', 'name_plural' => 'Moderators'],
            ],
            'group_user' => [
                ['user_id' => self::WRITER, 'group_id' => 100],
                ['user_id' => self::MODERATOR, 'group_id' => 101],
            ],
            'group_permission' => [
                ['group_id' => 100, 'permission' => 'blog.writeArticles'],
                ['group_id' => 101, 'permission' => 'blog.writeArticles'],
                ['group_id' => 101, 'permission' => 'blog.canApprovePosts'],
            ],
            Tag::class => [
                ['id' => 1, 'name' => 'Blog', 'slug' => 'blog', 'position' => 0, 'is_restricted' => false, 'is_hidden' => false],
            ],
            Discussion::class => [
                ['id' => self::PENDING_ARTICLE, 'title' => 'Pending article', 'slug' => 'pending-article', 'user_id' => self::WRITER, 'first_post_id' => 1, 'comment_count' => 1, 'created_at' => $now, 'last_posted_at' => $now],
            ],
            Post::class => [
                ['id' => 1, 'discussion_id' => self::PENDING_ARTICLE, 'number' => 1, 'user_id' => self::WRITER, 'type' => 'comment', 'content' => '<t><p>Body.</p></t>', 'created_at' => $now],
            ],
            'blog_meta' => [
                ['id' => 1, 'discussion_id' => self::PENDING_ARTICLE, 'summary' => 'Original summary.', 'is_featured' => 0, 'is_sized' => 0, 'is_pending_review' => 1],
            ],
        ]);

        $this->setting('blog_tags', '1');
    }

    /**
     * @param array<string, mixed> $payload subset of the stored payload to match
     */
    protected function assertLogExists(string $action, array $payload, int $actorId): void
    {
        $logs = $this->database()->table('audit_log')->where('action', $action)->get();

        foreach ($logs as $log) {
            $stored = json_decode($log->payload ?? 'null', true) ?? [];

            if (array_intersect_key($stored, $payload) == $payload && (int) $log->actor_id === $actorId) {
                return;
            }
        }

        $this->assertTrue(false, "No '{$action}' audit log entry with the expected payload and actor {$actorId}. Found: ".json_encode($logs));
    }

    protected function assertNoLog(string $action): void
    {
        $this->assertEquals(0, $this->database()->table('audit_log')->where('action', $action)->count());
    }

    /**
     * @param array<string, mixed> $attributes
     */
    protected function patchMeta(int $authenticatedAs, array $attributes): void
    {
        $response = $this->send(
            $this->request('PATCH', '/api/blogMeta/1', [
                'authenticatedAs' => $authenticatedAs,
                'json'            => [
                    'data' => [
                        'type'       => 'blogMeta',
                        'id'         => '1',
                        'attributes' => $attributes,
                    ],
                ],
            ])
        );

        $this->assertEquals(200, $response->getStatusCode());
    }

    #[Test]
    public function creating_an_article_is_logged(): void
    {
        $response = $this->send(
            $this->request('POST', '/api/discussions', [
                'authenticatedAs' => self::WRITER,
                'json'            => [
                    'data' => [
                        'attributes'    => [
                            'title'   => 'A new article that is long enough',
                            'content' => 'This is the body of the article, long enough to pass validation.',
                        ],
                        'relationships' => [
                            'tags' => [
                                'data' => [['type' => 'tags', 'id' => '1']],
                            ],
                        ],
                    ],
                ],
            ])
        );

        $this->assertEquals(201, $response->getStatusCode());

        $id = (int) json_decode($response->getBody()->getContents(), true)['data']['id'];

        $this->assertLogExists('article.created', ['discussion_id' => $id], self::WRITER);
    }

    #[Test]
    public function approving_an_article_is_logged(): void
    {
        $this->patchMeta(self::MODERATOR, ['isPendingReview' => false]);

        $this->assertLogExists('article.approved', ['discussion_id' => self::PENDING_ARTICLE], self::MODERATOR);
        // A pure approval is not also logged as a meta update.
        $this->assertNoLog('article.updated');
    }

    #[Test]
    public function updating_article_meta_is_logged_with_the_changed_fields(): void
    {
        $this->patchMeta(self::WRITER, ['summary' => 'Updated summary.']);

        $this->assertLogExists('article.updated', ['discussion_id' => self::PENDING_ARTICLE, 'changed' => ['summary']], self::WRITER);
        $this->assertNoLog('article.approved');
    }

    #[Test]
    public function a_no_op_update_is_not_logged(): void
    {
        $this->patchMeta(self::WRITER, ['summary' => 'Original summary.']);

        $this->assertNoLog('article.updated');
        $this->assertNoLog('article.approved');
    }

    #[Test]
    public function featuring_an_article_is_logged(): void
    {
        $this->patchMeta(self::WRITER, ['isFeatured' => true]);

        $this->assertLogExists('article.featured', ['discussion_id' => self::PENDING_ARTICLE], self::WRITER);
        // Featuring has its own action; it is not also a generic meta update.
        $this->assertNoLog('article.updated');
        $this->assertNoLog('article.unfeatured');
    }

    #[Test]
    public function unfeaturing_an_article_is_logged(): void
    {
        $this->patchMeta(self::WRITER, ['isFeatured' => true]);
        $this->patchMeta(self::WRITER, ['isFeatured' => false]);

        $this->assertLogExists('article.unfeatured', ['discussion_id' => self::PENDING_ARTICLE], self::WRITER);
        $this->assertNoLog('article.updated');
    }

    #[Test]
    public function featuring_alongside_a_content_change_logs_both_actions(): void
    {
        $this->patchMeta(self::WRITER, ['isFeatured' => true, 'summary' => 'Updated summary.']);

        $this->assertLogExists('article.featured', ['discussion_id' => self::PENDING_ARTICLE], self::WRITER);
        $this->assertLogExists('article.updated', ['discussion_id' => self::PENDING_ARTICLE, 'changed' => ['summary']], self::WRITER);
    }
}
