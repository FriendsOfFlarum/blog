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

use Flarum\Discussion\Search\FulltextFilter;
use Flarum\Search\Database\DatabaseSearchState;
use Flarum\Search\SearchCriteria;
use Flarum\Settings\SettingsRepositoryInterface;

class FilterDiscussionsForBlogPosts
{
    /**
     * FilterDiscussionsForBlogPosts constructor.
     */
    public function __construct(protected SettingsRepositoryInterface $settings)
    {
    }

    public function __invoke(DatabaseSearchState $filter, SearchCriteria $queryCriteria): void
    {
        // Do we need to filter?
        if (filter_var($this->settings->get('blog_filter_discussion_list'), FILTER_VALIDATE_BOOLEAN) === false) {
            return;
        }

        $activeFilters = $filter->getActiveFilters();
        $hideBlogPosts = true;

        // Loop through the active filters
        foreach ($activeFilters as $activeFilter) {
            if ($activeFilter instanceof BlogArticleFilter) {
                $hideBlogPosts = false;
            }
            if ($activeFilter instanceof FulltextFilter) {
                $hideBlogPosts = false;
            }
        }

        // Filter discussions from discussion list
        if ($hideBlogPosts) {
            // Drop empty/non-numeric entries: an unset `blog_tags` setting
            // explodes to [''], and binding '' against an integer column is a
            // fatal error on PostgreSQL.
            $tagsArray = array_filter(explode('|', (string) $this->settings->get('blog_tags', '')), 'is_numeric');

            if (empty($tagsArray)) {
                return;
            }

            $filter
                ->getQuery()
                ->where(function ($query) use ($tagsArray) {
                    foreach ($tagsArray as $tagId) {
                        $query->whereNotIn('discussions.id', function ($query) use ($tagId) {
                            $query->select('discussion_id')
                                ->from('discussion_tag')
                                ->where('tag_id', $tagId);
                        });
                    }
                });
        }
    }
}
