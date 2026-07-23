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

use Flarum\Tags\Tag;
use Flarum\Testing\integration\RetrievesAuthorizedUsers;
use Flarum\Testing\integration\TestCase;
use Flarum\User\User;
use PHPUnit\Framework\Attributes\Test;

/**
 * Characterizes the `isBlog` attribute {@see \FoF\Blog\Api\TagResourceFields}
 * adds to the tag resource: true for configured blog tags AND their children,
 * false for anything else. The frontend uses it to hide blog categories from
 * the tag overview.
 */
class TagIsBlogTest extends TestCase
{
    use RetrievesAuthorizedUsers;

    public function setUp(): void
    {
        parent::setUp();

        $this->extension('flarum-tags', 'fof-blog');

        $this->prepareDatabase([
            User::class => [
                $this->normalUser(),
            ],
            Tag::class => [
                ['id' => 1, 'name' => 'Blog', 'slug' => 'blog', 'position' => 0, 'is_restricted' => false, 'is_hidden' => false],
                ['id' => 2, 'name' => 'Blog category', 'slug' => 'blog-category', 'parent_id' => 1, 'is_restricted' => false, 'is_hidden' => false],
                ['id' => 3, 'name' => 'General', 'slug' => 'general', 'position' => 1, 'is_restricted' => false, 'is_hidden' => false],
            ],
        ]);

        $this->setting('blog_tags', '1');
    }

    /**
     * @return array<int, bool> tag id => isBlog
     */
    protected function isBlogByTagId(): array
    {
        $response = $this->send($this->request('GET', '/api/tags', ['authenticatedAs' => 1]));
        $this->assertEquals(200, $response->getStatusCode());

        $body = json_decode($response->getBody()->getContents(), true);

        $map = [];
        foreach ($body['data'] as $tag) {
            $map[(int) $tag['id']] = $tag['attributes']['isBlog'];
        }

        return $map;
    }

    #[Test]
    public function blog_tags_and_their_children_are_flagged_as_blog(): void
    {
        $isBlog = $this->isBlogByTagId();

        $this->assertTrue($isBlog[1], 'Configured blog tag should be isBlog');
        $this->assertTrue($isBlog[2], 'Child of a blog tag should be isBlog');
        $this->assertFalse($isBlog[3], 'Regular tag should not be isBlog');
    }
}
