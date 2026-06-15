<?php

namespace FoF\Blog\Access;

use Flarum\User\User;
use Illuminate\Database\Eloquent\Builder;

class ScopeDiscussionVisibility
{
    /**
     * @param User $actor
     * @param Builder $query
     */
    public function __invoke(User $actor, Builder $query): void
    {
        // Hide blogposts which arent published or are still pending approval
        // Writers will have access to the posts if they are still pending for review
        if(!$actor->hasPermission('blog.canApprovePosts') && !$actor->hasPermission('blog.writeArticles')) {
            $query->whereNotIn('discussions.id', function ($query) {
                return $query
                    ->select('discussion_id')
                    ->from('blog_meta')
                    ->where('is_pending_review', 1);
            });
        }
    }
}