<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

/**
 * Discount coupons.
 *
 * Validation happens server-side only. The client's coupon value is never
 * trusted for pricing — CheckoutController recomputes the discount here.
 */
final class Coupon
{
    /** Look up an active, in-window coupon by code (case-insensitive). */
    public static function find(string $code): ?array
    {
        $code = strtoupper(trim($code));
        if ($code === '') {
            return null;
        }
        $now = date('Y-m-d H:i:s');
        return Database::fetch(
            "SELECT * FROM coupons
             WHERE UPPER(code) = ? AND is_active = 1
               AND (starts_at IS NULL OR starts_at <= ?)
               AND (ends_at   IS NULL OR ends_at   >  ?)
               AND (max_uses IS NULL OR used_count < max_uses)
             LIMIT 1",
            [$code, $now, $now]
        );
    }

    /**
     * Validate a coupon against a subtotal.
     *
     * @return array{ok: bool, discount: float, message: string}
     */
    public static function validate(string $code, float $subtotal): array
    {
        $coupon = self::find($code);
        if ($coupon === null) {
            return ['ok' => false, 'discount' => 0.0, 'message' => 'That coupon code is not valid or has expired.'];
        }
        $min = (float) $coupon['min_subtotal'];
        if ($subtotal < $min) {
            return [
                'ok' => false,
                'discount' => 0.0,
                'message' => 'This coupon requires a minimum order of ' . price($min) . '.',
            ];
        }
        return ['ok' => true, 'discount' => self::discountFor($coupon, $subtotal), 'message' => ''];
    }

    /** Discount amount for an already-validated coupon. */
    public static function discountFor(array $coupon, float $subtotal): float
    {
        if ($subtotal <= 0) {
            return 0.0;
        }
        $discount = $coupon['type'] === 'percent'
            ? $subtotal * ((float) $coupon['value'] / 100)
            : (float) $coupon['value'];
        // Never discount below zero or above the subtotal.
        return max(0.0, min($discount, $subtotal));
    }

    /** Increment the usage counter after an order is placed. */
    public static function consume(int $couponId): void
    {
        Database::query('UPDATE coupons SET used_count = used_count + 1 WHERE id = ?', [$couponId]);
    }
}
