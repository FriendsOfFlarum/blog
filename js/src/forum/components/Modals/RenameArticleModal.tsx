import Modal, { IInternalModalAttrs } from 'flarum/common/components/Modal';
import Button from 'flarum/common/components/Button';
import ItemList from 'flarum/common/utils/ItemList';
import Stream from 'flarum/common/utils/Stream';
import app from 'flarum/forum/app';
import type Discussion from 'flarum/common/models/Discussion';
import type Mithril from 'mithril';

export interface IRenameArticleModalAttrs extends IInternalModalAttrs {
  article: Discussion;
  redirect?: boolean;
  onChange?: (title: string) => void;
}

export default class RenameArticleModal extends Modal<IRenameArticleModalAttrs> {
  protected article!: Discussion;
  protected name!: Stream<string>;
  protected redirect?: boolean;

  oninit(vnode: Mithril.Vnode<IRenameArticleModalAttrs, this>) {
    super.oninit(vnode);

    this.article = this.attrs.article;

    this.name = Stream(this.article.title() || '');

    this.redirect = this.attrs.redirect;
  }

  className() {
    return 'Modal--small Support-Modal';
  }

  title() {
    return app.translator.trans('fof-blog.forum.tools.rename_article');
  }

  content() {
    return (
      <div className="Modal-body">
        <div className="Form">{this.fields().toArray()}</div>
      </div>
    );
  }

  fields() {
    const items = new ItemList<Mithril.Children>();

    items.add(
      'name',
      <div className="Form-group">
        <label>{app.translator.trans('fof-blog.forum.article.title')}:</label>
        <input className="FormControl" placeholder={app.translator.trans('fof-blog.forum.article.title')} bidi={this.name} />
      </div>,
      50
    );

    items.add(
      'submit',
      <div className="Form-group">
        {Button.component(
          {
            type: 'submit',
            className: 'Button Button--primary SupportModal-save',
            loading: this.loading,
          },
          app.translator.trans('core.forum.composer_edit.submit_button')
        )}
      </div>,
      -10
    );

    return items;
  }

  submitData() {
    return {
      title: this.name(),
    };
  }

  onsubmit(e: SubmitEvent) {
    e.preventDefault();

    this.loading = true;

    // Do not save
    if (this.attrs.onChange) {
      this.attrs.onChange(this.name());
      this.hide();

      return;
    }

    this.article
      .save({
        title: this.name(),
      })
      .then(
        () => {
          this.hide();

          // Redirect
          if (this.redirect) {
            const url = `/blog/${this.article.slug()}`;

            m.route.set(url, true);
            window.history.replaceState(null, document.title, url);
          }
        },
        (response) => {
          this.loading = false;
          this.onerror(response);
        }
      );
  }
}
