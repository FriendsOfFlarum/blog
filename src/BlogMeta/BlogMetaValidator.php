<?php

namespace FoF\Blog\BlogMeta;

use Flarum\Foundation\AbstractValidator;

class BlogMetaValidator extends AbstractValidator
{
    /**
     * {@inheritdoc}
     */
    protected $rules = [
        'featured_image' => ['string', 'nullable'],
        'summary' => ['string', 'nullable'],
        'is_featured' => ['boolean'],
        'is_sized' => ['boolean'],
        'is_pending_review' => ['boolean']
    ];
}