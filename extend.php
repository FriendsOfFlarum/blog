<?php

/*
 * This file is part of fof/seo.
 *
 * Copyright (c) FriendsOfFlarum.
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace FoF\Blog;

use Flarum\Api\Controller as FlarumController;
use Flarum\Api\Serializer\BasicDiscussionSerializer;
use Flarum\Api\Serializer\ForumSerializer;
use Flarum\Discussion\Discussion;
use Flarum\Discussion\Event\Saving;
use Flarum\Discussion\Filter\DiscussionFilterer;
use Flarum\Discussion\Search\DiscussionSearcher;
use Flarum\Extend;
use Flarum\Tags\Api\Serializer\TagSerializer;
use FoF\Blog\Access\ScopeDiscussionVisibility;
use FoF\Blog\Api\AttachForumSerializerAttributes;
use FoF\Blog\Api\AttatchTagSerializerAttributes;
use FoF\Blog\Api\Controller\CreateBlogMetaController;
use FoF\Blog\Api\Controller\DeleteDefaultBlogImageController;
use FoF\Blog\Api\Controller\UpdateBlogMetaController;
use FoF\Blog\Api\Controller\UploadDefaultBlogImageController;
use FoF\Blog\Api\Serializer\BlogMetaSerializer;
use FoF\Blog\BlogMeta\BlogMeta;
use FoF\Blog\Controller\BlogComposerController;
use FoF\Blog\Controller\BlogItemController;
use FoF\Blog\Controller\BlogOverviewController;
use FoF\Blog\Listeners\CreateBlogMetaOnDiscussionCreate;
use FoF\Blog\Query\BlogArticleFilterGambit;
use FoF\Blog\Query\FilterDiscussionsForBlogPosts;
use FoF\Blog\SeoPage\SeoBlogArticleMeta;
use FoF\Blog\SeoPage\SeoBlogOverviewMeta;
use FoF\Blog\Subscribers\SeoBlogSubscriber;

return [
    (new Extend\Frontend('forum'))
        ->js(__DIR__.'/js/dist/forum.js')
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
        ->css(__DIR__.'/less/Admin.less'),

    (new Extend\Routes('api'))
        ->post('/blogMeta', 'blog.meta', CreateBlogMetaController::class)
        ->patch('/blogMeta/{id}', 'blog.meta.edit', UpdateBlogMetaController::class)
        ->post('/blog_default_image', 'blog.default_image.upload', UploadDefaultBlogImageController::class)
        ->delete('/blog_default_image', 'blog.default_image.delete', DeleteDefaultBlogImageController::class),

    new Extend\Locales(__DIR__.'/locale'),

    (new Extend\Model(Discussion::class))
        ->hasOne('blogMeta', BlogMeta::class, 'discussion_id'),

    (new Extend\ModelVisibility(Discussion::class))
        ->scope(ScopeDiscussionVisibility::class),

    (new Extend\ApiController(FlarumController\CreateDiscussionController::class))
        ->addInclude(['blogMeta', 'firstPost', 'user']),

    (new Extend\ApiController(FlarumController\ListDiscussionsController::class))
        ->addInclude(['blogMeta', 'firstPost', 'user']),

    (new Extend\ApiController(FlarumController\ShowDiscussionController::class))
        ->addInclude(['blogMeta', 'firstPost', 'user']),

    (new Extend\ApiController(FlarumController\UpdateDiscussionController::class))
        ->addInclude(['blogMeta', 'firstPost', 'user']),

    (new Extend\ApiSerializer(BasicDiscussionSerializer::class))
        ->hasOne('blogMeta', BlogMetaSerializer::class),

    (new Extend\ApiSerializer(ForumSerializer::class))
        ->attributes(AttachForumSerializerAttributes::class),

    (new Extend\ApiSerializer(TagSerializer::class))
        ->attributes(AttatchTagSerializerAttributes::class),

    (new Extend\Filter(DiscussionFilterer::class))
        ->addFilterMutator(FilterDiscussionsForBlogPosts::class),

    (new Extend\SimpleFlarumSearch(DiscussionSearcher::class))
        ->addGambit(BlogArticleFilterGambit::class),

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
];
