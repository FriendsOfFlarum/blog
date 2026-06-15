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
     * @var User
     */
    public $actor;

    /**
     * @var mixed
     */
    public $id;

    /**
     * @var array<string, mixed>
     */
    public $data;

    /**
     * @param mixed                $id
     * @param array<string, mixed> $data
     */
    public function __construct(User $actor, $id, array $data)
    {
        $this->actor = $actor;
        $this->id = $id;
        $this->data = $data;
    }
}
