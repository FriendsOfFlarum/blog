import app from 'flarum/forum/app';
import Component, { type ComponentAttrs } from 'flarum/common/Component';
import Link from 'flarum/common/components/Link';
import type Tag from 'flarum/tags/common/models/Tag';
import type Mithril from 'mithril';

export interface BlogCategoriesAttrs extends ComponentAttrs {}

export default class BlogCategories extends Component<BlogCategoriesAttrs> {
  blogCategories!: string[] | null;

  oninit(vnode: Mithril.Vnode<BlogCategoriesAttrs, this>) {
    super.oninit(vnode);

    this.blogCategories = app.forum.attribute<string[] | null>('blogTags');
  }

  view(): Mithril.Children {
    return (
      <div className="BlogCategories BlogSideWidget">
        <h3>{app.translator.trans('fof-blog.forum.categories')}</h3>

        {this.blogCategories &&
          this.blogCategories.map((tagId) => {
            const tag = app.store.getById<Tag>('tags', tagId);

            if (!tag) return null;

            const tags: Mithril.Children[] = [];
            let showSubTags = this.blogCategories!.length === 1 || tag.slug() === m.route.param('slug');

            // Add tags
            app.store.all<Tag>('tags').forEach((_tag) => {
              if (_tag.isChild() && _tag.parent() === tag) {
                if (_tag.slug() === m.route.param('slug')) {
                  showSubTags = true;
                }

                tags.push(this.categoryItem(_tag));
              }
            });

            return showSubTags ? [this.categoryItem(tag), ...tags] : this.categoryItem(tag);
          })}
      </div>
    );
  }

  // Category item
  categoryItem(tag: Tag): Mithril.Children {
    return (
      <Link
        href={app.route('blogCategory', { slug: tag.slug() })}
        className={`BlogSideWidget-item BlogSideWidget-item-${tag.id()} ${
          tag.isChild() && app.forum.attribute<boolean>('blogCategoryHierarchy') ? 'BlogSideWidget-item-child' : ''
        }`}
      >
        <span
          className={!tag.icon() ? 'BlogSideWidget-item-colored' : ''}
          style={{ backgroundColor: !tag.icon() ? tag.color() ?? undefined : undefined }}
        >
          <i className={tag.icon() ?? undefined} />
        </span>
        {tag.name()}
      </Link>
    );
  }
}
