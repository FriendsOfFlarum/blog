import { IFormModalAttrs } from 'flarum/common/components/FormModal';
import FormModal from 'flarum/common/components/FormModal';
import type Mithril from 'mithril';
export interface SelectCategoriesModalAttrs extends IFormModalAttrs {
    /**
     * Currently selected tag ids.
     */
    selected: string[];
    /**
     * Receives the validated tag ids when the selection is submitted.
     */
    onsubmit: (ids: string[]) => void;
}
/**
 * Lets the admin pick which top-level tags are blog categories. The selection
 * is handed back through `onsubmit`; persisting it is the caller's concern.
 */
export default class SelectCategoriesModal extends FormModal<SelectCategoriesModalAttrs> {
    blogCategories: string[];
    hasChanges: boolean;
    oninit(vnode: Mithril.Vnode<SelectCategoriesModalAttrs, this>): void;
    title(): string | any[];
    className(): string;
    content(): JSX.Element;
    onsubmit(e: SubmitEvent): void;
}
