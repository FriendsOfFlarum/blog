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

use Flarum\User\User;
use Illuminate\Database\Eloquent\Builder;

class ScopeDiscussionVisibility
{
    /**
     * @param User    $actor
     * @param Builder $query
     */
    public function __invoke(User $actor, Builder $query): void
    {
        // Users who can approve posts see everything, pending or not.
        if ($actor->hasPermission('blog.canApprovePosts')) {
            return;
        }

        // Everyone else: hide articles still pending review — except an author's
        // own pending articles, so they can still see what they submitted.
        $query->whereNotIn('discussions.id', function ($query) use ($actor) {
            $query
                ->select('bm.discussion_id')
                ->from('blog_meta as bm')
                ->join('discussions as bd', 'bd.id', '=', 'bm.discussion_id')
                ->where('bm.is_pending_review', 1);

            if ($actor->exists) {
                $query->where('bd.user_id', '!=', $actor->id);
            }
        });
    }
}
