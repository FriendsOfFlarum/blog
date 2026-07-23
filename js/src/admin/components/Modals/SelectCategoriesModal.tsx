import app from 'flarum/admin/app';
import { IFormModalAttrs } from 'flarum/common/components/FormModal';
import FormModal from 'flarum/common/components/FormModal';
import Button from 'flarum/common/components/Button';
import Switch from 'flarum/common/components/Switch';
import Tag from 'ext:flarum/tags/common/models/Tag';
import type Mithril from 'mithril';

export interface SelectCategoriesModalAttrs extends IFormModalAttrs {
  /**
   * Currently selected tag ids.
   */
  selected: string[];

  /**
   * Receives the validated tag ids when the selection is submitted.
   */
  onsubmit: (ids: string[]) => void;
}

/**
 * Lets the admin pick which top-level tags are blog categories. The selection
 * is handed back through `onsubmit`; persisting it is the caller's concern.
 */
export default class SelectCategoriesModal extends FormModal<SelectCategoriesModalAttrs> {
  blogCategories!: string[];
  hasChanges: boolean = false;

  oninit(vnode: Mithril.Vnode<SelectCategoriesModalAttrs, this>) {
    super.oninit(vnode);

    this.blogCategories = [...this.attrs.selected];
    this.hasChanges = false;
  }

  title() {
    return app.translator.trans('fof-blog.admin.settings.select_categories_button');
  }

  className() {
    return 'Modal modal-dialog FoFBlog-TagsModal';
  }

  content() {
    return (
      <div>
        <div className="Modal-body">
          <p>
            {app.translator.trans('fof-blog.admin.settings.categories_modal.description')}{' '}
            <a href={app.forum.attribute<string>('baseUrl') + '/blog'} target="_blank">
              {app.translator.trans('fof-blog.admin.settings.categories_modal.visit_blog')}
            </a>
          </p>

          <table className={'FoFBlog-TagsTable'}>
            <thead>
              <th width="35"></th>
              <th>{app.translator.trans('fof-blog.admin.settings.categories_modal.tag_name_column')}</th>
              <th width="50"></th>
            </thead>
            <tbody>
              {app.store.all('tags').length === 0 && (
                <tr>
                  <td colspan="3">{app.translator.trans('fof-blog.admin.settings.categories_modal.no_tags')}</td>
                </tr>
              )}

              {(app.store.all('tags') as Tag[]).map((tag: Tag) => {
                // Skip all tags that aren't main categories
                if (tag.parent()) {
                  return;
                }

                const id = tag.id();

                // Toggle tag
                const toggleTag = () => {
                  if (typeof id === 'undefined') {
                    return;
                  }

                  const currentIndex = this.blogCategories.indexOf(id);
                  this.hasChanges = true;

                  // Remove tag
                  if (currentIndex >= 0) {
                    this.blogCategories.splice(currentIndex, 1);
                  } else {
                    // Add tag
                    this.blogCategories.push(id);
                  }
                };

                return (
                  <tr>
                    <td>
                      <i className={tag.icon() ?? ''} />
                    </td>
                    <td onclick={toggleTag}>{tag.name()}</td>
                    <td>
                      <Switch state={typeof id !== 'undefined' && this.blogCategories.indexOf(id) >= 0} onchange={toggleTag} />
                    </td>
                  </tr>
                );
              })}
            </tbody>
          </table>
        </div>
        <div style="padding: 25px 30px; text-align: center;">
          <Button type="submit" className="Button Button--primary">
            {this.hasChanges
              ? app.translator.trans('core.admin.settings.submit_button')
              : app.translator.trans('fof-blog.admin.settings.categories_modal.close')}
          </Button>
        </div>
      </div>
    );
  }

  onsubmit(e: SubmitEvent) {
    e.preventDefault();

    if (this.hasChanges) {
      // Filter out ghost tags (deleted tags) before handing the ids back.
      this.attrs.onsubmit(this.blogCategories.filter((tagId) => app.store.getById('tags', tagId)));
    }

    this.hide();
  }
}
