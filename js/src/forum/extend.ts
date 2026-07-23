import commonExtend from '../common/extend';
import Extend from 'flarum/common/extenders';
import BlogOverview from './pages/BlogOverview';
import BlogItem from './pages/BlogItem';
import BlogComposer from './pages/BlogComposer';
import BlogMeta from '../common/Models/BlogMeta';
import Discussion from 'flarum/common/models/Discussion';
import Tag from 'ext:flarum/tags/common/models/Tag';

export default [
  new Extend.Routes() //
    // Order matters: the more specific routes must be registered before the
    // catch-all `/blog/:id`, otherwise `/blog/compose` and `/blog/category/...`
    // get captured as an article id.
    .add('blog', '/blog', BlogOverview)
    .add('blogComposer', '/blog/compose', BlogComposer)
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
