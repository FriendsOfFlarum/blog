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
use Flarum\Extension\ExtensionManager;
use Flarum\Frontend\Document;
use Illuminate\Support\Arr;
use Psr\Http\Message\ServerRequestInterface;

class Overview
{
    public function __construct(
        protected Client $api,
        protected ExtensionManager $extensionManager
    ) {
    }

    public function __invoke(Document $document, ServerRequestInterface $request): Document
    {
        $queryParams = $request->getQueryParams();

        // 2.x has no server-side gambit parsing, so use structured filters
        // rather than an `is:blog tag:...` query string.
        $filter = [
            'blog' => true,
        ];

        if ($category = Arr::get($queryParams, 'category')) {
            $filter['tag'] = $category;
        }

        // Add language support
        if ($this->extensionManager->isEnabled('fof-discussion-language')) {
            $filter['language'] = $document->language;
        }

        // Preload blog posts, mirroring the includes the frontend list requests
        // (an explicit `include` replaces the endpoint defaults).
        $apiDocument = $this->getApiDocument($request, [
            'filter'  => $filter,
            'sort'    => '-createdAt',
            'include' => 'user,tags,tags.parent,blogMeta',
        ]);

        $document->payload['apiDocument'] = $apiDocument;

        return $document;
    }

    /**
     * Preload blog posts.
     *
     * @param array<string, mixed> $params
     */
    private function getApiDocument(ServerRequestInterface $request, array $params): object
    {
        return json_decode($this->api->withParentRequest($request)->withQueryParams($params)->get('/discussions')->getBody());
    }
}
