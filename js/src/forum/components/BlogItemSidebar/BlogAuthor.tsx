import app from 'flarum/forum/app';
import Component from 'flarum/common/Component';
import ItemList from 'flarum/common/utils/ItemList';
import listItems from 'flarum/common/helpers/listItems';
import avatar from 'flarum/common/helpers/avatar';
import Link from 'flarum/common/components/Link';
import Discussion from 'flarum/common/models/Discussion';
import User from 'flarum/common/models/User';
import type Mithril from 'mithril';

interface BlogAuthorAttrs {
  loading?: boolean;
  article?: Discussion;
  user?: User;
}

// `bio()` is provided by the optional fof/user-bio extension and is not part of
// core's User typings, so we declare it as an optional augmentation here.
type AuthorWithBio = User & {
  bio?: () => string | null;
};

export default class BlogAuthor extends Component<BlogAuthorAttrs> {
  view(vnode: Mithril.Vnode<BlogAuthorAttrs, this>) {
    const author: AuthorWithBio | null | undefined = !this.attrs.loading
      ? this.attrs.article
        ? (this.attrs.article.user() as AuthorWithBio | null) || null
        : this.attrs.user
      : null;

    return (
      <div className={'FlarumBlog-Article-Author'}>
        <div
          className={`FlarumBlog-Article-Author-background ${this.attrs.loading ? 'FlarumBlog-Author-Ghost' : ''}`}
          style={{
            backgroundColor: author && author.color() ? author.color() : null,
          }}
        />

        <div className={'FlarumBlog-Article-Author-Avatar'}>
          {author ? (
            <Link href={app.route('user', { username: author.username() })}>{avatar(author)}</Link>
          ) : (
            <span className={'Avatar FlarumBlog-Author-Ghost'} />
          )}
        </div>

        {author && (
          <div className={'FlarumBlog-Article-Author-Info'}>
            <Link href={app.route('user', { username: author.username() })} className={'FlarumBlog-Article-Author-Name'}>
              {author.displayName()}
            </Link>
            <p className={'FlarumBlog-Article-Author-Bio'}>{author.bio && author.bio()}</p>

            <ul className={'FlarumBlog-Article-Author-Extended'}>{listItems(this.items().toArray())}</ul>
          </div>
        )}

        {this.attrs.loading && (
          <div>
            <span className={'FlarumBlog-Article-Author-Name FlarumBlog-Author-Ghost'}>&nbsp;</span>
            <p className={'FlarumBlog-Article-Author-Bio FlarumBlog-Author-Ghost'}>&nbsp;</p>
            <p className={'FlarumBlog-Article-Author-Bio FlarumBlog-Author-Ghost'}>&nbsp;</p>
            <p className={'FlarumBlog-Article-Author-Bio FlarumBlog-Author-Ghost'}>&nbsp;</p>
          </div>
        )}
      </div>
    );
  }

  items(): ItemList<Mithril.Children> {
    return new ItemList<Mithril.Children>();
  }
}
