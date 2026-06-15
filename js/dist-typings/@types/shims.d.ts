import type BlogMeta from '../common/Models/BlogMeta';
import type Tag from 'flarum/tags/common/models/Tag';

declare global {
  /**
   * The s9e TextFormatter bundle exposed globally by Flarum core's
   * formatting layer. Only the `preview` helper is typed here, as that's
   * all this extension consumes.
   */
  const s9e: {
    TextFormatter: {
      preview(text: string, element: Element): void;
    };
  };
}

declare module 'flarum/common/models/Discussion' {
  export default interface Discussion {
    blogMeta: () => false | BlogMeta;
    // Provided at runtime by the flarum/tags extension. The tags ext ships this
    // augmentation in its own dist-typings/@types, but those aren't part of this
    // extension's tsconfig `include`, so we re-declare it here to keep types sound.
    tags: () => false | (Tag | undefined)[];
    canTag: () => boolean | undefined;
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
  export default interface Discussion {
    isBlog: () => boolean;
  }
}
