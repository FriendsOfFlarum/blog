import Model from 'flarum/common/Model';
import Discussion from 'flarum/common/models/Discussion';

export default class BlogMeta extends Model {
  discussion(): false | Discussion | null {
    return Model.hasOne<Discussion>('discussion').call(this);
  }

  featuredImage(): string | null {
    return Model.attribute<string | null>('featuredImage').call(this);
  }

  summary(): string | null {
    return Model.attribute<string | null>('summary').call(this);
  }

  isFeatured(): boolean {
    return Model.attribute<boolean>('isFeatured').call(this);
  }

  isSized(): boolean {
    return Model.attribute<boolean>('isSized').call(this);
  }

  isPendingReview(): boolean {
    return Model.attribute<boolean>('isPendingReview').call(this);
  }
}
