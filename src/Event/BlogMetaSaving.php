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
use FoF\Blog\BlogMeta\BlogMeta;

class BlogMetaSaving
{
    public function __construct(public BlogMeta $blogMeta, public User $actor, public array $data)
    {
    }
}
