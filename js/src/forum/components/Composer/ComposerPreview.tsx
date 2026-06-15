import Component, { ComponentAttrs } from 'flarum/common/Component';
import type Mithril from 'mithril';

export interface ComposerPreviewAttrs extends ComponentAttrs {
  content?: string;
}

export default class ComposerPreview extends Component<ComposerPreviewAttrs> {
  view() {
    return <div />;
  }

  oncreate(vnode: Mithril.VnodeDOM<ComposerPreviewAttrs, this>) {
    super.oncreate(vnode);

    s9e.TextFormatter.preview(this.attrs.content || '', vnode.dom);
  }
}
