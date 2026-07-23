<?php

/*
 * This file is part of fof/blog.
 *
 * Copyright (c) FriendsOfFlarum.
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace FoF\Blog\Controller;

use Flarum\Api\Client;
use Flarum\Frontend\Document;
use Flarum\Http\Exception\RouteNotFoundException;
use Flarum\Http\UrlGenerator;
use Flarum\Settings\SettingsRepositoryInterface;
use Flarum\Tags\TagRepository;
use Illuminate\Support\Arr;
use Psr\Http\Message\ServerRequestInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class BlogItemController
{
    public function __construct(protected Client $api, protected UrlGenerator $url, protected SettingsRepositoryInterface $settings, protected TagRepository $tagRepository, protected TranslatorInterface $translator)
    {
    }

    public function __invoke(Document $document, ServerRequestInterface $request): Document
    {
        $queryParams = $request->getQueryParams();

        // Find blog item
        $apiDocument = $this->getApiDocument($request, (int) Arr::get($queryParams, 'id'));

        // Article not found
        if ($apiDocument === null) {
            return $document;
        }

        // Set payload
        $document->payload['apiDocument'] = $apiDocument;

        return $document;
    }

    /**
     * Preload blog posts.
     *
     * @param ServerRequestInterface $request
     * @param int                    $id
     *
     * @return mixed
     */
    private function getApiDocument(ServerRequestInterface $request, int $id)
    {
        $response = $this->api->withParentRequest($request)->get("/discussions/{$id}");

        if ($response->getStatusCode() === 404) {
            throw new RouteNotFoundException();
        }

        return json_decode($response->getBody());
    }
}
