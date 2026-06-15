import ExtensionPage, { ExtensionPageAttrs } from 'flarum/admin/components/ExtensionPage';
import type Mithril from 'mithril';
type RedirectsEnabled = 'both' | 'discussions_only' | 'tags_only' | 'none';
export default class BlogSettings extends ExtensionPage {
    hasChanges: boolean;
    isSaving: boolean;
    redirectsEnabled: RedirectsEnabled;
    hideTagsInList: boolean;
    allowComments: boolean;
    hideOnDiscussionList: boolean;
    requiresReviewOnPost: boolean;
    addCategoryHierarchy: boolean;
    addSidebarNav: boolean;
    featuredCount: number | string;
    blogAddHero: boolean;
    oninit(vnode: Mithril.Vnode<ExtensionPageAttrs, this>): void;
    content(): JSX.Element;
    /**
     * Save data
     */
    save(): void;
}
export {};
