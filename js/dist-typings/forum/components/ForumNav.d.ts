import Component, { ComponentAttrs } from 'flarum/common/Component';
import type ItemList from 'flarum/common/utils/ItemList';
import type Mithril from 'mithril';
export interface ForumNavAttrs extends ComponentAttrs {
}
export default class ForumNav extends Component<ForumNavAttrs> {
    view(vnode: Mithril.Vnode<ForumNavAttrs, this>): Mithril.Children;
    navItems(): ItemList<Mithril.Children>;
}
