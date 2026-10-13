import { jest } from '@jest/globals';
import bootstrapAdmin from '@flarum/jest-config/src/bootstrap/admin';
import app from 'flarum/admin/app';
import PermissionGrid from 'flarum/admin/components/PermissionGrid';
import GeneralSearchSource from 'flarum/admin/components/GeneralSearchSource';
import { extend } from '../../../src/admin';
import loadTranslations from '../loadTranslations';

/** Boots the admin through the extension's real entry point, so its extenders and initializer run as in production. */
beforeAll(() => {
  bootstrapAdmin({
    extensions: {
      'fof-blog': { id: 'fof-blog', extra: { 'flarum-extension': { title: 'FoF Blog' } } },
    },
  });
  loadTranslations();
  app.bootExtensions({ 'fof-blog': { extend } } as any);
  app.boot();
});

afterEach(() => jest.restoreAllMocks());

// The admin search reads permission categories straight off the prototype, with no attrs (#198).
it('adds the blog category when permissionItems is called outside a rendered grid', () => {
  const handleError = jest.spyOn(app, 'handleErrorOnce');

  const items = PermissionGrid.prototype.permissionItems();

  expect(handleError).not.toHaveBeenCalled();
  expect(items.has('blog')).toBe(true);
  expect(items.get('blog').children.map((child) => child.permission)).toEqual([
    'blog.writeArticles',
    'blog.autoApprovePosts',
    'blog.canApprovePosts',
  ]);
});

it('searches the admin without an extension error', async () => {
  const handleError = jest.spyOn(app, 'handleErrorOnce');

  await new GeneralSearchSource().search('upload', 10);

  expect(handleError).not.toHaveBeenCalled();
});

it('lists blog permissions in admin search under the Blog heading', async () => {
  const source = new GeneralSearchSource();

  await source.search('blog articles', 10);

  const result = (source as any).results.get('blog articles').find((r: { id: string }) => r.id === 'fof-blog-blog-blog.writeArticles');

  expect(result.tree.map(String)).toEqual(['Permissions', 'Blog', 'Write and edit blog articles']);
});
