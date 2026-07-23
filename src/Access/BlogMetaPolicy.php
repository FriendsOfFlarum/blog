<?php

/*
 * This file is part of fof/blog.
 *
 * Copyright (c) FriendsOfFlarum.
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace FoF\Blog\Access;

use Flarum\User\Access\AbstractPolicy;
use Flarum\User\User;
use FoF\Blog\BlogMeta;

class BlogMetaPolicy extends AbstractPolicy
{
    /**
     * Publishing a pending article is reserved for users who can approve posts.
     */
    public function approve(User $actor, BlogMeta $blogMeta): ?string
    {
        if ($actor->hasPermission('blog.canApprovePosts')) {
            return $this->allow();
        }

        return null;
    }
}
