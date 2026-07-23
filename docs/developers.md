# Developer reference

## Namespaces & modules

| | Value |
| --- | --- |
| PHP namespace | `FoF\Blog\` |
| Composer package | `fof/blog` |
| Extension ID | `fof-blog` |
| Frontend modules | `ext:fof/blog/<frontend>/<path>` (e.g. `import BlogMeta from 'ext:fof/blog/common/models/BlogMeta'`) |
| CSS class prefix | `FoFBlog-` |

> Migrating integrations from the old extension? Replace `V17Development\FlarumBlog`
> with `FoF\Blog`, and the `v17development/blog` / `v17development-flarum-blog`
> frontend references with `fof/blog`. Since 2.0 the old `FlarumBlog-` /
> `Flarum-Blog-` / `V17Blog-` CSS classes are consolidated under `FoFBlog-`.

## The `BlogMeta` model

`FoF\Blog\BlogMeta` holds the per-article metadata, stored in the
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

`BlogMeta` is a JSON:API resource of type `blogMeta`. Single-discussion
responses (`Show`/`Create`/`Update`) include it by default; on the discussion
*index*, request it explicitly:

```
GET /api/discussions?include=blogMeta
```

Serialized attributes: `featuredImage`, `summary`, `isFeatured`, `isSized`,
`isPendingReview` (the boolean flags are cast to real booleans).

| Endpoint | Purpose |
| --- | --- |
| `POST /api/blogMeta` | Create blog metadata for an existing discussion (upserts — a discussion only ever has one meta record) |
| `PATCH /api/blogMeta/{id}` | Update blog metadata |
| `POST /api/blog_default_image` | Upload the default article image (admin) |
| `DELETE /api/blog_default_image` | Remove the default article image (admin) |

Notes on the write model:

- Creating an article via `POST /api/discussions` may carry the metadata inline
  as the write-only `newBlogMeta` attribute (`{featuredImage, summary, isSized}`
  or `null`). It is consumed server-side and never serialized back — responses
  carry the `blogMeta` relationship instead.
- On `PATCH /api/blogMeta/{id}`, `isPendingReview` is always accepted but only
  takes effect when an approver publishes a *pending* article; for anyone else
  the flag is silently ignored. Content fields (`summary`, `featuredImage`,
  `isFeatured`, `isSized`) are writable by writers only.
- Review state on create is computed server-side: discussions older than 30
  seconds (conversions of existing discussions) are auto-approved; fresh ones
  follow the **Require review** setting and the author's auto-approve
  permission.

Blog metadata is also created automatically: when a discussion is saved into a
blog tag, a `BlogMeta` row is created (subject to the `blog.writeArticles`
permission and the review settings).

## Events

All events live in `FoF\Blog\Event` and carry the `BlogMeta` (plus the acting
`User` where available):

| Event | Fired |
| --- | --- |
| `BlogMetaCreated` | After a meta record is created (a discussion became a blog article) |
| `BlogMetaSaving` | Before a meta record is saved via the API — mutate it or veto with an exception |
| `BlogMetaUpdated` | After meta *content* changed (`changed` lists the modified attributes; excludes pure review/featured transitions) |
| `ArticleApproved` | After a pending article was published |
| `ArticleFeatured` / `ArticleUnfeatured` | After an article was (un)featured |

## Authorization

Abilities are defined on `FoF\Blog\Access\BlogMetaPolicy`: `update` (writers or
approvers may hit the update endpoint), `edit` (meta content is writer-only) and
`approve` (publishing is approver-only). See
[Permissions & the review workflow](permissions.md).

## Audit integration

When [`flarum/audit`](https://github.com/flarum/audit) is enabled, the blog
registers the actions `article.created`, `article.approved`, `article.featured`,
`article.unfeatured` and `article.updated` (grouped under **fof-blog** in the
audit settings), each carrying the `discussion_id` — `article.updated` also
records the changed fields. The integration is registered conditionally via
`Extend\Conditional`, mirroring the SEO integration.

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
