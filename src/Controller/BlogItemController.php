<?php

namespace FoF\Blog\Controller;

use Flarum\Frontend\Document;
use Flarum\Api\Client;
use Flarum\Http\UrlGenerator;
use Flarum\Http\Exception\RouteNotFoundException;
use Flarum\Settings\SettingsRepositoryInterface;
use Psr\Http\Message\ServerRequestInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Flarum\Tags\TagRepository;
use Illuminate\Support\Arr;
use FoF\Blog\BlogMeta\BlogMeta;

class BlogItemController
{
    /**
     * @var Client
     */
    protected $api;

    /**
     * @var UrlGenerator
     */
    protected $url;

    /**
     * @var SettingsRepositoryInterface
     */
    protected $settings;

    /**
     * @var TranslatorInterface
     */
    protected $translator;

    /**
     * @var TagRepository
     */
    protected $tagRepository;

    public function __construct(Client $api, UrlGenerator $url, SettingsRepositoryInterface $settings, TagRepository $tagRepository, TranslatorInterface $translator)
    {
        $this->api = $api;
        $this->url = $url;
        $this->settings = $settings;
        $this->translator = $translator;
        $this->tagRepository = $tagRepository;
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
     * Preload blog posts
     *
     * @param ServerRequestInterface $request
     * @param int $id
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
