import IndexSidebar from 'flarum/forum/components/IndexSidebar';
import app from 'flarum/forum/app';
import { extend } from 'flarum/common/extend';
import LinkButton from 'flarum/common/components/LinkButton';
import type Mithril from 'mithril';
import type ItemList from 'flarum/common/utils/ItemList';

export default function addSidebarNav(): void {
  extend(IndexSidebar.prototype, 'navItems', function (this: IndexSidebar, items: ItemList<Mithril.Children>) {
    if (app.forum.attribute<boolean>('blogAddSidebarNav')) {
      items.add(
        'blog',
        <LinkButton icon="fas fa-comment" href={app.route('blog')}>
          {app.translator.trans('fof-blog.forum.blog')}
        </LinkButton>,
        15
      );
    }

    return items;
  });
}
