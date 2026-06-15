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

use Flarum\Api\Controller\UploadImageController;
use Flarum\Settings\SettingsRepositoryInterface;
use Illuminate\Contracts\Filesystem\Factory;
use Intervention\Image\Image;
use Intervention\Image\ImageManager;
use Psr\Http\Message\UploadedFileInterface;

class UploadDefaultBlogImageController extends UploadImageController
{
    /**
     * {@inheritdoc}
     */
    protected $filePathSettingKey = 'blog_default_image_path';

    /**
     * {@inheritdoc}
     */
    protected $filenamePrefix = 'blog_default_image';

    /**
     * @var ImageManager
     */
    protected $imageManager;

    public function __construct(SettingsRepositoryInterface $settings, Factory $filesystemFactory, ImageManager $imageManager)
    {
        parent::__construct($settings, $filesystemFactory);

        $this->imageManager = $imageManager;
    }

    /**
     * Maximum stored width, in pixels. The default image is used as a full-width
     * "cover" hero background (see less/Forum/Item.less), so it needs to stay
     * crisp on large and hi-DPI/retina displays — hence a generous cap rather
     * than a thumbnail size. Taller-than-wide images are bounded by height too.
     */
    const MAX_WIDTH = 2000;

    const MAX_HEIGHT = 1200;

    /**
     * {@inheritdoc}
     */
    protected function makeImage(UploadedFileInterface $file): Image
    {
        $image = $this->imageManager->make($file->getStream()->getMetadata('uri'));

        // Downscale oversized uploads to keep stored assets reasonable, while
        // preserving aspect ratio and never upscaling smaller images.
        if ($image->width() > self::MAX_WIDTH || $image->height() > self::MAX_HEIGHT) {
            $image->resize(self::MAX_WIDTH, self::MAX_HEIGHT, function ($constraint) {
                $constraint->aspectRatio();
                $constraint->upsize();
            });
        }

        return $image->encode('png');
    }
}
