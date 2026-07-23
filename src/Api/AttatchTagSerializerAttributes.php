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

use Flarum\Settings\SettingsRepositoryInterface;
use Flarum\Tags\Api\Serializer\TagSerializer;
use Flarum\Tags\Tag;

class AttatchTagSerializerAttributes
{
    public function __construct(protected SettingsRepositoryInterface $settings)
    {
    }

    /**
     * @param TagSerializer        $serializer
     * @param Tag                  $model
     * @param array<string, mixed> $attributes
     *
     * @return array<string, mixed>
     */
    public function __invoke(TagSerializer $serializer, Tag $model, array $attributes): array
    {
        // Get blog tags
        $blogTags = explode('|', $this->settings->get('blog_tags', ''));

        // Add isBlog attribute
        $attributes['isBlog'] = (bool) in_array($model->id, $blogTags) || ($model->parent_id && in_array($model->parent->id, $blogTags));

        return $attributes;
    }
}
