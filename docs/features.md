# Features & configuration

All settings live on the *Administration → Extensions → FoF Blog* page.

## Blog tags

The core of the extension. Choose one or more tags to act as **blog tags**. Any
discussion in one of these tags becomes a blog article and appears on the blog
overview. Existing discussions in those tags are picked up automatically.

## Article metadata

Each blog article carries its own metadata, edited from the **article settings**
dialog (the cog on an article) or set when composing:

- **Summary** — a short excerpt shown on the overview cards.
- **Featured image** — a URL shown as the article's cover. With
  [`fof/upload`](https://github.com/FriendsOfFlarum/upload) enabled you can pick
  one from your uploaded files; otherwise paste any image URL.
- **Highlighted** — marks the article as *featured*, surfacing it in the
  featured row at the top of the overview.
- **Sized** — a display flag controlling the article card's layout.

## Presentation settings

| Setting | What it controls |
| --- | --- |
| **Featured count** | How many highlighted articles appear in the featured row (default 3). |
| **Add hero** | Show the forum hero above the blog overview. |
| **Add sidebar navigation** | Add a link to the blog in the index sidebar. |
| **Category hierarchy** | Render blog categories as a parent/child hierarchy. |
| **Hide tags in list** | Hide the blog tags from the normal discussion list. |
| **Default article image** | A fallback cover image used when an article has none. Stored on the `flarum-assets` filesystem disk and downscaled to sane dimensions on upload. |

## Comments

By default, articles allow comments (they are ordinary discussions underneath).
Disable **allow comments** to lock new articles on creation, turning them into
comment-free posts.

## Redirects

The **redirects** setting controls how the blog and the normal forum interact:

- **both** *(default)* — blog discussions redirect to their blog article, and
  blog tag pages redirect to the blog overview.
- **discussions only** — only discussion → article redirects.
- **tags only** — only tag page → blog overview redirects.
- **none** — no redirects.

## Blog as home page

You can set the blog overview as the forum's home page from
*Administration → Basics → Home page*, where **Blog** appears as an option.

## SEO

When [`fof/seo`](https://github.com/FriendsOfFlarum/seo) is installed **and
enabled**, blog overview and article pages emit tailored SEO metadata
(title, description from the article summary, the featured or default image, and
`BlogPosting` structured data). The integration is registered conditionally, so
the blog works fine without SEO installed.
