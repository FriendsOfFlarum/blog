import app from 'flarum/admin/app';
import Extend from 'flarum/common/extenders';
import commonExtend from '../common/extend';
import { BLOG_PERMISSION_CATEGORY } from './permissions';

export default [
  ...commonExtend,

  new Extend.Admin()
    .setting(
      () => ({
        setting: 'blog_tags',
        type: 'fof-blog.select-categories',
        label: app.translator.trans('fof-blog.admin.settings.categories_heading'),
      }),
      100
    )
    .setting(
      () => ({
        setting: 'blog_allow_comments',
        type: 'boolean',
        label: app.translator.trans('fof-blog.admin.settings.allow_comments_label'),
        help: app.translator.trans('fof-blog.admin.settings.allow_comments_text'),
      }),
      95
    )
    .setting(
      () => ({
        setting: 'blog_requires_review',
        type: 'boolean',
        label: app.translator.trans('fof-blog.admin.settings.require_review_label'),
        help: app.translator.trans('fof-blog.admin.settings.require_review_text'),
      }),
      90
    )
    .setting(
      () => ({
        setting: 'blog_filter_discussion_list',
        type: 'boolean',
        label: app.translator.trans('fof-blog.admin.settings.hide_on_discussion_list_label'),
        help: app.translator.trans('fof-blog.admin.settings.hide_on_discussion_list_text'),
      }),
      85
    )
    .setting(
      () => ({
        setting: 'blog_add_sidebar_nav',
        type: 'boolean',
        label: app.translator.trans('fof-blog.admin.settings.add_sidebar_nav_label'),
        help: app.translator.trans('fof-blog.admin.settings.add_sidebar_nav_text'),
      }),
      80
    )
    .setting(
      () => ({
        setting: 'blog_add_hero',
        type: 'boolean',
        label: app.translator.trans('fof-blog.admin.settings.add_hero_label'),
        help: app.translator.trans('fof-blog.admin.settings.add_hero_text'),
      }),
      75
    )
    .setting(
      () => ({
        setting: 'blog_featured_count',
        type: 'number',
        min: 0,
        label: app.translator.trans('fof-blog.admin.settings.featured_count_label'),
        help: app.translator.trans('fof-blog.admin.settings.featured_count_text'),
      }),
      70
    )
    .setting(
      () => ({
        setting: 'blog_hide_tags',
        type: 'boolean',
        label: app.translator.trans('fof-blog.admin.settings.hide_tags_in_taglist_label'),
        help: app.translator.trans('fof-blog.admin.settings.hide_tags_in_taglist_text'),
      }),
      65
    )
    .setting(
      () => ({
        setting: 'blog_category_hierarchy',
        type: 'boolean',
        label: app.translator.trans('fof-blog.admin.settings.show_tag_hierarchy_label'),
        help: app.translator.trans('fof-blog.admin.settings.show_tag_hierarchy_text'),
      }),
      60
    )
    .setting(
      () => ({
        setting: 'blog_redirects_enabled',
        type: 'select',
        default: 'both',
        options: {
          both: app.translator.trans('fof-blog.admin.settings.redirects_options.both'),
          discussions_only: app.translator.trans('fof-blog.admin.settings.redirects_options.discussions_only'),
          tags_only: app.translator.trans('fof-blog.admin.settings.redirects_options.tags_only'),
          none: app.translator.trans('fof-blog.admin.settings.redirects_options.none'),
        },
        label: app.translator.trans('fof-blog.admin.settings.redirects_heading'),
        help: app.translator.trans('fof-blog.admin.settings.redirects_text'),
      }),
      55
    )
    .setting(
      () => ({
        setting: 'blog_default_image_path',
        type: 'image-upload',
        name: 'blog_default_image',
        routePath: 'blog_default_image',
        value: () => app.data.settings.blog_default_image_path,
        url: () => app.forum.attribute<string>('blogDefaultImageUrl'),
        label: app.translator.trans('fof-blog.admin.settings.default_article_image_label'),
        help: app.translator.trans('fof-blog.admin.settings.default_article_image_text'),
      }),
      50
    )
    .permission(
      () => ({
        icon: 'fas fa-pencil-alt',
        label: app.translator.trans('fof-blog.admin.permissions.write_articles'),
        permission: 'blog.writeArticles',
      }),
      BLOG_PERMISSION_CATEGORY,
      90
    )
    .permission(
      () => ({
        icon: 'far fa-star',
        label: app.translator.trans('fof-blog.admin.permissions.auto_approve_posts'),
        permission: 'blog.autoApprovePosts',
      }),
      BLOG_PERMISSION_CATEGORY,
      90
    )
    .permission(
      () => ({
        icon: 'far fa-thumbs-up',
        label: app.translator.trans('fof-blog.admin.permissions.approve_posts'),
        permission: 'blog.canApprovePosts',
      }),
      BLOG_PERMISSION_CATEGORY,
      90
    ),
];
