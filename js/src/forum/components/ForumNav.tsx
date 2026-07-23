import IndexSidebar from 'flarum/forum/components/IndexSidebar';
import app from 'flarum/forum/app';
import Component, { ComponentAttrs } from 'flarum/common/Component';
import IndexPage from 'flarum/forum/components/IndexPage';
import SelectDropdown from 'flarum/common/components/SelectDropdown';
import type ItemList from 'flarum/common/utils/ItemList';
import type Mithril from 'mithril';

export interface ForumNavAttrs extends ComponentAttrs {}

export default class ForumNav extends Component<ForumNavAttrs> {
  view(vnode: Mithril.Vnode<ForumNavAttrs, this>): Mithril.Children {
    return (
      <div className="BlogForumNav BlogSideWidget">
        <h3>{app.translator.trans('fof-blog.forum.forum_nav')}</h3>
        <nav className="IndexPage-nav sideNav">
          <SelectDropdown buttonClassName="Button" className="App-titleControl">
            {this.navItems().toArray()}
          </SelectDropdown>
        </nav>
      </div>
    );
  }

  navItems(): ItemList<Mithril.Children> {
    return IndexSidebar.prototype.navItems();
  }
}
