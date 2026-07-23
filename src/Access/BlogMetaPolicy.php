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
     * Both writers and approvers may hit the update endpoint — which of the
     * fields they may actually write is governed by `edit`/`approve`.
     */
    public function update(User $actor, BlogMeta $blogMeta): ?string
    {
        if ($actor->hasPermission('blog.writeArticles') || $actor->hasPermission('blog.canApprovePosts')) {
            return $this->allow();
        }

        return null;
    }

    /**
     * Editing an article's meta content is reserved for writers.
     */
    public function edit(User $actor, BlogMeta $blogMeta): ?string
    {
        if ($actor->hasPermission('blog.writeArticles')) {
            return $this->allow();
        }

        return null;
    }

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
