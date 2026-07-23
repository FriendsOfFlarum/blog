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

class UpdateBlogMeta
{
    /**
     * @param mixed                $id
     * @param array<string, mixed> $data
     */
    public function __construct(public User $actor, public $id, public array $data)
    {
    }
}
