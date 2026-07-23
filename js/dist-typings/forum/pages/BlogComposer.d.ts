import Page, { IPageAttrs } from 'flarum/common/components/Page';
import Discussion from 'flarum/common/models/Discussion';
import Tag from 'ext:flarum/tags/common/models/Tag';
import Model from 'flarum/common/Model';
import ItemList from 'flarum/common/utils/ItemList';
import Stream from 'flarum/common/utils/Stream';
import type Mithril from 'mithril';
import BlogMeta from '../../common/models/BlogMeta';
/**
 * A `discussion-languages` record provided by the optional
 * `fof/discussion-language` extension.
 */
interface DiscussionLanguage extends Model {
    code(): string;
}
export default class BlogComposer extends Page<IPageAttrs> {
    protected languages: DiscussionLanguage[];
    protected articleLanguage: Stream<string>;
    protected article: Discussion;
    protected blogMeta: BlogMeta | null;
    protected tags: Tag[];
    protected isSaving: boolean;
    oninit(vnode: Mithril.Vnode<IPageAttrs, this>): void;
    openTagsModal(e?: Event | null): void;
    openNameArticleModal(e?: Event | null): void;
    openBlogSettings(e: Event): void;
    view(): JSX.Element;
    pageItems(): ItemList<Mithril.Children>;
    articleWrapperItems(): ItemList<Mithril.Children>;
    articleItems(): ItemList<Mithril.Children>;
    create(): void;
}
export {};
