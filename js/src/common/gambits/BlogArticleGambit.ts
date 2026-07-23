import app from 'flarum/common/app';
import { BooleanGambit } from 'flarum/common/query/IGambit';

export default class BlogArticleGambit extends BooleanGambit {
  key(): string {
    return app.translator.trans('fof-blog.lib.gambits.blog.key', {}, true);
  }

  filterKey(): string {
    return 'blog';
  }
}
