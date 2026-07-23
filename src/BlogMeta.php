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

use Flarum\Database\AbstractModel;
use Flarum\Database\ScopeVisibilityTrait;
use Flarum\Discussion\Discussion;
use Flarum\Foundation\EventGeneratorTrait;
use FoF\Blog\Event\ArticleApproved;
use FoF\Blog\Event\ArticleFeatured;
use FoF\Blog\Event\ArticleUnfeatured;
use FoF\Blog\Event\BlogMetaCreated;
use FoF\Blog\Event\BlogMetaUpdated;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Arr;

/**
 * @property int             $id
 * @property int             $discussion_id
 * @property string|null     $featured_image
 * @property string|null     $summary
 * @property bool|null       $is_featured
 * @property bool|null       $is_sized
 * @property bool|null       $is_pending_review
 * @property Discussion|null $discussion
 */
class BlogMeta extends AbstractModel
{
    use EventGeneratorTrait;
    use ScopeVisibilityTrait;

    protected $table = 'blog_meta';

    protected $casts = [
        'is_featured'       => 'bool',
        'is_sized'          => 'bool',
        'is_pending_review' => 'bool',
    ];

    public static function build(int $discussionId, ?string $featuredImage, ?string $summary, ?bool $isFeatured, ?bool $isSized, bool $isPendingReview): static
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

    public static function boot()
    {
        parent::boot();

        static::created(function (self $blogMeta) {
            $blogMeta->raise(new BlogMetaCreated($blogMeta));
        });

        static::updated(function (self $blogMeta) {
            // Publishing a pending article is its own domain event...
            if ($blogMeta->wasChanged('is_pending_review') && !$blogMeta->is_pending_review) {
                $blogMeta->raise(new ArticleApproved($blogMeta));
            }

            // ...as is (un)featuring an article...
            if ($blogMeta->wasChanged('is_featured')) {
                $blogMeta->raise($blogMeta->is_featured ? new ArticleFeatured($blogMeta) : new ArticleUnfeatured($blogMeta));
            }

            // ...while any other change is a meta content update.
            $changed = array_keys(Arr::except($blogMeta->getChanges(), ['is_pending_review', 'is_featured']));

            if ($changed !== []) {
                $blogMeta->raise(new BlogMetaUpdated($blogMeta, $changed));
            }
        });
    }

    public function discussion(): BelongsTo
    {
        return $this->belongsTo(Discussion::class);
    }
}
