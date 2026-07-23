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
    requestParams(): PaginatedListRequestParams;
}
