<?php

/*
 * This file is part of fof/seo.
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
    /**
     * @var SettingsRepositoryInterface
     */
    protected $settings;

    /**
     * @param SettingsRepositoryInterface $settings
     */
    public function __construct(SettingsRepositoryInterface $settings)
    {
        // Get Flarum settings
        $this->settings = $settings;
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
