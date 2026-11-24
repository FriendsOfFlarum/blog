import bootstrapForum from '@flarum/jest-config/src/bootstrap/forum';
import app from 'flarum/forum/app';
import extend from '../../../src/common/extend';

beforeAll(() => {
  bootstrapForum();
  app.bootExtensions({ 'fof-blog': { extend } });
  app.boot();
  app.translator.addTranslations({
    'core.lib.gambits.group_keys.is': 'статус',
    'fof-blog.lib.gambits.blog.key': 'блог',
  });
});

it('turns the translated gambit into the blog filter', () => {
  expect(app.search.gambits.apply('discussions', { q: 'статус:блог' })).toStrictEqual({ q: '', blog: true });
});

it('still turns the English is:blog into the blog filter on a translated forum', () => {
  expect(app.search.gambits.apply('discussions', { q: 'is:blog' })).toStrictEqual({ q: '', blog: true });
});

it('still turns the negated English -is:blog into the negated blog filter on a translated forum', () => {
  expect(app.search.gambits.apply('discussions', { q: '-is:blog' })).toStrictEqual({ q: '', '-blog': true });
});
