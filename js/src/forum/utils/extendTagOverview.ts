import Mithril from 'mithril';
import app from 'flarum/forum/app';
import { extend } from 'flarum/common/extend';
import IndexPage from 'flarum/forum/components/IndexPage';
import TagsPage from 'ext:flarum/tags/components/TagsPage';
import ItemList from 'flarum/common/utils/ItemList';
import Tag from 'ext:flarum/tags/common/models/Tag';

/**
 * A vnode whose `attrs` may carry a `className`, as produced by Flarum's
 * component views. Used while walking the rendered `TagsPage` markup.
 */
type VnodeWithClassName = Mithril.Vnode<{ className?: string }, unknown>;

export default function extendTagOverview(): void {
  extend(TagsPage.prototype, 'view', function (this: TagsPage, markup: JSX.Element): JSX.Element {
    // Pending xhr to load all tags, throw back loading indicator.
    if (this.loading) {
      return markup;
    }

    if (app.forum.attribute<boolean>('blogHideTags') == false) return markup;

    // Get blog tag ID's
    const blogTags = app.forum.attribute<string[]>('blogTags') || [];

    const tag_tiles_parent = findChild(markup, 'TagsPage-content', true);
    const tag_tiles = getChildren(tag_tiles_parent)[0] as VnodeWithClassName | undefined;

    if (!tag_tiles_parent || !tag_tiles) return markup;

    const tags = this.tags ?? [];

    // Map through the tiles and remove tiles that are part of the blog
    tag_tiles.children = getChildren(tag_tiles).map((tile, i) => {
      return blogTags.indexOf((tags[i] as Tag)?.id() ?? '') >= 0 ? null : tile;
    });

    return markup;
  });

  extend(IndexPage.prototype, 'navItems', function (this: IndexPage, items: ItemList<Mithril.Children>): void {
    if (app.forum.attribute<boolean>('blogHideTags') == false) return;

    const blogTags = app.forum.attribute<string[]>('blogTags') || [];

    blogTags.forEach((id) => {
      items.remove(`tag${id}`);
    });
  });
}

function findChild(parent: Mithril.Children, childClass: string, recursive = false, maxDepth = 50, depth = 0): VnodeWithClassName | null {
  const children = getChildren(parent);
  let child: VnodeWithClassName | null = null;

  for (let i = 0; i < children.length; i++) {
    const vnode = children[i] as VnodeWithClassName | null;
    const childClassName = vnode?.attrs?.className || '';
    if (childClassName.includes(childClass)) {
      child = vnode;
      break;
    }
  }

  // Recursive search
  if (recursive && !child && depth < maxDepth) {
    for (const subParent of children) {
      const subChild = findChild(subParent, childClass, true, maxDepth, depth + 1);
      if (subChild) {
        return subChild;
      }
    }
  }

  return child;
}

function getChildren(parent: Mithril.Children): Mithril.ChildArray {
  if (Array.isArray(parent)) {
    return parent;
  }
  const children = (parent as Mithril.Vnode<unknown, unknown> | null | undefined)?.children;
  if (!Array.isArray(children)) {
    return [];
  }
  return children;
}
