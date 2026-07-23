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
 * Dispatched after an article's blog meta content has been changed. Pure
 * review-state transitions are covered by {@see ArticleApproved} instead.
 */
class BlogMetaUpdated
{
    /**
     * @param string[] $changed the model attributes that were changed
     */
    public function __construct(
        public BlogMeta $blogMeta,
        public array $changed,
        public ?User $actor = null
    ) {
    }
}
