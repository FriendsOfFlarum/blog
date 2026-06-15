import ComposerBody from 'flarum/forum/components/ComposerBody';
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
     * Attributes passed into the composer body.
     */
    attrs: ComposerAttrs;
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
    jumpToPreview?: (e: Event) => void;
    oninit(vnode: Mithril.Vnode<ComposerAttrs, this>): void;
    view(): JSX.Element;
    onsubmit(): void;
}
