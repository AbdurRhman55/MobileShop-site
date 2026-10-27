<?php

declare(strict_types=1);

namespace App\Seo;

use App\Core\Database;
use App\Core\HttpRedirect;

/**
 * Redirect manager.
 *
 * Slug changes automatically create 301 entries so old URLs keep their link
 * equity. Chain resolution follows redirects transitively with a loop guard.
 * Deleted products: 301 to the replacement ONLY when one is explicitly set;
 * otherwise the old URL returns a genuine 410 Gone — never a blanket
 * redirect-to-homepage.
 */
final class RedirectManager
{
    /** Record a permanent redirect (used when an entity slug changes). */
    public static function record(string $fromPath, string $toPath, string $reason = ''): void
    {
        $fromPath = Url::normalizePath($fromPath);
        $toPath   = Url::normalizePath($toPath);
        if ($fromPath === $toPath) {
            return;
        }
        // Never point a redirect at itself indirectly (loop).
        if (self::resolvesTo($toPath, $fromPath)) {
            return; // would create a loop — drop the entry
        }
        Database::query(
            'INSERT INTO redirects (from_path, to_path, status_code, reason) VALUES (?, ?, 301, ?)
             ON DUPLICATE KEY UPDATE to_path = VALUES(to_path), status_code = 301, reason = VALUES(reason)',
            [$fromPath, $toPath, $reason]
        );
        SitemapGenerator::flushCache();
    }

    /** Follow the redirect chain for a path (returns final target or null). */
    public static function resolve(string $path): ?array
    {
        $path      = Url::normalizePath($path);
        $seen      = [$path => true];
        $current   = $path;
        $status    = 301;

        for ($i = 0; $i < 10; $i++) {
            $row = Database::fetch('SELECT * FROM redirects WHERE from_path = ?', [$current]);
            if ($row === null) {
                return $current !== $path ? ['to_path' => $current, 'status_code' => $status] : null;
            }
            $status = (int) $row['status_code'];
            $next   = Url::normalizePath((string) $row['to_path']);
            if (isset($seen[$next])) {
                // Loop detected — remove the offending entry and stop.
                Database::query('DELETE FROM redirects WHERE from_path = ?', [$current]);
                return $current !== $path ? ['to_path' => $current, 'status_code' => $status] : null;
            }
            $seen[$next] = true;
            $current     = $next;
        }
        return ['to_path' => $current, 'status_code' => $status];
    }

    /** Would $path eventually redirect to $target? (loop guard) */
    private static function resolvesTo(string $path, string $target): bool
    {
        $result = self::resolve($path);
        return $result !== null && Url::normalizePath($result['to_path']) === Url::normalizePath($target);
    }

    /**
     * Follow redirects for a request path and issue the HTTP redirect if one
     * exists. Returns true when a redirect was issued.
     */
    public static function redirectIfFound(string $path): bool
    {
        $result = self::resolve($path);
        if ($result === null) {
            return false;
        }
        HttpRedirect::to($result['to_path'], (int) $result['status_code']);
        return true; // unreachable, but keeps static analysis honest
    }

    /** Delete a redirect entry (admin). */
    public static function forget(int $id): void
    {
        Database::query('DELETE FROM redirects WHERE id = ?', [$id]);
    }
}
