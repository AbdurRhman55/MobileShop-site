<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

/**
 * Flash-sale campaigns.
 *
 * Integrity rules (enforced here, not in the view):
 *  1. A campaign is "live" only inside its [starts_at, ends_at] window. An
 *     expired campaign is never rendered as active and never produces a
 *     countdown. starts_at in the future ⇒ scheduled, not live.
 *  2. The displayed discount is computed from a real reference price (the
 *     product's normal `price`). We never invent a struck-through price.
 *  3. The countdown target is the campaign's real end time (server clock /
 *     configured TZ). The client only interpolates from that absolute value,
 *     so a page refresh never resets the timer.
 */
final class FlashSale
{
    /** Live campaign (started, not yet ended, active) with the nearest end. */
    public static function current(): ?array
    {
        $now = self::now();
        $row = Database::fetch(
            "SELECT * FROM flash_sales
             WHERE is_active = 1
               AND (starts_at IS NULL OR starts_at <= ?)
               AND (ends_at   IS NULL OR ends_at   >  ?)
             ORDER BY (ends_at IS NULL) ASC, ends_at ASC, id ASC
             LIMIT 1",
            [$now, $now]
        );
        return $row ?: null;
    }

    /** Products in a campaign, enriched with genuine pricing + discount info. */
    public static function items(array $sale, int $limit = 12): array
    {
        $saleId = (int) $sale['id'];
        $rows = Database::fetchAll(
            "SELECT p.*, b.name AS brand_name, b.slug AS brand_slug,
                    fi.campaign_price, fi.discount_pct, fi.position AS item_position
             FROM flash_sale_items fi
             JOIN products p ON p.id = fi.product_id
             LEFT JOIN brands b ON b.id = p.brand_id
             WHERE fi.flash_sale_id = ? AND p.status = 'active'
             ORDER BY fi.position ASC, p.id ASC
             LIMIT " . max(1, (int) $limit),
            [$saleId]
        );

        $out = [];
        foreach ($rows as $p) {
            $normal   = (float) $p['price'];                 // reference price
            $campaign = self::campaignPrice($p, $sale, $normal);
            if ($campaign === null || $campaign >= $normal) {
                // No genuine saving → skip rather than display a fake "sale".
                continue;
            }
            $p['normal_price']  = $normal;
            $p['sale_price_eff']= $campaign;
            $p['saving_amount'] = $normal - $campaign;
            $p['discount_pct']  = (int) round((1 - $campaign / $normal) * 100);
            $p['flash_end']     = $sale['ends_at'] ?? null;
            $out[] = $p;
        }
        return $out;
    }

    /** Absolute unix seconds the campaign ends, or null when open-ended. */
    public static function endsAtUnix(?array $sale): ?int
    {
        if ($sale === null || empty($sale['ends_at'])) {
            return null;
        }
        return (int) strtotime((string) $sale['ends_at']);
    }

    /**
     * The live campaign price for ONE product, or null when it is not in a
     * live campaign.
     *
     * This is the single source of truth the CART must use so that the price a
     * customer was shown (homepage flash strip, product page) is exactly the
     * price they are charged. It returns null — never a higher number — when
     * there is no genuine saving, so callers can fall back to the product's own
     * price.
     */
    public static function livePriceFor(int $productId): ?float
    {
        if ($productId <= 0) {
            return null;
        }
        $sale = self::current();
        if ($sale === null) {
            return null;
        }
        $row = Database::fetch(
            'SELECT p.price, p.sale_price, fi.campaign_price, fi.discount_pct
             FROM flash_sale_items fi
             JOIN products p ON p.id = fi.product_id
             WHERE fi.flash_sale_id = ? AND fi.product_id = ? AND p.status = "active"
             LIMIT 1',
            [(int) $sale['id'], $productId]
        );
        if ($row === null) {
            return null;
        }
        $normal = (float) $row['price'];
        $price  = self::campaignPrice($row, $sale, $normal);
        // Only honour it when it is genuinely below the reference price.
        if ($price === null || $price >= $normal || $price <= 0) {
            return null;
        }
        return $price;
    }

    /* -------------------------------------------------------- internals -- */

    /**
     * Resolve the campaign price for a product line.
     * Explicit campaign_price wins; otherwise discount_pct applies to normal.
     */
    private static function campaignPrice(array $p, array $sale, float $normal): ?float
    {
        if ($p['campaign_price'] !== null && $p['campaign_price'] !== '') {
            $cp = (float) $p['campaign_price'];
            return $cp > 0 ? $cp : null;
        }
        if ($p['discount_pct'] !== null && (int) $p['discount_pct'] > 0) {
            $pct = min(90, (int) $p['discount_pct']);        // clamp silly values
            return round($normal * (1 - $pct / 100), 2);
        }
        // Fall back to the product's own sale price if it genuinely saves money.
        if ($p['sale_price'] !== null && (float) $p['sale_price'] < $normal) {
            return (float) $p['sale_price'];
        }
        return null;
    }

    /** Current time in the app's configured timezone, as a MySQL datetime. */
    private static function now(): string
    {
        $tz = \App\Core\Config::get('app.timezone', 'Asia/Karachi');
        try {
            $dt = new \DateTimeImmutable('now', new \DateTimeZone((string) $tz));
        } catch (\Throwable) {
            $dt = new \DateTimeImmutable('now');
        }
        return $dt->format('Y-m-d H:i:s');
    }
}
