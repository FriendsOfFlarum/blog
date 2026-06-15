<?php

/*
 * This file is part of fof/seo.
 *
 * Copyright (c) FriendsOfFlarum.
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace FoF\Blog\Api\Controller;

use Flarum\Api\Controller\AbstractListController;
use Flarum\Api\Serializer\DiscussionSerializer;
use Flarum\Discussion\Discussion;
use Flarum\Http\RequestUtil;
use Flarum\Http\UrlGenerator;
use Psr\Http\Message\ServerRequestInterface;
use Tobscure\JsonApi\Document;

class ListBlogPostsController extends AbstractListController
{
    /**
     * {@inheritdoc}
     */
    public $serializer = DiscussionSerializer::class;

    public $include = ['discussion', 'discussion.user', 'discussion.lastpost', 'tags'];

    public $sortFields = ['id'];

    /**
     * @var UrlGenerator
     */
    protected $url;

    /**
     * @param UrlGenerator $url
     */
    public function __construct(UrlGenerator $url)
    {
        $this->url = $url;
    }

    /**
     * {@inheritdoc}
     */
    protected function data(ServerRequestInterface $request, Document $document)
    {
        $actor = RequestUtil::getActor($request);

        // $this->assertCan($actor, 'blog.viewBlogs');

        // Params
        $limit = $this->extractLimit($request);
        $offset = $this->extractOffset($request);

        // Build query
        $query = Discussion::whereVisibleTo($actor);

        $results = $query
            ->from('discussion_tag')
            ->whereIn('tag_id', 'blog')
            ->skip($offset)
            ->take($limit + 1)
            ->get();

        // Check for more results
        $hasMoreResults = $limit > 0 && $results->count() > $limit;

        // Pop
        if ($hasMoreResults) {
            $results->pop();
        }

        // Add pagination to the request
        $document->addPaginationLinks(
            $this->url->to('api')->route('api.blog'),
            $request->getQueryParams(),
            $offset,
            $limit,
            $hasMoreResults ? null : 0
        );

        return $results;
    }
}
