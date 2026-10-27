<?php

declare(strict_types=1);

namespace App\Seo;

/**
 * robots.txt generator.
 *
 * Allows crawling of public product/category/brand/blog/page URLs and blocks
 * private application areas (admin, cart, checkout, account, internal search).
 * Always references the production sitemap via the canonical domain.
 *
 * NOTE: robots.txt is a crawl-guidance file, NOT access control. Private
 * areas are additionally protected by authentication; pages that must stay out
 * of the index use the robots meta tag (noindex) as the reliable mechanism.
 */
final class RobotsTxt
{
    public static function generate(): string
    {
        $lines = [];

        if (!SeoSettings::siteIndexable()) {
            // Staging guard — do not accidentally index a staging environment.
            $lines[] = '# Lala Mobiles — STAGING: indexing disabled by administrator.';
            $lines[] = 'User-agent: *';
            $lines[] = 'Disallow: /';
            $lines[] = '';
            $lines[] = 'Sitemap: ' . Url::absolute('/sitemap.xml');
            return implode("\n", $lines) . "\n";
        }

        $lines[] = '# Lala Mobiles — robots.txt';
        $lines[] = '# Generated dynamically. CSS/JS/images are NOT blocked so Google can';
        $lines[] = '# render pages correctly (Core Web Vitals).';
        $lines[] = '';

        $lines[] = 'User-agent: *';
        $lines[] = 'Allow: /';
        // Private / non-indexable application areas
        $lines[] = 'Disallow: /admin';
        $lines[] = 'Disallow: /cart';
        $lines[] = 'Disallow: /checkout';
        $lines[] = 'Disallow: /account';
        $lines[] = 'Disallow: /order/';
        $lines[] = 'Disallow: /search';
        $lines[] = 'Disallow: /feed/products.xml';   # feed is for Merchant Center, not crawl budget
        // Session-id / tracking parameter crawl traps
        $lines[] = 'Disallow: /*?sort=';
        $lines[] = 'Disallow: /*?page=2$';           # only deeper pagination is deduplicated
        $lines[] = '';

        // AI crawlers that opt out (respectful defaults; admin can override)
        $lines[] = 'User-agent: GPTBot';
        $lines[] = 'Disallow: /';
        $lines[] = '';
        $lines[] = 'User-agent: CCBot';
        $lines[] = 'Disallow: /';
        $lines[] = '';

        // Admin-managed extra rules
        $extra = trim((string) SeoSettings::get('robots_txt_extra', ''));
        if ($extra !== '') {
            $lines[] = '# Administrator rules:';
            foreach (explode("\n", $extra) as $line) {
                $lines[] = trim($line);
            }
            $lines[] = '';
        }

        $lines[] = 'Sitemap: ' . Url::absolute('/sitemap.xml');

        return implode("\n", $lines) . "\n";
    }
}
