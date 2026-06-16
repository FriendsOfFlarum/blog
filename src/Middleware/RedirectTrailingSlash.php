<?php

/*
 * This file is part of fof/blog.
 *
 * Copyright (c) FriendsOfFlarum.
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace FoF\Blog\Middleware;

use Laminas\Diactoros\Response\RedirectResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * Blog frontend routes are registered without a trailing slash (e.g. `/blog`,
 * `/blog/compose`). A trailing-slash variant (`/blog/`) does not match the
 * Mithril route, so a direct load or refresh would 404. This middleware
 * redirects any trailing-slash blog URL to its canonical, slash-less form.
 */
class RedirectTrailingSlash implements MiddlewareInterface
{
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $uri = $request->getUri();
        $path = $uri->getPath();

        if ($this->shouldRedirect($path)) {
            $canonical = rtrim($path, '/');

            return new RedirectResponse((string) $uri->withPath($canonical), 301);
        }

        return $handler->handle($request);
    }

    /**
     * Only act on blog paths that have a trailing slash — never the site root,
     * and never a bare `/` request.
     */
    private function shouldRedirect(string $path): bool
    {
        if ($path === '/' || !str_ends_with($path, '/')) {
            return false;
        }

        $trimmed = rtrim($path, '/');

        return $trimmed === '/blog' || str_starts_with($trimmed, '/blog/');
    }
}
