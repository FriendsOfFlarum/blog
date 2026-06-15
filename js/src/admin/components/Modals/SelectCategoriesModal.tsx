import app from 'flarum/admin/app';
import Modal, { IInternalModalAttrs } from 'flarum/common/components/Modal';
import Button from 'flarum/common/components/Button';
import Alert from 'flarum/common/components/Alert';
import saveSettings from 'flarum/admin/utils/saveSettings';
import Switch from 'flarum/common/components/Switch';
import Tag from 'flarum/tags/common/models/Tag';
import type Mithril from 'mithril';

export default class SelectCategoriesModal extends Modal<IInternalModalAttrs> {
  blogCategoriesOriginal!: string[];
  blogCategories!: string[];
  isSaving: boolean = false;
  hasChanges: boolean = false;

  oninit(vnode: Mithril.Vnode<IInternalModalAttrs, this>) {
    super.oninit(vnode);

    this.blogCategoriesOriginal = app.data.settings.blog_tags ? app.data.settings.blog_tags.split('|') : [];
    this.blogCategories = app.data.settings.blog_tags ? app.data.settings.blog_tags.split('|') : [];

    this.isSaving = false;
    this.hasChanges = false;
  }

  title() {
    return 'Select blog categories';
  }

  className() {
    return 'Modal modal-dialog FlarumBlog-TagsModal';
  }

  content() {
    return (
      <div>
        <div className="Modal-body">
          <p>
            Please select one or more tags that are considered blog tags.{' '}
            <a href={app.forum.attribute<string>('baseUrl') + '/blog'} target={'_blank'}>
              Visit your blog.
            </a>
          </p>

          <table className={'FlarumBlog-TagsTable'}>
            <thead>
              <th width="35"></th>
              <th>Tag name</th>
              <th width="50"></th>
            </thead>
            <tbody>
              {app.store.all('tags').length === 0 && (
                <tr>
                  <td colspan="3">You currently have no tags.</td>
                </tr>
              )}

              {(app.store.all('tags') as Tag[]).map((obj: Tag) => {
                // Skip all tags who aren't main categories
                if (obj.parent()) {
                  return;
                }

                const id = obj.id();

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
                      <i className={obj.icon() ?? ''} />
                    </td>
                    <td onclick={toggleTag}>{obj.name()}</td>
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
          <Button type="submit" className="Button Button--primary" loading={this.loading}>
            {this.hasChanges ? 'Save changes' : 'Close'}
          </Button>
        </div>
      </div>
    );
  }

  // Close or save setting
  onsubmit(e: SubmitEvent) {
    e.preventDefault();

    if (!this.hasChanges) {
      this.hide();
      return;
    }

    this.isSaving = true;

    // Validate tags and prevent ghost tags (deleted tags)
    let validBlogTags: string[] = [];

    this.blogCategories.map((tagId: string) => {
      if (app.store.getById('tags', tagId)) {
        validBlogTags.push(tagId);
      }
    });

    saveSettings({
      blog_tags: validBlogTags.join('|'),
    })
      .then(() => {
        app.alerts.show(
          Alert,
          {
            type: 'success',
          },
          app.translator.trans('core.admin.settings.saved_message')
        );

        this.hide();
      })
      .catch(() => {
        app.alerts.show(
          Alert,
          {
            type: 'error',
          },
          app.translator.trans('core.lib.error.generic_message')
        );
      })
      .then(() => {
        this.isSaving = false;
      });
  }
}
