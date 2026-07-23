import { IFormModalAttrs } from 'flarum/common/components/FormModal';
import FormModal from 'flarum/common/components/FormModal';
import ItemList from 'flarum/common/utils/ItemList';
import Stream from 'flarum/common/utils/Stream';
import type Discussion from 'flarum/common/models/Discussion';
import type Mithril from 'mithril';
export interface IRenameArticleModalAttrs extends IFormModalAttrs {
    article: Discussion;
    redirect?: boolean;
    onChange?: (title: string) => void;
}
export default class RenameArticleModal extends FormModal<IRenameArticleModalAttrs> {
    protected article: Discussion;
    protected name: Stream<string>;
    protected redirect?: boolean;
    oninit(vnode: Mithril.Vnode<IRenameArticleModalAttrs, this>): void;
    className(): string;
    title(): string | any[];
    content(): JSX.Element;
    fields(): ItemList<Mithril.Children>;
    submitData(): {
        title: any;
    };
    onsubmit(e: SubmitEvent): void;
}
