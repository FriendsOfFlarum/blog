# Permissions & the review workflow

FoF Blog adds its own permission category (**Blog**) under
*Administration → Permissions*. By default, all three permissions are granted to
**Moderators**.

## Permissions

| Permission | Key | Allows |
| --- | --- | --- |
| **Write articles** | `blog.writeArticles` | Create blog articles (create a discussion in a blog tag). Without it, creating a blog-tagged discussion is denied. |
| **Auto-approve posts** | `blog.autoApprovePosts` | Bypass the review queue — articles by these users are published immediately even when review is required. |
| **Approve posts** | `blog.canApprovePosts` | Approve articles that are pending review. |

These are surfaced to the frontend as the `canWriteBlogPosts` and
`canApproveBlogPosts` forum attributes.

## The review workflow

When **Require review** is enabled in the blog settings, a new article is created
in a *pending review* state and is hidden from the public overview until
approved — **unless** its author has **auto-approve posts**, in which case it is
published straight away.

The flow:

1. A user with **write articles** publishes an article.
2. If review is required and the author lacks **auto-approve posts**, the
   article's `is_pending_review` flag is set.
3. A user with **approve posts** approves it (clearing the flag), and it goes
   live.

If **Require review** is off, articles are published immediately on creation
(subject only to **write articles**).

## How it's enforced

Article creation is gated server-side: when a discussion is saved into a blog
tag, the extension checks `blog.writeArticles` and throws a permission error if
the actor lacks it. The pending-review flag is then computed from the
**Require review** setting and the author's **auto-approve posts** permission.
