<?php

/*
 * This file is part of fof/blog.
 *
 * Copyright (c) FriendsOfFlarum.
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace FoF\Blog\Search\Filter;

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
        // Drop empty/non-numeric entries: an unset `blog_tags` setting explodes
        // to [''], and binding '' against an integer column is a fatal error on
        // PostgreSQL (MySQL/SQLite silently coerce it to 0).
        $tagsArray = array_filter(explode('|', (string) $this->settings->get('blog_tags', '')), 'is_numeric');

        $state->getQuery()->where(function (Builder $query) use ($tagsArray, $negate) {
            // No blog tags configured: the blog filter matches nothing, and
            // its negation matches everything.
            if (empty($tagsArray)) {
                if (!$negate) {
                    $query->whereRaw('1 = 0');
                }

                return;
            }

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
