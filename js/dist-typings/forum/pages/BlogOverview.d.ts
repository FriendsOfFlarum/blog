import Page, { IPageAttrs } from 'flarum/common/components/Page';
import BlogListState, { BlogListParams } from '../states/BlogListState';
import type Mithril from 'mithril';
import type Discussion from 'flarum/common/models/Discussion';
import type Model from 'flarum/common/Model';
export default class BlogOverview extends Page {
    protected list: BlogListState;
    protected languages: Model[];
    protected currentSelectedLanguage: string;
    protected featuredCount: number;
    protected showCategories: boolean;
    protected showForumNav: boolean;
    oninit(vnode: Mithril.Vnode<IPageAttrs, this>): void;
    protected listParams(): BlogListParams;
    /**
     * All loaded articles; the first `featuredCount` are displayed as featured.
     */
    protected articles(): Discussion[];
    title(): Mithril.Children;
    /**
     * The blog reuses the forum's welcome hero, when enabled.
     */
    hero(): Mithril.Children;
    view(): (JSX.Element | Mithril.Children)[];
    newArticle(): void;
}
