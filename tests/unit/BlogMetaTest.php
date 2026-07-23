<?php

/*
 * This file is part of fof/blog.
 *
 * Copyright (c) FriendsOfFlarum.
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace FoF\Blog\Tests\unit;

use Flarum\Testing\unit\TestCase;
use FoF\Blog\BlogMeta\BlogMeta;
use PHPUnit\Framework\Attributes\Test;

class BlogMetaTest extends TestCase
{
    #[Test]
    public function build_assigns_all_attributes(): void
    {
        $meta = BlogMeta::build(5, 'cover.jpg', 'A summary.', true, false, true);

        $this->assertInstanceOf(BlogMeta::class, $meta);
        $this->assertSame(5, $meta->discussion_id);
        $this->assertSame('cover.jpg', $meta->featured_image);
        $this->assertSame('A summary.', $meta->summary);
        $this->assertTrue($meta->is_featured);
        $this->assertFalse($meta->is_sized);
        $this->assertTrue($meta->is_pending_review);
    }

    #[Test]
    public function build_allows_nullable_image_and_summary(): void
    {
        $meta = BlogMeta::build(1, null, null, false, false, false);

        $this->assertNull($meta->featured_image);
        $this->assertNull($meta->summary);
    }

    #[Test]
    public function blog_meta_uses_the_expected_table(): void
    {
        $this->assertSame('blog_meta', (new BlogMeta())->getTable());
    }
}
