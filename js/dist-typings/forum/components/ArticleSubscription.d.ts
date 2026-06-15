import Component, { ComponentAttrs } from 'flarum/common/Component';
import type Mithril from 'mithril';
import type Discussion from 'flarum/common/models/Discussion';
export interface ArticleSubscriptionAttrs extends ComponentAttrs {
    discussion: Discussion;
}
export default class ArticleSubscription extends Component<ArticleSubscriptionAttrs> {
    view(vnode: Mithril.Vnode<ArticleSubscriptionAttrs, this>): Mithril.Children;
    saveSubscription(discussion: Discussion, subscription: 'follow' | 'ignore' | null): void;
}
