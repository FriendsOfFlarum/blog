<?php

/*
 * This file is part of fof/seo.
 *
 * Copyright (c) FriendsOfFlarum.
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace FoF\Blog\Tests\integration;

use Flarum\Testing\integration\TestCase;

/**
 * Base class for blog integration tests. Boots the extension and provides
 * shared setup for tests that exercise the blog's API and forum routes.
 */
abstract class ForumTestCase extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->extension('fof-blog');
    }
}
