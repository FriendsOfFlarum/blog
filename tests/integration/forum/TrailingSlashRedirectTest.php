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
use PHPUnit\Framework\Attributes\Test;

/**
 * A trailing slash on a blog URL (e.g. `/blog/`) does not match the registered
 * `/blog` frontend route, so a direct load or refresh 404s. Blog URLs with a
 * trailing slash should redirect to their canonical, slash-less form.
 */
class TrailingSlashRedirectTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        $this->extension('flarum-tags', 'fof-blog');
    }

    #[Test]
    public function blog_overview_with_trailing_slash_redirects_to_canonical(): void
    {
        $response = $this->send($this->request('GET', '/blog/'));

        $this->assertSame(301, $response->getStatusCode(), 'Expected /blog/ to 301 redirect');
        $this->assertSame('/blog', $this->redirectPath($response));
    }

    #[Test]
    public function blog_overview_without_trailing_slash_is_served_normally(): void
    {
        $response = $this->send($this->request('GET', '/blog'));

        $this->assertSame(200, $response->getStatusCode());
        $this->assertStringContainsString('text/html', $response->getHeaderLine('Content-Type'));
    }

    #[Test]
    public function nested_blog_path_with_trailing_slash_redirects(): void
    {
        $response = $this->send($this->request('GET', '/blog/compose/'));

        $this->assertSame(301, $response->getStatusCode(), 'Expected /blog/compose/ to 301 redirect');
        $this->assertSame('/blog/compose', $this->redirectPath($response));
    }

    #[Test]
    public function the_forum_root_is_not_affected(): void
    {
        $response = $this->send($this->request('GET', '/'));

        $this->assertSame(200, $response->getStatusCode());
    }

    private function redirectPath(\Psr\Http\Message\ResponseInterface $response): string
    {
        $location = $response->getHeaderLine('Location');

        return parse_url($location, PHP_URL_PATH) ?? $location;
    }
}
