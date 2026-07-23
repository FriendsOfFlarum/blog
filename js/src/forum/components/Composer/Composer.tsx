import ComposerBody, { type IComposerBodyAttrs } from 'flarum/forum/components/ComposerBody';
import Button from 'flarum/common/components/Button';
import TextEditor from 'flarum/common/components/TextEditor';
import type ComposerState from 'flarum/forum/states/ComposerState';
import type { ComponentAttrs } from 'flarum/common/Component';
import type Mithril from 'mithril';

import app from 'flarum/forum/app';
import ComposerPreview from './ComposerPreview';

export interface ComposerAttrs extends ComponentAttrs {
  submitLabel?: Mithril.Children;
  placeholder?: string;
  disabled?: boolean;
  onsubmit?: () => void;
}

export default class Composer extends ComposerBody {
  /**
   * Attributes passed into the composer body. The blog-specific attrs are
   * intersected with the base attrs so the declaration stays compatible
   * with `ComposerBody`.
   */
  attrs!: ComposerAttrs & IComposerBodyAttrs;

  /**
   * The composer's state, holding the editable fields.
   */
  composer!: ComposerState;

  /**
   * Whether the preview tab is currently being shown.
   */
  previewContent = false;

  /**
   * Optional handler, provided by subclasses, to jump to the preview pane.
   */
  jumpToPreview?: () => void;

  oninit(vnode: Mithril.Vnode<ComposerAttrs & IComposerBodyAttrs, this>) {
    super.oninit(vnode);

    this.previewContent = false;
  }

  // Render
  view() {
    const content = this.composer.fields?.content;
    const hasContent = !!content && !!content() && content() !== '';
    const loading = this.loading || this.attrs.disabled;

    return (
      <div className={`Flarum-Blog-Composer ${loading ? 'Flarum-Blog-Composer-Loading' : ''}`}>
        <div className={'Flarum-Blog-Composer-tabs'}>
          <Button className={!this.previewContent ? 'AricleComposerButtonSelected' : undefined} onclick={() => (this.previewContent = false)}>
            {app.translator.trans('fof-blog.forum.composer.write')}
          </Button>
          <Button className={this.previewContent ? 'AricleComposerButtonSelected' : undefined} onclick={() => (this.previewContent = true)}>
            {app.translator.trans('fof-blog.forum.composer.view')}
          </Button>
        </div>

        <div className={`Composer Flarum-Blog-Composer-body ${this.previewContent ? 'Flarum-Blog-Composer-HideEditor' : ''}`}>
          {this.previewContent && (
            <div className={'Flarum-Blog-Composer-preview'}>
              {!hasContent && app.translator.trans('fof-blog.forum.composer.nothing_to_preview')}

              <ComposerPreview content={content()} />
            </div>
          )}

          <TextEditor
            submitLabel={this.attrs.submitLabel || app.translator.trans('core.forum.composer_edit.submit_button')}
            placeholder={this.attrs.placeholder}
            disabled={loading}
            composer={this.composer}
            preview={this.jumpToPreview && this.jumpToPreview.bind(this)}
            onchange={content}
            onsubmit={this.onsubmit.bind(this)}
            value={content()}
          />
        </div>
      </div>
    );
  }

  // Submit trigger
  onsubmit() {
    if (this.attrs.onsubmit) {
      this.attrs.onsubmit();
    }
  }
}
