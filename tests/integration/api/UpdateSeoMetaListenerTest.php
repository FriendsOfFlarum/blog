<?php

/*
 * This file is part of fof/blog.
 *
 * Copyright (c) FriendsOfFlarum.
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace FoF\Blog\Tests\integration\api;

use Carbon\Carbon;
use Flarum\Discussion\Discussion;
use Flarum\Post\Post;
use Flarum\Testing\integration\RetrievesAuthorizedUsers;
use Flarum\Testing\integration\TestCase;
use Flarum\User\User;
use PHPUnit\Framework\Attributes\Test;

/**
 * Characterizes {@see \FoF\Blog\Listener\UpdateSeoMeta}: updating an article's
 * blog meta syncs the fof/seo record, and the Open Graph image is only touched
 * when this extension (or its v17 predecessor) owns it.
 */
class UpdateSeoMetaListenerTest extends TestCase
{
    use RetrievesAuthorizedUsers;

    public function setUp(): void
    {
        parent::setUp();

        $this->extension('flarum-tags', 'fof-seo', 'fof-blog');

        $now = Carbon::parse('2025-01-01 00:00:00');

        $this->prepareDatabase([
            User::class => [
                $this->normalUser(),
            ],
            Discussion::class => [
                ['id' => 1, 'title' => 'Article', 'slug' => 'article', 'user_id' => 1, 'first_post_id' => 1, 'comment_count' => 1, 'created_at' => $now, 'last_posted_at' => $now],
            ],
            Post::class => [
                ['id' => 1, 'discussion_id' => 1, 'number' => 1, 'user_id' => 1, 'type' => 'comment', 'content' => '<t><p>Body.</p></t>', 'created_at' => $now],
            ],
            'blog_meta' => [
                ['id' => 1, 'discussion_id' => 1, 'summary' => 'Original summary.', 'featured_image' => 'original.png', 'is_featured' => 0, 'is_sized' => 0, 'is_pending_review' => 0],
            ],
        ]);

        $this->setting('blog_tags', '1');
    }

    /**
     * @param array<string, mixed> $attributes
     */
    protected function patchMeta(array $attributes): void
    {
        $response = $this->send(
            $this->request('PATCH', '/api/blogMeta/1', [
                'authenticatedAs' => 1,
                'json'            => [
                    'data' => [
                        'type'       => 'blogMeta',
                        'id'         => '1',
                        'attributes' => $attributes,
                    ],
                ],
            ])
        );

        $this->assertEquals(200, $response->getStatusCode());
    }

    protected function seoMeta(): ?object
    {
        return $this->database()->table('seo_meta')->where('object_type', 'blogs')->where('object_id', 1)->first();
    }

    #[Test]
    public function updating_blog_meta_syncs_the_seo_record(): void
    {
        $this->patchMeta(['summary' => 'Updated summary.']);

        $seoMeta = $this->seoMeta();

        $this->assertNotNull($seoMeta, 'Expected a seo_meta record for the article');
        $this->assertSame('Updated summary.', $seoMeta->description);
        $this->assertSame('Article', $seoMeta->title);
    }

    #[Test]
    public function og_image_owned_by_this_extension_is_updated(): void
    {
        $this->prepareDatabase([
            'seo_meta' => [
                ['id' => 1, 'object_type' => 'blogs', 'object_id' => 1, 'auto_update_data' => 1, 'open_graph_image' => 'original.png', 'open_graph_image_source' => 'fof-blog', 'created_at' => Carbon::parse('2025-01-01 00:00:00')],
            ],
        ]);

        $this->patchMeta(['featuredImage' => 'new-cover.png']);

        $seoMeta = $this->seoMeta();

        $this->assertSame('new-cover.png', $seoMeta->open_graph_image);
        $this->assertSame('fof-blog', $seoMeta->open_graph_image_source);
    }

    #[Test]
    public function og_image_with_the_legacy_v17_source_is_reclaimed(): void
    {
        $this->prepareDatabase([
            'seo_meta' => [
                ['id' => 1, 'object_type' => 'blogs', 'object_id' => 1, 'auto_update_data' => 1, 'open_graph_image' => 'old.png', 'open_graph_image_source' => 'v17development-flarum-blog', 'created_at' => Carbon::parse('2025-01-01 00:00:00')],
            ],
        ]);

        $this->patchMeta(['featuredImage' => 'new-cover.png']);

        $seoMeta = $this->seoMeta();

        $this->assertSame('new-cover.png', $seoMeta->open_graph_image);
        // New writes stamp the migrated identifier.
        $this->assertSame('fof-blog', $seoMeta->open_graph_image_source);
    }

    #[Test]
    public function og_image_owned_by_another_extension_is_left_alone(): void
    {
        $this->prepareDatabase([
            'seo_meta' => [
                ['id' => 1, 'object_type' => 'blogs', 'object_id' => 1, 'auto_update_data' => 1, 'open_graph_image' => 'foreign.png', 'open_graph_image_source' => 'some-other-extension', 'created_at' => Carbon::parse('2025-01-01 00:00:00')],
            ],
        ]);

        $this->patchMeta(['featuredImage' => 'new-cover.png', 'summary' => 'Updated summary.']);

        $seoMeta = $this->seoMeta();

        // The description still syncs, but the foreign-owned image is untouched.
        $this->assertSame('Updated summary.', $seoMeta->description);
        $this->assertSame('foreign.png', $seoMeta->open_graph_image);
        $this->assertSame('some-other-extension', $seoMeta->open_graph_image_source);
    }
}
