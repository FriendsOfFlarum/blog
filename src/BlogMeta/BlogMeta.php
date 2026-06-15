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

use Flarum\Database\AbstractModel;
use Flarum\Database\ScopeVisibilityTrait;
use Flarum\Discussion\Discussion;
use Flarum\Foundation\EventGeneratorTrait;
use FoF\Blog\Event\BlogMetaCreated;

/**
 * @property int                                $id
 * @property int                                $discussion_id
 * @property string|null                        $featured_image
 * @property string|null                        $summary
 * @property bool|null                          $is_featured
 * @property bool|null                          $is_sized
 * @property bool|null                          $is_pending_review
 * @property \Flarum\Discussion\Discussion|null $discussion
 */
class BlogMeta extends AbstractModel
{
    use EventGeneratorTrait;
    use ScopeVisibilityTrait;

    protected $table = 'blog_meta';

    /**
     * Guard discussion.
     */
    protected $guarded = [
        'discussion_id',
    ];

    public static function build(int $discussionId, ?string $featuredImage, ?string $summary, ?bool $isFeatured, ?bool $isSized, bool $isPendingReview): self
    {
        $blogMeta = new static();
        $blogMeta->discussion_id = $discussionId;
        $blogMeta->featured_image = $featuredImage;
        $blogMeta->summary = $summary;
        $blogMeta->is_featured = $isFeatured;
        $blogMeta->is_sized = $isSized;
        $blogMeta->is_pending_review = $isPendingReview;

        return $blogMeta;
    }

    /**
     * Boot the model.
     *
     * @return void
     */
    public static function boot()
    {
        parent::boot();

        static::created(function (self $blogMeta) {
            $blogMeta->raise(new BlogMetaCreated($blogMeta));
        });
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function discussion()
    {
        return $this->belongsTo(Discussion::class);
    }
}
