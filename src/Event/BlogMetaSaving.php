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

use Flarum\User\User;
use FoF\Blog\BlogMeta\BlogMeta;

class BlogMetaSaving
{
    /**
     * @var BlogMeta
     */
    public $blogMeta;

    /**
     * @var User
     */
    public $actor;

    /**
     * @var array
     */
    public $data;

    public function __construct(BlogMeta $blogMeta, User $actor, array $data)
    {
        $this->blogMeta = $blogMeta;
        $this->actor = $actor;
        $this->data = $data;
    }
}
