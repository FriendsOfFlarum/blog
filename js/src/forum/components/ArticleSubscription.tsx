import app from 'flarum/forum/app';
import Component, { ComponentAttrs } from 'flarum/common/Component';
import Button from 'flarum/common/components/Button';
import extractText from 'flarum/common/utils/extractText';
import Tooltip from 'flarum/common/components/Tooltip';
import type Mithril from 'mithril';
import type Discussion from 'flarum/common/models/Discussion';

export interface ArticleSubscriptionAttrs extends ComponentAttrs {
  discussion: Discussion;
}

export default class ArticleSubscription extends Component<ArticleSubscriptionAttrs> {
  view(vnode: Mithril.Vnode<ArticleSubscriptionAttrs, this>): Mithril.Children {
    const currentSubscriptionState = this.attrs.discussion.subscription?.();

    const preferences = app.session.user!.preferences() ?? {};
    const notifyEmail = preferences['notify_newPost_email'];
    const notifyTooltip = extractText(
      app.translator.trans(
        notifyEmail ? 'flarum-subscriptions.forum.sub_controls.notify_email_tooltip' : 'flarum-subscriptions.forum.sub_controls.notify_alert_tooltip'
      )
    );

    let title: Mithril.Children = app.translator.trans('flarum-subscriptions.forum.sub_controls.not_following_button');
    let icon = 'far fa-star';

    // Following
    if (currentSubscriptionState === 'follow') {
      title = app.translator.trans('flarum-subscriptions.forum.sub_controls.following_button');
      icon = 'fas fa-star';
    }

    return (
      <Tooltip text={notifyTooltip} position="bottom">
        <Button
          className={`Button BlogArticleSubscription SubscriptionMenu-button--${currentSubscriptionState}`}
          icon={icon}
          onclick={this.saveSubscription.bind(
            this,
            this.attrs.discussion,
            ['follow', 'ignore'].indexOf(currentSubscriptionState as string) !== -1 ? null : 'follow'
          )}
        >
          {title}
        </Button>
      </Tooltip>
    );
  }

  saveSubscription(discussion: Discussion, subscription: 'follow' | 'ignore' | null): void {
    discussion.save({ subscription });
  }
}
