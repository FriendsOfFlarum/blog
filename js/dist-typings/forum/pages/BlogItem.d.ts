import Page, { IPageAttrs } from 'flarum/common/components/Page';
import type PostStreamType from 'flarum/forum/components/PostStream';
import PostStreamState from 'flarum/forum/states/PostStreamState';
import ItemList from 'flarum/common/utils/ItemList';
import Discussion from 'flarum/common/models/Discussion';
import type { ApiResponseSingle } from 'flarum/common/Store';
import type Mithril from 'mithril';
type Article = ApiResponseSingle<Discussion>;
export default class BlogItem extends Page {
    protected near: number;
    protected loading: boolean;
    protected found: boolean;
    protected article: Article | null;
    protected stream?: PostStreamState;
    /**
     * Core's `PostStream` lives in a lazy chunk, so it is imported on demand
     * rather than statically.
     */
    protected PostStream?: typeof PostStreamType;
    oninit(vnode: Mithril.Vnode<IPageAttrs, this>): void;
    loadBlogItem(): void;
    show(article: Article): void;
    postItems(): ItemList<Mithril.Children>;
    contentItems(): ItemList<Mithril.Children>;
    articleItems(): ItemList<Mithril.Children>;
    view(): (false | JSX.Element)[];
    positionChanged(startNumber: number, endNumber: number): void;
}
export {};
