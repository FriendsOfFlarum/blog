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
 * Characterizes the `blog_filter_discussion_list` setting: when enabled, blog
 * articles are hidden from the regular discussion list — unless the request
 * explicitly filters for blog articles.
 */
class DiscussionListBlogFilterTest extends TestCase
{
    use RetrievesAuthorizedUsers;

    const BLOG_ARTICLE = 1;
    const REGULAR_DISCUSSION = 2;

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
                ['id' => 2, 'name' => 'General', 'slug' => 'general', 'position' => 1, 'is_restricted' => false, 'is_hidden' => false],
            ],
            Discussion::class => [
                ['id' => self::BLOG_ARTICLE, 'title' => 'Article', 'slug' => 'article', 'user_id' => 1, 'first_post_id' => 1, 'comment_count' => 1, 'created_at' => $now, 'last_posted_at' => $now],
                ['id' => self::REGULAR_DISCUSSION, 'title' => 'Discussion', 'slug' => 'discussion', 'user_id' => 1, 'first_post_id' => 2, 'comment_count' => 1, 'created_at' => $now, 'last_posted_at' => $now],
            ],
            Post::class => [
                ['id' => 1, 'discussion_id' => self::BLOG_ARTICLE, 'number' => 1, 'user_id' => 1, 'type' => 'comment', 'content' => '<t><p>Article body.</p></t>', 'created_at' => $now],
                ['id' => 2, 'discussion_id' => self::REGULAR_DISCUSSION, 'number' => 1, 'user_id' => 1, 'type' => 'comment', 'content' => '<t><p>Discussion body.</p></t>', 'created_at' => $now],
            ],
            'discussion_tag' => [
                ['discussion_id' => self::BLOG_ARTICLE, 'tag_id' => 1],
                ['discussion_id' => self::REGULAR_DISCUSSION, 'tag_id' => 2],
            ],
            'blog_meta' => [
                ['id' => 1, 'discussion_id' => self::BLOG_ARTICLE, 'is_pending_review' => 0],
            ],
        ]);

        $this->setting('blog_tags', '1');
    }

    /**
     * @param array<string, mixed> $filter
     *
     * @return int[] visible discussion ids
     */
    protected function visibleIds(array $filter = []): array
    {
        $request = $this->request('GET', '/api/discussions', ['authenticatedAs' => 1]);

        if ($filter !== []) {
            $request = $request->withQueryParams(['filter' => $filter]);
        }

        $response = $this->send($request);
        $this->assertEquals(200, $response->getStatusCode());

        $body = json_decode($response->getBody()->getContents(), true);

        return array_map(fn ($d) => (int) $d['id'], $body['data'] ?? []);
    }

    #[Test]
    public function blog_articles_show_in_the_discussion_list_by_default(): void
    {
        $visible = $this->visibleIds();

        $this->assertContains(self::BLOG_ARTICLE, $visible);
        $this->assertContains(self::REGULAR_DISCUSSION, $visible);
    }

    #[Test]
    public function blog_articles_are_hidden_from_the_discussion_list_when_the_setting_is_enabled(): void
    {
        $this->setting('blog_filter_discussion_list', '1');

        $visible = $this->visibleIds();

        $this->assertNotContains(self::BLOG_ARTICLE, $visible);
        $this->assertContains(self::REGULAR_DISCUSSION, $visible);
    }

    #[Test]
    public function the_blog_filter_still_returns_articles_when_hiding_is_enabled(): void
    {
        $this->setting('blog_filter_discussion_list', '1');

        $visible = $this->visibleIds(['blog' => true]);

        $this->assertContains(self::BLOG_ARTICLE, $visible);
        $this->assertNotContains(self::REGULAR_DISCUSSION, $visible);
    }

    /**
     * @param string $uri
     *
     * @return string[] resource types present in the `included` payload
     */
    protected function includedTypes(string $uri): array
    {
        $response = $this->send($this->request('GET', $uri, ['authenticatedAs' => 1]));
        $this->assertEquals(200, $response->getStatusCode());

        $body = json_decode($response->getBody()->getContents(), true);

        return array_values(array_unique(array_map(fn ($resource) => $resource['type'], $body['included'] ?? [])));
    }

    #[Test]
    public function the_discussion_list_does_not_carry_blog_data_by_default(): void
    {
        // Forum-wide discussion lists shouldn't pay for blog includes — the
        // blog overview requests them explicitly instead.
        $types = $this->includedTypes('/api/discussions');

        $this->assertNotContains('blogMeta', $types);
        $this->assertNotContains('posts', $types, 'firstPost should not be included on the index');
    }

    #[Test]
    public function a_single_discussion_still_includes_blog_meta_by_default(): void
    {
        $types = $this->includedTypes('/api/discussions/'.self::BLOG_ARTICLE);

        $this->assertContains('blogMeta', $types);
    }
}
