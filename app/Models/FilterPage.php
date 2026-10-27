<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

/** Filter landing pages (admin-curated indexable faceted URLs). */
final class FilterPage
{
    public static function bySlug(string $slug): ?array
    {
        return Database::fetch(
            "SELECT * FROM filter_pages WHERE slug = ? AND status = 'published'",
            [$slug]
        );
    }

    public static function all(): array
    {
        return Database::fetchAll('SELECT * FROM filter_pages ORDER BY name');
    }

    public static function allPublished(): array
    {
        return Database::fetchAll("SELECT * FROM filter_pages WHERE status = 'published' ORDER BY name");
    }

    public static function save(array $data, ?int $id = null): int
    {
        if ($id !== null) {
            $existing = Database::fetch('SELECT slug FROM filter_pages WHERE id = ?', [$id]);
            if ($existing !== null && $existing['slug'] !== $data['slug']) {
                \App\Seo\RedirectManager::record('/shop/' . $existing['slug'], '/shop/' . $data['slug'], 'Filter page slug changed');
            }
            Database::update('filter_pages', $id, $data);
            \App\Seo\SitemapGenerator::flushCache();
            return $id;
        }
        $newId = Database::insert('filter_pages', $data);
        \App\Seo\SitemapGenerator::flushCache();
        return $newId;
    }

    public static function delete(int $id): void
    {
        $row = Database::fetch('SELECT slug FROM filter_pages WHERE id = ?', [$id]);
        Database::query('DELETE FROM filter_pages WHERE id = ?', [$id]);
        if ($row !== null) {
            \App\Seo\RedirectManager::record('/shop/' . $row['slug'], '/', 'Filter page removed');
        }
        \App\Seo\SitemapGenerator::flushCache();
    }

    /** Products matching a filter page's stored filter JSON. */
    public static function matchingProducts(array $filterPage, int $page = 1, int $perPage = 12): array
    {
        $filters = json_decode((string) $filterPage['filters'], true) ?: [];
        $query   = [];
        foreach (['storage', 'ram', 'color'] as $dim) {
            if (!empty($filters[$dim])) {
                $query[$dim] = (array) $filters[$dim];
            }
        }
        foreach (['min_price', 'max_price', 'condition'] as $scalar) {
            if (isset($filters[$scalar]) && $filters[$scalar] !== '') {
                $query[$scalar] = $filters[$scalar];
            }
        }
        return \App\Models\Product::listing(
            $query,
            $filterPage['category_id'] !== null ? (int) $filterPage['category_id'] : null,
            $filterPage['brand_id'] !== null ? (int) $filterPage['brand_id'] : null,
            $page,
            $perPage
        );
    }
}
