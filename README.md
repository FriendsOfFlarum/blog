# FoF Blog

[![MIT license](https://img.shields.io/badge/license-MIT-blue.svg)](https://github.com/FriendsOfFlarum/blog/blob/1.x/LICENSE.md) [![Latest Stable Version](https://img.shields.io/packagist/v/fof/blog.svg)](https://packagist.org/packages/fof/blog) [![Total Downloads](https://img.shields.io/packagist/dt/fof/blog.svg)](https://packagist.org/packages/fof/blog)

A [Flarum](https://flarum.org) extension that adds a blog section to your forum — a dedicated blog overview, article pages, and an article composer, built on top of your existing discussions and tags.

## Installation

```sh
composer require fof/blog:"*"
```

## Updating

```sh
composer update fof/blog
php flarum cache:clear
```

## Migrating from v17development/flarum-blog

This extension was transferred to FriendsOfFlarum and was previously published as `v17development/flarum-blog`.

For forum admins, migration is a one-line change — swap the package, keep your settings and existing blog articles:

```sh
composer remove v17development/flarum-blog
composer require fof/blog
php flarum cache:clear
```

`fof/blog` declares `replace: { "v17development/flarum-blog": "*" }`, so anything that depended on the old package is satisfied by the new one, and the two can never be installed at once.

Third-party extension authors should update any references from the `V17Development\FlarumBlog` PHP namespace to `FoF\Blog`, and from the `v17development/blog` / `v17development-flarum-blog` frontend modules to `fof/blog`.

> **Note:** SEO integration now targets [`fof/seo`](https://github.com/FriendsOfFlarum/seo) (`FoF\Seo`). If you relied on SEO tags for your blog, ensure `fof/seo` is installed and enabled.

## Features

- Adds a **Blog overview** page and **article detail** pages to your forum
- An article **composer** for writing blog posts
- Builds on the [`flarum/tags`](https://github.com/flarum/tags) extension — articles are tag-driven discussions
- Per-article **summary** and **featured image**
- **Highlighted** (featured) posts on the overview
- **Categories** sidebar, with optional category hierarchy
- Optionally disable or allow **comments** by default
- Optional **review workflow** — articles can require approval before publishing, with dedicated write/approve permissions
- Set the **blog overview as the forum home page**
- Works with already-existing discussions/articles
- Ghost (skeleton) loading states
- Configurable **default article image**, stored on the `flarum-assets` filesystem disk

## Works with

Compatible — but not required — alongside:

- [flarum/tags](https://github.com/flarum/tags) — required; articles are tag-based
- [flarum/sticky](https://github.com/flarum/sticky) — sticky indicator on articles
- [fof/seo](https://github.com/FriendsOfFlarum/seo) — SEO meta & structured data for blog pages
- [fof/upload](https://github.com/FriendsOfFlarum/upload) — pick a featured image from your uploaded files
- [fof/rich-text](https://github.com/FriendsOfFlarum/rich-text) — rich-text editing in the composer
- [fof/discussion-language](https://github.com/FriendsOfFlarum/discussion-language) — per-language blog overviews

## Screenshots

### Blog overview page

[![Blog overview](https://i.gyazo.com/dfbba7a46aa153d8c6905733bd9b58c0.gif)](https://gyazo.com/dfbba7a46aa153d8c6905733bd9b58c0)

### Blog article

[![Blog article](https://i.gyazo.com/32e901c6aa4cc85144777d16756ec7b0.gif)](https://gyazo.com/32e901c6aa4cc85144777d16756ec7b0)

### Blog tools

[![Blog tools](https://i.imgur.com/xa8izBD.png)](https://imgur.com/a/zwClPHd)

### Blog settings

[![Blog settings](https://i.imgur.com/Iyca8AJ.png)](https://imgur.com/a/mnxRtBh)

### Admin settings

[![Admin settings](https://i.imgur.com/0F0XvYk.png)](https://imgur.com/a/QMTn3Ud)

## Credits

This extension was originally created and maintained by [V17 Development](https://v17.dev) as `v17development/flarum-blog`. FriendsOfFlarum is grateful for their work and continues development with their blessing.

Sponsored by [Glowing Blue](https://glowingblue.com/).

## Links

- [Packagist](https://packagist.org/packages/fof/blog)
- [GitHub](https://github.com/FriendsOfFlarum/blog)
- [Issues](https://github.com/FriendsOfFlarum/blog/issues)
- [Support](https://discuss.flarum.org/d/39435)

## License

This extension is licensed under the MIT License. See the [LICENSE.md](https://github.com/FriendsOfFlarum/blog/blob/1.x/LICENSE.md) file for details.
