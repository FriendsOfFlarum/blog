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

class ForumResourceFields
{
    /**
     * @var Cloud
     */
    protected $assetsDir;

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
            Schema\Arr::make('blogTags')
                ->get(fn () => explode('|', (string) $this->settings->get('blog_tags', ''))),
            Schema\Str::make('blogRedirectsEnabled')
                ->get(fn () => (string) $this->settings->get('blog_redirects_enabled', 'both')),
            Schema\Boolean::make('blogCommentsEnabled')
                ->get(fn () => (bool) $this->settings->get('blog_allow_comments', true)),
            Schema\Boolean::make('blogHideTags')
                ->get(fn () => (bool) $this->settings->get('blog_hide_tags', true)),
            Schema\Str::make('blogDefaultImage')
                ->nullable()
                ->get(fn () => $this->settings->get('blog_default_image_path', null)),
            // Resolve the full URL from the assets disk so a relocated/cloud disk
            // (e.g. S3) is reflected, rather than assuming a local `/assets/` path.
            Schema\Str::make('blogDefaultImageUrl')
                ->nullable()
                ->get(function () {
                    $blogDefaultImage = $this->settings->get('blog_default_image_path', null);

                    return $blogDefaultImage ? $this->assetsDir->url($blogDefaultImage) : null;
                }),
            Schema\Boolean::make('canWriteBlogPosts')
                ->get(fn ($forum, Context $context) => $context->getActor()->can('blog.writeArticles')),
            Schema\Boolean::make('canApproveBlogPosts')
                ->get(fn ($forum, Context $context) => $context->getActor()->can('blog.canApprovePosts')),
            Schema\Boolean::make('blogCategoryHierarchy')
                ->get(fn () => (bool) $this->settings->get('blog_category_hierarchy', true)),
            Schema\Boolean::make('blogAddSidebarNav')
                ->get(fn () => (bool) $this->settings->get('blog_add_sidebar_nav', true)),
            Schema\Integer::make('blogFeaturedCount')
                ->get(fn () => (int) $this->settings->get('blog_featured_count', 3)),
            Schema\Boolean::make('blogAddHero')
                ->get(fn () => (bool) $this->settings->get('blog_add_hero', true)),
        ];
    }
}
