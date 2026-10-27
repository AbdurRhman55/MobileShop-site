<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

/**
 * Category + navigation helpers.
 *
 * The navbar only shows top-level categories that have active products in
 * their subtree, unless an administrator forces them visible/hidden via
 * nav_mode. This keeps empty menus out of the header while still letting
 * the store owner expose a category the moment stock arrives.
 */
final class Catalog
{
    /** @return array<int, array> */
    public static function allActiveBrands(): array
    {
        if (class_exists(Brand::class) && method_exists(Brand::class, 'allActive')) {
            try {
                return Brand::allActive();
            } catch (\Throwable $e) {
            }
        }

        try {
            return Database::fetchAll("SELECT id, slug, name, logo, image FROM brands WHERE status = 'active' OR is_active = 1 ORDER BY COALESCE(position, sort_order, 0) ASC, name ASC");
        } catch (\Throwable $e) {
            try {
                return Database::fetchAll('SELECT id, slug, name FROM brands WHERE status = "active" ORDER BY name ASC');
            } catch (\Throwable $e2) {
                try {
                    return Database::fetchAll('SELECT id, slug, name FROM brands ORDER BY name ASC');
                } catch (\Throwable $e3) {
                    return [];
                }
            }
        }
    }

    public static function brandBySlug(string $slug): ?array
    {
        if (class_exists(Brand::class) && method_exists(Brand::class, 'findBySlug')) {
            try {
                $b = Brand::findBySlug($slug);
                if ($b) {
                    return $b;
                }
            } catch (\Throwable $e) {
            }
        }
        try {
            $row = Database::fetch('SELECT * FROM brands WHERE slug = ? LIMIT 1', [$slug]);
            if ($row) {
                return $row;
            }
        } catch (\Throwable $e) {
        }
        try {
            $row = Database::fetch("SELECT * FROM brands WHERE slug = ? AND status = 'active' LIMIT 1", [$slug]);
            if ($row) {
                return $row;
            }
        } catch (\Throwable $e) {
        }
        return null;
    }

    public static function categoryById(int $id): ?array
    {
        return Database::fetch('SELECT * FROM categories WHERE id = ?', [$id]);
    }

    public static function categoryBySlug(string $slug): ?array
    {
        return Database::fetch("SELECT * FROM categories WHERE slug = ? AND (is_active = 1 OR status = 'active') LIMIT 1", [$slug]);
    }

    public static function saveCategory(array $data, ?int $id = null): int
    {
        if ($id !== null && $id > 0) {
            Database::update('categories', $data, 'id = ?', [$id]);
            return $id;
        }

        return Database::insert('categories', $data);
    }
    /** @return array<int, array> */
    public static function all(): array
    {
        return Database::fetchAll('SELECT * FROM categories ORDER BY parent_id IS NULL DESC, sort_order ASC, position ASC, name ASC');
    }

    private static function collectSubtreeIds(int $rootId): array
    {
        $ids = [(int) $rootId];
        $stack = [(int) $rootId];
        while ($stack !== []) {
            $current = array_pop($stack);
            $children = Database::fetchAll('SELECT id FROM categories WHERE parent_id = ?', [$current]);
            foreach ($children as $child) {
                $cid = (int) $child['id'];
                if (in_array($cid, $ids, true)) {
                    continue;
                }
                $ids[] = $cid;
                $stack[] = $cid;
            }
        }
        return $ids;
    }

    /** @return array<int, array> */
    public static function navCategories(): array
    {
        $rows = Database::fetchAll(
            "SELECT c.*
             FROM categories c
             WHERE c.is_active = 1
               AND (c.parent_id IS NULL OR c.parent_id = 0)
               AND c.nav_mode <> 'hide'
             ORDER BY COALESCE(c.nav_priority, c.position, c.sort_order) ASC, c.name ASC"
        );

        $items = [];

        foreach ($rows as $row) {
            $mode = (string) ($row['nav_mode'] ?? 'auto');
            if ($mode === 'hide') {
                continue;
            }

            $count = self::activeProductCountInSubtree((int) $row['id']);

            if ($mode === 'auto' && $count === 0) {
                continue;
            }

            $children = Database::fetchAll(
                'SELECT c.* FROM categories c WHERE c.is_active = 1 AND c.parent_id = ? ORDER BY COALESCE(c.nav_priority, c.position, c.sort_order) ASC, c.name ASC',
                [(int) $row['id']]
            );

            $filteredChildren = [];
            foreach ($children as $child) {
                $cmode = (string) ($child['nav_mode'] ?? 'auto');
                if ($cmode === 'hide') {
                    continue;
                }
                $csub = self::collectSubtreeIds((int) $child['id']);
                if ($csub === []) {
                    continue;
                }
                $ph = implode(',', array_fill(0, count($csub), '?'));
                $crow = Database::fetch("SELECT COUNT(*) AS c FROM products p WHERE p.status = 'active' AND p.category_id IN ($ph)", array_map('intval', $csub));
                $ccount = (int) ($crow['c'] ?? 0);
                if ($cmode === 'auto' && $ccount === 0) {
                    continue;
                }
                $child['product_count'] = $ccount;
                $filteredChildren[] = $child;
            }

            $items[] = [
                'id'            => (int) $row['id'],
                'slug'          => (string) $row['slug'],
                'name'          => (string) $row['name'],
                'icon'          => $row['icon'] ?? null,
                'product_count' => $count,
                'children'      => $filteredChildren,
            ];
        }

        return $items;
    }

    public static function activeProductCountInSubtree(int $rootId): int
    {
        $ids = self::collectSubtreeIds($rootId);
        if ($ids === []) {
            return 0;
        }
        $ph = implode(',', array_fill(0, count($ids), '?'));
        $row = Database::fetch("SELECT COUNT(*) AS c FROM products p WHERE p.status = 'active' AND p.category_id IN ($ph)", array_map('intval', $ids));
        return (int) ($row['c'] ?? 0);
    }

    /** @return array<int, array> */
    public static function children(int $parentId): array
    {
        return Database::fetchAll(
            "SELECT * FROM categories WHERE parent_id = ? AND (is_active = 1 OR status = 'active') ORDER BY COALESCE(sort_order, position, 0) ASC, name ASC",
            [$parentId]
        );
    }

    public static function childCategories(int $parentId): array
    {
        return self::children($parentId);
    }

    public static function findBySlug(string $slug): ?array
    {
        return Database::fetch('SELECT * FROM categories WHERE slug = ? AND is_active = 1', [$slug]);
    }

    public static function subtreeIds(int $rootId): array
    {
        return self::collectSubtreeIds($rootId);
    }

    public static function descendantCategoryIds(int $rootId): array
    {
        return self::collectSubtreeIds($rootId);
    }

    /** @return array<int, array> */
    public static function categoryTrail(array $category): array
    {
        $trail = [];
        $trail[] = $category;
        $parentId = isset($category['parent_id']) ? (int) $category['parent_id'] : 0;
        $current = $category;
        $depth = 0;
        while ($parentId > 0 && $depth < 10) {
            $parent = Database::fetch('SELECT * FROM categories WHERE id = ?', [$parentId]);
            if (!$parent) {
                break;
            }
            array_unshift($trail, $parent);
            $parentId = isset($parent['parent_id']) ? (int) $parent['parent_id'] : 0;
            $current = $parent;
            $depth++;
        }
        return $trail;
    }

    /** @return array<int, array> */
    public static function brandsForCategoryIds(array $categoryIds): array
    {
        if ($categoryIds === []) {
            return [];
        }

        $placeholders = implode(',', array_fill(0, count($categoryIds), '?'));
        $sql = "SELECT b.id, b.slug, b.name, COUNT(DISTINCT p.id) AS product_count
                FROM brands b
                INNER JOIN products p ON p.brand_id = b.id
                WHERE p.status = 'active'
                  AND b.status = 'active'
                  AND p.category_id IN ($placeholders)
                GROUP BY b.id, b.slug, b.name
                HAVING product_count > 0
                ORDER BY b.name ASC";

        return Database::fetchAll($sql, array_map('intval', $categoryIds));
    }

    /** @return array<int, array> */
    public static function brandsOnSale(): array
    {
        try {
            $sql = "SELECT b.id, b.slug, b.name, COUNT(DISTINCT p.id) AS product_count
                    FROM brands b
                    INNER JOIN products p ON p.brand_id = b.id
                    WHERE p.status = 'active'
                      AND b.status = 'active'
                      AND p.compare_at_price IS NOT NULL
                      AND p.compare_at_price > p.price
                    GROUP BY b.id, b.slug, b.name
                    HAVING product_count > 0
                    ORDER BY b.name ASC";

            return Database::fetchAll($sql);
        } catch (\Throwable $e) {
            try {
                $sql = "SELECT b.id, b.slug, b.name, COUNT(DISTINCT p.id) AS product_count
                        FROM brands b
                        INNER JOIN products p ON p.brand_id = b.id
                        WHERE p.status = 'active'
                          AND b.status = 'active'
                          AND p.sale_price IS NOT NULL
                          AND p.sale_price > 0
                          AND p.sale_price < p.price
                        GROUP BY b.id, b.slug, b.name
                        HAVING product_count > 0
                        ORDER BY b.name ASC";

                return Database::fetchAll($sql);
            } catch (\Throwable $e2) {
                return [];
            }
        }
    }

    public static function brandsForSearch(string $term): array
    {
        $term = trim($term);
        if ($term === '') {
            return [];
        }

        $like = '%' . $term . '%';
        try {
            $sql = "SELECT b.id, b.slug, b.name, COUNT(DISTINCT p.id) AS product_count
                    FROM brands b
                    INNER JOIN products p ON p.brand_id = b.id
                    WHERE p.status = 'active'
                      AND b.status = 'active'
                      AND (p.name LIKE ? OR p.sku LIKE ? OR p.search_keywords LIKE ?)
                    GROUP BY b.id, b.slug, b.name
                    HAVING product_count > 0
                    ORDER BY b.name ASC";

            return Database::fetchAll($sql, [$like, $like, $like]);
        } catch (\Throwable $e) {
            try {
                $sql = "SELECT b.id, b.slug, b.name, COUNT(DISTINCT p.id) AS product_count
                        FROM brands b
                        INNER JOIN products p ON p.brand_id = b.id
                        WHERE p.status = 'active'
                          AND b.status = 'active'
                          AND (p.name LIKE ? OR p.sku LIKE ?)
                        GROUP BY b.id, b.slug, b.name
                        HAVING product_count > 0
                        ORDER BY b.name ASC";

                return Database::fetchAll($sql, [$like, $like]);
            } catch (\Throwable $e2) {
                return [];
            }
        }
    }
}
