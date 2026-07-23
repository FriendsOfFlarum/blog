import { IFormModalAttrs } from 'flarum/common/components/FormModal';
import FormModal from 'flarum/common/components/FormModal';
import type Mithril from 'mithril';
export default class SelectCategoriesModal extends FormModal<IFormModalAttrs> {
    blogCategoriesOriginal: string[];
    blogCategories: string[];
    isSaving: boolean;
    hasChanges: boolean;
    oninit(vnode: Mithril.Vnode<IFormModalAttrs, this>): void;
    title(): string;
    className(): string;
    content(): JSX.Element;
    onsubmit(e: SubmitEvent): void;
}
