import commonExtend from '../common/extend';
import Extend from 'flarum/common/extenders';
import BlogOverview from './pages/BlogOverview';
import BlogItem from './pages/BlogItem';
import BlogMeta from '../common/Models/BlogMeta';
import Discussion from 'flarum/common/models/Discussion';
import Tag from 'ext:flarum/tags/common/models/Tag';

export default [
  ...commonExtend,

  new Extend.Routes() //
    // Order matters: the more specific routes must be registered before the
    // catch-all `/blog/:id`, otherwise `/blog/compose` and `/blog/category/...`
    // get captured as an article id.
    .add('blog', '/blog', BlogOverview)
    // The composer page's `Composer` component extends core's `ComposerBody`,
    // which lives in a lazy core chunk — a class heritage needs its parent at
    // module-evaluation time, so the parent chunk is loaded before our page.
    // https://docs.flarum.org/2.x/extend/code-splitting#extending-split-components
    .add('blogComposer', '/blog/compose', () => import('flarum/forum/components/ComposerBody').then(() => import('./pages/BlogComposer')))
    .add('blogCategory', '/blog/category/:slug', BlogOverview)
    .add('blogArticle', '/blog/:id', BlogItem)
    .add('blogArticle.near', '/blog/:id/:near', BlogItem),

  new Extend.Store() //
    .add('blogMeta', BlogMeta),

  new Extend.Model(Discussion) //
    .hasOne<BlogMeta>('blogMeta'),

  new Extend.Model(Tag) //
    .attribute<boolean>('isBlog'),
];
