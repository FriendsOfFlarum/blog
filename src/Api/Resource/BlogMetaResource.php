<?php

/*
 * This file is part of fof/blog.
 *
 * Copyright (c) FriendsOfFlarum.
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace FoF\Blog\Api\Resource;

use Carbon\Carbon;
use Flarum\Api\Context;
use Flarum\Api\Endpoint;
use Flarum\Api\Resource;
use Flarum\Api\Schema;
use Flarum\Settings\SettingsRepositoryInterface;
use FoF\Blog\BlogMeta\BlogMeta;
use FoF\Blog\BlogMeta\BlogMetaValidator;
use FoF\Blog\Event\BlogMetaSaving;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;
use Tobyz\JsonApiServer\Context as OriginalContext;

/**
 * @extends Resource\AbstractDatabaseResource<BlogMeta>
 */
class BlogMetaResource extends Resource\AbstractDatabaseResource
{
    public function __construct(
        protected SettingsRepositoryInterface $settings,
        protected BlogMetaValidator $validator
    ) {
    }

    public function type(): string
    {
        return 'blogMeta';
    }

    public function model(): string
    {
        return BlogMeta::class;
    }

    public function scope(Builder $query, OriginalContext $context): void
    {
        $query->whereVisibleTo($context->getActor());
    }

    public function newModel(OriginalContext $context): object
    {
        // A discussion only ever has one meta record: "creating" meta for a
        // discussion that already has one updates the existing record instead.
        if ($context->creating(self::class) && ($discussionId = Arr::get($context->body(), 'data.relationships.discussion.data.id'))) {
            return BlogMeta::firstOrNew(['discussion_id' => (int) $discussionId]);
        }

        return parent::newModel($context);
    }

    public function endpoints(): array
    {
        return [
            Endpoint\Create::make()
                ->authenticated()
                ->can('blog.writeArticles')
                ->defaultInclude(['discussion']),
            Endpoint\Update::make()
                ->authenticated()
                ->can('blog.writeArticles')
                ->defaultInclude(['discussion']),
        ];
    }

    public function fields(): array
    {
        return [
            Schema\Str::make('featuredImage')
                ->nullable()
                ->writable(),
            Schema\Str::make('summary')
                ->nullable()
                ->writable(),
            Schema\Boolean::make('isFeatured')
                ->writable(),
            Schema\Boolean::make('isSized')
                ->writable(),
            Schema\Boolean::make('isPendingReview')
                // The field stays writable so clients may always send it, but
                // the value only takes effect when an approver publishes a
                // pending article — anything else is silently ignored, as in
                // 1.x. On create the flag is computed in `creating()`.
                ->writable()
                ->set(function (BlogMeta $meta, ?bool $value, Context $context) {
                    if (
                        $context->updating()
                        && (bool) $meta->is_pending_review
                        && $context->getActor()->can('blog.canApprovePosts')
                    ) {
                        $meta->is_pending_review = (bool) $value;
                    }
                }),

            Schema\Relationship\ToOne::make('discussion')
                ->type('discussions')
                ->includable()
                ->requiredOnCreate()
                ->writableOnCreate(),
        ];
    }

    /**
     * @param BlogMeta $model
     */
    public function creating(object $model, OriginalContext $context): ?object
    {
        // Auto approve if the article already existed or it does not require a review.
        if ($model->discussion->created_at->diffInSeconds(Carbon::now(), true) > 30 || !$this->settings->get('blog_requires_review')) {
            $model->is_pending_review = false;
        } else {
            $model->is_pending_review = !$context->getActor()->can('blog.autoApprovePosts');
        }

        return $model;
    }

    /**
     * @param BlogMeta $model
     */
    public function saving(object $model, OriginalContext $context): ?object
    {
        // Allow extensions to add their own attributes.
        $this->events->dispatch(
            new BlogMetaSaving($model, $context->getActor(), Arr::get($context->body(), 'data', []))
        );

        $this->validator->assertValid($model->getDirty());

        return $model;
    }
}
