import app from 'flarum/forum/app';
import redirector from './utils/redirector';
import extendTagOverview from './utils/extendTagOverview';
import discussionRouting from './utils/discussionRouting';
import compat from './compat';
import addSidebarNav from './utils/addSidebarNav';

export { default as extend } from './extend';

// Register Flarum Blog
app.initializers.add(
  'fof-blog',
  () => {
    // Redirect discussions/tags to their blog post/overview
    redirector();

    // Extend tag overview.
    // Hide tags which are used as blog category
    extendTagOverview();

    // Make that blog articles have a blog route and not a discussion route
    discussionRouting();

    // Add a link to the blog to the IndexPage sidebar, if enabled.
    addSidebarNav();
  },
  -100000
);

compat();
