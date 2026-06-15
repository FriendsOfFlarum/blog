<?php

/*
 * This file is part of fof/seo.
 *
 * Copyright (c) FriendsOfFlarum.
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace FoF\Blog\Event;

use FoF\Blog\BlogMeta\BlogMeta;

class BlogMetaCreated
{
    /**
     * @var BlogMeta
     */
    public $blogMeta;

    public function __construct(BlogMeta $blogMeta)
    {
        $this->blogMeta = $blogMeta;
    }
}
