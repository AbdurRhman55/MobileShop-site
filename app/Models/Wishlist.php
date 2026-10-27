<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

/**
 * Anonymous, cookie-token wishlist.
 *
 * Works with no customer account. The token is a 64-char random value stored
 * in a first-party cookie; rows are namespaced by that token so one visitor
 * can never read or mutate another visitor's list.
 */
final class Wishlist
{
    private const COOKIE = 'lala_wish';
    private const MAX    = 200;

    /** Read (and lazily mint) the anonymous token. Empty string if cookies off. */
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

    /** Read the token WITHOUT minting one. Used on render paths. */
    private static function readToken(): string
    {
        $t = (string) ($_COOKIE[self::COOKIE] ?? '');
        return preg_match('/^[a-f0-9]{64}$/', $t) === 1 ? $t : '';
    }

    public static function count(): int
    {
        $t = self::readToken();
        return $t === '' ? 0 : SiteContent::wishlistCount($t);
    }

    public static function has(int $productId): bool
    {
        $t = self::readToken();
        if ($t === '' || $productId <= 0) {
            return false;
        }
        return Database::fetch(
            'SELECT 1 FROM wishlists WHERE token = ? AND product_id = ?',
            [$t, $productId]
        ) !== null;
    }

    /** Add if absent. Returns true when the list changed. */
    public static function add(int $productId): bool
    {
        $t = self::token();
        if ($t === '' || $productId <= 0 || !self::productExists($productId)) {
            return false;
        }
        if (self::count() >= self::MAX) {
            return false;
        }
        Database::query(
            'INSERT IGNORE INTO wishlists (token, product_id) VALUES (?, ?)',
            [$t, $productId]
        );
        return self::has($productId);
    }

    /** Remove if present. Returns true when the list changed. */
    public static function remove(int $productId): bool
    {
        $t = self::token();
        if ($t === '' || $productId <= 0) {
            return false;
        }
        $had = self::has($productId);
        Database::query(
            'DELETE FROM wishlists WHERE token = ? AND product_id = ?',
            [$t, $productId]
        );
        return $had;
    }

    /**
     * Flip the saved state and return the NEW state.
     *
     * The controller forwards this straight to the browser as
     * `active`, so it must be the resulting state — not "did anything change".
     * (Returning `remove()` here made every removal report active:true.)
     */
    public static function toggle(int $productId): bool
    {
        if (self::has($productId)) {
            self::remove($productId);
            return false;
        }
        self::add($productId);
        return self::has($productId);
    }

    /** Wishlist rows joined to product data for the /wishlist page. */
    public static function items(): array
    {
        $t = self::token();
        if ($t === '') {
            return [];
        }
        return Database::fetchAll(
            "SELECT p.*, b.name AS brand_name, b.slug AS brand_slug, w.created_at AS wished_at
             FROM wishlists w
             JOIN products p ON p.id = w.product_id
             LEFT JOIN brands b ON b.id = p.brand_id
             WHERE w.token = ? AND p.status = 'active'
             ORDER BY w.created_at DESC",
            [$t]
        );
    }

    /**
     * Only live storefront products may be saved.
     *
     * Without the status check a stale id (or a draft product) could be
     * written to the table and then silently disappear from the wishlist page.
     */
    private static function productExists(int $productId): bool
    {
        return Database::fetch(
            "SELECT 1 FROM products WHERE id = ? AND status = 'active'",
            [$productId]
        ) !== null;
    }
}
