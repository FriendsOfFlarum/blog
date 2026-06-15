import app from 'flarum/forum/app';

import IndexPage from 'flarum/forum/components/IndexPage';
import Page, { IPageAttrs } from 'flarum/common/components/Page';
import Button from 'flarum/common/components/Button';
import BlogCategories from '../components/BlogCategories';
import Link from 'flarum/common/components/Link';
import extractText from 'flarum/common/utils/extractText';
import type LanguageDropdownType from '@fof/discussion-language/forum/components/LanguageDropdown';
import ForumNav from '../components/ForumNav';
import BlogOverviewItem from '../components/BlogOverviewItem';
import FeaturedBlogItem from '../components/FeaturedBlogItem';
import type Mithril from 'mithril';
import type Discussion from 'flarum/common/models/Discussion';
import type Tag from 'flarum/tags/common/models/Tag';
import type Model from 'flarum/common/Model';
import type { ApiResponsePlural } from 'flarum/common/Store';

export default class BlogOverview extends Page {
  protected isLoading!: boolean;
  protected isLoadingMore!: boolean;
  protected featuredPosts!: Discussion[];
  protected posts!: Discussion[];
  protected hasMore!: string | null;
  protected languages!: Model[];
  protected currentSelectedLanguage!: string;
  protected featuredCount!: number;
  protected showCategories!: boolean;
  protected showForumNav!: boolean;

  oninit(vnode: Mithril.Vnode<IPageAttrs, this>) {
    super.oninit(vnode);

    app.setTitle(app.translator.trans('fof-blog.forum.blog') as string);

    this.bodyClass = 'BlogOverviewPage';

    this.isLoading = true;
    this.featuredPosts = [];
    this.posts = [];
    this.hasMore = null;
    this.isLoadingMore = false;

    this.languages = app.store.all<Model>('discussion-languages');

    this.currentSelectedLanguage = m.route.param('lang') ? m.route.param('lang') : app.data.locale;

    // Send history push
    app.history.push('blog', extractText(app.translator.trans('fof-blog.forum.blog')));

    this.loadBlogOverview();

    this.featuredCount = parseInt(app.forum.attribute('blogFeaturedCount'));

    this.showCategories = true;
    this.showForumNav = true;
  }

  // Load blog overview
  loadBlogOverview() {
    const preloadBlogOverview = app.preloadedApiDocument<Discussion[]>();

    if (preloadBlogOverview) {
      // We must wrap this in a setTimeout because if we are mounting this
      // component for the first time on page load, then any calls to m.redraw
      // will be ineffective and thus any configs (scroll code) will be run
      // before stuff is drawn to the page.
      setTimeout(this.show.bind(this, preloadBlogOverview), 0);
    } else {
      this.reloadData();
    }

    m.redraw();
  }

  reloadData() {
    let q = `is:blog${m.route.param('slug') ? ` tag:${m.route.param('slug')}` : ''}`;

    if (this.languages !== null && this.languages.length >= 1) {
      q += ` language:${this.currentSelectedLanguage}`;
    }

    app.store
      .find<Discussion[]>('discussions', {
        filter: {
          q,
        },
        sort: '-createdAt',
      })
      .then(this.show.bind(this))
      .catch(() => {
        m.redraw();
      });
  }

  // Show blog posts
  show(articles: ApiResponsePlural<Discussion>) {
    if (articles.length === 0) {
      this.isLoading = false;
      m.redraw();

      return;
    }

    // Set pagination
    this.hasMore = articles.payload.links && articles.payload.links.next ? articles.payload.links.next : null;

    this.featuredPosts = articles.slice(0, this.featuredCount);
    this.posts = articles.length > this.featuredCount ? articles.slice(this.featuredCount, articles.length) : [];

    this.isLoading = false;

    m.redraw();
  }

  // Load more blog posts
  loadMore() {
    this.isLoadingMore = true;

    app.store
      .find<Discussion[]>(this.hasMore!.replace(app.forum.attribute('apiUrl'), ''))
      .then((data) => {
        data.map((article) => this.posts.push(article));

        // Update hasmore button
        this.hasMore = data.payload.links && data.payload.links.next ? data.payload.links.next : null;
      })
      .catch(() => {})
      .then(() => {
        this.isLoadingMore = false;
        m.redraw();
      });
  }

  title(): Mithril.Children {
    if (!m.route.param('slug')) {
      return <h2>{app.translator.trans('fof-blog.forum.recent_posts')}</h2>;
    }

    const tag = app.store.all<Tag>('tags').filter((tag) => tag.slug() === m.route.param('slug'));

    return (
      <h2>
        {tag && tag[0] && tag[0].name()}
        <small>
          {' '}
          - <Link href={app.route('blog')}>{app.translator.trans('fof-blog.forum.return_to_overview')}</Link>
        </small>
      </h2>
    );
  }

  view() {
    const defaultImage = app.forum.attribute('blogDefaultImageUrl')
      ? `url(${app.forum.attribute('blogDefaultImageUrl')})`
      : null;

    let LanguageDropdown: typeof LanguageDropdownType | undefined;
    if ('fof-discussion-language' in flarum.extensions) {
      const dl = flarum.extensions['fof-discussion-language'] as { components?: { LanguageDropdown: typeof LanguageDropdownType } };
      LanguageDropdown = dl.components?.LanguageDropdown;
    }

    return [
      app.forum.attribute('blogAddHero') == true && IndexPage.prototype.hero(),
      <div className={'FlarumBlogOverview'}>
        <div className={'container'}>
          <div className={'BlogFeatured'}>
            <div className={'BlogOverviewButtons'}>
              {app.forum.attribute('canWriteBlogPosts') && (
                <Button className={'Button'} onclick={() => this.newArticle()} icon={'fas fa-pencil-alt'}>
                  {app.translator.trans('fof-blog.forum.compose.write_article')}
                </Button>
              )}

              {this.languages !== null && this.languages.length >= 1 && LanguageDropdown && (
                <LanguageDropdown
                  selected={this.currentSelectedLanguage}
                  onclick={(language: unknown) => {
                    if (typeof language !== 'string') return;

                    this.currentSelectedLanguage = language;

                    m.route.set(document.location.pathname, {
                      lang: language,
                    });

                    this.reloadData();
                  }}
                />
              )}
            </div>

            {this.title()}

            <div style={{ clear: 'both' }} />

            <div class="BlogFeatured-list">
              {/* Ghost data */}
              {this.isLoading &&
                [...new Array(this.featuredCount).fill(undefined)].map(() => (
                  <div class="BlogFeatured-list-item BlogFeatured-list-item-ghost">
                    <div class="BlogFeatured-list-item-details">
                      <h4>&nbsp;</h4>

                      <div class="data">
                        <span>
                          <i class="far fa-wave" />
                        </span>
                      </div>
                    </div>
                  </div>
                ))}

              {!this.isLoading &&
                this.featuredPosts.length >= 0 &&
                this.featuredPosts.map((article) => <FeaturedBlogItem article={article} defaultImage={defaultImage} />)}
            </div>
          </div>

          <div className={'BlogScrubber'}>
            <div className={'BlogList'}>
              {this.isLoading &&
                [false, false, true, false].map((state) => {
                  return (
                    <div className={`BlogList-item BlogList-item-${state === true ? 'sized' : 'default'} BlogList-item-ghost`}>
                      <div className={'BlogList-item-photo FlarumBlog-default-image'}></div>
                      <div className={'BlogList-item-content'}>
                        <h4>&nbsp;</h4>
                        <p>&nbsp;</p>

                        <div className={'data'}>
                          <span>
                            <i className={'far fa-wave'} />
                          </span>
                        </div>
                      </div>
                    </div>
                  );
                })}

              {!this.isLoading &&
                this.posts.length >= 1 &&
                this.posts.map((article) => <BlogOverviewItem article={article} defaultImage={defaultImage} />)}

              {!this.isLoading && this.featuredPosts.length > 0 && this.hasMore === null && (
                <p className={'FlarumBlog-reached-end'}>{app.translator.trans('fof-blog.forum.no_more_posts')}</p>
              )}

              {!this.isLoading && this.featuredPosts.length === 0 && this.posts.length === 0 && (
                <p className={'FlarumBlog-reached-end'}>{app.translator.trans('fof-blog.forum.category_empty')}</p>
              )}

              {!this.isLoading && this.hasMore !== null && (
                <div className={'FlarumBlog-reached-load-more'}>
                  <Button className={'Button'} onclick={() => this.loadMore()} icon={'fas fa-chevron-down'} loading={this.isLoadingMore}>
                    {app.translator.trans('core.forum.discussion_list.load_more_button')}
                  </Button>
                </div>
              )}
            </div>

            <div className={'Sidebar'}>
              {this.showCategories && <BlogCategories />}
              {this.showForumNav && <ForumNav />}
            </div>
          </div>
        </div>
      </div>,
    ];
  }

  newArticle() {
    let tags: Tag[] = [];

    // Get current category
    const currentCategory = app.store.getBy<Tag>('tags', 'slug', m.route.param('slug'));

    if (currentCategory) {
      tags.push(currentCategory);
    }

    // Redirect to the composer
    m.route.set(
      app.route('blogComposer', {
        tags: tags.map((tag) => tag.id()).join(),
        lang: this.languages.length > 0 ? this.currentSelectedLanguage : undefined,
      })
    );
  }
}
