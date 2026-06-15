import Component from 'flarum/common/Component';
import ItemList from 'flarum/common/utils/ItemList';
import Discussion from 'flarum/common/models/Discussion';
import User from 'flarum/common/models/User';
import type Mithril from 'mithril';
interface BlogAuthorAttrs {
    loading?: boolean;
    article?: Discussion;
    user?: User;
}
export default class BlogAuthor extends Component<BlogAuthorAttrs> {
    view(vnode: Mithril.Vnode<BlogAuthorAttrs, this>): JSX.Element;
    items(): ItemList<Mithril.Children>;
}
export {};
