/**
 * fof/discussion-language is an optional integration and is not installed in
 * this dev environment, so its typings are shimmed with the minimal surface
 * this extension consumes.
 */
declare module '@fof/discussion-language/forum/components/LanguageDropdown' {
  import Component, { ComponentAttrs } from 'flarum/common/Component';
  import Mithril from 'mithril';

  export interface LanguageDropdownAttrs extends ComponentAttrs {
    selected: string;
    onclick: (language: unknown) => void;
  }

  export default class LanguageDropdown extends Component<LanguageDropdownAttrs> {
    view(vnode: Mithril.Vnode<LanguageDropdownAttrs>): Mithril.Children;
  }
}
