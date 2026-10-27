<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Database;
use App\Core\HttpRedirect;
use App\Models\Redirect;
use App\Models\SeoMeta;
use App\Models\FilterPage;
use App\Seo\MerchantFeed;
use App\Seo\RobotsTxt;
use App\Seo\SeoAudit;
use App\Seo\SeoSettings;
use App\Seo\SitemapGenerator;
use App\Seo\Url;

/**
 * SEO administration: global settings, audit reports (+CSV), redirects,
 * sitemap control, filter landing pages, Merchant feed.
 */
final class SeoAdminController extends AdminBaseController
{
    public function index(): void
    {
        $this->renderAdmin('admin/seo/index', [
            'settings'    => SeoSettings::all(),
            'auditSummary'=> SeoAudit::summary(),
            'redirectCount' => (int) Database::fetchColumn('SELECT COUNT(*) FROM redirects'),
            'filterPageCount' => (int) Database::fetchColumn("SELECT COUNT(*) FROM filter_pages WHERE status='published'"),
            'feedEnabled' => SeoSettings::feedEnabled(),
            'sitemapUrl'  => Url::absolute('/sitemap.xml'),
            'robotsTxt'   => RobotsTxt::generate(),
            'flash'       => $this->takeFlash(),
        ]);
    }

    /* ------------------------------------------------------ settings -- */

    public function settings(): void
    {
        $this->renderAdmin('admin/seo/settings', [
            'settings' => SeoSettings::all(),
            'flash'    => $this->takeFlash(),
        ]);
    }

    public function saveSettings(): void
    {
        // Whitelist of editable setting keys — nothing else can be written.
        $keys = [
            'canonical_domain', 'site_name', 'site_tagline',
            'default_seo_title', 'default_seo_description',
            'homepage_seo_title', 'homepage_seo_description',
            'og_default_image', 'twitter_default_card',
            'organization_name', 'organization_logo', 'organization_phone',
            'organization_email', 'organization_address', 'organization_city',
            'organization_region', 'organization_postal', 'organization_country',
            'geo_latitude', 'geo_longitude', 'opening_hours',
            'social_facebook', 'social_instagram', 'social_tiktok', 'social_youtube',
            'robots_index_site', 'robots_txt_extra',
            'sitemap_enabled', 'blog_enabled', 'feed_enabled',
            'feed_shipping_country', 'feed_shipping_region', 'feed_shipping_service',
            'feed_shipping_price', 'feed_free_shipping_over',
            'google_search_console_verification', 'ga4_measurement_id', 'gtm_container_id',
            'bing_webmaster_verification', 'merchant_center_id',
            'analytics_consent_required', 'review_min_for_aggregate',
        ];

        foreach ($keys as $key) {
            $value = $this->request->input($key);
            if ($value === null) {
                continue;
            }
            if (is_array($value)) {
                $value = implode(',', array_map('strval', $value));
            }
            $value = trim((string) $value);

            // Server-side validation.
            if ($key === 'canonical_domain') {
                // Must be a valid https URL; normalised to origin (no path/trailing slash).
                $parsed = parse_url($value);
                if ($parsed === false || empty($parsed['host'])) {
                    $this->flash('Canonical domain must be a valid URL (e.g. https://www.lalamobiles.pk).', 'error');
                    HttpRedirect::to('/admin/seo/settings', 303);
                }
                $value = 'https://' . $parsed['host'] . (isset($parsed['port']) ? ':' . $parsed['port'] : '');
            }
            if ($key === 'ga4_measurement_id' && $value !== '' && !preg_match('/^G-[A-Z0-9]{6,}$/', $value)) {
                $this->flash('GA4 Measurement ID must look like G-XXXXXXXXXX.', 'error');
                HttpRedirect::to('/admin/seo/settings', 303);
            }
            if ($key === 'gtm_container_id' && $value !== '' && !preg_match('/^GTM-[A-Z0-9]+$/', $value)) {
                $this->flash('GTM Container ID must look like GTM-XXXXXXX.', 'error');
                HttpRedirect::to('/admin/seo/settings', 303);
            }
            if ($key === 'geo_latitude' && $value !== '' && !is_numeric($value)) {
                $this->flash('Latitude must be numeric.', 'error');
                HttpRedirect::to('/admin/seo/settings', 303);
            }
            if ($key === 'geo_longitude' && $value !== '' && !is_numeric($value)) {
                $this->flash('Longitude must be numeric.', 'error');
                HttpRedirect::to('/admin/seo/settings', 303);
            }
            // Verification codes: alphanumeric + dashes only (no markup injection).
            if (str_contains($key, 'verification')) {
                $value = preg_replace('/[^A-Za-z0-9_\-]/', '', $value) ?? '';
            }

            SeoSettings::set($key, $value);
        }

        SitemapGenerator::flushCache();
        $this->flash('SEO settings saved. Sitemap cache flushed.');
        HttpRedirect::to('/admin/seo/settings', 303);
    }

    /* --------------------------------------------------------- audit -- */

    public function audit(): void
    {
        $this->renderAdmin('admin/seo/audit', [
            'reports' => SeoAudit::reports(),
            'flash'   => $this->takeFlash(),
        ]);
    }

    public function exportAudit(string $report): void
    {
        $data = SeoAudit::report($report);
        if ($data === null) {
            HttpRedirect::to('/admin/seo/audit', 303);
        }
        $filename = 'seo-audit-' . $report . '-' . date('Ymd-His') . '.csv';
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('X-Content-Type-Options: nosniff');
        echo SeoAudit::toCsv($data);
    }

    /* ----------------------------------------------------- redirects -- */

    public function redirects(): void
    {
        $this->renderAdmin('admin/seo/redirects', [
            'rows'  => Redirect::all(),
            'flash' => $this->takeFlash(),
        ]);
    }

    public function addRedirect(): void
    {
        $from = trim((string) $this->request->input('from_path', ''));
        $to   = trim((string) $this->request->input('to_path', ''));
        $status = (int) $this->request->input('status_code', 301);

        if ($from === '' || $to === '') {
            $this->flash('Both source and destination are required.', 'error');
            HttpRedirect::to('/admin/seo/redirects', 303);
        }
        if (!in_array($status, [301, 302], true)) {
            $status = 301;
        }
        try {
            // HttpRedirect::safeTarget enforces same-site, path-only destinations.
            \App\Core\HttpRedirect::safeTarget($to);
            \App\Seo\RedirectManager::record($from, $to, 'Manual entry');
            Database::query('UPDATE redirects SET status_code = ? WHERE from_path = ?', [$status, Url::normalizePath($from)]);
            $this->flash('Redirect created.');
        } catch (\App\Core\Http $e) {
            $this->flash('Invalid destination — only same-site paths are allowed (open-redirect protection).', 'error');
        }
        HttpRedirect::to('/admin/seo/redirects', 303);
    }

    public function deleteRedirect(string $id): void
    {
        Redirect::delete((int) $id);
        $this->flash('Redirect deleted.', 'warning');
        HttpRedirect::to('/admin/seo/redirects', 303);
    }

    /* ------------------------------------------------------- sitemap -- */

    public function sitemap(): void
    {
        $preview = '';
        try {
            $preview = SitemapGenerator::indexXml();
        } catch (\Throwable $e) {
            $preview = 'Error generating sitemap: ' . $e->getMessage();
        }
        $this->renderAdmin('admin/seo/sitemap', [
            'preview'  => $preview,
            'sitemapUrl' => Url::absolute('/sitemap.xml'),
            'flash'    => $this->takeFlash(),
        ]);
    }

    public function flushSitemap(): void
    {
        SitemapGenerator::flushCache();
        $this->flash('Sitemap cache flushed. The sitemap will regenerate on next request.');
        HttpRedirect::to('/admin/seo/sitemap', 303);
    }

    /* --------------------------------------------------- filter pages -- */

    public function filterPages(): void
    {
        $this->renderAdmin('admin/seo/filter-pages/index', [
            'rows'  => FilterPage::all(),
            'flash' => $this->takeFlash(),
        ]);
    }

    public function createFilterPage(): void
    {
        $this->renderAdmin('admin/seo/filter-pages/form', [
            'page'       => null,
            'seo'        => null,
            'categories' => Database::fetchAll("SELECT id,name FROM categories WHERE status='active' ORDER BY name"),
            'brands'     => Database::fetchAll("SELECT id,name FROM brands WHERE status='active' ORDER BY name"),
            'flash'      => $this->takeFlash(),
        ]);
    }

    public function storeFilterPage(): void
    {
        $data = $this->collectFilterPage();
        if ($data === null) {
            $this->flash('Name is required.', 'error');
            HttpRedirect::to('/admin/seo/filter-pages/create', 303);
        }
        $id = FilterPage::save($data);
        $this->saveFilterSeo($id);
        $this->flash('Filter landing page created. It is now an indexable SEO URL.');
        HttpRedirect::to('/admin/seo/filter-pages', 303);
    }

    public function editFilterPage(string $id): void
    {
        $page = Database::fetch('SELECT * FROM filter_pages WHERE id = ?', [(int) $id]);
        if ($page === null) {
            HttpRedirect::to('/admin/seo/filter-pages', 303);
        }
        $this->renderAdmin('admin/seo/filter-pages/form', [
            'page'       => $page,
            'seo'        => SeoMeta::get('filter', (int) $id),
            'categories' => Database::fetchAll("SELECT id,name FROM categories WHERE status='active' ORDER BY name"),
            'brands'     => Database::fetchAll("SELECT id,name FROM brands WHERE status='active' ORDER BY name"),
            'flash'      => $this->takeFlash(),
        ]);
    }

    public function updateFilterPage(string $id): void
    {
        $data = $this->collectFilterPage();
        if ($data === null) {
            $this->flash('Name is required.', 'error');
            HttpRedirect::to("/admin/seo/filter-pages/$id/edit", 303);
        }
        FilterPage::save($data, (int) $id);
        $this->saveFilterSeo((int) $id);
        $this->flash('Filter landing page updated.');
        HttpRedirect::to("/admin/seo/filter-pages/$id/edit", 303);
    }

    public function deleteFilterPage(string $id): void
    {
        FilterPage::delete((int) $id);
        $this->flash('Filter page deleted.', 'warning');
        HttpRedirect::to('/admin/seo/filter-pages', 303);
    }

    private function collectFilterPage(): ?array
    {
        $name = trim((string) $this->request->input('name', ''));
        if ($name === '') {
            return null;
        }
        // Build the filter JSON from individual form fields (validated types only).
        $filters = [];
        foreach (['storage', 'ram', 'color'] as $dim) {
            $raw = trim((string) $this->request->input($dim, ''));
            if ($raw !== '') {
                $filters[$dim] = array_values(array_filter(array_map('trim', explode(',', $raw))));
            }
        }
        foreach (['min_price', 'max_price'] as $band) {
            $raw = trim((string) $this->request->input($band, ''));
            if ($raw !== '' && is_numeric($raw)) {
                $filters[$band] = (float) $raw;
            }
        }
        $condition = (string) $this->request->input('condition', '');
        if (in_array($condition, ['new', 'used', 'refurbished'], true)) {
            $filters['condition'] = $condition;
        }

        return [
            'name'         => $name,
            'slug'         => slugify((string) $this->request->input('slug', '') ?: $name),
            'category_id'  => ((int) $this->request->input('category_id', 0)) ?: null,
            'brand_id'     => ((int) $this->request->input('brand_id', 0)) ?: null,
            'filters'      => json_encode($filters, JSON_UNESCAPED_SLASHES),
            'intro'        => sanitizeRichHtml((string) $this->request->input('intro', '')),
            'is_indexable' => (int) $this->request->input('is_indexable', 1) ? 1 : 0,
            'status'       => $this->request->input('status') === 'published' ? 'published' : 'draft',
        ];
    }

    private function saveFilterSeo(int $id): void
    {
        SeoMeta::save('filter', $id, [
            'seo_title'        => (string) $this->request->input('seo_title', ''),
            'meta_description' => (string) $this->request->input('meta_description', ''),
            'robots'           => (string) $this->request->input('robots', ''),
        ]);
    }

    /* ---------------------------------------------------------- feed -- */

    public function feed(): void
    {
        $xml    = MerchantFeed::generateXml(20);
        $errors = MerchantFeed::validate($xml);
        $this->renderAdmin('admin/seo/feed', [
            'xml'         => $xml,
            'errors'      => $errors,
            'enabled'     => SeoSettings::feedEnabled(),
            'feedUrl'     => Url::absolute('/feed/products.xml'),
            'count'       => (int) Database::fetchColumn("SELECT COUNT(*) FROM products WHERE status='active'"),
            'flash'       => $this->takeFlash(),
        ]);
    }

    public function validateFeed(): void
    {
        $xml    = MerchantFeed::generateXml();
        $errors = MerchantFeed::validate($xml);
        if ($errors === []) {
            $this->flash('Feed is valid XML with correctly formatted prices (' . strlen($xml) . ' bytes).');
        } else {
            $this->flash('Feed issues: ' . implode(' ', $errors), 'error');
        }
        HttpRedirect::to('/admin/seo/feed', 303);
    }
}
