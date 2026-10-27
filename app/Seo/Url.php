<?php

declare(strict_types=1);

namespace App\Seo;

use App\Core\Config;

/**
 * Canonical URL builder.
 *
 * ABSOLUTE URLs are always derived from the configured canonical domain in the
 * `settings` table — never from the request Host header. This prevents
 * host-header injection from producing poisoned canonicals, sitemaps or
 * structured data. The request host is used only for relative asset links.
 */
final class Url
{
    /** Canonical origin, e.g. "https://www.lalamobiles.pk" (no trailing slash). */
    public static function origin(): string
    {
        $domain = SeoSettings::get('canonical_domain', Config::get('app.url'));
        $domain = rtrim($domain, '/');
        // Force https on production canonical domain regardless of stored value.
        $domain = preg_replace('#^http://#i', 'https://', $domain) ?? $domain;
        return $domain;
    }

    /** Absolute canonical URL for a site path ("/product/x" → "https://…/product/x"). */
    public static function absolute(string $path = '/'): string
    {
        if (preg_match('#^https?://#i', $path)) {
            // Already absolute — but only trust it if it is on the canonical origin.
            $host = parse_url($path, PHP_URL_HOST) ?: '';
            $canonicalHost = parse_url(self::origin(), PHP_URL_HOST) ?: '';
            if (strcasecmp($host, $canonicalHost) === 0) {
                return $path;
            }
            // External URL: return it unchanged (used for og:image CDN links etc.)
            // but never treat it as a canonical target.
            return $path;
        }
        return self::origin() . '/' . ltrim($path, '/');
    }

    /**
     * Normalise a path: collapse duplicate slashes, ensure leading slash,
     * strip trailing slash (except root). Used for redirects + canonicals.
     */
    public static function normalizePath(string $path): string
    {
        $path = '/' . ltrim($path, '/');
        $path = preg_replace('#/{2,}#', '/', $path) ?? $path;
        if ($path !== '/') {
            $path = rtrim($path, '/');
        }
        return $path;
    }

    /**
     * Canonical path for an entity, honouring an admin override when set.
     * Overrides must be site-relative paths — external URLs are rejected.
     */
    public static function canonicalPath(string $autoPath, ?string $override): string
    {
        if ($override !== null && trim($override) !== '') {
            $override = trim($override);
            // Only site-relative paths are valid canonical overrides.
            if (str_starts_with($override, '/') && !str_starts_with($override, '//')) {
                return self::normalizePath($override);
            }
        }
        return self::normalizePath($autoPath);
    }
}
