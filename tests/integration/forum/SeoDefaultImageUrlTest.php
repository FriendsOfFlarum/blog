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
use Flarum\Extend;
use Flarum\Foundation\Paths;
use Flarum\Http\UrlGenerator;
use Flarum\Post\Post;
use Flarum\Testing\integration\TestCase;
use Flarum\User\User;
use PHPUnit\Framework\Attributes\Test;

/**
 * The blog article SEO driver falls back to the configured default image when
 * an article has no Open Graph image of its own. That URL must be resolved from
 * the `flarum-assets` disk (so a relocated/cloud disk is honoured), not built
 * by hardcoding `<base>/assets/<path>`.
 *
 * To prove the disk is actually consulted, the test points the `flarum-assets`
 * disk at a distinct CDN URL — which a hardcoded `/assets/` path would never
 * produce.
 */
class SeoDefaultImageUrlTest extends TestCase
{
    const CDN = 'https://cdn.example.com/assets';

    public function setUp(): void
    {
        parent::setUp();

        $this->extension('flarum-tags', 'fof-seo', 'fof-blog');

        // Relocate the assets disk to a CDN so its url() differs from the
        // forum base + /assets path.
        $this->extend(
            (new Extend\Filesystem())
                ->disk('flarum-assets', function (Paths $paths, UrlGenerator $url) {
                    return [
                        'root' => "$paths->public/assets",
                        'url'  => self::CDN,
                    ];
                })
        );

        $now = Carbon::parse('2025-01-01 00:00:00');

        $this->prepareDatabase([
            User::class => [
                ['id' => 1, 'username' => 'admin', 'email' => 'admin@machine.local', 'is_email_confirmed' => 1],
            ],
            Discussion::class => [
                ['id' => 1, 'title' => 'Article', 'slug' => 'article', 'user_id' => 1, 'first_post_id' => 1, 'comment_count' => 1, 'created_at' => $now, 'last_posted_at' => $now],
            ],
            Post::class => [
                ['id' => 1, 'discussion_id' => 1, 'number' => 1, 'user_id' => 1, 'type' => 'comment', 'content' => '<t><p>Body.</p></t>', 'created_at' => $now],
            ],
            'blog_meta' => [
                // No featured_image -> the article has no OG image of its own.
                ['id' => 1, 'discussion_id' => 1, 'summary' => 'A summary.', 'is_featured' => 0, 'is_sized' => 0, 'is_pending_review' => 0],
            ],
        ]);

        $this->setting('blog_default_image_path', 'blog-default-abc123.png');
    }

    protected function ogImage(string $html): ?string
    {
        if (preg_match('/<meta\s+property="og:image"\s+content="([^"]*)"/i', $html, $m) === 1) {
            return html_entity_decode($m[1], ENT_QUOTES | ENT_HTML5);
        }

        return null;
    }

    #[Test]
    public function article_default_og_image_is_resolved_from_the_assets_disk(): void
    {
        $response = $this->send($this->request('GET', '/blog/1-article'));
        $this->assertEquals(200, $response->getStatusCode());

        $ogImage = $this->ogImage((string) $response->getBody());

        // Must point at the (relocated) assets disk, not a hardcoded /assets/ path.
        $this->assertSame(self::CDN.'/blog-default-abc123.png', $ogImage);
    }
}
