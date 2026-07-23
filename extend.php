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
use FoF\Blog\BlogMeta\BlogMeta;
use FoF\Blog\Controller\BlogComposerController;
use FoF\Blog\Controller\BlogItemController;
use FoF\Blog\Controller\BlogOverviewController;
use FoF\Blog\Listeners\CreateBlogMetaOnDiscussionCreate;
use FoF\Blog\Middleware\RedirectTrailingSlash;
use FoF\Blog\Query\BlogArticleFilter;
use FoF\Blog\Query\FilterDiscussionsForBlogPosts;
use FoF\Blog\SeoPage\SeoBlogArticleMeta;
use FoF\Blog\SeoPage\SeoBlogOverviewMeta;
use FoF\Blog\Subscribers\SeoBlogSubscriber;

return [
    // Core resolves trailing-slash URLs internally, but we still want a single
    // canonical URL per blog page, so redirect `/blog/...` to its slash-less form.
    (new Extend\Middleware('forum'))
        ->insertBefore(ResolveRoute::class, RedirectTrailingSlash::class),

    (new Extend\Frontend('forum'))
        ->js(__DIR__.'/js/dist/forum.js')
        ->jsDirectory(__DIR__.'/js/dist/forum')
        ->css(__DIR__.'/less/Forum.less')
        ->route('/blog', 'blog.overview', BlogOverviewController::class)
        ->route('/blog/compose', 'blog.compose', BlogComposerController::class)
        ->route('/blog/category/{category}', 'blog.category', BlogOverviewController::class)
        ->route('/blog/{id:[\d\S]+(?:-[^/]*)?}', 'blog.post', BlogItemController::class)
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
        ->endpoint(
            [Endpoint\Index::class, Endpoint\Show::class, Endpoint\Create::class, Endpoint\Update::class],
            fn (Endpoint\Index|Endpoint\Show|Endpoint\Create|Endpoint\Update $endpoint) => $endpoint
                ->addDefaultInclude(['blogMeta', 'firstPost', 'user'])
        ),

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
                ->subscribe(SeoBlogSubscriber::class),
        ]),

    (new Extend\SearchDriver(\Flarum\Search\Database\DatabaseSearchDriver::class))
        ->addFilter(DiscussionSearcher::class, BlogArticleFilter::class)
        ->addMutator(DiscussionSearcher::class, FilterDiscussionsForBlogPosts::class),
];
