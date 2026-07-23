<?php

/*
 * This file is part of fof/blog.
 *
 * Copyright (c) FriendsOfFlarum.
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace FoF\Blog\Content;

use Flarum\Api\Client;
use Flarum\Frontend\Document;
use Flarum\Http\Exception\RouteNotFoundException;
use Illuminate\Support\Arr;
use Psr\Http\Message\ServerRequestInterface;

class Item
{
    public function __construct(protected Client $api)
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

        $document->payload['apiDocument'] = $apiDocument;

        return $document;
    }

    /**
     * Preload the blog article.
     */
    private function getApiDocument(ServerRequestInterface $request, int $id): mixed
    {
        $response = $this->api->withParentRequest($request)->get("/discussions/{$id}");

        if ($response->getStatusCode() === 404) {
            throw new RouteNotFoundException();
        }

        return json_decode($response->getBody());
    }
}
