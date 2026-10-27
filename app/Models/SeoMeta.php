<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

/**
 * SEO metadata persistence (admin editor writes, MetaResolver reads).
 *
 * Lives in its own file so the PSR-style autoloader can resolve App\Models\SeoMeta.
 */
final class SeoMeta
{
    public static function get(string $entityType, int $entityId): ?array
    {
        return Database::fetch(
            'SELECT * FROM seo_meta WHERE entity_type = ? AND entity_id = ?',
            [$entityType, $entityId]
        );
    }

    /**
     * Upsert an SEO meta row. Empty strings are stored as NULL so that
     * "fall back to auto defaults" logic stays predictable.
     */
    public static function save(string $entityType, int $entityId, array $data): void
    {
        $fields = [
            'seo_title', 'meta_description', 'canonical_path', 'robots',
            'og_title', 'og_description', 'og_image', 'og_type',
            'twitter_card', 'twitter_title', 'twitter_description', 'twitter_image',
            'custom_head',
        ];
        $clean = [];
        foreach ($fields as $f) {
            $value = trim((string) ($data[$f] ?? ''));
            $clean[$f] = $value === '' ? null : $value;
        }

        // Server-side validation of canonical override: must be a site-relative
        // path (single leading slash) so an off-site canonical can never be stored.
        if ($clean['canonical_path'] !== null
            && (!str_starts_with($clean['canonical_path'], '/') || str_starts_with($clean['canonical_path'], '//'))) {
            $clean['canonical_path'] = null;
        }

        $existing = self::get($entityType, $entityId);
        if ($existing !== null) {
            Database::update('seo_meta', (int) $existing['id'], $clean);
        } else {
            $clean['entity_type'] = $entityType;
            $clean['entity_id']   = $entityId;
            Database::insert('seo_meta', $clean);
        }
        \App\Seo\SitemapGenerator::flushCache();
    }

    public static function delete(string $entityType, int $entityId): void
    {
        Database::query('DELETE FROM seo_meta WHERE entity_type = ? AND entity_id = ?', [$entityType, $entityId]);
        \App\Seo\SitemapGenerator::flushCache();
    }
}
