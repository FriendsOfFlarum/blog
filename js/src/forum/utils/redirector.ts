import IndexPage from 'flarum/forum/components/IndexPage';
import DiscussionPage from 'flarum/forum/components/DiscussionPage';
import { extend, override } from 'flarum/common/extend';
import app from 'flarum/forum/app';
import type Discussion from 'flarum/common/models/Discussion';
import type Tag from 'flarum/tags/common/models/Tag';

export default function (): void {
  // Redirect tag to blog category
  extend(IndexPage.prototype, 'oncreate', function (this: IndexPage & { currentTag?: () => Tag | undefined }) {
    const tag = app.currentTag?.();
    const tagRedirectEnabled = app.forum.attribute('blogRedirectsEnabled') === 'both' || app.forum.attribute('blogRedirectsEnabled') === 'tags_only';

    // Only trigger when it's a tag page and the redirects are enabled
    if (tag && tagRedirectEnabled) {
      const blogTags = app.forum.attribute<string[]>('blogTags');

      // Tag is inside list
      const parent = tag.parent();
      if (blogTags.indexOf(tag.id()!) >= 0 || (parent && blogTags.indexOf(parent.id()!) >= 0)) {
        m.route.set(app.route('blog'));
      }
    }
  });

  // Redirect discussion to blog article
  override(DiscussionPage.prototype, 'show', function (this: DiscussionPage, original, discussion: Discussion, ...rest: unknown[]) {
    const discussionRedirectEnabled =
      app.forum.attribute('blogRedirectsEnabled') === 'both' || app.forum.attribute('blogRedirectsEnabled') === 'discussions_only';

    const tags = discussion.tags();

    if (discussionRedirectEnabled && discussion && tags && tags.length > 0) {
      const blogTags = app.forum.attribute<string[]>('blogTags');

      const foundTags = tags.filter((tag: Tag | undefined) => {
        const parent = tag?.parent();
        return (tag && blogTags.indexOf(tag.id()!) >= 0) || (parent && blogTags.indexOf(parent.id()!) >= 0);
      });

      // Only redirect if the discussion has blog tags
      if (foundTags.length > 0) {
        // Redirect to blog article
        const url = app.route('blogArticle', {
          id: discussion.slug(),
        });

        m.route.set(url, null, { replace: true });

        return null;
      }
    }

    // Forward every argument: show() also receives the page of posts that
    // core preloaded (embedded in the server-rendered document, or fetched in
    // parallel with the discussion). Dropping it makes the post stream
    // re-request posts core had already loaded.
    return original(...([discussion, ...rest] as Parameters<typeof original>));
  });
}
