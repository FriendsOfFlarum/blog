import Component from 'flarum/common/Component';
import type { CommonFieldOptions } from 'flarum/common/components/FormGroup';
import type Stream from 'flarum/common/utils/Stream';
import type Mithril from 'mithril';
export interface SelectCategoriesSettingComponentOptions extends CommonFieldOptions {
    type: 'fof-blog.select-categories';
}
export interface SelectCategoriesSettingComponentAttrs extends SelectCategoriesSettingComponentOptions {
    settingValue: Stream<string>;
}
/**
 * Custom setting field for the pipe-separated `blog_tags` setting: shows how
 * many categories are selected and opens the selection modal. The value flows
 * through the setting stream, so the page's regular dirty-tracking and save
 * apply.
 */
export default class SelectCategoriesSettingComponent<CustomAttrs extends SelectCategoriesSettingComponentAttrs = SelectCategoriesSettingComponentAttrs> extends Component<CustomAttrs> {
    view(): Mithril.Children;
}
