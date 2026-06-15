# Installation

## Install

Require the package with Composer:

```bash
composer require fof/blog
```

Then enable **FoF Blog** from the *Administration → Extensions* page (or with
`php flarum extension:enable fof-blog`).

[`flarum/tags`](https://github.com/flarum/tags) is required and must be enabled —
blog articles are discussions in designated tags.

## First-time setup

1. Go to *Administration → Extensions → FoF Blog*.
2. Under **Blog tags**, choose which tag(s) hold your blog articles. Any
   discussion in one of these tags is treated as a blog article.
3. Grant the **Write articles** permission to the groups who should be able to
   publish (see [Permissions & the review workflow](permissions.md)).

That's it — visit `/blog` to see the overview.

## Upgrading from `v17development/flarum-blog`

This package declares `replace` for `v17development/flarum-blog`, so Composer
swaps it out automatically. Your settings and existing blog articles are
preserved.

```bash
composer remove v17development/flarum-blog
composer require fof/blog
php flarum migrate
php flarum cache:clear
```

Third-party extensions integrating with the blog should update references from
the `V17Development\FlarumBlog` PHP namespace to `FoF\Blog`, and from the
`v17development/blog` / `v17development-flarum-blog` frontend modules to
`fof/blog`. See the [developer reference](developers.md).

> **SEO:** SEO integration now targets [`fof/seo`](https://github.com/FriendsOfFlarum/seo)
> (`FoF\Seo`). If you want SEO tags for your blog pages, install and enable it.

## Optional companions

| Extension | Adds |
| --- | --- |
| [`fof/seo`](https://github.com/FriendsOfFlarum/seo) | SEO meta & structured data for blog pages |
| [`fof/upload`](https://github.com/FriendsOfFlarum/upload) | Pick a featured image from uploaded files |
| [`fof/rich-text`](https://github.com/FriendsOfFlarum/rich-text) | Rich-text editing in the composer |
| [`fof/discussion-language`](https://github.com/FriendsOfFlarum/discussion-language) | Per-language blog overviews |
| [`flarum/sticky`](https://github.com/flarum/sticky) | Sticky indicator on articles |
