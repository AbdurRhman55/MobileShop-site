<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Http;
use App\Models\Content;
use App\Seo\SeoSettings;
use App\Seo\StructuredData;

/** Blog index + post. Blog can be disabled entirely from admin settings. */
final class BlogController extends BaseController
{
    public function index(): void
    {
        if (!SeoSettings::blogEnabled()) {
            throw new Http(404, 'The blog is not available.');
        }

        $page    = max(1, (int) ($this->request->query('page') ?? 1));
        $listing = Content::blogIndex($page);

        $canonicalPath = $page > 1 ? '/blog?page=' . $page : '/blog';
        $seo = $this->resolveMeta(
            'blog',
            0,
            'Mobile Phone Guides & Tips — Lala Mobiles Blog',
            'Buying guides, comparisons and repair advice for mobile phones in Pakistan from the Lala Mobiles team.',
            $canonicalPath,
            ['robots' => $page > 3 ? 'nofollow' : 'index,follow', 'og_type' => 'website'],
        );

        $crumbs = [['Home', '/'], ['Blog', '/blog']];
        $schema = [$this->breadcrumbs($crumbs)];

        $this->renderPage($seo, $schema, 'blog-index', [
            'listing'     => $listing,
            'breadcrumbs' => $crumbs,
            'basePath'    => '/blog',
        ]);
    }

    public function show(string $slug): void
    {
        if (!SeoSettings::blogEnabled()) {
            throw new Http(404, 'The blog is not available.');
        }

        $post = Content::blogBySlug($slug);
        if ($post === null) {
            if (\App\Models\Redirect::resolve('/blog/' . $slug) !== null) {
                \App\Seo\RedirectManager::redirectIfFound('/blog/' . $slug);
            }
            throw new Http(404, 'This article does not exist.');
        }

        $canonicalPath = '/blog/' . $post['slug'];
        $autoTitle = $post['title'];
        $autoDesc  = (string) ($post['excerpt'] ?? '') ?: excerpt(strip_tags((string) $post['body']), 155);

        $seo = $this->resolveMeta(
            'blog',
            (int) $post['id'],
            $autoTitle,
            $autoDesc,
            $canonicalPath,
            ['og_type' => 'article'],
        );

        $crumbs = [
            ['Home', '/'],
            ['Blog', '/blog'],
            [$post['title'], $canonicalPath],
        ];

        $schema = [
            $this->breadcrumbs($crumbs),
            StructuredData::blogPosting($post, \App\Seo\Url::absolute($canonicalPath)),
        ];

        $this->renderPage($seo, $schema, 'blog-post', [
            'post'        => $post,
            'breadcrumbs' => $crumbs,
        ]);
    }
}
