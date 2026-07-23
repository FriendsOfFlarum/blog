<?php

/*
 * This file is part of fof/blog.
 *
 * Copyright (c) FriendsOfFlarum.
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace FoF\Blog;

use Flarum\Api\Endpoint;
use Flarum\Api\Resource;
use Flarum\Api\Schema;
use Flarum\Discussion\Discussion;
use Flarum\Discussion\Event\Saving;
use Flarum\Discussion\Search\DiscussionSearcher;
use Flarum\Extend;
use Flarum\Http\Middleware\ResolveRoute;
use Flarum\Tags\Api\Resource\TagResource;
use FoF\Blog\Access\ScopeDiscussionVisibility;
use FoF\Blog\Api\Controller\DeleteDefaultBlogImageController;
use FoF\Blog\Api\Controller\UploadDefaultBlogImageController;
use FoF\Blog\Api\ForumResourceFields;
use FoF\Blog\Api\Resource\BlogMetaResource;
use FoF\Blog\Api\TagResourceFields;
use FoF\Blog\Listener\CreateBlogMetaOnDiscussionCreate;
use FoF\Blog\Listener\UpdateSeoMeta;
use FoF\Blog\Middleware\RedirectTrailingSlash;
use FoF\Blog\Search\Filter\BlogArticleFilter;
use FoF\Blog\Search\HideBlogPostsFromAllDiscussionsPage;
use FoF\Blog\SeoPage\SeoBlogArticleMeta;
use FoF\Blog\SeoPage\SeoBlogOverviewMeta;

return [
    // Core resolves trailing-slash URLs internally, but we still want a single
    // canonical URL per blog page, so redirect `/blog/...` to its slash-less form.
    (new Extend\Middleware('forum'))
        ->insertBefore(ResolveRoute::class, RedirectTrailingSlash::class),

    (new Extend\Frontend('forum'))
        ->js(__DIR__.'/js/dist/forum.js')
        ->jsDirectory(__DIR__.'/js/dist/forum')
        ->css(__DIR__.'/less/Forum.less')
        ->route('/blog', 'blog.overview', Content\Overview::class)
        ->route('/blog/compose', 'blog.compose', Content\Compose::class)
        ->route('/blog/category/{category}', 'blog.category', Content\Overview::class)
        ->route('/blog/{id:[\d\S]+(?:-[^/]*)?}', 'blog.post', Content\Item::class)
    // Shall we add RSS?
    // ->get('/blog/rss.xml', 'blog.rss.xml', RSS::class)
    ,
    (new Extend\Frontend('admin'))
        ->js(__DIR__.'/js/dist/admin.js')
        ->jsDirectory(__DIR__.'/js/dist/admin')
        ->css(__DIR__.'/less/Admin.less'),

    (new Extend\Routes('api'))
        ->post('/blog_default_image', 'blog.default_image.upload', UploadDefaultBlogImageController::class)
        ->delete('/blog_default_image', 'blog.default_image.delete', DeleteDefaultBlogImageController::class),

    new Extend\Locales(__DIR__.'/locale'),

    (new Extend\Model(Discussion::class))
        ->hasOne('blogMeta', BlogMeta::class, 'discussion_id'),

    (new Extend\ModelVisibility(Discussion::class))
        ->scope(ScopeDiscussionVisibility::class),

    (new Extend\Policy())
        ->modelPolicy(BlogMeta::class, Access\BlogMetaPolicy::class),

    new Extend\ApiResource(BlogMetaResource::class),

    (new Extend\ApiResource(Resource\DiscussionResource::class))
        ->fields(fn () => [
            // Write-only input sent by the blog composer when creating an
            // article. The value is consumed from the discussion `Saving`
            // event's raw data by `CreateBlogMetaOnDiscussionCreate` — this
            // field only exists so the payload passes field validation. It is
            // never serialized; responses carry the `blogMeta` relationship
            // below (fields are keyed by name, so the attribute cannot share it).
            Schema\Arr::make('newBlogMeta')
                ->writableOnCreate()
                ->nullable()
                ->visible(false)
                ->set(fn () => null),
            Schema\Relationship\ToOne::make('blogMeta')
                ->type('blogMeta')
                ->includable(),
        ])
        // Core's Show/Create endpoints already include `user` and `firstPost`
        // by default, so only `blogMeta` is added — and NOT on Index: forum-wide
        // discussion lists shouldn't pay for blog data. The blog overview
        // requests its own includes explicitly (see BlogListState).
        ->endpoint(
            [Endpoint\Show::class, Endpoint\Create::class, Endpoint\Update::class],
            fn (Endpoint\Show|Endpoint\Create|Endpoint\Update $endpoint) => $endpoint
                ->addDefaultInclude(['blogMeta'])
        ),

    (new Extend\Settings())
        ->default('blog_tags', '')
        ->default('blog_redirects_enabled', 'both')
        ->default('blog_allow_comments', true)
        ->default('blog_hide_tags', true)
        ->default('blog_category_hierarchy', true)
        ->default('blog_add_sidebar_nav', true)
        ->default('blog_featured_count', 3)
        ->default('blog_add_hero', true)
        ->default('blog_requires_review', false)
        ->default('blog_filter_discussion_list', false)
        ->serializeToForum('blogTags', 'blog_tags', fn ($value) => explode('|', (string) $value))
        ->serializeToForum('blogRedirectsEnabled', 'blog_redirects_enabled', 'strval')
        ->serializeToForum('blogCommentsEnabled', 'blog_allow_comments', 'boolval')
        ->serializeToForum('blogHideTags', 'blog_hide_tags', 'boolval')
        ->serializeToForum('blogDefaultImage', 'blog_default_image_path')
        ->serializeToForum('blogCategoryHierarchy', 'blog_category_hierarchy', 'boolval')
        ->serializeToForum('blogAddSidebarNav', 'blog_add_sidebar_nav', 'boolval')
        ->serializeToForum('blogFeaturedCount', 'blog_featured_count', 'intval')
        ->serializeToForum('blogAddHero', 'blog_add_hero', 'boolval'),

    // Computed forum attributes that cannot be plain setting serializations.
    (new Extend\ApiResource(Resource\ForumResource::class))
        ->fields(ForumResourceFields::class),

    (new Extend\ApiResource(TagResource::class))
        ->fields(TagResourceFields::class),

    (new Extend\Event())
        ->listen(Saving::class, CreateBlogMetaOnDiscussionCreate::class),

    (new Extend\Conditional())
        ->whenExtensionEnabled('fof-seo', fn () => [
            (new \FoF\Seo\Extend\SEO())
                ->addExtender('blog_category', SeoBlogOverviewMeta::class)
                ->addExtender('blog_article', SeoBlogArticleMeta::class),

            (new Extend\Event())
                ->subscribe(UpdateSeoMeta::class),
        ])
        ->whenExtensionEnabled('flarum-audit', fn () => [
            (new \Flarum\Audit\Extend\Audit())
                ->group('fof-blog')
                ->listen(Event\BlogMetaCreated::class, 'article.created', fn (Event\BlogMetaCreated $event) => [
                    'discussion_id'  => $event->blogMeta->discussion_id,
                    'pending_review' => (bool) $event->blogMeta->is_pending_review,
                ])
                ->listen(Event\ArticleApproved::class, 'article.approved', fn (Event\ArticleApproved $event) => [
                    'discussion_id' => $event->blogMeta->discussion_id,
                ])
                ->listen(Event\ArticleFeatured::class, 'article.featured', fn (Event\ArticleFeatured $event) => [
                    'discussion_id' => $event->blogMeta->discussion_id,
                ])
                ->listen(Event\ArticleUnfeatured::class, 'article.unfeatured', fn (Event\ArticleUnfeatured $event) => [
                    'discussion_id' => $event->blogMeta->discussion_id,
                ])
                ->listen(Event\BlogMetaUpdated::class, 'article.updated', fn (Event\BlogMetaUpdated $event) => [
                    'discussion_id' => $event->blogMeta->discussion_id,
                    'changed'       => $event->changed,
                ]),
        ]),

    (new Extend\SearchDriver(\Flarum\Search\Database\DatabaseSearchDriver::class))
        ->addFilter(DiscussionSearcher::class, BlogArticleFilter::class)
        ->addMutator(DiscussionSearcher::class, HideBlogPostsFromAllDiscussionsPage::class),
];
