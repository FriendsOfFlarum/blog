<?php

namespace FoF\Blog\Controller;

use Flarum\Frontend\Document;
use Psr\Http\Message\ServerRequestInterface;

class BlogComposerController
{
    public function __invoke(Document $document, ServerRequestInterface $request): Document
    {
        return $document;
    }
}