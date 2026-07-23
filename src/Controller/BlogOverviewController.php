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
use Flarum\Extension\ExtensionManager;
use Flarum\Frontend\Document;
use Illuminate\Support\Arr;
use Psr\Http\Message\ServerRequestInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class BlogOverviewController
{
    public function __construct(protected Client $api, protected TranslatorInterface $translator, protected ExtensionManager $extensionManager)
    {
    }

    public function __invoke(Document $document, ServerRequestInterface $request): Document
    {
        $queryParams = $request->getQueryParams();

        $q = '';

        // Add language support
        if ($this->extensionManager->isEnabled('fof-discussion-language')) {
            $q = "language:{$document->language} ";
        }

        $q .= 'is:blog'.(Arr::get($queryParams, 'category') ? ' tag:'.Arr::get($queryParams, 'category') : '');

        // Preload blog posts
        $apiDocument = $this->getApiDocument($request, [
            'filter' => [
                'q' => $q,
            ],
            'sort' => '-createdAt',
        ]);

        // Set payload
        $document->payload['apiDocument'] = $apiDocument;

        return $document;
    }

    /**
     * Preload blog posts.
     *
     * @param ServerRequestInterface $request
     * @param array                  $params
     *
     * @return object
     */
    private function getApiDocument(ServerRequestInterface $request, array $params)
    {
        return json_decode($this->api->withParentRequest($request)->withQueryParams($params)->get('/discussions')->getBody());
    }
}
