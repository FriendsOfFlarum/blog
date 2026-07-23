import Form from 'flarum/common/components/Form';
import app from 'flarum/forum/app';
import { IFormModalAttrs } from 'flarum/common/components/FormModal';
import FormModal from 'flarum/common/components/FormModal';
import Button from 'flarum/common/components/Button';
import ItemList from 'flarum/common/utils/ItemList';
import Stream from 'flarum/common/utils/Stream';
import Switch from 'flarum/common/components/Switch';
import Model, { type SaveAttributes } from 'flarum/common/Model';
import type Mithril from 'mithril';
import type Discussion from 'flarum/common/models/Discussion';
import type RequestError from 'flarum/common/utils/RequestError';

/**
 * The `blogMeta` resource. The runtime model (`common/Models/BlogMeta`) is built
 * with `mixin()`, which is typed as returning a plain `object`, so its
 * attribute getters are not visible to TypeScript. This interface mirrors those
 * getters on top of the base `Model` so this component is fully typed.
 */
export interface BlogMeta extends Model {
  discussion: () => Discussion | false;
  featuredImage: () => string | null;
  summary: () => string | null;
  isFeatured: () => boolean;
  isSized: () => boolean;
  isPendingReview: () => boolean;
}

export interface BlogPostSettingsModalAttrs extends IFormModalAttrs {
  article?: Discussion;
  meta?: BlogMeta;
  isComposer?: boolean;
  onsubmit?: (meta: BlogMeta) => void;
}

export default class BlogPostSettingsModal extends FormModal<BlogPostSettingsModalAttrs> {
  meta!: BlogMeta;
  isNew!: boolean;

  summary!: Stream<string>;
  featuredImage!: Stream<string>;
  isFeatured!: Stream<boolean>;
  isSized!: Stream<boolean>;
  isPendingReview!: Stream<boolean>;

  oninit(vnode: Mithril.Vnode<BlogPostSettingsModalAttrs, this>) {
    super.oninit(vnode);

    if (this.attrs.article) {
      this.meta =
        this.attrs.article && this.attrs.article.blogMeta()
          ? (this.attrs.article.blogMeta() as BlogMeta)
          : app.store.createRecord<BlogMeta>('blogMeta');
    } else {
      this.meta = this.attrs.meta ? this.attrs.meta : app.store.createRecord<BlogMeta>('blogMeta');
    }

    this.isNew = !this.meta.exists;

    this.summary = Stream(this.meta.summary() || '');

    this.featuredImage = Stream(this.meta.featuredImage() || '');

    this.isFeatured = Stream(this.meta.isFeatured() || false);
    this.isSized = Stream(this.meta.isSized() || false);
    this.isPendingReview = Stream(this.meta.isPendingReview() || false);
  }

  className() {
    return 'Modal--small Support-Modal';
  }

  title() {
    return app.translator.trans('fof-blog.forum.article_settings.title');
  }

  content() {
    return (
      <div className="Modal-body">
        <Form>{this.fields().toArray()}</Form>
      </div>
    );
  }

  fields() {
    const items = new ItemList<Mithril.Children>();

    items.add(
      'summary',
      <div className="Form-group">
        <label>{app.translator.trans('fof-blog.forum.article_settings.fields.summary.title')}:</label>
        <textarea
          className="FormControl"
          style={{
            maxWidth: '100%',
            minWidth: '100%',
            width: '100%',
            minHeight: '120px',
          }}
          bidi={this.summary}
          placeholder={app.translator.trans('fof-blog.forum.article_settings.fields.summary.placeholder')}
        />

        <small>{app.translator.trans('fof-blog.forum.article_settings.fields.summary.helper_text')}</small>
      </div>,
      30
    );

    let fofUploadButton: Mithril.Children = null;

    if ('fof-upload' in flarum.extensions && app.forum.attribute('fof-upload.canUpload')) {
      fofUploadButton = (
        <Button
          class="Button Button--icon"
          onclick={async () => {
            // fof/upload is an optional integration, so its modules are lazily
            // resolved from the extension registry.
            const [{ default: FileManagerModal }, { default: Uploader }] = await Promise.all([
              import('ext:fof/upload/forum/components/FileManagerModal'),
              import('ext:fof/upload/forum/handler/Uploader'),
            ]);

            app.modal.show(
              FileManagerModal,
              {
                uploader: new Uploader(),
                onSelect: (files: Array<string | number>) => {
                  const file = app.store.getById<Model & { url: () => string }>('files', files[0] as string);

                  this.featuredImage(file!.url());
                },
              },
              true
            );
          }}
          icon="fas fa-cloud-upload-alt"
        />
      );
    }

    items.add(
      'image',
      <div className="Form-group V17Blog-ArticleImage">
        <label>{app.translator.trans('fof-blog.forum.article_settings.fields.image.title')}:</label>
        <div data-upload-enabled={!!fofUploadButton}>
          <input type="text" className="FormControl" bidi={this.featuredImage} placeholder="https://" />
          {fofUploadButton}
        </div>

        <small>{app.translator.trans('fof-blog.forum.article_settings.fields.image.helper_text')}</small>

        {this.featuredImage() !== '' && (
          <img
            src={this.featuredImage()}
            alt="Article image"
            title={app.translator.trans('fof-blog.forum.article_settings.fields.image.title')}
            style={{ width: '100%', marginTop: '15px' }}
          />
        )}
      </div>,
      30
    );

    items.add(
      'sized',
      <div className="Form-group">
        <Switch
          state={this.isSized() == true}
          onchange={(val: boolean) => {
            this.isSized(val);
          }}
        >
          <b>{app.translator.trans('fof-blog.forum.article_settings.fields.highlight.title')}</b>
          <div className="helpText" style={{ fontWeight: 500 }}>
            {app.translator.trans('fof-blog.forum.article_settings.fields.highlight.helper_text')}
          </div>
        </Switch>
      </div>,
      -10
    );

    items.add(
      'submit',
      <div className="Form-group">
        <Button type="submit" className="Button Button--primary SupportModal-save" loading={this.loading}>
          {app.translator.trans('core.forum.composer_edit.submit_button')}
        </Button>
      </div>,
      -10
    );

    return items;
  }

  submitData(): SaveAttributes {
    return {
      summary: this.summary(),
      featuredImage: this.featuredImage(),
      isFeatured: this.isFeatured(),
      isSized: this.isSized(),
      isPendingReview: this.isPendingReview(),
      relationships: (this.isNew && !this.attrs.isComposer
        ? {
            discussion: this.attrs.article,
          }
        : null) as unknown as SaveAttributes['relationships'],
    };
  }

  onsubmit(e: SubmitEvent) {
    e.preventDefault();

    // Submit data
    if (this.attrs.onsubmit) {
      // Update attributes
      this.meta.pushData({
        attributes: this.submitData(),
      });

      // Push
      this.attrs.onsubmit(this.meta);

      this.hide();
      return;
    }

    this.loading = true;

    this.meta.save(this.submitData()).then(
      () => {
        if (this.attrs.article) {
          this.attrs.article.pushData({
            relationships: {
              blogMeta: this.meta,
            },
          });
        }

        this.hide();
        m.redraw();
      },
      (response: RequestError) => {
        this.loading = false;
        this.onerror(response);
      }
    );
  }
}
