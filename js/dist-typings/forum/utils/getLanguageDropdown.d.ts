import type LanguageDropdownType from '@fof/discussion-language/forum/components/LanguageDropdown';
/**
 * Resolves the `LanguageDropdown` component from the optional
 * fof/discussion-language extension, when it is enabled.
 */
export default function getLanguageDropdown(): typeof LanguageDropdownType | undefined;
