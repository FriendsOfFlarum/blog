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

use Flarum\Api\Context;
use Flarum\Api\Schema;
use Flarum\Settings\SettingsRepositoryInterface;
use Illuminate\Contracts\Filesystem\Cloud;
use Illuminate\Contracts\Filesystem\Factory;

/**
 * Computed blog attributes on the forum resource. Plain settings are
 * serialized declaratively via `Extend\Settings` in extend.php.
 */
class ForumResourceFields
{
    protected Cloud $assetsDir;

    public function __construct(protected SettingsRepositoryInterface $settings, Factory $filesystemFactory)
    {
        /** @var Cloud $assetsDir */
        $assetsDir = $filesystemFactory->disk('flarum-assets');
        $this->assetsDir = $assetsDir;
    }

    /**
     * @return array<Schema\Attribute>
     */
    public function __invoke(): array
    {
        return [
            // Resolve the full URL from the assets disk so a relocated/cloud disk
            // (e.g. S3) is reflected, rather than assuming a local `/assets/` path.
            Schema\Str::make('blogDefaultImageUrl')
                ->nullable()
                ->get(function () {
                    $blogDefaultImage = $this->settings->get('blog_default_image_path');

                    return $blogDefaultImage ? $this->assetsDir->url($blogDefaultImage) : null;
                }),
            Schema\Boolean::make('canWriteBlogPosts')
                ->get(fn ($forum, Context $context) => $context->getActor()->can('blog.writeArticles')),
            Schema\Boolean::make('canApproveBlogPosts')
                ->get(fn ($forum, Context $context) => $context->getActor()->can('blog.canApprovePosts')),
        ];
    }
}
