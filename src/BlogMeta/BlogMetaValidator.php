<?php

/*
 * This file is part of fof/blog.
 *
 * Copyright (c) FriendsOfFlarum.
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace FoF\Blog\BlogMeta;

use Flarum\Foundation\AbstractValidator;

class BlogMetaValidator extends AbstractValidator
{
    /**
     * {@inheritdoc}
     */
    protected $rules = [
        'featured_image'    => ['string', 'nullable'],
        'summary'           => ['string', 'nullable'],
        'is_featured'       => ['boolean'],
        'is_sized'          => ['boolean'],
        'is_pending_review' => ['boolean'],
    ];
}
