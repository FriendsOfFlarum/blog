import app from 'flarum/admin/app';
import Component from 'flarum/common/Component';
import Button from 'flarum/common/components/Button';
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
export default class SelectCategoriesSettingComponent<
  CustomAttrs extends SelectCategoriesSettingComponentAttrs = SelectCategoriesSettingComponentAttrs
> extends Component<CustomAttrs> {
  view(): Mithril.Children {
    const selected = (this.attrs.settingValue() || '').split('|').filter(Boolean);

    return (
      <div className="Form-group FoFBlog-SelectCategories">
        <label>{this.attrs.label}</label>
        {this.attrs.help && <p className="helpText">{this.attrs.help}</p>}
        <p>
          {selected.length === 0
            ? app.translator.trans('fof-blog.admin.settings.no_categories_selected')
            : app.translator.trans('fof-blog.admin.settings.selected_category_count', { count: selected.length })}
        </p>
        <Button
          className="Button"
          onclick={() =>
            app.modal.show(() => import('./Modals/SelectCategoriesModal'), {
              selected,
              onsubmit: (ids: string[]) => this.attrs.settingValue(ids.join('|')),
            })
          }
        >
          {app.translator.trans('fof-blog.admin.settings.select_categories_button')}
        </Button>
      </div>
    );
  }
}
