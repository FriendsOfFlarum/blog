<?php

namespace V17Development\FlarumBlog\BlogMeta;

use Flarum\Database\AbstractModel;
use Flarum\Database\ScopeVisibilityTrait;
use Flarum\Discussion\Discussion;
use Flarum\Foundation\EventGeneratorTrait;
use V17Development\FlarumBlog\Event\BlogMetaCreated;

/**
 * @property string|null $summary
 * @property string|null $featured_image
 * @property bool $is_featured
 * @property bool $is_sized
 * @property bool $is_pending_review
 * @property int $discussion_id
 */
class BlogMeta extends AbstractModel
{
    use EventGeneratorTrait;
    use ScopeVisibilityTrait;

    protected $table = 'blog_meta';

    /**
     * Guard discussion
     */
    protected $guarded = [
        'discussion_id'
    ];

    public static function build($discussionId, $featuredImage, $summary, $isFeatured, $isSized, $isPendingReview)
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
