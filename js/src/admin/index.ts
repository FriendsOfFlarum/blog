import app from 'flarum/admin/app';
import { extend } from 'flarum/common/extend';
import BasicsPage from 'flarum/admin/components/BasicsPage';
import PermissionGrid from 'flarum/admin/components/PermissionGrid';
import { BLOG_PERMISSION_CATEGORY } from './permissions';

export { default as extend } from './extend';

app.initializers.add('fof-blog', () => {
  // Add the blog's custom permission category to the permission grid
  extend(PermissionGrid.prototype, 'permissionItems', function (items) {
    // Add blog permissions
    items.add(
      'blog',
      {
        label: app.translator.trans('fof-blog.admin.blog'),
        children: this.attrs.extensionId
          ? app.registry.getExtensionPermissions(this.attrs.extensionId!, BLOG_PERMISSION_CATEGORY).toArray()
          : app.registry.getAllPermissions(BLOG_PERMISSION_CATEGORY).toArray(),
      },
      80
    );
  });

  extend(BasicsPage, 'homePageItems', (items) => {
    items.add('blog', {
      path: '/blog',
      label: app.translator.trans('fof-blog.admin.blog'),
    });
  });
});
