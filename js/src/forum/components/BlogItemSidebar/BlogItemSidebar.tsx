import Component, { ComponentAttrs } from 'flarum/common/Component';
import ItemList from 'flarum/common/utils/ItemList';
import listItems from 'flarum/common/helpers/listItems';
import Discussion from 'flarum/common/models/Discussion';
import BlogAuthor from './BlogAuthor';
import BlogCategories from '../BlogCategories';
import ForumNav from '../ForumNav';
import type Mithril from 'mithril';

export interface BlogItemSidebarAttrs extends ComponentAttrs {
  article: Discussion | null;
  loading: boolean;
}

export default class BlogItemSidebar extends Component<BlogItemSidebarAttrs> {
  view(vnode: Mithril.Vnode<BlogItemSidebarAttrs, this>) {
    return (
      <div className={'FoFBlog-Article-Sidebar'}>
        <ul>{listItems(this.items().toArray())}</ul>
      </div>
    );
  }

  items(): ItemList<Mithril.Children> {
    const itemlist = new ItemList<Mithril.Children>();

    itemlist.add('author', <BlogAuthor {...this.attrs} />, 0);

    itemlist.add('categories', <BlogCategories {...this.attrs} />, 0);

    itemlist.add('nav', <ForumNav {...this.attrs} />, 0);

    return itemlist;
  }
}
