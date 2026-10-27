<?php

declare(strict_types=1);

namespace App\Seo;

use App\Core\Database;

/**
 * Dynamic XML sitemap generator.
 *
 * Emits a sitemap index at /sitemap.xml pointing at section sitemaps:
 *   /sitemap/products.xml   /sitemap/categories.xml   /sitemap/pages.xml
 *   /sitemap/blog.xml (when blog enabled)   /sitemap/filter-pages.xml
 *
 * Only canonical, public, indexable URLs are included. Large sections are
 * split into numbered files respecting Google's 50,000 URL / 50 MB limits.
 * Results are cached on disk for one hour and invalidated on content change
 * (MAX(updated_at) is checked against the cached fingerprint).
 */
final class SitemapGenerator
{
    private const MAX_URLS = 50000;

    /** Cache file per section, keyed by content fingerprint. */
    private static function cacheDir(): string
    {
        $dir = BASE_PATH . '/storage/cache/sitemaps';
        if (!is_dir($dir)) {
            mkdir($dir, 0775, true);
        }
        return $dir;
    }

    /** Build the sitemap index document. */
    public static function indexXml(): string
    {
        if (!SeoSettings::siteIndexable() || SeoSettings::get('sitemap_enabled', '1') !== '1') {
            // Staging guard: empty index (no URLs to crawl).
            return self::xmlHeader() . '<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"></sitemapindex>';
        }

        $sections = self::sections();
        $lastmod  = self::globalLastModified();

        $out = [self::xmlHeader()];
        $out[] = '<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';
        foreach ($sections as $section => $count) {
            $files = (int) ceil(max(1, $count) / self::MAX_URLS);
            for ($i = 1; $i <= $files; $i++) {
                $loc  = Url::absolute('/sitemap/' . $section . '-' . $i . '.xml');
                $out[] = '  <sitemap><loc>' . xml_e($loc) . '</loc><lastmod>' . $lastmod . '</lastmod></sitemap>';
            }
        }
        $out[] = '</sitemapindex>';
        return implode("\n", $out);
    }

    /** Build one section sitemap (page $page of the section). */
    public static function sectionXml(string $section, int $page = 1): string
    {
        if (!SeoSettings::siteIndexable() || SeoSettings::get('sitemap_enabled', '1') !== '1') {
            return self::xmlHeader() . '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"></urlset>';
        }

        $cacheFile = self::cacheDir() . '/' . $section . '-' . $page . '.xml';
        $fpFile    = self::cacheDir() . '/' . $section . '.fp';

        $fingerprint = self::sectionFingerprint($section);
        if (is_readable($cacheFile) && is_readable($fpFile) && trim((string) file_get_contents($fpFile)) === $fingerprint) {
            $age = time() - (int) filemtime($cacheFile);
            if ($age < (int) \App\Core\Config::get('sitemap.cache_ttl', 3600)) {
                return (string) file_get_contents($cacheFile);
            }
        }

        $urls = self::collectUrls($section, $page);
        $out  = [self::xmlHeader()];
        $out[] = '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';
        foreach ($urls as $u) {
            $out[] = '  <url>';
            $out[] = '    <loc>' . xml_e($u['loc']) . '</loc>';
            if (!empty($u['lastmod'])) {
                $out[] = '    <lastmod>' . xml_e($u['lastmod']) . '</lastmod>';
            }
            if (!empty($u['changefreq'])) {
                $out[] = '    <changefreq>' . xml_e($u['changefreq']) . '</changefreq>';
            }
            if (isset($u['priority'])) {
                $out[] = '    <priority>' . number_format((float) $u['priority'], 1) . '</priority>';
            }
            $out[] = '  </url>';
        }
        $out[] = '</urlset>';
        $xml = implode("\n", $out);

        // Validate well-formedness before caching/serving.
        $prev = libxml_use_internal_errors(true);
        $ok   = simplexml_load_string($xml) !== false;
        libxml_clear_errors();
        libxml_use_internal_errors($prev);
        if (!$ok) {
            error_log("[SEO] Sitemap section '$section' produced invalid XML; serving empty urlset.");
            $xml = self::xmlHeader() . '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"></urlset>';
        }

        file_put_contents($cacheFile, $xml);
        file_put_contents($fpFile, $fingerprint);
        return $xml;
    }

    /** Drop the on-disk cache (called by the admin after bulk changes). */
    public static function flushCache(): void
    {
        foreach (glob(self::cacheDir() . '/*') ?: [] as $file) {
            @unlink($file);
        }
    }

    /* -------------------------------------------------------- internals -- */

    /** @return array<string, int> section => total URL count */
    private static function sections(): array
    {
        $sections = [
            'home'         => 1,
            'products'     => self::countActive('products'),
            'categories'   => self::countActive('categories'),
            'brands'       => self::countActive('brands'),
            'pages'        => self::countActive('pages'),
            'filter-pages' => self::countActive('filter_pages'),
        ];
        if (SeoSettings::blogEnabled()) {
            $sections['blog'] = self::countActive('blog_posts');
        }
        return $sections;
    }

    private static function countActive(string $table): int
    {
        switch ($table) {
            case 'products':
                return (int) Database::fetchColumn(
                    "SELECT COUNT(*) FROM products p
                     WHERE p.status IN ('active','discontinued')" . self::indexableClause('product', 'p')
                );
            case 'categories':
                return (int) Database::fetchColumn(
                    "SELECT COUNT(*) FROM categories c WHERE c.status = 'active'" . self::indexableClause('category', 'c')
                );
            case 'brands':
                return (int) Database::fetchColumn(
                    "SELECT COUNT(*) FROM brands b WHERE b.status = 'active'" . self::indexableClause('brand', 'b')
                );
            case 'pages':
                return (int) Database::fetchColumn(
                    "SELECT COUNT(*) FROM pages p WHERE p.status = 'published'" . self::indexableClause('page', 'p')
                );
            case 'filter_pages':
                return (int) Database::fetchColumn(
                    "SELECT COUNT(*) FROM filter_pages f WHERE f.status = 'published' AND f.is_indexable = 1"
                    . self::indexableClause('filter', 'f')
                );
            case 'blog_posts':
                return (int) Database::fetchColumn(
                    "SELECT COUNT(*) FROM blog_posts b WHERE b.status = 'published'" . self::indexableClause('blog', 'b')
                );
            default:
                return 0;
        }
    }

    /**
     * SQL fragment excluding entities the admin explicitly marked noindex.
     *
     * Sitemaps must never list a URL whose robots meta says noindex — search
     * engines treat that as a contradiction. Requires a LEFT JOIN alias that
     * the caller has already joined as `m` on seo_meta.
     */
    private static function indexableClause(string $entityType, string $alias): string
    {
        return " AND NOT EXISTS (
            SELECT 1 FROM seo_meta m
            WHERE m.entity_type = '{$entityType}'
              AND m.entity_id = {$alias}.id
              AND m.robots LIKE '%noindex%'
        )";
    }

    /** @return array<int, array{loc:string,lastmod:string,changefreq?:string,priority?:float}> */
    private static function collectUrls(string $section, int $page): array
    {
        $offset = ($page - 1) * self::MAX_URLS;

        switch ($section) {
            case 'home':
                return [[
                    'loc' => Url::absolute('/'),
                    'lastmod' => self::globalLastModified(),
                    'changefreq' => 'daily',
                    'priority' => 1.0,
                ]];

            case 'products':
                $rows = Database::fetchAll(
                    "SELECT p.slug, p.updated_at FROM products p
                     WHERE p.status IN ('active','discontinued')" . self::indexableClause('product', 'p') . "
                     ORDER BY p.id ASC LIMIT " . self::MAX_URLS . " OFFSET $offset"
                );
                return array_map(static fn(array $r) => [
                    'loc'      => Url::absolute('/product/' . $r['slug']),
                    'lastmod'  => date('c', strtotime($r['updated_at'])),
                    'priority' => 0.8,
                ], $rows);

            case 'categories':
                $cats = Database::fetchAll(
                    "SELECT c.slug, c.updated_at FROM categories c WHERE c.status = 'active'"
                    . self::indexableClause('category', 'c') . ' ORDER BY c.id'
                );
                return array_map(static fn(array $r) => [
                    'loc' => Url::absolute('/category/' . $r['slug']),
                    'lastmod' => date('c', strtotime($r['updated_at'])),
                    'priority' => 0.9,
                ], $cats);

            case 'brands':
                $brands = Database::fetchAll(
                    "SELECT b.slug, b.updated_at FROM brands b WHERE b.status = 'active'"
                    . self::indexableClause('brand', 'b') . ' ORDER BY b.id'
                );
                return array_map(static fn(array $r) => [
                    'loc' => Url::absolute('/brand/' . $r['slug']),
                    'lastmod' => date('c', strtotime($r['updated_at'])),
                    'priority' => 0.8,
                ], $brands);

            case 'pages':
                $rows = Database::fetchAll(
                    "SELECT p.slug, p.page_type, p.updated_at FROM pages p WHERE p.status = 'published'"
                    . self::indexableClause('page', 'p') . ' ORDER BY p.id'
                );
                return array_map(static fn(array $r) => [
                    'loc'     => Url::absolute(($r['page_type'] === 'repair' ? '/repair/' : '/page/') . $r['slug']),
                    'lastmod' => date('c', strtotime($r['updated_at'])),
                    'priority' => 0.7,
                ], $rows);

            case 'filter-pages':
                $rows = Database::fetchAll(
                    "SELECT f.slug, f.updated_at FROM filter_pages f
                     WHERE f.status = 'published' AND f.is_indexable = 1"
                    . self::indexableClause('filter', 'f') . ' ORDER BY f.id'
                );
                return array_map(static fn(array $r) => [
                    'loc'     => Url::absolute('/shop/' . $r['slug']),
                    'lastmod' => date('c', strtotime($r['updated_at'])),
                    'priority' => 0.7,
                ], $rows);

            case 'blog':
                if (!SeoSettings::blogEnabled()) {
                    return [];
                }
                $rows = Database::fetchAll(
                    "SELECT b.slug, b.updated_at FROM blog_posts b WHERE b.status = 'published'"
                    . self::indexableClause('blog', 'b') . ' ORDER BY b.published_at DESC'
                );
                return array_map(static fn(array $r) => [
                    'loc'     => Url::absolute('/blog/' . $r['slug']),
                    'lastmod' => date('c', strtotime($r['updated_at'])),
                    'changefreq' => 'monthly',
                    'priority' => 0.6,
                ], $rows);
        }
        return [];
    }

    /** Content fingerprint used to invalidate the cache when data changes. */
    private static function sectionFingerprint(string $section): string
    {
        $tables = match ($section) {
            'products'     => 'products',
            'categories'   => 'categories',
            'brands'       => 'brands',
            'pages'        => 'pages',
            'filter-pages' => 'filter_pages',
            'blog'         => 'blog_posts',
            default        => 'settings',
        };
        $parts = [];
        foreach (explode(',', $tables) as $t) {
            $max = Database::fetchColumn("SELECT COALESCE(MAX(updated_at), '1970-01-01') FROM `$t`");
            $cnt = Database::fetchColumn("SELECT COUNT(*) FROM `$t`");
            $parts[] = "$t:$max:$cnt";
        }
        return implode('|', $parts);
    }

    private static function globalLastModified(): string
    {
        $max = Database::fetchColumn(
            "SELECT GREATEST(
                COALESCE((SELECT MAX(updated_at) FROM products), '1970-01-01'),
                COALESCE((SELECT MAX(updated_at) FROM categories), '1970-01-01'),
                COALESCE((SELECT MAX(updated_at) FROM blog_posts), '1970-01-01'),
                COALESCE((SELECT MAX(updated_at) FROM pages), '1970-01-01')
            )"
        );
        return date('c', strtotime((string) $max));
    }

    private static function xmlHeader(): string
    {
        return "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
    }
}
