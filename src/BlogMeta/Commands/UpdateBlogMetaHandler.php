<?php

/*
 * This file is part of fof/blog.
 *
 * Copyright (c) FriendsOfFlarum.
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace FoF\Blog\BlogMeta\Commands;

use Flarum\Discussion\DiscussionRepository;
use Flarum\Foundation\ValidationException;
use Flarum\Settings\SettingsRepositoryInterface;
use FoF\Blog\BlogMeta\BlogMeta;
use FoF\Blog\BlogMeta\BlogMetaValidator;
use FoF\Blog\Event\BlogMetaSaving;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Support\Arr;
use Symfony\Contracts\Translation\TranslatorInterface;

class UpdateBlogMetaHandler
{
    public function __construct(protected DiscussionRepository $discussion, protected TranslatorInterface $translator, protected SettingsRepositoryInterface $settings, protected BlogMetaValidator $validator, protected Dispatcher $dispatcher)
    {
    }

    /**
     * Handle new support blog meta.
     */
    public function handle(UpdateBlogMeta $command): BlogMeta
    {
        $actor = $command->actor;

        // Make sure the actor can edit blog-data
        $actor->assertCan('blog.writeArticles');

        // Data
        $id = $command->id;
        $data = $command->data;

        // Validate ID
        if (empty($command->id) || !is_numeric($command->id)) {
            throw new ValidationException([
                'message' => $this->translator->trans(
                    'fof-blog.forum.validation.missing_id'
                ),
            ]);
        }

        // Update new blog meta
        /** @var BlogMeta $blogMeta */
        $blogMeta = BlogMeta::findOrFail($command->id);

        // Featured image
        if (Arr::has($data, 'attributes.featuredImage')) {
            $blogMeta->featured_image = Arr::get($data, 'attributes.featuredImage', null);
        }

        // Summary
        if (Arr::has($data, 'attributes.summary')) {
            $blogMeta->summary = Arr::get($data, 'attributes.summary', null);
        }

        // Is featured
        if (Arr::has($data, 'attributes.isFeatured')) {
            $blogMeta->is_featured = Arr::get($data, 'attributes.isFeatured', false);
        }

        // Is sized
        if (Arr::has($data, 'attributes.isSized')) {
            $blogMeta->is_sized = Arr::get($data, 'attributes.isSized', false);
        }

        // Update pending review
        if ($actor->can('blog.canApprovePosts') && $blogMeta->is_pending_review && Arr::has($data, 'attributes.isPendingReview')) {
            $blogMeta->is_pending_review = Arr::get($data, 'attributes.isPendingReview', false);
        }

        // Allow extensions to add their own attributes
        $this->dispatcher->dispatch(
            new BlogMetaSaving($blogMeta, $actor, $data)
        );

        // Validate
        $this->validator->assertValid($blogMeta->getDirty());

        $blogMeta->save();

        return $blogMeta;
    }
}
