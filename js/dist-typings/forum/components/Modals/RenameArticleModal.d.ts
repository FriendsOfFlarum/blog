/// <reference types="flarum/@types/translator-icu-rich" />
import Modal, { IInternalModalAttrs } from 'flarum/common/components/Modal';
import ItemList from 'flarum/common/utils/ItemList';
import Stream from 'flarum/common/utils/Stream';
import type Discussion from 'flarum/common/models/Discussion';
import type Mithril from 'mithril';
export interface IRenameArticleModalAttrs extends IInternalModalAttrs {
    article: Discussion;
    redirect?: boolean;
    onChange?: (title: string) => void;
}
export default class RenameArticleModal extends Modal<IRenameArticleModalAttrs> {
    protected article: Discussion;
    protected name: Stream<string>;
    protected redirect?: boolean;
    oninit(vnode: Mithril.Vnode<IRenameArticleModalAttrs, this>): void;
    className(): string;
    title(): import("@askvortsov/rich-icu-message-formatter").NestedStringArray;
    content(): JSX.Element;
    fields(): ItemList<Mithril.Children>;
    submitData(): {
        title: any;
    };
    onsubmit(e: SubmitEvent): void;
}
