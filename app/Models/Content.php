<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

/** Blog + static/repair pages model. */
final class Content
{
    /* ------------------------------------------------------------ blog -- */

    public static function blogBySlug(string $slug): ?array
    {
        return Database::fetch(
            "SELECT * FROM blog_posts WHERE slug = ? AND status = 'published'",
            [$slug]
        );
    }

    public static function blogIndex(int $page = 1, int $perPage = 10): array
    {
        $total = (int) Database::fetchColumn("SELECT COUNT(*) FROM blog_posts WHERE status = 'published'");
        $rows  = Database::fetchAll(
            "SELECT * FROM blog_posts WHERE status = 'published'
             ORDER BY published_at DESC LIMIT " . (int) $perPage . ' OFFSET ' . (int) (($page - 1) * $perPage)
        );
        return ['items' => $rows, 'total' => $total, 'page' => $page, 'pages' => (int) ceil($total / $perPage)];
    }

    public static function savePost(array $data, ?int $id = null): int
    {
        if ($id !== null) {
            $existing = Database::fetch('SELECT slug FROM blog_posts WHERE id = ?', [$id]);
            if ($existing !== null && $existing['slug'] !== $data['slug']) {
                \App\Seo\RedirectManager::record('/blog/' . $existing['slug'], '/blog/' . $data['slug'], 'Blog slug changed');
            }
            Database::update('blog_posts', $id, $data);
            \App\Seo\SitemapGenerator::flushCache();
            return $id;
        }
        $newId = Database::insert('blog_posts', $data);
        \App\Seo\SitemapGenerator::flushCache();
        return $newId;
    }

    /* ------------------------------------------------------------ pages -- */

    public static function pageBySlug(string $slug, ?string $type = null): ?array
    {
        if ($type !== null) {
            return Database::fetch(
                "SELECT * FROM pages WHERE slug = ? AND page_type = ? AND status = 'published'",
                [$slug, $type]
            );
        }
        return Database::fetch("SELECT * FROM pages WHERE slug = ? AND status = 'published'", [$slug]);
    }

    public static function pageById(int $id): ?array
    {
        return Database::fetch('SELECT * FROM pages WHERE id = ?', [$id]);
    }

    public static function publishedRepairPages(): array
    {
        return Database::fetchAll(
            "SELECT * FROM pages WHERE page_type = 'repair' AND status = 'published' ORDER BY service_name"
        );
    }

    public static function savePage(array $data, ?int $id = null): int
    {
        if ($id !== null) {
            $existing = Database::fetch('SELECT slug, page_type FROM pages WHERE id = ?', [$id]);
            if ($existing !== null && $existing['slug'] !== $data['slug']) {
                $prefix = ($data['page_type'] ?? $existing['page_type']) === 'repair' ? '/repair/' : '/page/';
                \App\Seo\RedirectManager::record($prefix . $existing['slug'], $prefix . $data['slug'], 'Page slug changed');
            }
            Database::update('pages', $id, $data);
            \App\Seo\SitemapGenerator::flushCache();
            return $id;
        }
        $newId = Database::insert('pages', $data);
        \App\Seo\SitemapGenerator::flushCache();
        return $newId;
    }
}
