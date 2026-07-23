<?php

/*
 * This file is part of fof/blog.
 *
 * Copyright (c) FriendsOfFlarum.
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace FoF\Blog\Tests\integration\forum;

use Carbon\Carbon;
use Flarum\Discussion\Discussion;
use Flarum\Post\Post;
use Flarum\Tags\Tag;
use Flarum\Testing\integration\RetrievesAuthorizedUsers;
use Flarum\Testing\integration\TestCase;
use Flarum\User\User;
use PHPUnit\Framework\Attributes\Test;

/**
 * Characterizes the server-rendered blog routes registered in extend.php:
 * the overview (`/blog`, `/blog/category/{slug}`) and the article page
 * (`/blog/{id}`). These controllers return a frontend Document and must serve
 * HTML without erroring for guests.
 */
class BlogRoutesTest extends TestCase
{
    use RetrievesAuthorizedUsers;

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
            ],
            Discussion::class => [
                ['id' => 1, 'title' => 'First Article', 'slug' => 'first-article', 'user_id' => 1, 'first_post_id' => 1, 'comment_count' => 1, 'created_at' => $now, 'last_posted_at' => $now],
            ],
            Post::class => [
                ['id' => 1, 'discussion_id' => 1, 'number' => 1, 'user_id' => 1, 'type' => 'comment', 'content' => '<t><p>Article body.</p></t>', 'created_at' => $now],
            ],
            'discussion_tag' => [
                ['discussion_id' => 1, 'tag_id' => 1],
            ],
            'blog_meta' => [
                ['id' => 1, 'discussion_id' => 1, 'summary' => 'A short summary.', 'is_featured' => 1, 'is_sized' => 0, 'is_pending_review' => 0],
            ],
        ]);
    }

    protected function fetchHtml(string $path): string
    {
        $response = $this->send($this->request('GET', $path));

        $this->assertEquals(200, $response->getStatusCode(), "Expected 200 from {$path}");
        $this->assertStringContainsString('text/html', $response->getHeaderLine('Content-Type'));

        return (string) $response->getBody();
    }

    #[Test]
    public function blog_overview_renders_for_a_guest(): void
    {
        $html = $this->fetchHtml('/blog');

        $this->assertNotEmpty($html);
    }

    #[Test]
    public function blog_overview_preloads_an_api_document(): void
    {
        $html = $this->fetchHtml('/blog');

        // The overview controller preloads blog discussions into the document payload.
        $this->assertStringContainsString('apiDocument', $html);
    }

    #[Test]
    public function blog_category_route_renders_for_a_guest(): void
    {
        $html = $this->fetchHtml('/blog/category/blog');

        $this->assertNotEmpty($html);
    }

    #[Test]
    public function blog_article_route_renders_for_a_guest(): void
    {
        $html = $this->fetchHtml('/blog/1-first-article');

        $this->assertNotEmpty($html);
    }
}
