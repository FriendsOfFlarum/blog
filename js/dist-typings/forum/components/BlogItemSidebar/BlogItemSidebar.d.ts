import Component, { ComponentAttrs } from 'flarum/common/Component';
import ItemList from 'flarum/common/utils/ItemList';
import Discussion from 'flarum/common/models/Discussion';
import type Mithril from 'mithril';
export interface BlogItemSidebarAttrs extends ComponentAttrs {
    article: Discussion | null;
    loading: boolean;
}
export default class BlogItemSidebar extends Component<BlogItemSidebarAttrs> {
    view(vnode: Mithril.Vnode<BlogItemSidebarAttrs, this>): JSX.Element;
    items(): ItemList<Mithril.Children>;
}
