import Component, { type ComponentAttrs } from 'flarum/common/Component';
import DiscussionControls from 'flarum/forum/utils/DiscussionControls';
import Alert from 'flarum/common/components/Alert';
import Button from 'flarum/common/components/Button';
import Dropdown from 'flarum/common/components/Dropdown';
import extractText from 'flarum/common/utils/extractText';
import ItemList from 'flarum/common/utils/ItemList';
import app from 'flarum/forum/app';
import Discussion from 'flarum/common/models/Discussion';
import type Mithril from 'mithril';

type UnsafeModalClass = Parameters<typeof app.modal.show>[0];

/**
 * `lockAction` is added to `DiscussionControls` at runtime by the flarum/lock
 * extension and is therefore not part of core's typings.
 */
type DiscussionControlsWithLock = typeof DiscussionControls & {
  lockAction(this: Discussion): Promise<void>;
};

export interface BlogPostControllerAttrs extends ComponentAttrs {
  article: Discussion;
}

export default class BlogPostController extends Component<BlogPostControllerAttrs> {
  loadedPost: boolean = false;
  loading: boolean = false;

  manageArticleButtons(): ItemList<Mithril.Children> {
    const article = this.attrs.article;
    const items = new ItemList<Mithril.Children>();

    // Working for GlowingBlue version
    const languageExtension = flarum.extensions['fof-discussion-language'];
    const LanguageDiscussionModal =
      languageExtension && typeof languageExtension.components !== 'undefined'
        ? ((languageExtension.components as Record<string, UnsafeModalClass>).LanguageDiscussionModal as UnsafeModalClass | undefined)
        : null;

    // Rename article
    if (article.canRename()) {
      items.add(
        'rename',
        <Button className="Button" onclick={() => app.modal.show(() => import('./Modals/RenameArticleModal'), { article })} icon="fas fa-pencil-alt">
          {app.translator.trans('fof-blog.forum.tools.rename_article')}
        </Button>,
        100
      );
    }

    const articlePost = article.firstPost();

    // Edit article
    items.add(
      'edit',
      <Button
        className="Button"
        disabled={!articlePost || !articlePost.canEdit()}
        onclick={() => {
          app.composer
            .load(() => import('flarum/forum/components/EditPostComposer'), { post: articlePost })
            .then(() => {
              app.composer.show();
            });
        }}
        icon="fas fa-edit"
      >
        {app.translator.trans('fof-blog.forum.tools.edit_article')}
      </Button>,
      90
    );

    // Article settings
    items.add(
      'articleSettings',
      <Button className="Button" onclick={() => app.modal.show(() => import('./Modals/BlogPostSettingsModal'), { article })} icon="fas fa-cogs">
        {app.translator.trans('fof-blog.forum.tools.article_settings')}
      </Button>,
      80
    );

    // Update categories
    if (article.canTag()) {
      items.add(
        'tag',
        <Button
          className="Button"
          onclick={() => app.modal.show(() => import('ext:flarum/tags/forum/components/TagDiscussionModal'), { discussion: article })}
          icon="fas fa-tag"
        >
          {app.translator.trans('fof-blog.forum.tools.update_category')}
        </Button>,
        70
      );
    }

    const blogMeta = article.blogMeta();

    // Update article SEO. fof/seo is an optional integration, so its modal is
    // lazily resolved from the extension registry.
    if (blogMeta && 'fof-seo' in flarum.extensions && app.forum.attribute('canConfigureSeo')) {
      items.add(
        'seo',
        <Button
          className="Button"
          onclick={() =>
            app.modal.show(() => import('ext:fof/seo/common/Components/MetaSeoModal'), {
              objectType: 'blogs',
              objectId: blogMeta.id(),
            })
          }
          icon="fas fa-search"
        >
          {app.translator.trans('fof-seo.forum.controls.configure_seo')}
        </Button>,
        70
      );
    }

    // Approve article
    if (blogMeta && blogMeta.isPendingReview()) {
      items.add('separator1', <li className="Dropdown-separator" />, 65);

      items.add(
        'approve',
        <Button
          className="Button"
          disabled={!app.forum.attribute('canApproveBlogPosts')}
          onclick={() => {
            blogMeta
              .save({
                isPendingReview: false,
              })
              .then(
                () => {
                  app.alerts.show(Alert, { type: 'success' }, app.translator.trans('fof-blog.forum.review_article.approve_article_approved'));
                },
                () => {
                  // `BlogPostController` extends `Component`, which (unlike
                  // `Modal`) has no `handleErrors` method, so the previous
                  // `this.handleErrors(response)` call would have thrown. We
                  // simply reset the loading flag here; Flarum's global request
                  // error handler still surfaces the failure to the user.
                  this.loading = false;
                }
              );
          }}
          icon="fas fa-thumbs-up"
        >
          {app.translator.trans('fof-blog.forum.review_article.approve_article')}
        </Button>,
        60
      );
    }

    // Language
    if (article.canChangeLanguage && article.canChangeLanguage() && LanguageDiscussionModal) {
      items.add(
        'lang',
        <Button icon="fas fa-globe" onclick={() => app.modal.show(LanguageDiscussionModal, { discussion: article })}>
          {app.translator.trans('fof-discussion-language.forum.discussion_controls.change_language_button')}
        </Button>,
        50
      );
    }

    items.add('separator2', <li className="Dropdown-separator" />, 40);

    // Lock article (flarum/lock is an optional dependency)
    if (article.canLock?.()) {
      items.add(
        'lock',
        <Button
          className="Button"
          onclick={(DiscussionControls as DiscussionControlsWithLock).lockAction.bind(article)}
          icon={`fas ${article.isLocked?.() ? 'fa-comments' : 'fa-comment-slash'}`}
        >
          {article.isLocked?.()
            ? app.translator.trans('fof-blog.forum.tools.enable_comments')
            : app.translator.trans('fof-blog.forum.tools.disable_comments')}
        </Button>,
        30
      );
    }

    // Hide/show/delete
    if (article.canHide()) {
      // Article is hidden
      if (article.isHidden()) {
        // Recover article
        items.add(
          'recover',
          <Button className="Button" onclick={DiscussionControls.restoreAction.bind(article)} icon="fas fa-eye">
            {app.translator.trans('fof-blog.forum.tools.recover_article')}
          </Button>,
          20
        );

        // Delete article
        if (article.canDelete()) {
          items.add(
            'delete',
            <Button
              className="Button"
              onclick={() => {
                // Confirm deletion
                if (confirm(extractText(app.translator.trans('core.forum.discussion_controls.delete_confirmation')))) {
                  // Redirect if the current page is an blog article
                  if (app.history.getCurrent().name === 'blogArticle') {
                    if (app.previous) {
                      app.history.back();
                    } else {
                      m.route.set(app.route('blog'));
                    }
                  }

                  article.delete().then(() => {
                    m.redraw();
                  });
                }
              }}
              icon="far fa-trash-alt"
            >
              {app.translator.trans('fof-blog.forum.tools.delete_forever')}
            </Button>,
            10
          );
        }
      } else {
        // Hide article
        items.add(
          'hide',
          <Button className="Button" onclick={DiscussionControls.hideAction.bind(article)} icon="fas fa-eye-slash">
            {app.translator.trans('fof-blog.forum.tools.hide_article')}
          </Button>,
          0
        );
      }
    }

    return items;
  }

  view(vnode: Mithril.Vnode<BlogPostControllerAttrs, this>) {
    const article = this.attrs.article;

    const articlePost = article.firstPost();

    return (
      <div className={'FoFBlog-Article-Content-Edit-Button'}>
        <div className={'FoFBlog-Article-Content-Edit-Dropdown'}>
          <Dropdown
            icon="fas fa-cog"
            label="Manage"
            buttonClassName="Button"
            menuClassName="Dropdown-menu--right"
            onshow={() => {
              // Get post data to make sure they can edit the post
              if (articlePost && !articlePost.canEdit() && !this.loadedPost) {
                this.loadedPost = true;
                m.redraw();
              }
            }}
          >
            {this.manageArticleButtons().toArray()}
          </Dropdown>
        </div>
      </div>
    );
  }
}
