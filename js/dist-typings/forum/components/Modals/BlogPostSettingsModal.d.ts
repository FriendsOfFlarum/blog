/// <reference types="flarum/@types/translator-icu-rich" />
import Modal, { IInternalModalAttrs } from 'flarum/common/components/Modal';
import ItemList from 'flarum/common/utils/ItemList';
import Stream from 'flarum/common/utils/Stream';
import Model, { type SaveAttributes } from 'flarum/common/Model';
import type Mithril from 'mithril';
import type Discussion from 'flarum/common/models/Discussion';
/**
 * The `blogMeta` resource. The runtime model (`common/Models/BlogMeta`) is built
 * with `mixin()`, which is typed as returning a plain `object`, so its
 * attribute getters are not visible to TypeScript. This interface mirrors those
 * getters on top of the base `Model` so this component is fully typed.
 */
export interface BlogMeta extends Model {
    discussion: () => Discussion | false;
    featuredImage: () => string | null;
    summary: () => string | null;
    isFeatured: () => boolean;
    isSized: () => boolean;
    isPendingReview: () => boolean;
}
export interface BlogPostSettingsModalAttrs extends IInternalModalAttrs {
    article?: Discussion;
    meta?: BlogMeta;
    isComposer?: boolean;
    onsubmit?: (meta: BlogMeta) => void;
}
export default class BlogPostSettingsModal extends Modal<BlogPostSettingsModalAttrs> {
    meta: BlogMeta;
    isNew: boolean;
    summary: Stream<string>;
    featuredImage: Stream<string>;
    isFeatured: Stream<boolean>;
    isSized: Stream<boolean>;
    isPendingReview: Stream<boolean>;
    oninit(vnode: Mithril.Vnode<BlogPostSettingsModalAttrs, this>): void;
    className(): string;
    title(): import("@askvortsov/rich-icu-message-formatter").NestedStringArray;
    content(): JSX.Element;
    fields(): ItemList<Mithril.Children>;
    submitData(): SaveAttributes;
    onsubmit(e: SubmitEvent): void;
}
