<?php

/*
 * This file is part of fof/seo.
 *
 * Copyright (c) FriendsOfFlarum.
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace FoF\Blog\Tests\integration\api;

use Flarum\Settings\SettingsRepositoryInterface;
use Flarum\Testing\integration\RetrievesAuthorizedUsers;
use Flarum\Testing\integration\TestCase;
use Illuminate\Contracts\Filesystem\Cloud;
use Illuminate\Contracts\Filesystem\Factory;
use Intervention\Image\ImageManager;
use Laminas\Diactoros\UploadedFile;

/**
 * Characterizes the admin default blog image upload/delete
 * ({@see \FoF\Blog\Api\Controller\UploadDefaultBlogImageController} and
 * {@see \FoF\Blog\Api\Controller\DeleteDefaultBlogImageController}): the image
 * is stored on the relocatable `flarum-assets` disk, oversized uploads are
 * downscaled within the caps (without upscaling small ones), and the
 * `blog_default_image_path` setting tracks the stored filename.
 */
class DefaultBlogImageTest extends TestCase
{
    use RetrievesAuthorizedUsers;

    public function setUp(): void
    {
        parent::setUp();

        $this->extension('fof-blog');

        $this->prepareDatabase([
            'users' => [
                $this->normalUser(),
            ],
        ]);
    }

    protected function assetsDisk(): Cloud
    {
        /** @var Cloud $disk */
        $disk = $this->app()->getContainer()->make(Factory::class)->disk('flarum-assets');

        return $disk;
    }

    protected function settings(): SettingsRepositoryInterface
    {
        return $this->app()->getContainer()->make(SettingsRepositoryInterface::class);
    }

    /**
     * Generates a real PNG of the given dimensions in a temp file and wraps it
     * as an uploaded file, the way the controller expects to receive it.
     */
    protected function uploadedPng(int $width, int $height): UploadedFile
    {
        $path = tempnam(sys_get_temp_dir(), 'blogimg').'.png';

        (new ImageManager())->canvas($width, $height, '#3498db')->save($path);

        return new UploadedFile($path, filesize($path), UPLOAD_ERR_OK, 'cover.png', 'image/png');
    }

    protected function upload(int $authenticatedAs, UploadedFile $file)
    {
        $request = $this->request('POST', '/api/blog_default_image', ['authenticatedAs' => $authenticatedAs])
            ->withUploadedFiles(['blog_default_image' => $file]);

        return $this->send($request);
    }

    /**
     * @test
     */
    public function admin_can_upload_a_default_blog_image_to_the_assets_disk(): void
    {
        $response = $this->upload(1, $this->uploadedPng(800, 600));

        $this->assertEquals(200, $response->getStatusCode());

        $path = $this->settings()->get('blog_default_image_path');

        $this->assertNotNull($path, 'Expected blog_default_image_path to be set');
        $this->assertTrue($this->assetsDisk()->exists($path), 'Expected the image to exist on the flarum-assets disk');
    }

    /**
     * @test
     */
    public function oversized_upload_is_downscaled_within_the_caps(): void
    {
        // Far larger than the 2000x1200 caps.
        $this->upload(1, $this->uploadedPng(5000, 4000));

        $path = $this->settings()->get('blog_default_image_path');
        $stored = $this->assetsDisk()->get($path);

        $image = (new ImageManager())->make($stored);

        $this->assertLessThanOrEqual(2000, $image->width());
        $this->assertLessThanOrEqual(1200, $image->height());
        // Aspect ratio (5:4) preserved: 2000 wide would be 1600 tall, so height caps first => 1200 tall, 1500 wide.
        $this->assertSame(1200, $image->height());
        $this->assertSame(1500, $image->width());
    }

    /**
     * @test
     */
    public function smaller_image_is_not_upscaled(): void
    {
        $this->upload(1, $this->uploadedPng(640, 480));

        $path = $this->settings()->get('blog_default_image_path');
        $image = (new ImageManager())->make($this->assetsDisk()->get($path));

        $this->assertSame(640, $image->width());
        $this->assertSame(480, $image->height());
    }

    /**
     * @test
     */
    public function admin_can_delete_the_default_blog_image(): void
    {
        $this->upload(1, $this->uploadedPng(800, 600));
        $path = $this->settings()->get('blog_default_image_path');
        $this->assertTrue($this->assetsDisk()->exists($path));

        $response = $this->send(
            $this->request('DELETE', '/api/blog_default_image', ['authenticatedAs' => 1])
        );

        $this->assertEquals(204, $response->getStatusCode());
        $this->assertNull($this->settings()->get('blog_default_image_path'));
        $this->assertFalse($this->assetsDisk()->exists($path), 'Expected the image to be removed from the disk');
    }

    /**
     * @test
     */
    public function non_admin_cannot_upload_a_default_blog_image(): void
    {
        // normalUser() (id 2) is not an admin.
        $response = $this->upload(2, $this->uploadedPng(800, 600));

        $this->assertEquals(403, $response->getStatusCode());
    }
}
