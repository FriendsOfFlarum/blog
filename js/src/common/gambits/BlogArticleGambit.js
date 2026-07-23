import app from 'flarum/common/app';
import { BooleanGambit } from 'flarum/common/query/IGambit';

export default class BlogArticleGambit extends BooleanGambit {
  key() {
    return app.translator.trans('fof-blog.lib.gambits.blog.key', {}, true);
  }

  filterKey() {
    return 'blog';
  }
}
