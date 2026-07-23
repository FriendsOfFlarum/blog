import Extend from 'flarum/common/extenders';
import BlogArticleGambit from './gambits/BlogArticleGambit';

export default [
  new Extend.Search() //
    .gambit('discussions', BlogArticleGambit),
];
