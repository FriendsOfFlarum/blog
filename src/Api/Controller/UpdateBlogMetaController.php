<?php

namespace FoF\Blog\Api\Controller;

use Flarum\Api\Controller\AbstractShowController;
use Flarum\Http\RequestUtil;
use Illuminate\Contracts\Bus\Dispatcher;
use Illuminate\Support\Arr;
use Psr\Http\Message\ServerRequestInterface;
use Tobscure\JsonApi\Document;
use FoF\Blog\Api\Serializer\BlogMetaSerializer;
use FoF\Blog\BlogMeta\Commands\UpdateBlogMeta;

class UpdateBlogMetaController extends AbstractShowController
{
    /**
     * {@inheritdoc}
     */
    public $serializer = BlogMetaSerializer::class;

    public $include = ['discussion'];

    /**
     * @var Dispatcher
     */
    protected $bus;

    /**
     * @param Dispatcher $bus
     */
    public function __construct(Dispatcher $bus)
    {
        $this->bus = $bus;
    }

    /**
     * {@inheritdoc}
     */
    protected function data(ServerRequestInterface $request, Document $document)
    {
        $queryParams = $request->getQueryParams();

        return $this->bus->dispatch(
            new UpdateBlogMeta(RequestUtil::getActor($request), Arr::get($queryParams, 'id'), Arr::get($request->getParsedBody(), 'data', []))
        );
    }
}
