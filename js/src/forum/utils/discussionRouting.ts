import app from 'flarum/forum/app';
import Discussion from 'flarum/common/models/Discussion';
import type Tag from 'flarum/tags/common/models/Tag';

export default function (): void {
  // Save the original function before we override it
  const original_discussion_route = app.route.discussion;

  /**
   * Generate a URL to a discussion OR a Blog Article.
   *
   * CORE_CODE_OVERRIDE: This overrides the standard function from flarum/core.
   * The code is inspired from js/src/forum/routes.js and now handles different types of discussions.
   * It will try to keep the original function executed if the discussion being
   * processed isn't a blog article.
   */
  app.route.discussion = (discussion: Discussion, near?: number): string => {
    const discussionRedirectEnabled =
      app.forum.attribute<string>('blogRedirectsEnabled') === 'both' || app.forum.attribute<string>('blogRedirectsEnabled') === 'discussions_only';

    let shouldRedirect = false;

    // `tags()` returns `false` when the tags relationship isn't loaded (issue #170),
    // so guard against it before treating the result as an array.
    const tags: Tag[] = (discussion.tags() || []).filter((tag): tag is Tag => Boolean(tag));

    if (discussionRedirectEnabled && tags.length > 0) {
      const blogTags = app.forum.attribute<string[]>('blogTags');

      const foundTags = tags.filter((tag) => {
        const parent = tag.parent();
        return blogTags.indexOf(tag.id()!) >= 0 || (parent && blogTags.indexOf(parent.id()!) >= 0);
      });

      if (foundTags.length > 0) {
        shouldRedirect = true;
      }
    }

    if (shouldRedirect) {
      return (discussion.lastReadPostNumber() ?? 0) > 1
        ? app.route('blogArticle.near', {
            id: discussion.slug(),
            near: discussion.lastReadPostNumber(),
          })
        : app.route('blogArticle', {
            id: discussion.slug(),
          });
    } else {
      return original_discussion_route(discussion, near);
    }
  };
}
