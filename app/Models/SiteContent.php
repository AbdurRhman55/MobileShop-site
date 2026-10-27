<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use App\Seo\SeoSettings;

/**
 * Site-wide content and engagement data driven by the `settings` table plus
 * the engagement tables added in migration 002.
 *
 * Design rules:
 *  1. Nothing here is fabricated. Payment methods, social links, trust claims
 *     and contact details are only shown when an administrator entered them.
 *  2. Social icons use real brand glyph paths, but the href comes from admin
 *     settings — we never invent an official/verified status.
 *  3. Counts are derived from real tables (orders, reviews, wishlists).
 */
final class SiteContent
{
    /* --------------------------------------------------- announcement bar -- */

    /**
     * Announcement bar items parsed from the `announcement_items` setting.
     * Stored as newline-separated "Text|https://url" (url optional).
     *
     * @return array<int, array{text: string, url: string}>
     */
    public static function announcementItems(): array
    {
        $raw  = (string) SeoSettings::get('announcement_items', '');
        $out  = [];
        foreach (preg_split('/\r?\n/', $raw) ?: [] as $line) {
            $line = trim($line);
            if ($line === '') {
                continue;
            }
            $parts = array_map('trim', explode('|', $line, 2));
            $text  = $parts[0];
            $url   = $parts[1] ?? '';
            if ($text === '') {
                continue;
            }
            // Open-redirect guard: only allow same-site or absolute http(s).
            if ($url !== '' && !self::isSafeUrl($url)) {
                $url = '';
            }
            $out[] = ['text' => $text, 'url' => $url];
        }
        return $out;
    }

    /* --------------------------------------------------------- social -- */

    /** Real brand glyph paths used for the social icons. */
    private const SOCIAL_GLYPHS = [
        'facebook' => 'M13.5 21v-7h2.4l.4-2.8h-2.8V9.4c0-.8.2-1.4 1.4-1.4h1.5V5.5c-.3 0-1.2-.1-2.2-.1-2.2 0-3.7 1.3-3.7 3.8v2.1H8v2.8h2.5v7h3Z',
        'instagram'=> 'M12 7.6A4.4 4.4 0 1 0 12 16.4 4.4 4.4 0 0 0 12 7.6Zm0 7.2a2.8 2.8 0 1 1 0-5.6 2.8 2.8 0 0 1 0 5.6Zm5.6-7.4a1 1 0 1 1-2 0 1 1 0 0 1 2 0ZM8.4 3.5h7.2A4.9 4.9 0 0 1 20.5 8.4v7.2a4.9 4.9 0 0 1-4.9 4.9H8.4a4.9 4.9 0 0 1-4.9-4.9V8.4a4.9 4.9 0 0 1 4.9-4.9Zm0 1.7A3.2 3.2 0 0 0 5.2 8.4v7.2a3.2 3.2 0 0 0 3.2 3.2h7.2a3.2 3.2 0 0 0 3.2-3.2V8.4a3.2 3.2 0 0 0-3.2-3.2H8.4Z',
        'whatsapp' => 'M12 3.8a8.1 8.1 0 0 0-7 12.3L3.8 20.2l4.2-1.1A8.1 8.1 0 1 0 12 3.8Zm0 1.7a6.4 6.4 0 1 1-3.3 11.9l-.3-.2-2.5.7.7-2.4-.2-.3A6.4 6.4 0 0 1 12 5.5Zm-2.6 3c-.2 0-.5 0-.7.4-.2.3-.9.9-.9 2.1s.9 2.4 1 2.6c.2.2 1.8 2.9 4.5 3.9 2.2.9 2.6.7 3.1.7.5 0 1.6-.6 1.8-1.3.2-.6.2-1.2.2-1.3-.1-.1-.3-.2-.6-.3l-2-1c-.3-.1-.5-.2-.7.1l-1 1.2c-.2.2-.3.2-.6.1a5.2 5.2 0 0 1-2.6-2.3c-.2-.3 0-.5.1-.6l.5-.6c.2-.2.2-.3.3-.5 0-.2 0-.4-.1-.5l-.9-2c-.2-.5-.4-.4-.6-.4Z',
        'youtube'  => 'M21.6 8.2s-.2-1.4-.8-2c-.7-.8-1.6-.8-2-.9C16 5.1 12 5.1 12 5.1s-4 0-6.8.2c-.4 0-1.3 0-2 .9-.6.6-.8 2-.8 2S2.2 9.8 2.2 11.4v1.5c0 1.6.2 3.2.2 3.2s.2 1.4.8 2c.7.8 1.7.8 2.1.9 1.5.1 6.7.2 6.7.2s4 0 6.8-.2c.4 0 1.3-.1 2-.9.6-.6.8-2 .8-2s.2-1.6.2-3.2v-1.5c0-1.6-.2-3.2-.2-3.2ZM10 14.6V9.9l5.2 2.4-5.2 2.3Z',
        'tiktok'   => 'M16.5 3.2c.3 1.9 1.4 3.4 3.3 3.7v2.5c-1.3.1-2.5-.2-3.6-.9v5.9c0 3.2-2.4 5.6-5.5 5.6a5.4 5.4 0 0 1-5.5-5.5c0-3.1 2.6-5.6 5.7-5.4v2.7c-1.6-.3-3 .8-3 2.6 0 1.6 1.2 2.8 2.8 2.8s2.7-1.2 2.7-2.9V3.2h3.1Z',
    ];

    /**
     * Social links.
     *
     * Primary source: the per-platform settings this site actually stores
     * (`social_facebook`, `social_instagram`, `social_youtube`, `social_tiktok`,
     * `social_whatsapp`). Secondary source: a newline-separated
     * "platform|https://url" list in `social_links`, for bulk entry.
     *
     * Only platforms with a real glyph AND a safe URL are returned — an
     * unknown platform or a junk value is skipped rather than shown wrong.
     *
     * @return array<int, array{label: string, url: string, path: string}>
     */
    public static function socialLinks(): array
    {
        $out = [];

        // 1) Per-platform keys.
        foreach (array_keys(self::SOCIAL_GLYPHS) as $platform) {
            $url = trim((string) SeoSettings::get('social_' . $platform, ''));
            self::pushSocial($out, $platform, $url);
        }

        // 2) Optional combined list.
        $raw = (string) SeoSettings::get('social_links', '');
        foreach (preg_split('/\r?\n/', $raw) ?: [] as $line) {
            $line = trim($line);
            if ($line === '') {
                continue;
            }
            $parts    = array_map('trim', explode('|', $line, 2));
            $platform = strtolower($parts[0]);
            self::pushSocial($out, $platform, $parts[1] ?? '');
        }

        return $out;
    }

    /** Append a validated social entry, skipping unsafe URLs and duplicates. */
    private static function pushSocial(array &$out, string $platform, string $url): void
    {
        if ($url === '' || !self::isSafeUrl($url)) {
            return;
        }
        $path = self::SOCIAL_GLYPHS[$platform] ?? null;
        if ($path === null) {
            return; // unknown platform → skip rather than show a wrong glyph
        }
        foreach ($out as $existing) {
            if ($existing['url'] === $url) {
                return; // same URL twice (per-key + list) → render once
            }
        }
        $out[] = [
            'label' => ucfirst($platform),
            'url'   => $url,
            'path'  => $path,
        ];
    }

    /* -------------------------------------------------- payment methods -- */

    /**
     * Supported payment methods, parsed from the `payment_methods` setting
     * (newline-separated labels). Empty ⇒ nothing is claimed.
     *
     * @return string[]
     */
    public static function paymentMethods(): array
    {
        $raw = (string) SeoSettings::get('payment_methods', '');
        $out = [];
        foreach (preg_split('/\r?\n/', $raw) ?: [] as $line) {
            $line = trim($line);
            if ($line !== '') {
                $out[] = $line;
            }
        }
        return $out;
    }

    /* ------------------------------------------------------- homepage -- */

    /** Reusable map of section_key → config for the homepage renderer. */
    public static function homepageSections(): array
    {
        $rows = Database::fetchAll('SELECT * FROM homepage_sections ORDER BY position ASC, id ASC');
        $map  = [];
        foreach ($rows as $r) {
            $map[$r['section_key']] = $r;
        }
        return $map;
    }

    /* ------------------------------------------------------- engagement -- */

    /** Count of wishlisted products for an anonymous browser token. */
    public static function wishlistCount(string $token): int
    {
        if ($token === '') {
            return 0;
        }
        return (int) Database::fetchColumn(
            'SELECT COUNT(*) FROM wishlists WHERE token = ?',
            [$token]
        );
    }

    /**
     * Best sellers ranked by real units sold in order_items.
     *
     * @return array<int, array> products augmented with `units_sold`
     */
    public static function bestSellers(int $limit = 8): array
    {
        return Database::fetchAll(
            "SELECT p.*, b.name AS brand_name, b.slug AS brand_slug,
                    COALESCE(SUM(oi.quantity), 0) AS units_sold
             FROM products p
             LEFT JOIN brands b ON b.id = p.brand_id
             LEFT JOIN order_items oi ON oi.product_id = p.id
             LEFT JOIN orders o ON o.id = oi.order_id AND o.status <> 'cancelled'
             WHERE p.status = 'active'
             GROUP BY p.id
             ORDER BY units_sold DESC, p.id DESC
             LIMIT " . max(1, (int) $limit)
        );
    }

    /** Approved, verified-purchase reviews for the homepage testimonial row. */
    public static function verifiedReviews(int $limit = 6): array
    {
        return Database::fetchAll(
            "SELECT r.*, p.name AS product_name, p.slug AS product_slug
             FROM reviews r
             JOIN products p ON p.id = r.product_id
             WHERE r.approved = 1 AND r.verified_purchase = 1 AND r.body IS NOT NULL AND r.body <> ''
             ORDER BY r.created_at DESC
             LIMIT " . max(1, (int) $limit)
        );
    }

    /** Brands that actually have active products (no invented partnerships). */
    public static function trendingBrands(int $limit = 8): array
    {
        return Database::fetchAll(
            "SELECT b.*, COUNT(p.id) AS product_count
             FROM brands b
             JOIN products p ON p.brand_id = b.id AND p.status = 'active'
             GROUP BY b.id
             ORDER BY product_count DESC, b.name ASC
             LIMIT " . max(1, (int) $limit)
        );
    }

    /* ---------------------------------------------------------- helpers -- */

    /**
     * Open-redirect guard: allow relative paths and absolute http(s) URLs.
     * Rejects protocol-relative ("//evil.com") and javascript: targets.
     */
    public static function isSafeUrl(string $url): bool
    {
        $url = trim($url);
        if ($url === '') {
            return false;
        }
        // Relative, same-site path.
        if ($url[0] === '/' && (!isset($url[1]) || $url[1] !== '/')) {
            return true;
        }
        $scheme = strtolower((string) parse_url($url, PHP_URL_SCHEME));
        return in_array($scheme, ['http', 'https'], true);
    }
}
