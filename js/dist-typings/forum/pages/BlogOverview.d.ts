import Page, { IPageAttrs } from 'flarum/common/components/Page';
import type Mithril from 'mithril';
import type Discussion from 'flarum/common/models/Discussion';
import type Model from 'flarum/common/Model';
import type { ApiResponsePlural } from 'flarum/common/Store';
export default class BlogOverview extends Page {
    protected isLoading: boolean;
    protected isLoadingMore: boolean;
    protected featuredPosts: Discussion[];
    protected posts: Discussion[];
    protected hasMore: string | null;
    protected languages: Model[];
    protected currentSelectedLanguage: string;
    protected featuredCount: number;
    protected showCategories: boolean;
    protected showForumNav: boolean;
    oninit(vnode: Mithril.Vnode<IPageAttrs, this>): void;
    loadBlogOverview(): void;
    reloadData(): void;
    show(articles: ApiResponsePlural<Discussion>): void;
    loadMore(): void;
    title(): Mithril.Children;
    view(): (false | JSX.Element)[];
    newArticle(): void;
}
