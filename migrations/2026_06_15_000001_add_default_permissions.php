<?php

/*
 * This file is part of fof/seo.
 *
 * Copyright (c) FriendsOfFlarum.
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

use Flarum\Database\Migration;
use Flarum\Group\Group;

return Migration::addPermissions([
    'blog.writeArticles'    => Group::MODERATOR_ID,
    'blog.autoApprovePosts' => Group::MODERATOR_ID,
    'blog.canApprovePosts'  => Group::MODERATOR_ID,
]);
