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

use Flarum\Api\Controller\UploadImageController;
use Intervention\Image\Interfaces\EncodedImageInterface;
use Psr\Http\Message\UploadedFileInterface;

class UploadDefaultBlogImageController extends UploadImageController
{
    protected string $filePathSettingKey = 'blog_default_image_path';
    protected string $filenamePrefix = 'blog_default_image';
    protected string $fileExtension = 'png';

    /**
     * Maximum stored width, in pixels. The default image is used as a full-width
     * "cover" hero background (see less/Forum/Item.less), so it needs to stay
     * crisp on large and hi-DPI/retina displays — hence a generous cap rather
     * than a thumbnail size. Taller-than-wide images are bounded by height too.
     */
    const MAX_WIDTH = 2000;

    const MAX_HEIGHT = 1200;

    protected function makeImage(UploadedFileInterface $file): EncodedImageInterface
    {
        // Downscale oversized uploads to keep stored assets reasonable:
        // `scaleDown()` preserves the aspect ratio and never upscales
        // smaller images.
        return $this->imageManager
            ->read($file->getStream()->getMetadata('uri'))
            ->scaleDown(self::MAX_WIDTH, self::MAX_HEIGHT)
            ->toPng();
    }
}
