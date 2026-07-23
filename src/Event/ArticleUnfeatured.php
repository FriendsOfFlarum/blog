<?php

/*
 * This file is part of fof/blog.
 *
 * Copyright (c) FriendsOfFlarum.
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace FoF\Blog\Event;

use Flarum\User\User;
use FoF\Blog\BlogMeta;

/**
 * Dispatched after an article has been unfeatured from the blog overview.
 */
class ArticleUnfeatured
{
    public function __construct(
        public BlogMeta $blogMeta,
        public ?User $actor = null
    ) {
    }
}
