<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Http;
use App\Models\Content;
use App\Seo\StructuredData;

/**
 * Static pages (/page/{slug}) and repair service pages (/repair/{slug}).
 * Repair pages additionally emit Service schema when genuine service data
 * (service_name, price range) is configured.
 */
final class PageController extends BaseController
{
    public function show(string $slug): void
    {
        $page = Content::pageBySlug($slug, 'static');
        if ($page === null) {
            throw new Http(404, 'This page does not exist.');
        }
        $this->render($page, '/page/' . $page['slug'], 'static');
    }

    public function repair(string $slug): void
    {
        $page = Content::pageBySlug($slug, 'repair');
        if ($page === null) {
            throw new Http(404, 'This service page does not exist.');
        }
        $this->render($page, '/repair/' . $page['slug'], 'repair');
    }

    private function render(array $page, string $canonicalPath, string $kind): void
    {
        $autoTitle = $page['title'];
        $autoDesc  = (string) ($page['intro'] ?? '') ?: excerpt(strip_tags((string) $page['body']), 155);

        $seo = $this->resolveMeta(
            'page',
            (int) $page['id'],
            $autoTitle,
            $autoDesc,
            $canonicalPath,
            ['og_type' => 'website'],
        );

        $crumbs = [['Home', '/'], [$page['title'], $canonicalPath]];
        $schema = [$this->breadcrumbs($crumbs)];

        // Repair service schema — only when the page genuinely carries it.
        $service = StructuredData::service($page, \App\Seo\Url::absolute($canonicalPath));
        if ($service !== null) {
            $schema[] = $service;
        }

        $this->renderPage($seo, $schema, $kind === 'repair' ? 'repair' : 'page', [
            'page'        => $page,
            'breadcrumbs' => $crumbs,
        ]);
    }
}
