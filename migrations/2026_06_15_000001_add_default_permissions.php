<?php

use Flarum\Database\Migration;
use Flarum\Group\Group;

return Migration::addPermissions([
    'blog.writeArticles' => Group::MODERATOR_ID,
    'blog.autoApprovePosts' => Group::MODERATOR_ID,
    'blog.canApprovePosts' => Group::MODERATOR_ID
]);
