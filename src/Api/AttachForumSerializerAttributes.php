<?php

/*
 * This file is part of fof/seo.
 *
 * Copyright (c) FriendsOfFlarum.
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace FoF\Blog\Api;

use Flarum\Api\Serializer\ForumSerializer;
use Flarum\Settings\SettingsRepositoryInterface;
use Illuminate\Contracts\Filesystem\Cloud;
use Illuminate\Contracts\Filesystem\Factory;

class AttachForumSerializerAttributes
{
    /**
     * @var SettingsRepositoryInterface
     */
    protected $settings;

    /**
     * @var Cloud
     */
    protected $assetsDir;

    public function __construct(SettingsRepositoryInterface $settings, Factory $filesystemFactory)
    {
        $this->settings = $settings;

        /** @var Cloud $assetsDir */
        $assetsDir = $filesystemFactory->disk('flarum-assets');
        $this->assetsDir = $assetsDir;
    }

    /**
     * @param ForumSerializer      $serializer
     * @param mixed                $model
     * @param array<string, mixed> $attributes
     *
     * @return array<string, mixed>
     */
    public function __invoke(ForumSerializer $serializer, $model, array $attributes): array
    {
        // Populate forum settings
        $attributes['blogTags'] = explode('|', $this->settings->get('blog_tags', ''));
        $attributes['blogRedirectsEnabled'] = $this->settings->get('blog_redirects_enabled', 'both');
        $attributes['blogCommentsEnabled'] = $this->settings->get('blog_allow_comments', true);
        $attributes['blogHideTags'] = $this->settings->get('blog_hide_tags', true);
        $blogDefaultImage = $this->settings->get('blog_default_image_path', null);
        $attributes['blogDefaultImage'] = $blogDefaultImage;
        // Resolve the full URL from the assets disk so a relocated/cloud disk
        // (e.g. S3) is reflected, rather than assuming a local `/assets/` path.
        $attributes['blogDefaultImageUrl'] = $blogDefaultImage ? $this->assetsDir->url($blogDefaultImage) : null;
        $attributes['canWriteBlogPosts'] = $serializer->getActor()->can('blog.writeArticles');
        $attributes['blogCategoryHierarchy'] = $this->settings->get('blog_category_hierarchy', true);
        $attributes['blogAddSidebarNav'] = $this->settings->get('blog_add_sidebar_nav', true);
        $attributes['canApproveBlogPosts'] = $serializer->getActor()->can('blog.canApprovePosts');
        $attributes['blogFeaturedCount'] = $this->settings->get('blog_featured_count', 3);
        $attributes['blogAddHero'] = $this->settings->get('blog_add_hero', true);

        return $attributes;
    }
}
