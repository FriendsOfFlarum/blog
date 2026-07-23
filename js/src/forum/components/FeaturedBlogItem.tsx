import Component from 'flarum/common/Component';
import Link from 'flarum/common/components/Link';
import Tooltip from 'flarum/common/components/Tooltip';
import humanTime from 'flarum/common/helpers/humanTime';
import Icon from 'flarum/common/components/Icon';
import Discussion from 'flarum/common/models/Discussion';
import ItemList from 'flarum/common/utils/ItemList';
import classList from 'flarum/common/utils/classList';
import app from 'flarum/forum/app';
import type Mithril from 'mithril';

interface Attrs {
  article: Discussion;
  defaultImage: string;
}

export default class FeaturedBlogItem extends Component<Attrs> {
  topItems(): ItemList<Mithril.Children> {
    const { article } = this.attrs;
    const blogMeta = article.blogMeta();
    const tags = article.tags() || [];

    const items = new ItemList<Mithril.Children>();

    items.add(
      'tags',
      <span class="BlogFeatured-list-item-tags">
        {tags.map((tag) => (
          <span class="dataItem">{tag?.name()}</span>
        ))}
      </span>,
      100
    );

    // Sticky is an optional dependency, so we can't
    // assume method existence.
    if (article.isSticky?.()) {
      items.add(
        'sticky',
        <span class="BlogFeatured-list-item-isSticky dataItem">
          <Icon name="fas fa-thumbtack" />
        </span>,
        80
      );
    }

    if ((blogMeta && blogMeta.isPendingReview()) || article.isHidden()) {
      items.add(
        'hidden',
        <span class="BlogFeatured-list-item-isHidden dataItem">
          <Icon name="fas fa-eye-slash" />
        </span>,
        60
      );
    }

    if (blogMeta && blogMeta.isPendingReview()) {
      items.add(
        'pendingReview',
        <Tooltip text={app.translator.trans('fof-blog.forum.review_article.pending_review')} position="bottom">
          <span class="BlogFeatured-list-item-pendingReview dataItem">
            <Icon name="far fa-clock" /> {app.translator.trans('fof-blog.forum.review_article.pending_review_title')}
          </span>
        </Tooltip>,
        40
      );
    }

    return items;
  }

  dataItems(): ItemList<Mithril.Children> {
    const { article } = this.attrs;

    const items = new ItemList<Mithril.Children>();

    const createdAt = article.createdAt();
    const user = article.user();

    items.add(
      'createdAt',
      <span class="BlogFeatured-list-item-details-createdAt">
        <Icon name="far fa-clock" /> {createdAt ? humanTime(createdAt) : ''}
      </span>,
      100
    );

    items.add(
      'author',
      <span class="BlogFeatured-list-item-details-author">
        <Icon name="far fa-user" /> {(user && user.displayName()) || app.translator.trans('core.lib.username.deleted_text')}
      </span>,
      80
    );

    items.add(
      'replies',
      <span class="BlogFeatured-list-item-details-replies">
        <Icon name="far fa-comment" /> {(article.commentCount() || 0) - 1}
      </span>,
      60
    );

    return items;
  }

  view(vnode: Mithril.Vnode<Attrs, this>) {
    const { article, defaultImage } = this.attrs;
    const blogMeta = article.blogMeta();
    const tags = article.tags() || [];

    const featuredImage = blogMeta ? blogMeta.featuredImage() : null;
    const blogImage = featuredImage ? `url(${featuredImage})` : defaultImage;

    return (
      <Link
        href={app.route('blogArticle', {
          id: `${article.slug()}`,
        })}
        className={classList(
          'BlogFeatured-list-item',
          tags.map((tag) => `BlogFeatured-list-item-category-${tag?.id()}`),
          'FlarumBlog-default-image'
        )}
        style={{ backgroundImage: blogImage }}
      >
        <div class="BlogFeatured-list-item-top">{this.topItems().toArray()}</div>

        <div className={'BlogFeatured-list-item-details'}>
          <h4>{article.title()}</h4>

          <div className={'data'}>{this.dataItems().toArray()}</div>
        </div>
      </Link>
    );
  }
}
