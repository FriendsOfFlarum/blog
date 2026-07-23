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

use Flarum\Frontend\Document;
use Psr\Http\Message\ServerRequestInterface;

class Compose
{
    public function __invoke(Document $document, ServerRequestInterface $request): Document
    {
        return $document;
    }
}
