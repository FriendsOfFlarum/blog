import DiscussionListState, { DiscussionListParams } from 'flarum/forum/states/DiscussionListState';
import type { PaginatedListRequestParams } from 'flarum/common/states/PaginatedListState';

export interface BlogListParams extends DiscussionListParams {
  /**
   * Slug of the blog category (tag) to list, if any.
   */
  category?: string;

  /**
   * fof/discussion-language language code to filter by, if enabled.
   */
  language?: string;
}

/**
 * Paginated list of blog articles, newest first.
 */
export default class BlogListState extends DiscussionListState<BlogListParams> {
  requestParams(): PaginatedListRequestParams {
    const filter: Record<string, string> = { blog: 'true' };

    if (this.params.category) filter.tag = this.params.category;
    if (this.params.language) filter.language = this.params.language;

    return {
      // Explicit includes replace the endpoint defaults, so everything the
      // overview items render must be listed here.
      include: ['user', 'tags', 'tags.parent', 'blogMeta'],
      filter,
      sort: '-createdAt',
    };
  }
}
