<?php

declare(strict_types=1);

namespace App\Seo;

use App\Core\Database;

/**
 * Google Merchant Center product feed (XML).
 *
 * Generates a valid Merchant Center feed from live catalogue data:
 * id, title, description, link, image_link, additional images, price,
 * sale_price, availability, brand, condition, google product category,
 * shipping, and variant identifiers (gtin/mpn) ONLY when genuinely present.
 *
 * Never invents GTIN/MPN. Prices and availability come straight from the
 * products table so the feed always matches the storefront.
 */
final class MerchantFeed
{
    public const CONDITION_MAP = [
        'new'        => 'new',
        'used'       => 'used',
        'refurbished'=> 'refurbished',
    ];

    /** Availability string for Merchant Center. */
    public static function availability(int $stock, string $status): string
    {
        if ($status !== 'active') {
            return 'https://schema.org/OutOfStock';
        }
        if ($stock <= 0) {
            return 'https://schema.org/OutOfStock';
        }
        if ($stock <= 5) {
            return 'https://schema.org/LimitedAvailability';
        }
        return 'https://schema.org/InStock';
    }

    public static function generateXml(int $limit = 5000): string
    {
        $products = Database::fetchAll(
            "SELECT p.*, b.name AS brand_name, c.name AS category_name
             FROM products p
             LEFT JOIN brands b ON b.id = p.brand_id
             LEFT JOIN categories c ON c.id = p.category_id
             WHERE p.status = 'active'
             ORDER BY p.id ASC
             LIMIT " . max(1, $limit)
        );

        $out = ['<?xml version="1.0" encoding="UTF-8"?>'];
        $out[] = '<rss xmlns:g="http://base.google.com/ns/1.0" version="2.0">';
        $out[] = '  <channel>';
        $out[] = '    <title>' . xml_e((string) SeoSettings::get('site_name', 'Lala Mobiles')) . '</title>';
        $out[] = '    <link>' . xml_e(Url::absolute('/')) . '</link>';
        $out[] = '    <description>Product feed for Lala Mobiles</description>';

        foreach ($products as $p) {
            $out[] = self::item($p);
        }

        $out[] = '  </channel>';
        $out[] = '</rss>';
        return implode("\n", $out);
    }

    private static function item(array $p): string
    {
        $canonical = Url::absolute('/product/' . $p['slug']);
        $price     = $p['sale_price'] !== null ? $p['sale_price'] : $p['price'];

        $lines = ['    <item>'];
        $lines[] = '      <g:id>' . xml_e($p['sku'] ?: 'prod-' . $p['id']) . '</g:id>';
        $lines[] = '      <g:title>' . xml_e($p['name']) . '</g:title>';
        $lines[] = '      <g:description>' . xml_e(excerpt((string) ($p['short_desc'] ?? $p['description'] ?? ''), 5000)) . '</g:description>';
        $lines[] = '      <g:link>' . xml_e($canonical) . '</g:link>';
        $lines[] = '      <g:image_link>' . xml_e(Url::absolute((string) $p['main_image'])) . '</g:image_link>';

        // Additional images
        $extraImages = Database::fetchAll(
            'SELECT path FROM product_images WHERE product_id = ? ORDER BY position ASC LIMIT 10',
            [(int) $p['id']]
        );
        foreach ($extraImages as $img) {
            $lines[] = '      <g:additional_image_link>' . xml_e(Url::absolute($img['path'])) . '</g:additional_image_link>';
        }

        $lines[] = '      <g:price>' . xml_e(price_decimal($price) . ' PKR') . '</g:price>';
        if ($p['sale_price'] !== null) {
            $lines[] = '      <g:sale_price>' . xml_e(price_decimal($p['sale_price']) . ' PKR') . '</g:sale_price>';
        }
        $lines[] = '      <g:availability>' . xml_e(self::availability((int) $p['stock'], $p['status'])) . '</g:availability>';
        $lines[] = '      <g:condition>' . xml_e(self::CONDITION_MAP[$p['condition']] ?? 'new') . '</g:condition>';

        if (!empty($p['brand_name'])) {
            $lines[] = '      <g:brand>' . xml_e($p['brand_name']) . '</g:brand>';
        }
        // GTIN / MPN — only when genuinely present in the database.
        if (!empty($p['gtin'])) {
            $lines[] = '      <g:gtin>' . xml_e($p['gtin']) . '</g:gtin>';
        }
        if (!empty($p['mpn'])) {
            $lines[] = '      <g:mpn>' . xml_e($p['mpn']) . '</g:mpn>';
        }
        if (!empty($p['sku'])) {
            $lines[] = '      <g:identifier_exists>' . xml_e((!empty($p['gtin']) || !empty($p['mpn'])) ? 'yes' : 'no') . '</g:identifier_exists>';
        }

        // Google product category — map our category names to a GPC id when known.
        $gpc = self::googleProductCategory((string) ($p['category_name'] ?? ''));
        if ($gpc !== null) {
            $lines[] = '      <g:google_product_category>' . xml_e($gpc) . '</g:google_product_category>';
        }

        // Shipping (from settings — required by Merchant Center for many regions)
        $country = (string) SeoSettings::get('feed_shipping_country', 'PK');
        $region  = (string) SeoSettings::get('feed_shipping_region', '');
        $service = (string) SeoSettings::get('feed_shipping_service', 'Standard');
        $shipPrice = (string) SeoSettings::get('feed_shipping_price', '0.00 PKR');
        $freeOver  = (string) SeoSettings::get('feed_free_shipping_over', '');
        $lines[] = '      <g:shipping>';
        $lines[] = '        <g:shipping_service>' . xml_e($service) . '</g:shipping_service>';
        $lines[] = '        <g:country>' . xml_e($country) . '</g:country>';
        if ($region !== '') {
            $lines[] = '        <g:region>' . xml_e($region) . '</g:region>';
        }
        $lines[] = '        <g:price>' . xml_e($shipPrice) . '</g:price>';
        $lines[] = '      </g:shipping>';
        if ($freeOver !== '') {
            $lines[] = '      <g:free_shipping_threshold>' . xml_e($freeOver) . '</g:free_shipping_threshold>';
        }

        // Item group for variants (colour/storage) so Merchant Center groups them.
        $variants = Database::fetchAll(
            'SELECT label FROM product_variants WHERE product_id = ? AND is_active = 1 ORDER BY position LIMIT 20',
            [(int) $p['id']]
        );
        if ($variants !== []) {
            $lines[] = '      <g:item_group_id>' . xml_e($p['sku'] ?: 'prod-' . $p['id']) . '</g:item_group_id>';
        }

        $lines[] = '      <g:product_type>' . xml_e($p['category_name'] ?? '') . '</g:product_type>';
        $lines[] = '    </item>';
        return implode("\n", $lines);
    }

    /** Rough Google Product Category mapping for common mobile-shop categories. */
    private static function googleProductCategory(string $categoryName): ?string
    {
        $map = [
            'Smartphones'       => '267',  // Cell Phones & Smartphones
            'Samsung Phones'    => '267',
            'iPhone'            => '267',
            'Xiaomi Phones'     => '267',
            'Budget Phones'     => '267',
            'Used Phones'       => '267',
            'Refurbished Phones'=> '267',
            'Mobile Accessories'=> '736',  // Accessories
            'Power Banks'       => '736',
        ];
        return $map[$categoryName] ?? null;
    }

    /** Quick structural validation of the generated feed. */
    public static function validate(string $xml): array
    {
        $errors = [];
        $prev = libxml_use_internal_errors(true);
        $doc  = simplexml_load_string($xml);
        libxml_clear_errors();
        libxml_use_internal_errors($prev);

        if ($doc === false) {
            $errors[] = 'Feed is not well-formed XML.';
            return $errors;
        }
        if ($doc->channel->item === null && count($doc->channel->item ?? []) === 0) {
            $errors[] = 'Feed contains no items — check that active products exist.';
        }
        // Price format sanity: "123456.00 PKR"
        foreach ($doc->channel->item as $item) {
            $price = (string) $item->children('g', true)->price;
            if (!preg_match('/^\d+(\.\d{2})? [A-Z]{3}$/', $price)) {
                $errors[] = 'Invalid price format: "' . $price . '" (expected "0.00 CUR").';
                break;
            }
        }
        return $errors;
    }
}
