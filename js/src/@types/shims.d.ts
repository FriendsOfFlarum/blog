import type BlogMeta from '../common/models/BlogMeta';

declare module 'flarum/common/models/Discussion' {
  export default interface Discussion {
    blogMeta: () => false | BlogMeta;
    // `tags()` and `canTag()` come from the flarum/tags augmentation
    // (vendor/flarum/tags/js/dist-typings/@types), included via tsconfig.
    // Provided by flarum/lock only when the extension is enabled, hence optional.
    isLocked?: () => boolean | undefined;
    canLock?: () => boolean | undefined;
    // Provided by flarum/sticky only when the extension is enabled, hence optional.
    isSticky?: () => boolean | undefined;
    // Provided by flarum/subscriptions only when the extension is enabled, hence optional.
    subscription?: () => string | null | undefined;
    // Provided by the optional fof/discussion-language extension.
    canChangeLanguage?: () => boolean | undefined;
  }
}

declare module 'flarum/tags/common/models/Tag' {
  export default interface Tag {
    isBlog: () => boolean;
  }
}
