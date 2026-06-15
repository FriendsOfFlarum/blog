import Component from 'flarum/common/Component';
import Discussion from 'flarum/common/models/Discussion';
import ItemList from 'flarum/common/utils/ItemList';
import type Mithril from 'mithril';
interface Attrs {
    article: Discussion;
    defaultImage: string;
}
export default class BlogOverviewItem extends Component<Attrs> {
    titleItems(): ItemList<Mithril.Children>;
    dataItems(): ItemList<Mithril.Children>;
    contentItems(): ItemList<Mithril.Children>;
    getImage(): string;
    view(vnode: Mithril.Vnode<Attrs, this>): JSX.Element;
}
export {};
