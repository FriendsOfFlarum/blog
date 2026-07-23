import app from 'flarum/forum/app';
import Page, { IPageAttrs } from 'flarum/common/components/Page';
import Button from 'flarum/common/components/Button';
import Link from 'flarum/common/components/Link';
import Discussion from 'flarum/common/models/Discussion';
import Tag from 'ext:flarum/tags/common/models/Tag';
import Model from 'flarum/common/Model';
import ItemList from 'flarum/common/utils/ItemList';
import Stream from 'flarum/common/utils/Stream';
import extractText from 'flarum/common/utils/extractText';
import type LanguageDropdownType from '@fof/discussion-language/forum/components/LanguageDropdown';
import type Mithril from 'mithril';

import BlogAuthor from '../components/BlogItemSidebar/BlogAuthor';
import Composer from '../components/Composer/Composer';
import BlogMeta from '../../common/Models/BlogMeta';

/**
 * A `discussion-languages` record provided by the optional
 * `fof/discussion-language` extension.
 */
interface DiscussionLanguage extends Model {
  code(): string;
}

export default class BlogComposer extends Page<IPageAttrs> {
  protected languages!: DiscussionLanguage[];
  protected articleLanguage!: Stream<string>;
  protected article!: Discussion;
  protected blogMeta!: BlogMeta | null;
  protected tags!: Tag[];
  protected isSaving!: boolean;

  oninit(vnode: Mithril.Vnode<IPageAttrs, this>) {
    super.oninit(vnode);

    app.setTitle(app.translator.trans('fof-blog.forum.blog') as string);

    // User cannot write blogs
    if (!app.forum.attribute('canWriteBlogPosts')) {
      m.route.set(app.route('blog'));
      return;
    }

    // Send history push
    app.history.push('blogComposer', extractText(app.translator.trans('fof-blog.forum.blog')));

    // Get languages (if enabled)
    this.languages = app.store.all<DiscussionLanguage>('discussion-languages') || [];

    // Set body class
    this.bodyClass = 'BlogItemPage BlogItemPage--composer';

    // Article data
    this.articleLanguage = Stream(m.route.param('lang') ? m.route.param('lang') : app.data.locale);
    this.article = app.store.createRecord<Discussion>('discussions');
    this.blogMeta = null;

    this.tags = [];

    // Pre-select tags
    const tagsParam = m.route.param('tags') as string | string[] | undefined;
    if (tagsParam) {
      const tagList = Array.isArray(tagsParam) ? tagsParam : tagsParam.split(',');

      if (tagsParam.length > 0) {
        tagList.forEach((tagId) => {
          const foundTag = app.store.getById<Tag>('tags', tagId);

          if (foundTag) {
            this.tags.push(foundTag);
          }
        });
      }
    }

    this.isSaving = false;
  }

  openTagsModal(e: Event | null = null) {
    if (e) {
      e.preventDefault();
    }

    if (this.isSaving) return;

    app.modal.show(() => import('ext:flarum/tags/forum/components/TagDiscussionModal'), {
      selectedTags: this.tags,
      onsubmit: (tags: Tag[]) => {
        this.tags = tags;
      },
    });
  }

  openNameArticleModal(e: Event | null = null) {
    if (e) {
      e.preventDefault();
    }

    if (this.isSaving) return;

    app.modal.show(() => import('../components/Modals/RenameArticleModal'), {
      article: this.article,
      onChange: (title: string) => {
        this.article.pushData({
          attributes: {
            title,
          },
        });
      },
    });
  }

  openBlogSettings(e: Event) {
    e.preventDefault();

    if (this.isSaving) return;

    app.modal.show(() => import('../components/Modals/BlogPostSettingsModal'), {
      meta: this.blogMeta,
      onsubmit: (meta: BlogMeta) => (this.blogMeta = meta),
    });
  }

  view() {
    return (
      <div className={'FlarumBlogItem'}>
        <div className={'container'}>{this.pageItems().toArray()}</div>
      </div>
    );
  }

  pageItems(): ItemList<Mithril.Children> {
    const items = new ItemList<Mithril.Children>();

    items.add(
      'toolButtons',
      <div className="FlarumBlog-ToolButtons">
        <Link href={app.route('blog')} className="Button" loading={this.isSaving} icon="fas fa-angle-left">
          <i class="icon fas fa-angle-left Button-icon" />
          <span class="Button-label">{app.translator.trans('fof-blog.forum.return_to_overview')}</span>
        </Link>
      </div>,
      100
    );

    items.add('article', <div className={'FlarumBlog-Article'}>{this.articleWrapperItems().toArray()}</div>, 90);

    return items;
  }

  articleWrapperItems(): ItemList<Mithril.Children> {
    const items = new ItemList<Mithril.Children>();

    items.add('container', <div className="FlarumBlog-Article-Container">{this.articleItems().toArray()}</div>, 100);

    items.add(
      'sidebar',
      <div className="FlarumBlog-Article-Sidebar">
        <BlogAuthor user={app.session.user} />
      </div>,
      90
    );

    return items;
  }

  articleItems(): ItemList<Mithril.Children> {
    const items = new ItemList<Mithril.Children>();

    const defaultImage = app.forum.attribute('blogDefaultImageUrl') ? `url(${app.forum.attribute<string>('blogDefaultImageUrl')})` : null;

    const blogImage = this.blogMeta && this.blogMeta.featuredImage() ? `url(${this.blogMeta.featuredImage()})` : defaultImage;

    let LanguageDropdown: typeof LanguageDropdownType | undefined;
    if ('fof-discussion-language' in flarum.extensions) {
      const dl = flarum.extensions['fof-discussion-language'] as { components?: { LanguageDropdown: typeof LanguageDropdownType } };
      LanguageDropdown = dl.components?.LanguageDropdown;
    }

    items.add(
      'content',
      <div className="FlarumBlog-Article-Content">
        <div
          className={`FlarumBlog-Article-Image FlarumBlog-default-image`}
          style={{
            backgroundImage: blogImage,
            cursor: 'pointer',
          }}
          onclick={(e: Event) => this.openBlogSettings(e)}
        />

        <div className={'FlarumBlog-Article-Content-Edit-Button'}>
          <div className={this.languages.length === 0 ? 'FlarumBlog-Article-Content-Edit-Dropdown' : 'FlarumBlog-Article-Content-EditButtons'}>
            {LanguageDropdown && this.languages !== null && this.languages.length >= 1 && (
              <LanguageDropdown
                selected={this.articleLanguage()}
                onclick={(language: unknown) => {
                  if (typeof language !== 'string') return;

                  this.articleLanguage(language);
                }}
              />
            )}

            <Button className={'Button'} onclick={(e: Event) => this.openBlogSettings(e)} icon={'fas fa-pencil-alt'} loading={this.isSaving}>
              {app.translator.trans('fof-blog.forum.composer.update_settings')}
            </Button>
          </div>
        </div>

        {/* Article Categories */}
        <div className={'FlarumBlog-Article-Categories'}>
          {this.tags.map((tag) => (
            <button class="Button Button--text" onclick={(e: Event) => this.openTagsModal(e)}>
              {tag.name()}
            </button>
          ))}

          <button class="Button Button--text" onclick={(e: Event) => this.openTagsModal(e)}>
            {this.tags.length === 0
              ? app.translator.trans('fof-blog.forum.composer.select_category')
              : app.translator.trans('fof-blog.forum.composer.edit_categories')}{' '}
            <i className={'fas fa-edit'} />
          </button>
        </div>

        <div className={'FlarumBlog-Article-Post'}>
          {/* Article name */}
          <h1 onclick={() => this.openNameArticleModal()} className="FlarumBlog-Article-Title" style={{ cursor: 'pointer' }}>
            {this.article && this.article.title() && this.article.title() !== ''
              ? this.article.title()
              : app.translator.trans('fof-blog.forum.composer.no_title')}

            <button class="Button Button--text" onclick={(e: Event) => e.preventDefault()}>
              <i className={'fas fa-edit'} />
            </button>
          </h1>

          <div className="Post-body">
            <Composer
              composer={app.composer}
              originalContent={''}
              submitLabel={app.translator.trans('fof-blog.forum.composer.post_article')}
              placeholder={app.translator.trans('fof-blog.forum.composer.enter_message_here')}
              onsubmit={() => this.create()}
              disabled={this.isSaving}
            />
          </div>
        </div>
      </div>,
      100
    );

    items.add(
      'commentsPlaceholder',
      <div className="FlarumBlog-Article-Comments">
        <h4>{app.translator.trans('fof-blog.forum.comment_section.comments')} (0)</h4>
        {/* Locked */}

        <div className="Post-body">
          <blockquote class="uncited">
            <div>
              <span className="fas fa-ban" style={{ marginRight: '5px' }} /> {app.translator.trans('fof-blog.forum.composer.comment_section')}
            </div>
          </blockquote>
        </div>
      </div>,
      90
    );

    return items;
  }

  create() {
    const blogTags = app.forum.attribute<string[]>('blogTags') || [];

    // Force tags
    if (this.tags.length === 0) {
      this.openTagsModal();
      return;
    }

    // Force title
    if (!this.article.title() || this.article.title() === '') {
      this.openNameArticleModal();
      return;
    }

    // Find blog tags
    const findblogTags = this.tags.filter((tag) => {
      return blogTags.indexOf(tag.id()!) >= 0;
    });

    // No blog tags selected
    if (findblogTags.length === 0) {
      alert(app.translator.trans('fof-blog.forum.composer.no_blog_tags_selected') as string);
      return;
    }

    if (
      (this.blogMeta === null || (!this.blogMeta.featuredImage() && !app.forum.attribute('blogDefaultImage')) || !this.blogMeta.summary()) &&
      !confirm(app.translator.trans('fof-blog.forum.composer.post_without_blog_info') as string)
    ) {
      return;
    }

    const relationships: { tags: Tag[]; language?: DiscussionLanguage } = {
      tags: this.tags,
    };

    // Add languages if possible
    if (this.languages.length > 0) {
      relationships.language = app.store.getBy<DiscussionLanguage>('discussion-languages', 'code', this.articleLanguage());
    }

    const data = {
      title: this.article.title(),
      content: app.composer.fields?.content(),
      relationships,
      blogMeta:
        this.blogMeta !== null
          ? {
              featuredImage: this.blogMeta.featuredImage(),
              summary: this.blogMeta.summary(),
              isSized: this.blogMeta.isSized(),
            }
          : null,
    };

    this.isSaving = true;

    this.article
      .save(data)
      .then((article) => {
        setTimeout(() => {
          // Redirect to the article
          m.route.set(app.route('blogArticle', { id: `${(article as Discussion).slug()}` }));
        }, 500);
      })
      .catch(() => {
        this.isSaving = false;
        m.redraw();
      });
  }
}
