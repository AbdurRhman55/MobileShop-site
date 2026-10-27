<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

/**
 * Redirect lookup used by the router's 404 fallback.
 * Delegates to Seo\RedirectManager for chain resolution.
 */
final class Redirect
{
    public static function resolve(string $path): ?array
    {
        return \App\Seo\RedirectManager::resolve($path);
    }

    public static function all(): array
    {
        return Database::fetchAll('SELECT * FROM redirects ORDER BY created_at DESC');
    }

    public static function create(string $from, string $to, int $status = 301, string $reason = ''): void
    {
        \App\Seo\RedirectManager::record($from, $to, $reason);
        // Overwrite status if a custom one was requested.
        Database::query(
            'UPDATE redirects SET status_code = ? WHERE from_path = ?',
            [$status, \App\Seo\Url::normalizePath($from)]
        );
    }

    public static function delete(int $id): void
    {
        \App\Seo\RedirectManager::forget($id);
    }
}

