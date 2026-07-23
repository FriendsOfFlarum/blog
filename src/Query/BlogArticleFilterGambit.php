<?php

/*
 * This file is part of fof/blog.
 *
 * Copyright (c) FriendsOfFlarum.
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace FoF\Blog\Query;

use Flarum\Search\AbstractRegexGambit;
use Flarum\Search\SearchState;
use Flarum\Settings\SettingsRepositoryInterface;
use Illuminate\Database\Query\Builder;

class BlogArticleFilterGambit extends AbstractRegexGambit
{
    public function __construct(protected SettingsRepositoryInterface $settings)
    {
    }

    protected function getGambitPattern(): string
    {
        return 'is:blog';
    }

    protected function conditions(SearchState $search, array $matches, $negate)
    {
        $tagsArray = explode('|', $this->settings->get('blog_tags', ''));

        $search->getQuery()->where(function (Builder $query) use ($tagsArray, $negate) {
            foreach ($tagsArray as $tagId) {
                $subquery = function (Builder $query) use ($tagId) {
                    $query->select('discussion_id')
                        ->from('discussion_tag')
                        ->where('tag_id', $tagId);
                };

                if ($negate) {
                    $query->orWhereNotIn('discussions.id', $subquery);
                } else {
                    $query->orWhereIn('discussions.id', $subquery);
                }
            }
        });
    }
}
