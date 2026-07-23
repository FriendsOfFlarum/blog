import { BooleanGambit } from 'flarum/common/query/IGambit';
export default class BlogArticleGambit extends BooleanGambit {
    key(): string;
    filterKey(): string;
}
