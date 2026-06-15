import Modal, { IInternalModalAttrs } from 'flarum/common/components/Modal';
import type Mithril from 'mithril';
export default class SelectCategoriesModal extends Modal<IInternalModalAttrs> {
    blogCategoriesOriginal: string[];
    blogCategories: string[];
    isSaving: boolean;
    hasChanges: boolean;
    oninit(vnode: Mithril.Vnode<IInternalModalAttrs, this>): void;
    title(): string;
    className(): string;
    content(): JSX.Element;
    onsubmit(e: SubmitEvent): void;
}
