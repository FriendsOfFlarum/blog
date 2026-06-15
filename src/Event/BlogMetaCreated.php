<?php

namespace FoF\Blog\Event;

use FoF\Blog\BlogMeta\BlogMeta;

class BlogMetaCreated
{
    /**
     * @var BlogMeta
     */
    public $blogMeta;

    public function __construct(BlogMeta $blogMeta)
    {
        $this->blogMeta = $blogMeta;
    }
}
