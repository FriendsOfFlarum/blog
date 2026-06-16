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
use Flarum\Testing\integration\TestCase;

/**
 * The blog overview SEO driver must set a canonical URL pointing at the blog,
 * otherwise (e.g. when the blog overview is the forum home page) the canonical
 * falls back to core's default (`/all`). Issue #105.
 */
class SeoOverviewCanonicalTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        $this->extension('flarum-tags', 'fof-seo', 'fof-blog');

        $now = Carbon::parse('2025-01-01 00:00:00');

        $this->prepareDatabase([
            'tags' => [
                ['id' => 1, 'name' => 'Blog', 'slug' => 'blog', 'position' => 0, 'is_restricted' => false, 'is_hidden' => false],
            ],
        ]);

        $this->setting('blog_tags', '1');
    }

    protected function canonical(string $html): ?string
    {
        if (preg_match('/<link\s+rel="canonical"\s+href="([^"]*)"/i', $html, $m) === 1) {
            return html_entity_decode($m[1], ENT_QUOTES | ENT_HTML5);
        }

        return null;
    }

    /**
     * @test
     */
    public function blog_overview_sets_a_canonical_pointing_at_the_blog(): void
    {
        $html = (string) $this->send($this->request('GET', '/blog'))->getBody();

        $this->assertSame('http://localhost/blog', $this->canonical($html));
    }

    /**
     * @test
     */
    public function blog_category_sets_a_canonical_pointing_at_the_category(): void
    {
        $html = (string) $this->send($this->request('GET', '/blog/category/blog'))->getBody();

        $this->assertSame('http://localhost/blog/category/blog', $this->canonical($html));
    }
}
