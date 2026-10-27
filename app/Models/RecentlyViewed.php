<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

/**
 * Recently-viewed products, namespaced by the anonymous browser token.
 * Capped per token in application code (oldest pruned on write).
 */
final class RecentlyViewed
{
    private const COOKIE = 'lala_vid';
    private const MAX    = 12;

    /** Read (and lazily mint) the anonymous visitor token. */
    public static function token(): string
    {
        $t = (string) ($_COOKIE[self::COOKIE] ?? '');
        if (preg_match('/^[a-f0-9]{64}$/', $t) === 1) {
            return $t;
        }
        $t = bin2hex(random_bytes(32));
        if (!headers_sent()) {
            setcookie(self::COOKIE, $t, [
                'expires'  => time() + 86400 * 365,
                'path'     => '/',
                'httponly' => true,
                'samesite' => 'Lax',
                'secure'   => !empty($_SERVER['HTTPS']),
            ]);
        }
        $_COOKIE[self::COOKIE] = $t;
        return $t;
    }

    /** Record a product view (upsert + prune). */
    public static function record(int $productId): void
    {
        $t = self::token();
        if ($t === '' || $productId <= 0) {
            return;
        }
        try {
            Database::query(
                'INSERT INTO recently_viewed (token, product_id) VALUES (?, ?)
                 ON DUPLICATE KEY UPDATE viewed_at = CURRENT_TIMESTAMP',
                [$t, $productId]
            );
            // Prune to the cap, oldest first.
            Database::query(
                'DELETE FROM recently_viewed WHERE token = ?
                 AND product_id NOT IN (
                    SELECT product_id FROM (
                        SELECT product_id FROM recently_viewed
                        WHERE token = ? ORDER BY viewed_at DESC LIMIT ' . self::MAX . '
                    ) keep
                 )',
                [$t, $t]
            );
        } catch (\Throwable) {
            // Non-critical: never break a page render on tracking failure.
        }
    }

    /** Recent products (excluding the current one) for the product page. */
    public static function items(int $excludeId = 0, int $limit = 6): array
    {
        $t = self::token();
        if ($t === '') {
            return [];
        }
        return Database::fetchAll(
            "SELECT p.*, b.name AS brand_name, b.slug AS brand_slug
             FROM recently_viewed rv
             JOIN products p ON p.id = rv.product_id
             LEFT JOIN brands b ON b.id = p.brand_id
             WHERE rv.token = ? AND p.status = 'active' AND p.id <> ?
             ORDER BY rv.viewed_at DESC
             LIMIT " . max(1, (int) $limit),
            [$t, $excludeId]
        );
    }
}
