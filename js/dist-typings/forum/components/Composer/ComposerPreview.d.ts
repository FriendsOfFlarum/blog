import Component, { ComponentAttrs } from 'flarum/common/Component';
import type Mithril from 'mithril';
export interface ComposerPreviewAttrs extends ComponentAttrs {
    content?: string;
}
export default class ComposerPreview extends Component<ComposerPreviewAttrs> {
    view(): JSX.Element;
    oncreate(vnode: Mithril.VnodeDOM<ComposerPreviewAttrs, this>): void;
}
