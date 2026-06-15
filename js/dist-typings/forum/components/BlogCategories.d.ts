import Component, { type ComponentAttrs } from 'flarum/common/Component';
import type Tag from 'flarum/tags/common/models/Tag';
import type Mithril from 'mithril';
export interface BlogCategoriesAttrs extends ComponentAttrs {
}
export default class BlogCategories extends Component<BlogCategoriesAttrs> {
    blogCategories: string[] | null;
    oninit(vnode: Mithril.Vnode<BlogCategoriesAttrs, this>): void;
    view(): Mithril.Children;
    categoryItem(tag: Tag): Mithril.Children;
}
