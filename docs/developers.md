# Developer reference

## Namespaces & modules

| | Value |
| --- | --- |
| PHP namespace | `FoF\Blog\` |
| Composer package | `fof/blog` |
| Extension ID | `fof-blog` |
| Frontend module | `fof/blog` |

> Migrating integrations from the old extension? Replace `V17Development\FlarumBlog`
> with `FoF\Blog`, and the `v17development/blog` / `v17development-flarum-blog`
> frontend references with `fof/blog`.

## The `BlogMeta` model

`FoF\Blog\BlogMeta\BlogMeta` holds the per-article metadata, stored in the
`blog_meta` table and related one-to-one to a `Discussion` via `discussion_id`.

| Attribute | Type | Notes |
| --- | --- | --- |
| `discussion_id` | int | Owning discussion |
| `featured_image` | string\|null | Cover image URL |
| `summary` | string\|null | Overview excerpt |
| `is_featured` | bool | Highlighted on the overview |
| `is_sized` | bool | Display/layout flag |
| `is_pending_review` | bool | Awaiting approval |

A `Discussion` gains a `blogMeta` relationship (registered via `Extend\Model`),
so `$discussion->blogMeta` returns the `BlogMeta` or `null`.

## API

`BlogMeta` is serialized as the `blogMeta` resource and included on the
discussion endpoints. Request it with `?include=blogMeta`:

```
GET /api/discussions/{id}?include=blogMeta
```

Serialized attributes: `featuredImage`, `summary`, `isFeatured`, `isSized`,
`isPendingReview` (the boolean flags are cast to real booleans).

| Endpoint | Purpose |
| --- | --- |
| `POST /api/blogMeta` | Create blog metadata for a discussion |
| `PATCH /api/blogMeta/{id}` | Update blog metadata |
| `POST /api/blog_default_image` | Upload the default article image (admin) |
| `DELETE /api/blog_default_image` | Remove the default article image (admin) |

Blog metadata is also created automatically: when a discussion is saved into a
blog tag, a `BlogMeta` row is created (subject to the `blog.writeArticles`
permission and the review settings).

## Forum attributes

The Forum serializer is extended with these attributes, read by the frontend:

`blogTags`, `blogRedirectsEnabled`, `blogCommentsEnabled`, `blogHideTags`,
`blogDefaultImage`, `blogDefaultImageUrl`, `blogCategoryHierarchy`,
`blogAddSidebarNav`, `blogFeaturedCount`, `blogAddHero`, `canWriteBlogPosts`,
`canApproveBlogPosts`.

> `blogDefaultImageUrl` is resolved server-side from the `flarum-assets`
> filesystem disk, so it reflects a relocated/cloud disk. Prefer it over building
> a URL from `blogDefaultImage` (the raw path) yourself.

## SEO integration

When `fof/seo` is enabled, the blog registers SEO page drivers conditionally via
`Extend\Conditional()->whenExtensionEnabled('fof-seo', …)`, so it never loads SEO
classes when the extension is absent or disabled. The drivers
(`FoF\Blog\SeoPage\*`) implement `FoF\Seo\Page\PageDriverInterface` and populate
`FoF\Seo\SeoProperties` for the blog overview and article routes.

## Default image storage

The default article image is written through the `flarum-assets` filesystem disk
(via `Illuminate\Contracts\Filesystem\Factory`), not a hardcoded local path, so
it follows wherever assets are configured (including cloud storage). Uploads are
downscaled to a maximum of 2000×1200, preserving aspect ratio and never
upscaling.

## Running the tests

```bash
composer test          # unit + integration
composer test:unit
composer test:integration
composer analyse:phpstan
```

Integration tests require a database; run `composer test:setup` once to
initialise it (see [Flarum testing docs](https://docs.flarum.org/extend/testing)).
