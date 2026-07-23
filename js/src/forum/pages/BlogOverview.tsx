import app from 'flarum/forum/app';

import WelcomeHero from 'flarum/forum/components/WelcomeHero';
import Page, { IPageAttrs } from 'flarum/common/components/Page';
import Button from 'flarum/common/components/Button';
import BlogCategories from '../components/BlogCategories';
import Link from 'flarum/common/components/Link';
import extractText from 'flarum/common/utils/extractText';
import getLanguageDropdown from '../utils/getLanguageDropdown';
import ForumNav from '../components/ForumNav';
import BlogOverviewItem from '../components/BlogOverviewItem';
import FeaturedBlogItem from '../components/FeaturedBlogItem';
import BlogListState, { BlogListParams } from '../states/BlogListState';
import type Mithril from 'mithril';
import type Discussion from 'flarum/common/models/Discussion';
import type Tag from 'flarum/tags/common/models/Tag';
import type Model from 'flarum/common/Model';

export default class BlogOverview extends Page {
  protected list!: BlogListState;
  protected languages!: Model[];
  protected currentSelectedLanguage!: string;
  protected featuredCount!: number;
  protected showCategories!: boolean;
  protected showForumNav!: boolean;

  oninit(vnode: Mithril.Vnode<IPageAttrs, this>) {
    super.oninit(vnode);

    app.setTitle(extractText(app.translator.trans('fof-blog.forum.blog')));

    this.bodyClass = 'BlogOverviewPage';

    this.languages = app.store.all<Model>('discussion-languages');

    this.currentSelectedLanguage = m.route.param('lang') ? m.route.param('lang') : app.data.locale;

    // Send history push
    app.history.push('blog', extractText(app.translator.trans('fof-blog.forum.blog')));

    this.featuredCount = parseInt(app.forum.attribute('blogFeaturedCount'));

    this.showCategories = true;
    this.showForumNav = true;

    // The state consumes the preloaded API document, when one is available.
    this.list = new BlogListState(this.listParams());
    this.list.refresh();
  }

  protected listParams(): BlogListParams {
    return {
      category: m.route.param('slug') || undefined,
      language: this.languages.length > 0 ? this.currentSelectedLanguage : undefined,
    };
  }

  /**
   * All loaded articles; the first `featuredCount` are displayed as featured.
   */
  protected articles(): Discussion[] {
    return this.list.getPages().flatMap((page) => page.items);
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

  /**
   * The blog reuses the forum's welcome hero, when enabled.
   */
  hero(): Mithril.Children {
    if (!app.forum.attribute<boolean>('blogAddHero')) return null;

    return <WelcomeHero />;
  }

  view() {
    const defaultImage = app.forum.attribute('blogDefaultImageUrl') ? `url(${app.forum.attribute('blogDefaultImageUrl')})` : null;

    const LanguageDropdown = getLanguageDropdown();

    const loading = this.list.isInitialLoading();
    const articles = this.articles();
    const featuredPosts = articles.slice(0, this.featuredCount);
    const posts = articles.slice(this.featuredCount);

    return [
      this.hero(),
      <div className={'FoFBlogOverview'}>
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

                    this.list.refreshParams(this.listParams(), 1);
                  }}
                />
              )}
            </div>

            {this.title()}

            <div style={{ clear: 'both' }} />

            <div class="BlogFeatured-list">
              {/* Ghost data */}
              {loading &&
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

              {!loading && featuredPosts.map((article) => <FeaturedBlogItem article={article} defaultImage={defaultImage} />)}
            </div>
          </div>

          <div className={'BlogScrubber'}>
            <div className={'BlogList'}>
              {/* Ghost layout mirrors the real list: the third item renders in the "sized" (highlighted) variant. */}
              {loading &&
                [false, false, true, false].map((sized) => {
                  return (
                    <div className={`BlogList-item BlogList-item-${sized ? 'sized' : 'default'} BlogList-item-ghost`}>
                      <div className={'BlogList-item-photo FoFBlog-default-image'}></div>
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

              {!loading && posts.map((article) => <BlogOverviewItem article={article} defaultImage={defaultImage} />)}

              {!loading && articles.length > 0 && !this.list.hasNext() && (
                <p className={'FoFBlog-reached-end'}>{app.translator.trans('fof-blog.forum.no_more_posts')}</p>
              )}

              {!loading && articles.length === 0 && <p className={'FoFBlog-reached-end'}>{app.translator.trans('fof-blog.forum.category_empty')}</p>}

              {!loading && this.list.hasNext() && (
                <div className={'FoFBlog-reached-load-more'}>
                  <Button className={'Button'} onclick={() => this.list.loadNext()} icon={'fas fa-chevron-down'} loading={this.list.isLoadingNext()}>
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
