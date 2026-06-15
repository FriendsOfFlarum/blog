# FoF Blog — Documentation

FoF Blog turns a set of tagged discussions into a fully-featured blog: a blog
overview page, dedicated article pages, an article composer, featured posts, an
optional review/approval workflow, and SEO integration.

> This extension is the FriendsOfFlarum continuation of the original
> `v17development/flarum-blog` extension and replaces it.

## What it does

- **Blog overview** at `/blog`, plus per-category overviews and an article
  composer at `/blog/compose`.
- **Article pages** at `/blog/{id}` rendered from the discussion and its first
  post, with an author sidebar, categories and (optionally) comments.
- **Tag-driven** — any discussion in one of the configured *blog tags* becomes a
  blog article. Existing discussions work retroactively.
- **Per-article metadata** — a summary, a featured image, a "highlighted"
  (featured) flag and a "sized" display flag, stored alongside the discussion.
- **Review workflow** — optionally require articles to be approved before they go
  live, gated by dedicated permissions.
- **Configurable presentation** — featured-post count, category hierarchy, hero,
  sidebar navigation, a default article image, and whether the blog replaces the
  forum home page.
- **SEO** — when [`fof/seo`](https://github.com/FriendsOfFlarum/seo) is enabled,
  blog pages emit tailored meta tags and structured data.

## Documentation

### Using the extension

- [Installation](installation.md)
- [Features & configuration](features.md)
- [Permissions & the review workflow](permissions.md)

### For extension developers

- [Developer reference](developers.md)

## Requirements

- Flarum `^1.8`
- PHP `^8.2`
- [`flarum/tags`](https://github.com/flarum/tags)
