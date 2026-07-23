import app from 'flarum/admin/app';
import Extend from 'flarum/common/extenders';
import commonExtend from '../common/extend';
import BlogSettings from './pages/BlogSettings';
import { BLOG_PERMISSION_CATEGORY } from './permissions';

export default [
  ...commonExtend,

  new Extend.Admin()
    .page(BlogSettings)
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
