<?php

declare(strict_types=1);

namespace App\Seo;

use App\Core\Database;

/**
 * Cached accessor for the `settings` table (SEO configuration).
 */
final class SeoSettings
{
    /** @var array<string, string|null>|null */
    private static ?array $cache = null;

    public static function all(): array
    {
        if (self::$cache === null) {
            self::$cache = [];
            foreach (Database::fetchAll('SELECT setting_key, setting_value FROM settings') as $row) {
                self::$cache[$row['setting_key']] = $row['setting_value'];
            }
        }
        return self::$cache;
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        $all = self::all();
        return array_key_exists($key, $all) && $all[$key] !== null && $all[$key] !== ''
            ? $all[$key]
            : $default;
    }

    /** Like get() but returns '' for empty values (for verification codes etc.). */
    public static function raw(string $key): string
    {
        $all = self::all();
        return (string) ($all[$key] ?? '');
    }

    public static function set(string $key, string $value): void
    {
        Database::query(
            'INSERT INTO settings (setting_key, setting_value) VALUES (?, ?)
             ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)',
            [$key, $value]
        );
        self::$cache = null;
    }

    public static function flush(): void
    {
        self::$cache = null;
    }

    /* ------------------------------------------------------- shortcuts -- */

    /** Is the whole site indexable? (0 = staging guard: noindex + robots disallow all) */
    public static function siteIndexable(): bool
    {
        return self::get('robots_index_site', '1') === '1';
    }

    public static function blogEnabled(): bool
    {
        return self::get('blog_enabled', '1') === '1';
    }

    public static function feedEnabled(): bool
    {
        return self::get('feed_enabled', '0') === '1';
    }

    public static function analyticsConsentRequired(): bool
    {
        return self::get('analytics_consent_required', '1') === '1';
    }

    public static function googleVerificationMeta(): string
    {
        return self::raw('google_search_console_verification');
    }

    public static function ga4Id(): string
    {
        return self::raw('ga4_measurement_id');
    }

    public static function gtmId(): string
    {
        return self::raw('gtm_container_id');
    }
}
