<?php

namespace FoF\Blog\Api\Controller;

use Flarum\Api\Controller\AbstractCreateController;
use Illuminate\Contracts\Bus\Dispatcher;
use Illuminate\Support\Arr;
use Psr\Http\Message\ServerRequestInterface;
use Tobscure\JsonApi\Document;
use FoF\Blog\Api\Serializer\BlogMetaSerializer;
use FoF\Blog\BlogMeta\Commands\CreateBlogMeta;
use Flarum\Http\RequestUtil;

class CreateBlogMetaController extends AbstractCreateController
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
        return $this->bus->dispatch(
            new CreateBlogMeta(RequestUtil::getActor($request), Arr::get($request->getParsedBody(), 'data', []))
        );
    }
}
