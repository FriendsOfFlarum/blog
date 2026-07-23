import { compat } from '@flarum/core/forum';
import '../common/Models/BlogMeta';
import './components/ArticleSubscription';
import './components/BlogCategories';
import './components/BlogItemSidebar/BlogAuthor';
import './components/BlogItemSidebar/BlogItemSidebar';
import './components/BlogOverviewItem';
import './components/BlogPostController';
import './components/Composer/Composer';
import './components/Composer/ComposerPreview';
import './components/FeaturedBlogItem';
import './components/ForumNav';
import './components/Modals/BlogPostSettingsModal';
import './components/Modals/RenameArticleModal';
import './pages/BlogItem';
import './pages/BlogOverview';

export default () => {
  Object.assign(compat, {
    'fof/blog/components/BlogItemSidebar': BlogItemSidebar,
    'fof/blog/components/BlogAuthor': BlogAuthor,

    'fof/blog/components/Composer/Composer': Composer,
    'fof/blog/components/Composer/ComposerPreview': ComposerPreview,

    'fof/blog/components/Modals/BlogPostSettingsModal': BlogPostSettingsModal,
    'fof/blog/components/Modals/RenameArticleModal': RenameArticleModal,

    'fof/blog/components/ArticleSubscription': ArticleSubscription,
    'fof/blog/components/BlogCategories': BlogCategories,
    'fof/blog/components/BlogOverviewItem': BlogOverviewItem,
    'fof/blog/components/BlogPostController': BlogPostController,
    'fof/blog/components/FeaturedBlogItem': FeaturedBlogItem,
    'fof/blog/components/ForumNav': ForumNav,

    'fof/blog/pages/BlogItem': BlogItem,
    'fof/blog/pages/BlogOverview': BlogOverview,

    'fof/blog/models/BlogMeta': BlogMeta,
  });
};
