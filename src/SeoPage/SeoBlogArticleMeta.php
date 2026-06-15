<?php

/*
 * This file is part of fof/seo.
 *
 * Copyright (c) FriendsOfFlarum.
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace FoF\Blog\SeoPage;

use Flarum\Discussion\DiscussionRepository;
use Flarum\Foundation\DispatchEventsTrait;
use Flarum\Http\UrlGenerator;
use Flarum\Settings\SettingsRepositoryInterface;
use FoF\Blog\BlogMeta\BlogMeta;
use FoF\Seo\Page\PageDriverInterface;
use FoF\Seo\SeoMeta\SeoMeta;
use FoF\Seo\SeoProperties;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Support\Arr;
use Psr\Http\Message\ServerRequestInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class SeoBlogArticleMeta implements PageDriverInterface
{
    use DispatchEventsTrait;

    /**
     * @var DiscussionRepository
     */
    protected $discussionRepository;

    /**
     * @var UrlGenerator
     */
    protected $urlGenerator;

    /**
     * @var TranslatorInterface
     */
    protected $translator;

    /**
     * @var SettingsRepositoryInterface
     */
    protected $settings;

    /**
     * @param DiscussionRepository $discussionRepository
     * @param TranslatorInterface  $translator
     */
    public function __construct(
        DiscussionRepository $discussionRepository,
        UrlGenerator $urlGenerator,
        Dispatcher $events,
        TranslatorInterface $translator,
        SettingsRepositoryInterface $settings
    ) {
        $this->discussionRepository = $discussionRepository;
        $this->urlGenerator = $urlGenerator;
        $this->events = $events;
        $this->translator = $translator;
        $this->settings = $settings;
    }

    public function extensionDependencies(): array
    {
        return [];
    }

    public function handleRoutes(): array
    {
        return ['blog.post'];
    }

    /**
     * @param ServerRequestInterface $request
     * @param SeoProperties          $properties
     */
    public function handle(
        ServerRequestInterface $request,
        SeoProperties $properties
    ): void {
        // Get discussion ID from params
        $discussionId = Arr::get($request->getQueryParams(), 'id');

        try {
            // Find discussion
            $discussion = $this->discussionRepository->findOrFail($discussionId);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            $properties->setTitle($this->translator->trans('v17development-flarum-blog.forum.blog'));

            // Do nothing, no model found
            return;
        }

        /** @var BlogMeta|null $blogMeta */
        $blogMeta = $discussion->blogMeta;

        // Backup in case no blog-meta exists
        if (!isset($blogMeta->id)) {
            $properties->setTitle($discussion->title);

            return;
        }

        // Get seo-meta-date
        $seoMeta = SeoMeta::findByObjectTypeOrCreate(
            'blogs',
            $blogMeta->id
        );

        // Run events in case the model was created
        $this->dispatchEventsFor($seoMeta);

        // Update ld-json
        $properties
            // Set page type article
            ->setMetaPropertyTag('og:type', 'article');

        // Generate data
        $properties->generateTagsFromMetaData($seoMeta);

        // Set schema JSON
        $properties->setSchemaJson('@type', 'BlogPosting');

        // Set default featured image
        if (!$seoMeta->open_graph_image && $this->settings->get('blog_default_image_path', null) !== null) {
            $properties->setImage($this->urlGenerator->to('forum')->base().'/assets/'.$this->settings->get('blog_default_image_path', null));
        }

        // Update knowledge base url
        $fullArticleUrl = $this->urlGenerator->to('forum')->route('blog.post', ['id' => $discussion->id.'-'.$discussion->slug]);
        $properties->setUrl($fullArticleUrl, false);
        $properties->setCanonicalUrl($fullArticleUrl, false);

        // Set blog article title
        $properties->setTitle($seoMeta->title.' - '.$this->translator->trans('v17development-flarum-blog.forum.blog'));
    }
}
