<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

/**
 * Session-backed cart. Simple, server-rendered, no cookies beyond the session.
 *
 * Lives in its own file so the PSR-style autoloader can resolve App\Models\Cart.
 */
final class Cart
{
    public static function items(): array
    {
        return $_SESSION['cart'] ?? [];
    }

    public static function add(int $productId, ?int $variantId, int $quantity): void
    {
        $product = Product::find($productId);
        if ($product === null || $product['status'] !== 'active') {
            return;
        }
        $unitPrice = Product::effectivePrice($product);
        $name      = $product['name'];
        $sku       = $product['sku'];

        // If the product is in a live flash campaign, honour the campaign price
        // the customer was shown. livePriceFor() only returns a genuine saving
        // below the normal price, so this can never raise the charge.
        $campaign = \App\Models\FlashSale::livePriceFor($productId);
        if ($campaign !== null && $campaign < $unitPrice) {
            $unitPrice = $campaign;
        }

        if ($variantId !== null) {
            $variant = Database::fetch(
                'SELECT * FROM product_variants WHERE id = ? AND product_id = ? AND is_active = 1',
                [$variantId, $productId]
            );
            if ($variant !== null) {
                $unitPrice += (float) $variant['price_delta'];
                $name .= ' — ' . $variant['label'];
                $sku   = $variant['sku'] ?: $sku;
            } else {
                $variantId = null;
            }
        }

        $key = $productId . ':' . ($variantId ?? 0);
        $_SESSION['cart'][$key] = [
            'product_id' => $productId,
            'variant_id' => $variantId,
            'name'       => $name,
            'sku'        => $sku,
            'unit_price' => $unitPrice,
            'quantity'   => ($_SESSION['cart'][$key]['quantity'] ?? 0) + max(1, $quantity),
            'image'      => $product['main_image'],
        ];
    }

    public static function updateQuantity(string $key, int $quantity): void
    {
        if (isset($_SESSION['cart'][$key])) {
            if ($quantity <= 0) {
                unset($_SESSION['cart'][$key]);
            } else {
                $_SESSION['cart'][$key]['quantity'] = $quantity;
            }
        }
    }

    public static function remove(string $key): void
    {
        unset($_SESSION['cart'][$key]);
    }

    public static function clear(): void
    {
        unset($_SESSION['cart']);
    }

    public static function subtotal(): float
    {
        $sum = 0.0;
        foreach (self::items() as $item) {
            $sum += $item['unit_price'] * $item['quantity'];
        }
        return $sum;
    }

    public static function count(): int
    {
        $n = 0;
        foreach (self::items() as $item) {
            $n += $item['quantity'];
        }
        return $n;
    }

    public static function shipping(): float
    {
        return self::subtotal() >= 50000 ? 0.0 : 250.0;
    }
}
