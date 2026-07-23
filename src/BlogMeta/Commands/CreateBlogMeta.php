<?php

/*
 * This file is part of fof/blog.
 *
 * Copyright (c) FriendsOfFlarum.
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace FoF\Blog\BlogMeta\Commands;

use Flarum\User\User;

class CreateBlogMeta
{
    public function __construct(public User $actor, public array $data)
    {
    }
}
