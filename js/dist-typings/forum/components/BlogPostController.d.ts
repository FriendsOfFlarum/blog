import Component, { type ComponentAttrs } from 'flarum/common/Component';
import ItemList from 'flarum/common/utils/ItemList';
import Discussion from 'flarum/common/models/Discussion';
import type Mithril from 'mithril';
export interface BlogPostControllerAttrs extends ComponentAttrs {
    article: Discussion;
}
export default class BlogPostController extends Component<BlogPostControllerAttrs> {
    loadedPost: boolean;
    loading: boolean;
    manageArticleButtons(): ItemList<Mithril.Children>;
    view(vnode: Mithril.Vnode<BlogPostControllerAttrs, this>): JSX.Element;
}
