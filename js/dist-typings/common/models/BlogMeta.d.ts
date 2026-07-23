import Model from 'flarum/common/Model';
import Discussion from 'flarum/common/models/Discussion';
export default class BlogMeta extends Model {
    discussion(): false | Discussion | null;
    featuredImage(): string | null;
    summary(): string | null;
    isFeatured(): boolean;
    isSized(): boolean;
    isPendingReview(): boolean;
}
