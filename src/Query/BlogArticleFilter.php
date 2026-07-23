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

use Flarum\Search\Database\DatabaseSearchState;
use Flarum\Search\Filter\FilterInterface;
use Flarum\Search\SearchState;
use Flarum\Settings\SettingsRepositoryInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\Builder as QueryBuilder;

/**
 * @implements FilterInterface<DatabaseSearchState>
 */
class BlogArticleFilter implements FilterInterface
{
    public function __construct(protected SettingsRepositoryInterface $settings)
    {
    }

    public function filter(SearchState $state, array|string $value, bool $negate): void
    {
        $tagsArray = explode('|', $this->settings->get('blog_tags', ''));

        $state->getQuery()->where(function (Builder $query) use ($tagsArray, $negate) {
            foreach ($tagsArray as $tagId) {
                $subquery = function (QueryBuilder $query) use ($tagId) {
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
    public function getFilterKey(): string
    {
        return 'blog';
    }
}
