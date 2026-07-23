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
use Flarum\Post\Post;
use Flarum\Tags\Tag;
use Flarum\Testing\integration\RetrievesAuthorizedUsers;
use Flarum\Testing\integration\TestCase;
use Flarum\User\User;
use PHPUnit\Framework\Attributes\Test;

/**
 * Characterizes {@see \FoF\Blog\Search\Filter\BlogArticleFilter} with multiple
 * configured blog tags: `filter[blog]` matches an article carrying ANY blog
 * tag, and its negation `filter[-blog]` matches only discussions carrying NONE.
 */
class BlogArticleFilterTest extends TestCase
{
    use RetrievesAuthorizedUsers;

    const ARTICLE_IN_FIRST_CATEGORY = 1;
    const ARTICLE_IN_SECOND_CATEGORY = 2;
    const REGULAR_DISCUSSION = 3;

    public function setUp(): void
    {
        parent::setUp();

        $this->extension('flarum-tags', 'fof-blog');

        $now = Carbon::parse('2025-01-01 00:00:00');

        $this->prepareDatabase([
            User::class => [
                $this->normalUser(),
            ],
            Tag::class => [
                ['id' => 1, 'name' => 'Blog', 'slug' => 'blog', 'position' => 0, 'is_restricted' => false, 'is_hidden' => false],
                ['id' => 2, 'name' => 'News', 'slug' => 'news', 'position' => 1, 'is_restricted' => false, 'is_hidden' => false],
                ['id' => 3, 'name' => 'General', 'slug' => 'general', 'position' => 2, 'is_restricted' => false, 'is_hidden' => false],
            ],
            Discussion::class => [
                ['id' => self::ARTICLE_IN_FIRST_CATEGORY, 'title' => 'First article', 'slug' => 'first-article', 'user_id' => 1, 'first_post_id' => 1, 'comment_count' => 1, 'created_at' => $now, 'last_posted_at' => $now],
                ['id' => self::ARTICLE_IN_SECOND_CATEGORY, 'title' => 'Second article', 'slug' => 'second-article', 'user_id' => 1, 'first_post_id' => 2, 'comment_count' => 1, 'created_at' => $now, 'last_posted_at' => $now],
                ['id' => self::REGULAR_DISCUSSION, 'title' => 'Discussion', 'slug' => 'discussion', 'user_id' => 1, 'first_post_id' => 3, 'comment_count' => 1, 'created_at' => $now, 'last_posted_at' => $now],
            ],
            Post::class => [
                ['id' => 1, 'discussion_id' => self::ARTICLE_IN_FIRST_CATEGORY, 'number' => 1, 'user_id' => 1, 'type' => 'comment', 'content' => '<t><p>First.</p></t>', 'created_at' => $now],
                ['id' => 2, 'discussion_id' => self::ARTICLE_IN_SECOND_CATEGORY, 'number' => 1, 'user_id' => 1, 'type' => 'comment', 'content' => '<t><p>Second.</p></t>', 'created_at' => $now],
                ['id' => 3, 'discussion_id' => self::REGULAR_DISCUSSION, 'number' => 1, 'user_id' => 1, 'type' => 'comment', 'content' => '<t><p>Third.</p></t>', 'created_at' => $now],
            ],
            'discussion_tag' => [
                ['discussion_id' => self::ARTICLE_IN_FIRST_CATEGORY, 'tag_id' => 1],
                ['discussion_id' => self::ARTICLE_IN_SECOND_CATEGORY, 'tag_id' => 2],
                ['discussion_id' => self::REGULAR_DISCUSSION, 'tag_id' => 3],
            ],
            'blog_meta' => [
                ['id' => 1, 'discussion_id' => self::ARTICLE_IN_FIRST_CATEGORY, 'is_pending_review' => 0],
                ['id' => 2, 'discussion_id' => self::ARTICLE_IN_SECOND_CATEGORY, 'is_pending_review' => 0],
            ],
        ]);

        // Two blog categories.
        $this->setting('blog_tags', '1|2');
    }

    /**
     * @param array<string, mixed> $filter
     *
     * @return int[] visible discussion ids
     */
    protected function visibleIds(array $filter): array
    {
        $response = $this->send(
            $this->request('GET', '/api/discussions', ['authenticatedAs' => 1])
                ->withQueryParams(['filter' => $filter])
        );
        $this->assertEquals(200, $response->getStatusCode());

        $body = json_decode($response->getBody()->getContents(), true);

        return array_map(fn ($d) => (int) $d['id'], $body['data'] ?? []);
    }

    #[Test]
    public function blog_filter_matches_articles_in_any_blog_category(): void
    {
        $visible = $this->visibleIds(['blog' => true]);

        $this->assertContains(self::ARTICLE_IN_FIRST_CATEGORY, $visible);
        $this->assertContains(self::ARTICLE_IN_SECOND_CATEGORY, $visible);
        $this->assertNotContains(self::REGULAR_DISCUSSION, $visible);
    }

    #[Test]
    public function negated_blog_filter_matches_only_discussions_with_no_blog_tag(): void
    {
        $visible = $this->visibleIds(['-blog' => true]);

        $this->assertNotContains(self::ARTICLE_IN_FIRST_CATEGORY, $visible);
        $this->assertNotContains(self::ARTICLE_IN_SECOND_CATEGORY, $visible);
        $this->assertContains(self::REGULAR_DISCUSSION, $visible);
    }
}
