<?php

/*
 * This file is part of fof/blog.
 *
 * Copyright (c) FriendsOfFlarum.
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace FoF\Blog\Api\Serializer;

use Flarum\Api\Serializer\AbstractSerializer;
use Flarum\Api\Serializer\DiscussionSerializer;
use FoF\Blog\BlogMeta\BlogMeta;
use Tobscure\JsonApi\Relationship;

class BlogMetaSerializer extends AbstractSerializer
{
    /**
     * {@inheritdoc}
     */
    protected $type = 'blogMeta';

    /**
     * {@inheritdoc}
     *
     * @param BlogMeta $meta
     *
     * @return array<string, mixed>
     */
    protected function getDefaultAttributes($meta): array
    {
        return [
            'featuredImage'     => $meta->featured_image,
            'summary'           => $meta->summary,
            'isFeatured'        => (bool) $meta->is_featured,
            'isSized'           => (bool) $meta->is_sized,
            'isPendingReview'   => (bool) $meta->is_pending_review,
        ];
    }

    /**
     * @param BlogMeta $meta
     */
    protected function discussion($meta): Relationship
    {
        return $this->hasOne($meta, DiscussionSerializer::class);
    }
}
