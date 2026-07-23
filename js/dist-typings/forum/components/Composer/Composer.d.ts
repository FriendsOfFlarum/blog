import ComposerBody, { type IComposerBodyAttrs } from 'flarum/forum/components/ComposerBody';
import type ComposerState from 'flarum/forum/states/ComposerState';
import type { ComponentAttrs } from 'flarum/common/Component';
import type Mithril from 'mithril';
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
    attrs: ComposerAttrs & IComposerBodyAttrs;
    /**
     * The composer's state, holding the editable fields.
     */
    composer: ComposerState;
    /**
     * Whether the preview tab is currently being shown.
     */
    previewContent: boolean;
    /**
     * Optional handler, provided by subclasses, to jump to the preview pane.
     */
    jumpToPreview?: () => void;
    oninit(vnode: Mithril.Vnode<ComposerAttrs & IComposerBodyAttrs, this>): void;
    view(): JSX.Element;
    onsubmit(): void;
}
