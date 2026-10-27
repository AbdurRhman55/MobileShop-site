<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Http;
use App\Seo\MerchantFeed;
use App\Seo\RobotsTxt;
use App\Seo\SitemapGenerator;
use App\Seo\SeoSettings;

/**
 * SEO endpoints: sitemaps, robots.txt, Merchant Center feed.
 *
 * Canonical URLs inside these documents are ALWAYS built from the configured
 * canonical domain (Seo\Url), never from the request Host header.
 */
final class SeoController extends BaseController
{
    public function sitemapIndex(): void
    {
        $this->xml(SitemapGenerator::indexXml(), 'sitemap index');
    }

    public function sitemapSection(string $file): void
    {
        // Accept only "{section}-{n}.xml" with a whitelisted section.
        if (!preg_match('/^([a-z][a-z0-9-]*)-(\d+)\.xml$/', $file, $m)) {
            throw new Http(404, 'Unknown sitemap.');
        }
        $allowed = ['home', 'products', 'categories', 'brands', 'pages', 'blog', 'filter-pages'];
        if (!in_array($m[1], $allowed, true)) {
            throw new Http(404, 'Unknown sitemap section.');
        }
        $this->xml(SitemapGenerator::sectionXml($m[1], (int) $m[2]), $m[1] . ' sitemap');
    }

    public function robots(): void
    {
        header('Content-Type: text/plain; charset=utf-8');
        header('Cache-Control: public, max-age=3600');
        echo RobotsTxt::generate();
    }

    public function feed(): void
    {
        if (!SeoSettings::feedEnabled()) {
            throw new Http(404, 'Product feed is not enabled.');
        }
        $this->xml(MerchantFeed::generateXml(), 'product feed');
    }

    private function xml(string $body, string $label): void
    {
        header('Content-Type: application/xml; charset=utf-8');
        header('Cache-Control: public, max-age=1800');
        header('X-Content-Type-Options: nosniff');
        echo $body;
    }
}
