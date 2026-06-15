import app from 'flarum/admin/app';
import { extend } from 'flarum/common/extend';
import BasicsPage from 'flarum/admin/components/BasicsPage';
import PermissionGrid from 'flarum/admin/components/PermissionGrid';
import BlogSettings from './pages/BlogSettings';
import { BLOG_PERMISSION_CATEGORY } from './permissions';

export { default as extend } from './extend';

app.initializers.add('fof-blog', () => {
  // Register extension settings page
  app.extensionData.for('fof-blog').registerPage(BlogSettings);

  app.extensionData
    .for('fof-blog')
    .registerPermission(
      {
        icon: 'fas fa-pencil-alt',
        label: app.translator.trans('fof-blog.admin.permissions.write_articles'),
        permission: 'blog.writeArticles',
      },
      BLOG_PERMISSION_CATEGORY,
      90
    )
    .registerPermission(
      {
        icon: 'far fa-star',
        label: app.translator.trans('fof-blog.admin.permissions.auto_approve_posts'),
        permission: 'blog.autoApprovePosts',
      },
      BLOG_PERMISSION_CATEGORY,
      90
    )
    .registerPermission(
      {
        icon: 'far fa-thumbs-up',
        label: app.translator.trans('fof-blog.admin.permissions.approve_posts'),
        permission: 'blog.canApprovePosts',
      },
      BLOG_PERMISSION_CATEGORY,
      90
    );

  // Add addPermissions
  extend(PermissionGrid.prototype, 'permissionItems', function (items) {
    // Add blog permissions
    items.add(
      'blog',
      {
        label: app.translator.trans('fof-blog.admin.blog'),
        children: this.attrs.extensionId
          ? app.extensionData.getExtensionPermissions(this.attrs.extensionId!, BLOG_PERMISSION_CATEGORY).toArray()
          : app.extensionData.getAllExtensionPermissions(BLOG_PERMISSION_CATEGORY).toArray(),
      },
      80
    );
  });

  extend(BasicsPage.prototype, 'homePageItems', (items) => {
    items.add('blog', {
      path: '/blog',
      label: app.translator.trans('fof-blog.admin.blog'),
    });
  });
});
