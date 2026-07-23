import Component from 'flarum/common/Component';
import Link from 'flarum/common/components/Link';
import Tooltip from 'flarum/common/components/Tooltip';
import humanTime from 'flarum/common/helpers/humanTime';
import Icon from 'flarum/common/components/Icon';
import Discussion from 'flarum/common/models/Discussion';
import classList from 'flarum/common/utils/classList';
import ItemList from 'flarum/common/utils/ItemList';
import app from 'flarum/forum/app';
import type Mithril from 'mithril';

interface Attrs {
  article: Discussion;
  defaultImage: string;
}

export default class BlogOverviewItem extends Component<Attrs> {
  titleItems(): ItemList<Mithril.Children> {
    const { article } = this.attrs;
    const blogMeta = article.blogMeta();

    const items = new ItemList<Mithril.Children>();

    items.add('title', <>{article.title()}</>, 100);

    if ((blogMeta && blogMeta.isPendingReview()) || article.isHidden()) {
      items.add('hidden', <Icon name="fas fa-eye-slash" class="BlogList-item-hidden" />, 80);
    }

    if (blogMeta && blogMeta.isPendingReview()) {
      items.add(
        'pendingReview',
        <Tooltip text={app.translator.trans('fof-blog.forum.review_article.pending_review')}>
          <Icon name="far fa-clock" class="BlogList-item-pendingReview" />
        </Tooltip>,
        40
      );
    }

    return items;
  }

  dataItems(): ItemList<Mithril.Children> {
    const { article } = this.attrs;
    const createdAt = article.createdAt();
    const user = article.user();

    const items = new ItemList<Mithril.Children>();

    items.add(
      'createdAt',
      <span class="BlogList-item-details-createdAt">
        <Icon name="far fa-clock" /> {createdAt ? humanTime(createdAt) : ''}
      </span>,
      100
    );

    items.add(
      'author',
      <span class="BlogList-item-details-author">
        <Icon name="far fa-user" /> {(user && user.displayName()) || app.translator.trans('core.lib.username.deleted_text')}
      </span>,
      80
    );

    items.add(
      'replies',
      <span class="BlogList-item-details-replies">
        <Icon name="far fa-comment" /> {(article.commentCount() || 1) - 1}
      </span>,
      60
    );

    return items;
  }

  contentItems(): ItemList<Mithril.Children> {
    const { article } = this.attrs;
    const blogMeta = article.blogMeta();
    const summary = (blogMeta && blogMeta.summary()) || '';

    const items = new ItemList<Mithril.Children>();

    items.add('title', <h4>{this.titleItems().toArray()}</h4>, 100);

    if (summary) items.add('summary', <p>{summary}</p>, 80);

    items.add('data', <div class="data">{this.dataItems().toArray()}</div>, 60);

    return items;
  }

  getImage(): string {
    const { article, defaultImage } = this.attrs;
    const blogMeta = article.blogMeta();

    const featuredImage = blogMeta ? blogMeta.featuredImage() : null;

    return featuredImage ? `url(${featuredImage})` : defaultImage;
  }

  view(vnode: Mithril.Vnode<Attrs, this>) {
    const { article, defaultImage } = this.attrs;
    const blogMeta = article.blogMeta();
    const tags = article.tags() || [];

    const blogImage = this.getImage();

    const isSized = blogMeta ? blogMeta.isSized() : false;

    return (
      <Link
        href={app.route('blogArticle', {
          id: `${article.slug()}`,
        })}
        className={classList(
          'BlogList-item',
          {
            'BlogList-item-sized': isSized,
            'BlogList-item-default': !isSized,
          },
          tags.map((tag) => `BlogList-item-category-${tag?.id()}`)
        )}
      >
        <div
          class={classList('BlogList-item-photo', {
            'FoFBlog-default-image': blogImage === defaultImage,
          })}
          style={{ backgroundImage: blogImage }}
        />

        <div class="BlogList-item-content">{this.contentItems().toArray()}</div>
      </Link>
    );
  }
}
