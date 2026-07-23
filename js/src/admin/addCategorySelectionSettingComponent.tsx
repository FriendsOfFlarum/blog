import { extend } from 'flarum/common/extend';
import FormGroup from 'flarum/common/components/FormGroup';
import type { IFormGroupAttrs } from 'flarum/common/components/FormGroup';
import SelectCategoriesSettingComponent from './components/SelectCategoriesSettingComponent';

/**
 * Registers the `fof-blog.select-categories` custom setting type used by the
 * declarative `blog_tags` setting registration.
 */
export default function addCategorySelectionSettingComponent() {
  extend(FormGroup.prototype, 'customFieldComponents', function (items) {
    items.add('fof-blog.select-categories', (attrs: IFormGroupAttrs) => {
      return <SelectCategoriesSettingComponent {...attrs} settingValue={attrs.stream} />;
    });
  });
}
