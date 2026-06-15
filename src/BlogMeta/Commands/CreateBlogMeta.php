<?php

namespace FoF\Blog\BlogMeta\Commands;

use Flarum\User\User;
use Flarum\Discussion\Discussion;

class CreateBlogMeta
{
    /**
     * @var User
     */
    public $actor;

    /**
     * @var array<string, mixed>
     */
    public $data;

    public function __construct(User $actor, array $data)
    {
        $this->actor = $actor;
        $this->data = $data;
    }
}
