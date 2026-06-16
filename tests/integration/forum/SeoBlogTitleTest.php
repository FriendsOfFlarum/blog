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

use Flarum\Testing\integration\TestCase;

/**
 * The blog SEO page drivers must use the migrated `fof-blog.*` translation keys.
 * Previously they referenced the old `v17development-flarum-blog.forum.blog`
 * key, which no longer exists in the locale, so the literal key leaked into the
 * rendered <title> / og:title / twitter:title.
 */
class SeoBlogTitleTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        $this->extension('flarum-tags', 'fof-seo', 'fof-blog');
    }

    protected function fetchHtml(string $path): string
    {
        $response = $this->send($this->request('GET', $path));

        $this->assertEquals(200, $response->getStatusCode(), "Expected 200 from {$path}");

        return (string) $response->getBody();
    }

    /**
     * @test
     */
    public function blog_overview_title_does_not_leak_the_raw_translation_key(): void
    {
        $html = $this->fetchHtml('/blog');

        $this->assertStringNotContainsString('v17development-flarum-blog', $html);
    }

    /**
     * Translations are not loaded in the test environment, so the raw key is
     * rendered verbatim. We assert the title references the migrated `fof-blog`
     * key rather than the dead `v17development-flarum-blog` one.
     *
     * @test
     */
    public function blog_overview_title_uses_the_migrated_translation_key(): void
    {
        $html = $this->fetchHtml('/blog');

        if (preg_match('/<title[^>]*>(.*?)<\/title>/is', $html, $m) === 1) {
            $this->assertStringContainsString('fof-blog.forum.blog', $m[1]);
        } else {
            $this->fail('No <title> tag found in the blog overview HTML');
        }
    }
}
