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

/**
 * Smoke test: the extension enables and the forum still serves a 200.
 * A real coverage suite is added incrementally as part of the FoF migration.
 */
class ExtensionBootsTest extends ForumTestCase
{
    /**
     * @test
     */
    public function forum_loads_with_extension_enabled(): void
    {
        $response = $this->send($this->request('GET', '/'));

        $this->assertSame(200, $response->getStatusCode());
    }
}
