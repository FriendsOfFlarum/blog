<?php

/*
 * This file is part of fof/blog.
 *
 * Copyright (c) FriendsOfFlarum.
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace FoF\Blog\Api;

use Flarum\Api\Schema;
use Flarum\Settings\SettingsRepositoryInterface;
use Flarum\Tags\Tag;

class TagResourceFields
{
    public function __construct(protected SettingsRepositoryInterface $settings)
    {
    }

    /**
     * @return array<Schema\Attribute>
     */
    public function __invoke(): array
    {
        return [
            Schema\Boolean::make('isBlog')
                ->get(function (Tag $tag) {
                    // Get blog tags
                    $blogTags = explode('|', (string) $this->settings->get('blog_tags', ''));

                    return in_array($tag->id, $blogTags) || ($tag->parent_id && in_array($tag->parent_id, $blogTags));
                }),
        ];
    }
}
