<?php

/*
 * This file is part of fof/blog.
 *
 * Copyright (c) FriendsOfFlarum.
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace FoF\Blog\Api\Controller;

use Flarum\Api\Controller\AbstractDeleteController;
use Flarum\Http\RequestUtil;
use Flarum\Settings\SettingsRepositoryInterface;
use Illuminate\Contracts\Filesystem\Factory;
use Illuminate\Contracts\Filesystem\Filesystem;
use Laminas\Diactoros\Response\EmptyResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class DeleteDefaultBlogImageController extends AbstractDeleteController
{
    /**
     * @var Filesystem
     */
    protected $uploadDir;

    public function __construct(protected SettingsRepositoryInterface $settings, Factory $filesystemFactory)
    {
        $this->uploadDir = $filesystemFactory->disk('flarum-assets');
    }

    /**
     * {@inheritdoc}
     */
    protected function delete(ServerRequestInterface $request): void
    {
        RequestUtil::getActor($request)->assertAdmin();

        $path = $this->settings->get('blog_default_image_path');
        $this->settings->set('blog_default_image_path', null);

        if ($path && $this->uploadDir->exists($path)) {
            $this->uploadDir->delete($path);
        }

        return new EmptyResponse(204);
    }
}
