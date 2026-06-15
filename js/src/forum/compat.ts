import { compat } from '@flarum/core/forum';
import BlogMeta from '../common/Models/BlogMeta';
import ArticleSubscription from './components/ArticleSubscription';
import BlogCategories from './components/BlogCategories';
import BlogAuthor from './components/BlogItemSidebar/BlogAuthor';
import BlogItemSidebar from './components/BlogItemSidebar/BlogItemSidebar';
import BlogOverviewItem from './components/BlogOverviewItem';
import BlogPostController from './components/BlogPostController';
import Composer from './components/Composer/Composer';
import ComposerPreview from './components/Composer/ComposerPreview';
import FeaturedBlogItem from './components/FeaturedBlogItem';
import ForumNav from './components/ForumNav';
import BlogPostSettingsModal from './components/Modals/BlogPostSettingsModal';
import RenameArticleModal from './components/Modals/RenameArticleModal';
import BlogItem from './pages/BlogItem';
import BlogOverview from './pages/BlogOverview';

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
