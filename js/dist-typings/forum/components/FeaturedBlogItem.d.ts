import Component from 'flarum/common/Component';
import Discussion from 'flarum/common/models/Discussion';
import ItemList from 'flarum/common/utils/ItemList';
import type Mithril from 'mithril';
interface Attrs {
    article: Discussion;
    defaultImage: string;
}
export default class FeaturedBlogItem extends Component<Attrs> {
    topItems(): ItemList<Mithril.Children>;
    dataItems(): ItemList<Mithril.Children>;
    view(vnode: Mithril.Vnode<Attrs, this>): JSX.Element;
}
export {};
