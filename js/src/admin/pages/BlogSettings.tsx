import app from 'flarum/admin/app';

import ExtensionPage, { ExtensionPageAttrs } from 'flarum/admin/components/ExtensionPage';
import saveSettings from 'flarum/admin/utils/saveSettings';
import Alert from 'flarum/common/components/Alert';
import Button from 'flarum/common/components/Button';
import FieldSet from 'flarum/common/components/FieldSet';
import Switch from 'flarum/common/components/Switch';
import UploadImageButton from 'flarum/common/components/UploadImageButton';
import type Mithril from 'mithril';

import SelectCategoriesModal from '../components/Modals/SelectCategoriesModal';

type RedirectsEnabled = 'both' | 'discussions_only' | 'tags_only' | 'none';

export default class BlogSettings extends ExtensionPage {
  // Form
  hasChanges: boolean = false;
  isSaving: boolean = false;

  // Settings
  redirectsEnabled!: RedirectsEnabled;
  hideTagsInList!: boolean;
  allowComments!: boolean;
  hideOnDiscussionList!: boolean;
  requiresReviewOnPost!: boolean;
  addCategoryHierarchy!: boolean;
  addSidebarNav!: boolean;
  featuredCount!: number | string;
  blogAddHero!: boolean;

  oninit(vnode: Mithril.Vnode<ExtensionPageAttrs, this>) {
    super.oninit(vnode);

    // Form
    this.hasChanges = false;
    this.isSaving = false;

    // Settings
    this.redirectsEnabled = (app.data.settings.blog_redirects_enabled as RedirectsEnabled) ?? 'both';
    this.hideTagsInList = (app.data.settings.blog_hide_tags as unknown as boolean) ?? true;
    this.allowComments = (app.data.settings.blog_allow_comments as unknown as boolean) ?? true;
    this.hideOnDiscussionList = (app.data.settings.blog_filter_discussion_list as unknown as boolean) ?? false;
    this.requiresReviewOnPost = (app.data.settings.blog_requires_review as unknown as boolean) ?? false;
    this.addCategoryHierarchy = (app.data.settings.blog_category_hierarchy as unknown as boolean) ?? true;
    this.addSidebarNav = (app.data.settings.blog_add_sidebar_nav as unknown as boolean) ?? true;
    this.featuredCount = (app.data.settings.blog_featured_count as unknown as number) ?? 3;
    this.blogAddHero = (app.data.settings.blog_add_hero as unknown as boolean) ?? true;

    // UploadImageButton reads `<name>Url` off the forum attributes to preview
    // the current image; point it at the disk-resolved URL from the server.
    if (app.forum.data.attributes) {
      app.forum.data.attributes.blog_default_imageUrl = app.forum.attribute('blogDefaultImageUrl');
    }
  }

  content() {
    const blogCategoriesCount = app.data.settings.blog_tags ? app.data.settings.blog_tags.split('|').length : 0;

    return (
      <div className="BasicsPage FlarumBlog">
        <div className="container">
          <div className={'FlarumBlog-SelectCategories'}>
            {blogCategoriesCount === 0
              ? app.translator.trans('fof-blog.admin.settings.no_categories_selected')
              : app.translator.trans('fof-blog.admin.settings.selected_category_count', { count: blogCategoriesCount })}

            <Button className="Button" onclick={() => app.modal.show(SelectCategoriesModal)}>
              {app.translator.trans('fof-blog.admin.settings.select_categories_button')}
            </Button>
          </div>

          {FieldSet.component(
            {
              label: app.translator.trans('fof-blog.admin.settings.blog_heading'),
            },
            [
              Switch.component(
                {
                  state: this.allowComments == true,
                  onchange: (val: boolean) => {
                    this.allowComments = val;
                    this.hasChanges = true;
                  },
                },
                [
                  <b>{app.translator.trans('fof-blog.admin.settings.allow_comments_label')}</b>,
                  <div className="helpText">{app.translator.trans('fof-blog.admin.settings.allow_comments_text')}</div>,
                ]
              ),
              Switch.component(
                {
                  state: this.requiresReviewOnPost == true,
                  onchange: (val: boolean) => {
                    this.requiresReviewOnPost = val;
                    this.hasChanges = true;
                  },
                },
                [
                  <b>{app.translator.trans('fof-blog.admin.settings.require_review_label')}</b>,
                  <div className="helpText">{app.translator.trans('fof-blog.admin.settings.require_review_text')}</div>,
                ]
              ),
              Switch.component(
                {
                  state: this.hideOnDiscussionList == true,
                  onchange: (val: boolean) => {
                    this.hideOnDiscussionList = val;
                    this.hasChanges = true;
                  },
                },
                [
                  <b>{app.translator.trans('fof-blog.admin.settings.hide_on_discussion_list_label')}</b>,
                  <div className="helpText">{app.translator.trans('fof-blog.admin.settings.hide_on_discussion_list_text')}</div>,
                ]
              ),
              Switch.component(
                {
                  state: this.addSidebarNav == true,
                  onchange: (val: boolean) => {
                    this.addSidebarNav = val;
                    this.hasChanges = true;
                  },
                },
                [
                  <b>{app.translator.trans('fof-blog.admin.settings.add_sidebar_nav_label')}</b>,
                  <div className="helpText">{app.translator.trans('fof-blog.admin.settings.add_sidebar_nav_text')}</div>,
                ]
              ),
              Switch.component(
                {
                  state: this.blogAddHero == true,
                  onchange: (val: boolean) => {
                    this.blogAddHero = val;
                    this.hasChanges = true;
                  },
                },
                [
                  <b>{app.translator.trans('fof-blog.admin.settings.add_hero_label')}</b>,
                  <div className="helpText">{app.translator.trans('fof-blog.admin.settings.add_hero_text')}</div>,
                ]
              ),

              <div className="Form-group">
                {<label>{app.translator.trans('fof-blog.admin.settings.featured_count_label')}</label>}
                <div className="helpText">{app.translator.trans('fof-blog.admin.settings.featured_count_text')}</div>
                <input
                  class="FormControl"
                  value={this.featuredCount}
                  oninput={(e: InputEvent) => {
                    this.featuredCount = (e.target as HTMLInputElement).value;
                    this.hasChanges = true;
                  }}
                  placeholder="3"
                  type="number"
                />
              </div>,
            ]
          )}

          {FieldSet.component(
            {
              label: app.translator.trans('fof-blog.admin.settings.categories_heading'),
            },
            [
              Switch.component(
                {
                  state: this.hideTagsInList == true,
                  onchange: (val: boolean) => {
                    this.hideTagsInList = val;
                    this.hasChanges = true;
                  },
                },
                [
                  <b>{app.translator.trans('fof-blog.admin.settings.hide_tags_in_taglist_label')}</b>,
                  <div className="helpText">{app.translator.trans('fof-blog.admin.settings.hide_tags_in_taglist_text')}</div>,
                ]
              ),
              Switch.component(
                {
                  state: this.addCategoryHierarchy == true,
                  onchange: (val: boolean) => {
                    this.addCategoryHierarchy = val;
                    this.hasChanges = true;
                  },
                },
                [
                  <b>{app.translator.trans('fof-blog.admin.settings.show_tag_hierarchy_label')}</b>,
                  <div className="helpText">{app.translator.trans('fof-blog.admin.settings.show_tag_hierarchy_text')}</div>,
                ]
              ),
            ]
          )}

          {FieldSet.component(
            {
              label: app.translator.trans('fof-blog.admin.settings.redirects_heading'),
            },
            [
              Switch.component(
                {
                  state: this.redirectsEnabled === 'both' || this.redirectsEnabled === 'discussions_only',
                  onchange: (val: boolean) => {
                    if (val) {
                      // Add
                      if (this.redirectsEnabled === 'tags_only') {
                        this.redirectsEnabled = 'both';
                      } else if (this.redirectsEnabled === 'none') {
                        this.redirectsEnabled = 'discussions_only';
                      }
                    } else {
                      if (this.redirectsEnabled === 'discussions_only') {
                        this.redirectsEnabled = 'none';
                      } else {
                        this.redirectsEnabled = 'tags_only';
                      }
                    }

                    this.hasChanges = true;
                  },
                },
                [
                  <b>{app.translator.trans('fof-blog.admin.settings.redirect_articles_label')}</b>,
                  <div className="helpText">{app.translator.trans('fof-blog.admin.settings.redirect_articles_text')}</div>,
                ]
              ),
              Switch.component(
                {
                  state: this.redirectsEnabled === 'both' || this.redirectsEnabled === 'tags_only',
                  onchange: (val: boolean) => {
                    if (val) {
                      // Add
                      if (this.redirectsEnabled === 'discussions_only') {
                        this.redirectsEnabled = 'both';
                      } else if (this.redirectsEnabled === 'none') {
                        this.redirectsEnabled = 'tags_only';
                      }
                    } else {
                      if (this.redirectsEnabled === 'tags_only') {
                        this.redirectsEnabled = 'none';
                      } else {
                        this.redirectsEnabled = 'discussions_only';
                      }
                    }

                    this.hasChanges = true;
                  },
                },
                [
                  <b>{app.translator.trans('fof-blog.admin.settings.redirect_tags_label')}</b>,
                  <div className="helpText">{app.translator.trans('fof-blog.admin.settings.redirect_tags_text')}</div>,
                ]
              ),
            ]
          )}

          {FieldSet.component(
            {
              label: app.translator.trans('fof-blog.admin.settings.default_article_image_label'),
            },
            [
              <div className="helpText">{app.translator.trans('fof-blog.admin.settings.default_article_image_text')}</div>,
              UploadImageButton.component({
                name: 'blog_default_image',
                routePath: 'blog_default_image',
                value: app.data.settings['blog_default_image_path'],
                url: app.forum.attribute('blog_default_imageUrl'),
              }),
            ]
          )}

          <Button loading={this.isSaving} className={'Button Button--primary'} onclick={() => this.save()} disabled={!this.hasChanges}>
            {app.translator.trans('core.admin.settings.submit_button')}
          </Button>
        </div>
      </div>
    );
  }

  /**
   * Save data
   */
  save() {
    this.isSaving = true;

    saveSettings({
      blog_add_sidebar_nav: this.addSidebarNav,
      blog_redirects_enabled: this.redirectsEnabled,
      blog_hide_tags: this.hideTagsInList,
      blog_requires_review: this.requiresReviewOnPost,
      blog_allow_comments: this.allowComments,
      blog_category_hierarchy: this.addCategoryHierarchy,
      blog_filter_discussion_list: this.hideOnDiscussionList,
      blog_featured_count: this.featuredCount,
      blog_add_hero: this.blogAddHero,
    })
      .then(() => {
        this.hasChanges = false;

        // Show saved message
        app.alerts.show(Alert, { type: 'success' }, app.translator.trans('core.admin.settings.saved_message'));
      })
      .catch(() => {})
      .then(() => {
        this.isSaving = false;
        m.redraw();
      });
  }
}
