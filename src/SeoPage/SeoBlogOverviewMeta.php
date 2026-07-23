<?php

/*
 * This file is part of fof/blog.
 *
 * Copyright (c) FriendsOfFlarum.
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace FoF\Blog\SeoPage;

use Flarum\Foundation\DispatchEventsTrait;
use Flarum\Http\UrlGenerator;
use Flarum\Locale\TranslatorInterface;
use Flarum\Tags\Tag;
use Flarum\Tags\TagRepository;
use FoF\Seo\Page\PageDriverInterface;
use FoF\Seo\SeoMeta\SeoMeta;
use FoF\Seo\SeoProperties;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Support\Arr;
use Psr\Http\Message\ServerRequestInterface;

class SeoBlogOverviewMeta implements PageDriverInterface
{
    use DispatchEventsTrait;

    public function __construct(
        protected TagRepository $tagRepository,
        protected Dispatcher $events,
        protected TranslatorInterface $translator,
        protected UrlGenerator $urlGenerator,
    ) {
    }

    public function extensionDependencies(): array
    {
        return [];
    }

    public function handleRoutes(): array
    {
        return ['blog.overview', 'blog.category'];
    }

    /**
     * @param ServerRequestInterface $request
     * @param SeoProperties          $properties
     */
    public function handle(
        ServerRequestInterface $request,
        SeoProperties $properties
    ): void {
        $properties->setTitle($this->translator->trans('fof-blog.forum.blog'));

        // Get tag slug from params
        $categorySlug = Arr::get($request->getQueryParams(), 'category');

        try {
            $category = Tag::where('slug', $categorySlug)->firstOrFail();
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            // No category: this is the plain blog overview. Canonicalise it to
            // the blog route so it does not fall back to core's default (e.g.
            // `/all` when the blog overview is the forum home page).
            $overviewUrl = $this->urlGenerator->to('forum')->route('blog.overview');
            $properties->setUrl($overviewUrl, false);
            $properties->setCanonicalUrl($overviewUrl, false);

            return;
        }

        // Get seo-meta-date
        $seoMeta = SeoMeta::findByModelOrCreate(
            $category
        );

        // Run events in case the model was created
        $this->dispatchEventsFor($seoMeta);

        // Generate data
        $properties->generateTagsFromMetaData($seoMeta);

        // Canonicalise to the category route.
        $categoryUrl = $this->urlGenerator->to('forum')->route('blog.category', ['category' => $category->slug]);
        $properties->setUrl($categoryUrl, false);
        $properties->setCanonicalUrl($categoryUrl, false);

        // Set blog title
        $properties->setTitle($seoMeta->title.' - '.$this->translator->trans('fof-blog.forum.blog'));
    }
}
