<?php

/*
 * This file is part of fof/seo.
 *
 * Copyright (c) FriendsOfFlarum.
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace FoF\Blog\Subscribers;

use Flarum\Discussion\Event as DiscussionEvent;
use Flarum\Post\CommentPost;
use FoF\Blog\BlogMeta\BlogMeta;
use FoF\Blog\Event\BlogMetaCreated;
use FoF\Blog\Event\BlogMetaSaving;
use FoF\Seo\SeoMeta\Event\Created;
use FoF\Seo\SeoMeta\SeoMeta;
use FoF\Seo\SeoProperties;
use Illuminate\Contracts\Events\Dispatcher;

/**
 * Subscribe to discussion creation, update or deleted.
 */
class SeoBlogSubscriber
{
    public function __construct(private SeoProperties $seoProperties)
    {
    }

    /**
     * Subscribe to events.
     *
     * @param Dispatcher $events
     */
    public function subscribe(Dispatcher $events): void
    {
        $events->listen(DiscussionEvent\Deleting::class, [$this, 'onDiscussionUpdate']);
        $events->listen(DiscussionEvent\Renamed::class, [$this, 'onDiscussionUpdate']);
        $events->listen(BlogMetaSaving::class, [$this, 'onBlogMetaUpdate']);
        $events->listen(BlogMetaCreated::class, [$this, 'onBlogMetaUpdate']);
        $events->listen(Created::class, [$this, 'onMetaCreated']);
    }

    /**
     * Handle model event.
     *
     * @param DiscussionEvent\Deleting|DiscussionEvent\Renamed $event
     */
    public function onDiscussionUpdate($event): void
    {
        // blogMeta is a relationship added to the core Discussion model via
        // Extend\Model in extend.php, so it is not declared on the core class.
        /** @var BlogMeta|null $blogMeta */
        $blogMeta = $event->discussion->blogMeta;

        // Only do something with discussions that have a blog_meta relationship
        if (!isset($blogMeta->id)) {
            return;
        }

        // Find relevant meta (findByObjectType creates the record if absent)
        $meta = SeoMeta::findByObjectType('blogs', $blogMeta->id);

        // Find and delete meta-data
        if ($event::class === DiscussionEvent\Deleting::class) {
            $meta->delete();

            return;
        }

        // Do not auto update
        if (!$meta->auto_update_data) {
            return;
        }

        $this->updateMeta($meta, $blogMeta);

        // Update
        $meta->save();
    }

    /**
     * Handle Blog meta update.
     *
     * @param BlogMetaSaving|BlogMetaCreated $event
     */
    public function onBlogMetaUpdate($event): void
    {
        // Make sure to only process meta's that have an ID
        if (!isset($event->blogMeta->id)) {
            return;
        }

        // Find meta
        $meta = SeoMeta::findByObjectType('blogs', $event->blogMeta->id);

        $this->updateMeta($meta, $event->blogMeta);

        // Update
        $meta->save();
    }

    /**
     * Handle SEO-meta created event for blogs.
     *
     * @param Created $event
     */
    public function onMetaCreated(Created $event): void
    {
        // Only update meta data if object type matches
        if ($event->objectType !== 'blogs') {
            return;
        }

        // Find blogMeta
        $blogMeta = BlogMeta::find($event->objectId);

        $this->updateMeta($event->seoMeta, $blogMeta);

        $event->seoMeta->save();
    }

    /**
     * Public function to update seoMeta.
     */
    public function updateMeta(SeoMeta $seoMeta, BlogMeta $blogMeta): void
    {
        $seoMeta->title = $blogMeta->discussion->title;

        $seoMeta->created_at = $blogMeta->discussion->created_at;

        $firstPost = $blogMeta->discussion->firstPost;

        // If a discussion has a first post, use edited_at time if intial post was more recent edited than the last post was posted
        if ($firstPost instanceof CommentPost) {
            $seoMeta->updated_at = $firstPost->edited_at > $blogMeta->discussion->last_posted_at ? $firstPost->edited_at : $blogMeta->discussion->last_posted_at;

            $content = $firstPost->formatContent();

            // Set estimated reading time
            $estimatedReadingTime = $this->seoProperties->getEstimatedReadingTime($content);

            // If higher than zero, update reading time
            if ($estimatedReadingTime > 0) {
                $seoMeta->estimated_reading_time = $estimatedReadingTime;
            }
        } else {
            $seoMeta->updated_at = $blogMeta->discussion->last_posted_at;
        }

        // Set description
        $seoMeta->description = $blogMeta->summary;

        // Set description
        $seoMeta->description = $blogMeta->summary;

        // Only update image if source was set to auto and is not managed by a different extension
        if (!$seoMeta->open_graph_image_source || $seoMeta->open_graph_image_source === 'auto' || $seoMeta->open_graph_image_source === 'v17development-flarum-blog') {
            $seoMeta->open_graph_image = $blogMeta->featured_image;
            $seoMeta->open_graph_image_source = 'v17development-flarum-blog';
        }
    }
}
