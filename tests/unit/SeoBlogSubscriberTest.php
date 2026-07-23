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
use FoF\Blog\Subscribers\SeoBlogSubscriber;
use PHPUnit\Framework\Attributes\Test;

class SeoBlogSubscriberTest extends TestCase
{
    /**
     * New writes must stamp the migrated `fof-blog` Open Graph image source.
     *
     */
    #[Test]
    public function og_image_source_is_the_migrated_identifier(): void
    {
        $this->assertSame('fof-blog', SeoBlogSubscriber::OG_IMAGE_SOURCE);
    }

    /**
     * The pre-migration identifier must stay recognised so images stored by
     * v17development/flarum-blog remain owned by this extension.
     *
     */
    #[Test]
    public function legacy_og_image_source_is_preserved_for_back_compat(): void
    {
        $this->assertSame('v17development-flarum-blog', SeoBlogSubscriber::LEGACY_OG_IMAGE_SOURCE);
    }
}
