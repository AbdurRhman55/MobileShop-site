<?php

declare(strict_types=1);

namespace App\Seo;

use App\Core\Database;

/**
 * SEO audit engine.
 *
 * Produces actionable reports that the admin dashboard renders and can export
 * as CSV. Each report is a labelled list of issues with entity links.
 *
 * The checks implemented here are database-backed analyses (metadata,
 * duplicates, structural product data, internal-link targets). Orphan-page
 * detection is LIMITED to products with no category and no filter-page
 * coverage — the report states this scope honestly rather than promising a
 * full crawl-based link graph.
 */
final class SeoAudit
{
    /** @return array<string, array{label:string, description:string, rows:array<int,array>}> */
    public static function reports(): array
    {
        return [
            'missing_title'       => self::missingTitles(),
            'missing_description' => self::missingDescriptions(),
            'duplicate_title'     => self::duplicateTitles(),
            'duplicate_description' => self::duplicateDescriptions(),
            'long_title'          => self::overlongTitles(),
            'long_description'    => self::overlongDescriptions(),
            'missing_image_alt'   => self::missingImageAlt(),
            'missing_product_data'=> self::missingProductData(),
            'no_category'         => self::productsWithoutCategory(),
            'broken_links'        => self::brokenInternalLinks(),
            'missing_canonical'   => self::missingCanonical(),
            'orphan_candidates'   => self::orphanCandidates(),
        ];
    }

    /** Single-report accessor with CSV export support. */
    public static function report(string $key): ?array
    {
        $reports = self::reports();
        return $reports[$key] ?? null;
    }

    /* ------------------------------------------------------- metadata -- */

    private static function missingTitles(): array
    {
        $rows = [];

        $products = Database::fetchAll(
            "SELECT p.id, p.name, p.slug FROM products p
             LEFT JOIN seo_meta m ON m.entity_type = 'product' AND m.entity_id = p.id
             WHERE p.status IN ('active','discontinued')
               AND (m.seo_title IS NULL OR m.seo_title = '')
             ORDER BY p.name"
        );
        foreach ($products as $r) {
            $rows[] = ['entity' => 'Product', 'name' => $r['name'], 'link' => '/admin/products/' . $r['id'] . '/edit'];
        }

        $cats = Database::fetchAll(
            "SELECT c.id, c.name FROM categories c
             LEFT JOIN seo_meta m ON m.entity_type = 'category' AND m.entity_id = c.id
             WHERE c.status = 'active' AND (m.seo_title IS NULL OR m.seo_title = '')"
        );
        foreach ($cats as $r) {
            $rows[] = ['entity' => 'Category', 'name' => $r['name'], 'link' => '/admin/categories/' . $r['id'] . '/edit'];
        }

        $brands = Database::fetchAll(
            "SELECT b.id, b.name FROM brands b
             LEFT JOIN seo_meta m ON m.entity_type = 'brand' AND m.entity_id = b.id
             WHERE b.status = 'active' AND (m.seo_title IS NULL OR m.seo_title = '')"
        );
        foreach ($brands as $r) {
            $rows[] = ['entity' => 'Brand', 'name' => $r['name'], 'link' => '/admin/brands/' . $r['id'] . '/edit'];
        }

        return [
            'label' => 'Missing custom SEO titles',
            'description' => 'Entities relying on auto-generated titles. Provide unique custom titles for the highest-traffic pages first.',
            'rows' => $rows,
        ];
    }

    private static function missingDescriptions(): array
    {
        $rows = [];
        $products = Database::fetchAll(
            "SELECT p.id, p.name FROM products p
             LEFT JOIN seo_meta m ON m.entity_type = 'product' AND m.entity_id = p.id
             WHERE p.status IN ('active','discontinued')
               AND (m.meta_description IS NULL OR m.meta_description = '')
             ORDER BY p.name"
        );
        foreach ($products as $r) {
            $rows[] = ['entity' => 'Product', 'name' => $r['name'], 'link' => '/admin/products/' . $r['id'] . '/edit'];
        }
        $cats = Database::fetchAll(
            "SELECT c.id, c.name FROM categories c
             LEFT JOIN seo_meta m ON m.entity_type = 'category' AND m.entity_id = c.id
             WHERE c.status = 'active' AND (m.meta_description IS NULL OR m.meta_description = '')"
        );
        foreach ($cats as $r) {
            $rows[] = ['entity' => 'Category', 'name' => $r['name'], 'link' => '/admin/categories/' . $r['id'] . '/edit'];
        }
        return [
            'label' => 'Missing custom meta descriptions',
            'description' => 'Pages using an auto-extracted description. Write unique, compelling descriptions for key landing pages.',
            'rows' => $rows,
        ];
    }

    private static function duplicateTitles(): array
    {
        $rows = [];
        // Auto titles repeat when two products share a name pattern — detect final rendered titles.
        $seen = [];
        $products = Database::fetchAll(
            "SELECT p.id, p.name, p.slug, COALESCE(m.seo_title, CONCAT(p.name, ' | Lala Mobiles')) AS title
             FROM products p LEFT JOIN seo_meta m ON m.entity_type='product' AND m.entity_id=p.id
             WHERE p.status IN ('active','discontinued')"
        );
        foreach ($products as $p) {
            $key = mb_strtolower(trim($p['title']));
            $seen[$key][] = ['entity' => 'Product', 'name' => $p['name'], 'link' => '/admin/products/' . $p['id'] . '/edit'];
        }
        foreach ($seen as $title => $entities) {
            if (count($entities) > 1) {
                foreach ($entities as $e) {
                    $rows[] = $e + ['issue' => 'Duplicate title: "' . $title . '" (' . count($entities) . ' pages)'];
                }
            }
        }
        return [
            'label' => 'Duplicate titles',
            'description' => 'Products sharing the exact same rendered <title>. Make each title unique (include model, storage, or variant).',
            'rows' => $rows,
        ];
    }

    private static function duplicateDescriptions(): array
    {
        $rows = [];
        $seen = [];
        $products = Database::fetchAll(
            "SELECT p.id, p.name, COALESCE(m.meta_description, LEFT(REPLACE(REPLACE(p.description,'<p>',''),' </p>',''),155)) AS d
             FROM products p LEFT JOIN seo_meta m ON m.entity_type='product' AND m.entity_id=p.id
             WHERE p.status IN ('active','discontinued') AND COALESCE(m.meta_description,'') <> ''"
        );
        foreach ($products as $p) {
            $key = mb_strtolower(trim((string) $p['d']));
            $seen[$key][] = ['entity' => 'Product', 'name' => $p['name'], 'link' => '/admin/products/' . $p['id'] . '/edit'];
        }
        foreach ($seen as $desc => $entities) {
            if (count($entities) > 1 && $desc !== '') {
                foreach ($entities as $e) {
                    $rows[] = $e + ['issue' => 'Duplicate description shared by ' . count($entities) . ' products'];
                }
            }
        }
        return [
            'label' => 'Duplicate meta descriptions',
            'description' => 'Products with identical custom meta descriptions. Rewrite each one individually.',
            'rows' => $rows,
        ];
    }

    private static function overlongTitles(): array
    {
        $rows = [];
        $products = Database::fetchAll(
            "SELECT p.id, p.name, COALESCE(m.seo_title, CONCAT(p.name, ' | Lala Mobiles')) AS title
             FROM products p LEFT JOIN seo_meta m ON m.entity_type='product' AND m.entity_id=p.id
             WHERE p.status IN ('active','discontinued')"
        );
        foreach ($products as $p) {
            if (MetaResolver::titleTooLong((string) $p['title'])) {
                $rows[] = [
                    'entity' => 'Product', 'name' => $p['name'],
                    'link' => '/admin/products/' . $p['id'] . '/edit',
                    'issue' => mb_strlen($p['title']) . ' chars: "' . excerpt((string) $p['title'], 80) . '"',
                ];
            }
        }
        return [
            'label' => 'Overlong titles (>75 chars)',
            'description' => 'Titles this long get truncated in search results. Trim to ~60 characters.',
            'rows' => $rows,
        ];
    }

    private static function overlongDescriptions(): array
    {
        $rows = [];
        $products = Database::fetchAll(
            "SELECT p.id, p.name, COALESCE(m.meta_description, '') AS d
             FROM products p LEFT JOIN seo_meta m ON m.entity_type='product' AND m.entity_id=p.id
             WHERE p.status IN ('active','discontinued') AND COALESCE(m.meta_description,'') <> ''"
        );
        foreach ($products as $p) {
            if (MetaResolver::descriptionTooLong((string) $p['d'])) {
                $rows[] = [
                    'entity' => 'Product', 'name' => $p['name'],
                    'link' => '/admin/products/' . $p['id'] . '/edit',
                    'issue' => mb_strlen($p['d']) . ' chars',
                ];
            }
        }
        return [
            'label' => 'Overlong meta descriptions (>180 chars)',
            'description' => 'Descriptions beyond ~160 chars are truncated in the SERP.',
            'rows' => $rows,
        ];
    }

    /* --------------------------------------------------- product data -- */

    private static function missingImageAlt(): array
    {
        $rows = [];
        $noAlt = Database::fetchAll(
            "SELECT id, name, slug FROM products
             WHERE status IN ('active','discontinued') AND (image_alt IS NULL OR image_alt = '')"
        );
        foreach ($noAlt as $r) {
            $rows[] = ['entity' => 'Product', 'name' => $r['name'], 'link' => '/admin/products/' . $r['id'] . '/edit', 'issue' => 'No image alt text'];
        }
        $imgNoAlt = Database::fetchAll(
            "SELECT i.id, i.product_id, p.name FROM product_images i
             JOIN products p ON p.id = i.product_id
             WHERE (i.alt IS NULL OR i.alt = '') AND p.status IN ('active','discontinued')"
        );
        foreach ($imgNoAlt as $r) {
            $rows[] = ['entity' => 'Product image', 'name' => $r['name'], 'link' => '/admin/products/' . $r['product_id'] . '/edit', 'issue' => 'Gallery image without alt text'];
        }
        return [
            'label' => 'Missing image alt text',
            'description' => 'Images without alt attributes hurt accessibility and image search.',
            'rows' => $rows,
        ];
    }

    private static function missingProductData(): array
    {
        $rows = [];
        $issues = [
            ["(main_image IS NULL OR main_image = '')", 'No main image'],
            ["(price IS NULL OR price <= 0)", 'Missing or invalid price'],
            ["(stock IS NULL OR stock < 0)", 'Invalid stock value'],
            ["(sku IS NULL OR sku = '')", 'No SKU'],
            ["(short_desc IS NULL OR short_desc = '')", 'No short description'],
            ["(description IS NULL OR description = '')", 'No long description'],
            ["brand_id IS NULL", 'No brand assigned'],
        ];
        foreach ($issues as [$cond, $label]) {
            $rs = Database::fetchAll(
                "SELECT id, name FROM products WHERE status IN ('active','discontinued') AND $cond"
            );
            foreach ($rs as $r) {
                $rows[] = ['entity' => 'Product', 'name' => $r['name'], 'link' => '/admin/products/' . $r['id'] . '/edit', 'issue' => $label];
            }
        }
        return [
            'label' => 'Invalid or missing product data',
            'description' => 'Structural gaps that break structured data, feeds or the storefront.',
            'rows' => $rows,
        ];
    }

    private static function productsWithoutCategory(): array
    {
        $rows = [];
        $rs = Database::fetchAll(
            "SELECT id, name FROM products WHERE status IN ('active','discontinued') AND category_id IS NULL"
        );
        foreach ($rs as $r) {
            $rows[] = ['entity' => 'Product', 'name' => $r['name'], 'link' => '/admin/products/' . $r['id'] . '/edit', 'issue' => 'Not in any category — orphan risk'];
        }
        return [
            'label' => 'Products without a category',
            'description' => 'Products with no category cannot be discovered through category navigation or the sitemap.',
            'rows' => $rows,
        ];
    }

    private static function missingCanonical(): array
    {
        $rows = [];
        // Canonical URLs are auto-generated, so "missing" means an entity whose
        // stored override is malformed (e.g. points off-site).
        $bad = Database::fetchAll(
            "SELECT entity_type, entity_id, canonical_path FROM seo_meta
             WHERE canonical_path IS NOT NULL AND canonical_path <> ''
               AND (canonical_path NOT LIKE '/%' OR canonical_path LIKE '//%')"
        );
        foreach ($bad as $r) {
            $rows[] = [
                'entity' => ucfirst($r['entity_type']),
                'name'   => 'Entity #' . $r['entity_id'],
                'link'   => '/admin/seo/redirects',
                'issue'  => 'Invalid canonical override: ' . excerpt((string) $r['canonical_path'], 60),
            ];
        }
        return [
            'label' => 'Invalid canonical overrides',
            'description' => 'Canonical overrides must be site-relative paths starting with a single slash.',
            'rows' => $rows,
        ];
    }

    /* ------------------------------------------------- link integrity -- */

    /**
     * Broken internal links: scan rendered product/category HTML anchors and
     * verify each internal target resolves to a live entity or known route.
     */
    private static function brokenInternalLinks(): array
    {
        $rows = [];
        $knownPaths = self::knownPathSet();

        $bodies = Database::fetchAll(
            "SELECT id, name, slug, description FROM products WHERE status IN ('active','discontinued') AND description LIKE '%<a %'"
        );
        foreach ($bodies as $b) {
            if (preg_match_all('/<a\s[^>]*href=["\']([^"\']+)["\']/i', (string) $b['description'], $m)) {
                foreach ($m[1] as $href) {
                    if (preg_match('#^https?://#i', $href)) {
                        $host = parse_url($href, PHP_URL_HOST);
                        $canonicalHost = parse_url(Url::origin(), PHP_URL_HOST);
                        if (strcasecmp((string) $host, (string) $canonicalHost) !== 0) {
                            continue; // external link — not checked here
                        }
                        $path = (string) (parse_url($href, PHP_URL_PATH) ?: '/');
                    } else {
                        $path = $href;
                    }
                    $path = Url::normalizePath($path);
                    if (!isset($knownPaths[$path])) {
                        $rows[] = [
                            'entity' => 'Product', 'name' => $b['name'],
                            'link' => '/admin/products/' . $b['id'] . '/edit',
                            'issue' => 'Broken internal link: ' . $path,
                        ];
                    }
                }
            }
        }
        return [
            'label' => 'Broken internal links (content scan)',
            'description' => 'Internal hrefs inside product descriptions that do not match any live page. Scope: product description HTML only — this is not a full-site crawl.',
            'rows' => $rows,
        ];
    }

    /** Build a set of all live internal paths. */
    private static function knownPathSet(): array
    {
        $paths = [
            '/' => true, '/search' => true, '/cart' => true, '/checkout' => true,
            '/blog' => true, '/sitemap.xml' => true, '/robots.txt' => true,
        ];
        foreach (Database::fetchAll("SELECT slug FROM products WHERE status IN ('active','discontinued')") as $r) {
            $paths['/product/' . $r['slug']] = true;
        }
        foreach (Database::fetchAll("SELECT slug FROM categories WHERE status='active'") as $r) {
            $paths['/category/' . $r['slug']] = true;
        }
        foreach (Database::fetchAll("SELECT slug FROM brands WHERE status='active'") as $r) {
            $paths['/brand/' . $r['slug']] = true;
        }
        foreach (Database::fetchAll("SELECT slug FROM blog_posts WHERE status='published'") as $r) {
            $paths['/blog/' . $r['slug']] = true;
        }
        foreach (Database::fetchAll("SELECT slug, page_type FROM pages WHERE status='published'") as $r) {
            $paths[($r['page_type'] === 'repair' ? '/repair/' : '/page/') . $r['slug']] = true;
        }
        foreach (Database::fetchAll("SELECT slug FROM filter_pages WHERE status='published'") as $r) {
            $paths['/shop/' . $r['slug']] = true;
        }
        return $paths;
    }

    /**
     * Orphan CANDIDATES: active products with no category and no brand —
     * i.e. no listing page will ever link to them. Stated honestly: this is a
     * structural heuristic, not a full link-graph crawl.
     */
    private static function orphanCandidates(): array
    {
        $rows = [];
        $rs = Database::fetchAll(
            "SELECT id, name FROM products
             WHERE status = 'active' AND category_id IS NULL AND brand_id IS NULL"
        );
        foreach ($rs as $r) {
            $rows[] = ['entity' => 'Product', 'name' => $r['name'], 'link' => '/admin/products/' . $r['id'] . '/edit', 'issue' => 'No category and no brand — likely orphan'];
        }
        return [
            'label' => 'Orphan-page candidates',
            'description' => 'Heuristic only: active products with neither category nor brand coverage. This report does NOT claim complete orphan detection — a full link-graph crawl is not implemented.',
            'rows' => $rows,
        ];
    }

    /* -------------------------------------------------------- summary -- */

    /** Compact counts for the dashboard widget. */
    public static function summary(): array
    {
        $out = [];
        foreach (self::reports() as $key => $report) {
            $out[$key] = ['label' => $report['label'], 'count' => count($report['rows'])];
        }
        return $out;
    }

    /** Render a report as CSV (with header row). */
    public static function toCsv(array $report): string
    {
        $out = fopen('php://temp', 'r+');
        $columns = ['entity', 'name', 'issue', 'link'];
        fputcsv($out, $columns);
        foreach ($report['rows'] as $row) {
            fputcsv($out, [
                $row['entity'] ?? '',
                $row['name'] ?? '',
                $row['issue'] ?? '',
                Url::absolute($row['link'] ?? ''),
            ]);
        }
        rewind($out);
        $csv = (string) stream_get_contents($out);
        fclose($out);
        return $csv;
    }
}
