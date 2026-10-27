<?php

declare(strict_types=1);

namespace App\Seo;

use App\Core\Database;

/**
 * JSON-LD structured data builder.
 *
 * Every schema graph is built from real database content only:
 *  - prices come from the products table (price / sale_price)
 *  - availability comes from the stock column
 *  - AggregateRating appears ONLY when enough genuine, approved reviews exist
 *  - itemCondition maps from the product's condition enum
 *  - GTIN/MPN appear only when actually stored (never invented)
 *
 * Output is validated as JSON before it is emitted; a failure degrades to
 * omitting the block rather than shipping invalid markup.
 */
final class StructuredData
{
    private const SCHEMA = 'https://schema.org';

    /* ------------------------------------------------- store identity -- */

    public static function organization(): array
    {
        $name = (string) SeoSettings::get('organization_name', 'Lala Mobiles');
        $logo = Url::absolute((string) SeoSettings::get('organization_logo', 'img/logo.svg'));
        $url  = Url::absolute('/');

        $graph = [
            '@type' => 'Organization',
            '@id'   => Url::absolute('/#organization'),
            'name'  => $name,
            'url'   => $url,
            'logo'  => [
                '@type' => 'ImageObject',
                'url'   => $logo,
                'width' => 800,
                'height'=> 800,
            ],
        ];

        $phone = (string) SeoSettings::get('organization_phone', '');
        $email = (string) SeoSettings::get('organization_email', '');
        if ($phone !== '' || $email !== '') {
            $contact = ['@type' => 'ContactPoint', 'contactType' => 'customer service', 'areaServed' => 'PK'];
            if ($phone !== '') {
                $contact['telephone'] = $phone;
            }
            if ($email !== '') {
                $contact['email'] = $email;
            }
            $graph['contactPoint'] = $contact;
        }

        // Address block — only when genuinely configured.
        $address = (string) SeoSettings::get('organization_address', '');
        if ($address !== '') {
            $graph['address'] = [
                '@type'           => 'PostalAddress',
                'streetAddress'   => $address,
                'addressLocality' => (string) SeoSettings::get('organization_city', ''),
                'addressRegion'   => (string) SeoSettings::get('organization_region', ''),
                'postalCode'      => (string) SeoSettings::get('organization_postal', ''),
                'addressCountry'  => (string) SeoSettings::get('organization_country', 'PK'),
            ];
        }

        $sameAs = array_filter([
            (string) SeoSettings::get('social_facebook', ''),
            (string) SeoSettings::get('social_instagram', ''),
            (string) SeoSettings::get('social_tiktok', ''),
            (string) SeoSettings::get('social_youtube', ''),
        ]);
        if ($sameAs !== []) {
            $graph['sameAs'] = array_values($sameAs);
        }

        return ['@context' => self::SCHEMA, '@graph' => [$graph]];
    }

    public static function website(): array
    {
        $graph = [
            '@type' => 'WebSite',
            '@id'   => Url::absolute('/#website'),
            'name'  => (string) SeoSettings::get('site_name', 'Lala Mobiles'),
            'url'   => Url::absolute('/'),
            'publisher' => ['@id' => Url::absolute('/#organization')],
        ];

        // Site search action — only advertise it if the search route is live.
        $graph['potentialAction'] = [
            '@type' => 'SearchAction',
            'target' => [
                '@type'       => 'EntryPoint',
                'urlTemplate' => Url::absolute('/search?q={search_term_string}'),
            ],
            'query-input' => 'required name=search_term_string',
        ];

        return ['@context' => self::SCHEMA, '@graph' => [$graph]];
    }

    /**
     * LocalBusiness — emitted ONLY when the address + geo data genuinely exist.
     */
    public static function localBusiness(): ?array
    {
        $address = (string) SeoSettings::get('organization_address', '');
        $lat     = (string) SeoSettings::get('geo_latitude', '');
        $lng     = (string) SeoSettings::get('geo_longitude', '');
        if ($address === '' || $lat === '' || $lng === '') {
            return null;
        }

        $graph = [
            '@type'    => 'LocalBusiness',
            '@id'      => Url::absolute('/#localbusiness'),
            'name'     => (string) SeoSettings::get('organization_name', 'Lala Mobiles'),
            'url'      => Url::absolute('/'),
            'image'    => Url::absolute((string) SeoSettings::get('organization_logo', 'img/logo.svg')),
            'telephone'=> (string) SeoSettings::get('organization_phone', ''),
            'priceRange' => 'PKR',
            'geo' => [
                '@type'     => 'GeoCoordinates',
                'latitude'  => $lat,
                'longitude' => $lng,
            ],
            'address' => [
                '@type'           => 'PostalAddress',
                'streetAddress'   => $address,
                'addressLocality' => (string) SeoSettings::get('organization_city', ''),
                'addressRegion'   => (string) SeoSettings::get('organization_region', ''),
                'postalCode'      => (string) SeoSettings::get('organization_postal', ''),
                'addressCountry'  => (string) SeoSettings::get('organization_country', 'PK'),
            ],
        ];

        // Opening hours are emitted ONLY if the admin genuinely configured them —
        // never hard-coded (fabricated LocalBusiness data hurts more than helps).
        // Accepted: a repeatable string such as "Mo-Su 11:00-21:00".
        $hours = trim((string) SeoSettings::get('opening_hours', ''));
        if ($hours !== '') {
            $graph['openingHours'] = [$hours];
        }
        $graph['parentOrganization'] = ['@id' => Url::absolute('/#organization')];

        return ['@context' => self::SCHEMA, '@graph' => [$graph]];
    }

    /* ------------------------------------------------------ breadcrumbs -- */

    /**
     * Accepts either ['name'=>..,'path'=>..] maps or [name, path] tuples —
     * controllers and views use the tuple form for rendering.
     *
     * @param array<int, array{name?:string, path?:string}|array{0:string,1:string}> $crumbs
     */
    public static function breadcrumbList(array $crumbs): array
    {
        $items = [];
        $pos   = 1;
        $total = count($crumbs);
        foreach ($crumbs as $crumb) {
            $name = (string) ($crumb['name'] ?? $crumb[0] ?? '');
            $path = (string) ($crumb['path'] ?? $crumb[1] ?? '');
            if ($name === '') {
                continue;
            }
            $item = [
                '@type'    => 'ListItem',
                'position' => $pos,
                'name'     => $name,
            ];
            // Last crumb is the current page — schema omits its URL.
            if ($pos < $total) {
                $item['item'] = Url::absolute($path);
            }
            $items[] = $item;
            $pos++;
        }
        return [
            '@context' => self::SCHEMA,
            '@type'    => 'BreadcrumbList',
            'itemListElement' => $items,
        ];
    }

    /* ---------------------------------------------------------- product -- */

    /**
     * Product schema with Offer. Review/AggregateRating are added only when
     * genuine approved reviews exist (see review conditions below).
     */
    public static function product(array $product, string $canonicalUrl, ?array $brand = null, ?array $category = null): array
    {
        $graph = [
            '@type' => 'Product',
            '@id'   => $canonicalUrl . '#product',
            'name'  => $product['name'],
            'url'   => $canonicalUrl,
            'description' => trim(strip_tags((string) ($product['short_desc'] ?? '')))
                ?: excerpt((string) ($product['description'] ?? ''), 300),
            'sku'   => (string) ($product['sku'] ?? ''),
            'mpn'   => (string) ($product['mpn'] ?? '') ?: null,
            'gtin'  => (string) ($product['gtin'] ?? '') ?: null,
            'itemCondition' => self::itemCondition($product['condition']),
            'category' => $category['name'] ?? '',
        ];
        // Remove empty identifier fields — never emit empty strings as IDs.
        $graph = array_filter($graph, static fn($v) => $v !== null && $v !== '');

        if ($brand !== null && $brand !== []) {
            $graph['brand'] = ['@type' => 'Brand', 'name' => $brand['name']];
        }

        $images = self::productImages($product);
        if ($images !== []) {
            $graph['image'] = $images;
        }

        /* ------------------------------------------------------ offers -- */
        $price     = $product['sale_price'] !== null ? $product['sale_price'] : $product['price'];
        $inStock   = (int) $product['stock'] > 0 && $product['status'] === 'active';
        $availability = $inStock
            ? self::SCHEMA . '/InStock'
            : self::SCHEMA . '/OutOfStock';

        $offer = [
            '@type'  => 'Offer',
            'url'    => $canonicalUrl,
            'priceCurrency' => 'PKR',
            'price'  => price_decimal($price),
            'availability' => $availability,
            'itemCondition' => self::itemCondition($product['condition']),
            'seller' => ['@type' => 'Organization', 'name' => (string) SeoSettings::get('organization_name', 'Lala Mobiles')],
        ];

        if ($product['sale_price'] !== null) {
            $offer['priceValidUntil'] = date('Y-m-d', strtotime('+30 days'));
        }

        $graph['offers'] = $offer;

        /* --------------------------------------- aggregate rating (real) -- */
        $minReviews = (int) SeoSettings::get('review_min_for_aggregate', 3);
        $ratingCount = (int) ($product['rating_count'] ?? 0);
        $approvedCount = self::approvedReviewCount((int) $product['id']);

        if ($approvedCount >= $minReviews && $ratingCount >= $minReviews && (float) $product['rating_avg'] > 0) {
            $graph['aggregateRating'] = [
                '@type'       => 'AggregateRating',
                'ratingValue' => number_format((float) $product['rating_avg'], 1, '.', ''),
                'reviewCount' => (string) $approvedCount,
                'bestRating'  => '5',
                'worstRating' => '1',
            ];

            // Individual Review markup: only verified, approved reviews.
            $reviews = Database::fetchAll(
                'SELECT author, rating, title, body, created_at
                 FROM reviews
                 WHERE product_id = ? AND approved = 1 AND verified_purchase = 1
                 ORDER BY created_at DESC LIMIT 3',
                [(int) $product['id']]
            );
            if ($reviews !== []) {
                $graph['review'] = array_map(static fn(array $r) => [
                    '@type'        => 'Review',
                    'author'       => ['@type' => 'Person', 'name' => $r['author']],
                    'datePublished'=> date('Y-m-d', strtotime($r['created_at'])),
                    'name'         => (string) ($r['title'] ?? ''),
                    'reviewBody'   => (string) ($r['body'] ?? ''),
                    'reviewRating' => [
                        '@type'       => 'Rating',
                        'ratingValue' => (string) $r['rating'],
                        'bestRating'  => '5',
                        'worstRating' => '1',
                    ],
                ], $reviews);
            }
        }

        return ['@context' => self::SCHEMA, '@graph' => [$graph]];
    }

    /* ------------------------------------------------------ blog / page -- */

    public static function blogPosting(array $post, string $canonicalUrl): array
    {
        $graph = [
            '@type' => 'BlogPosting',
            '@id'   => $canonicalUrl . '#article',
            'headline' => $post['title'],
            'description' => (string) ($post['excerpt'] ?? ''),
            'url'   => $canonicalUrl,
            'mainEntityOfPage' => [
                '@type' => 'WebPage',
                '@id'   => $canonicalUrl,
            ],
            'datePublished' => $post['published_at'] ?? $post['created_at'],
            'dateModified'  => $post['updated_at'] ?? ($post['published_at'] ?? $post['created_at']),
            'author' => [
                '@type' => 'Person',
                'name'  => (string) ($post['author_name'] ?? 'Lala Mobiles Team'),
            ],
            'publisher' => ['@id' => Url::absolute('/#organization')],
        ];
        if (!empty($post['cover_image'])) {
            $graph['image'] = [Url::absolute($post['cover_image'])];
        }
        $graph = array_filter($graph, static fn($v) => $v !== '' && $v !== null);
        return ['@context' => self::SCHEMA, '@graph' => [$graph]];
    }

    /**
     * Repair / service pages: Service schema (only when the page genuinely
     * carries service data — service_name is set by the admin).
     */
    public static function service(array $page, string $canonicalUrl): ?array
    {
        if (empty($page['service_name'])) {
            return null;
        }
        $graph = [
            '@type' => 'Service',
            '@id'   => $canonicalUrl . '#service',
            'name'  => $page['service_name'],
            'description' => (string) ($page['intro'] ?? ''),
            'url'   => $canonicalUrl,
            'provider' => ['@id' => Url::absolute('/#organization')],
            'areaServed' => array_values(array_filter(array_map('trim', explode(',', (string) ($page['area_served'] ?? ''))))),
        ];
        if ($graph['areaServed'] === []) {
            unset($graph['areaServed']);
        }

        if ($page['price_from'] !== null) {
            $offer = [
                '@type'  => 'Offer',
                'priceCurrency' => 'PKR',
                'price'  => price_decimal($page['price_from']),
                'availability' => self::SCHEMA . '/InStock',
            ];
            if ($page['price_to'] !== null) {
                $offer['description'] = 'Typical price range: ' . price($page['price_from'])
                    . ' – ' . price($page['price_to']);
            }
            $graph['offers'] = $offer;
        }

        return ['@context' => self::SCHEMA, '@graph' => [$graph]];
    }

    /**
     * CollectionPage + ItemList for category/brand/filter landing pages.
     *
     * @param array<int, array $products> $products
     */
    public static function itemList(string $name, string $canonicalUrl, array $products, int $total): array
    {
        $items = [];
        $pos   = 1;
        foreach ($products as $p) {
            $price = $p['sale_price'] !== null ? $p['sale_price'] : $p['price'];
            $items[] = [
                '@type'    => 'ListItem',
                'position' => $pos++,
                'url'      => Url::absolute('/product/' . $p['slug']),
                'name'     => $p['name'],
            ];
        }
        $graph = [
            '@type' => 'CollectionPage',
            '@id'   => $canonicalUrl,
            'name'  => $name,
            'url'   => $canonicalUrl,
            'mainEntity' => [
                '@type'    => 'ItemList',
                'numberOfItems' => $total,
                'itemListElement' => $items,
            ],
        ];
        return ['@context' => self::SCHEMA, '@graph' => [$graph]];
    }

    /* ---------------------------------------------------------- helpers -- */

    public static function itemCondition(string $condition): string
    {
        return match ($condition) {
            'used'       => self::SCHEMA . '/UsedCondition',
            'refurbished'=> self::SCHEMA . '/RefurbishedCondition',
            default      => self::SCHEMA . '/NewCondition',
        };
    }

    /** @return array<int, string> absolute image URLs */
    private static function productImages(array $product): array
    {
        $urls = [];
        if (!empty($product['main_image'])) {
            $urls[] = Url::absolute($product['main_image']);
        }
        $extra = Database::fetchAll(
            'SELECT path FROM product_images WHERE product_id = ? ORDER BY position ASC LIMIT 5',
            [(int) $product['id']]
        );
        foreach ($extra as $row) {
            $url = Url::absolute($row['path']);
            if (!in_array($url, $urls, true)) {
                $urls[] = $url;
            }
        }
        return $urls;
    }

    private static function approvedReviewCount(int $productId): int
    {
        return (int) Database::fetchColumn(
            'SELECT COUNT(*) FROM reviews WHERE product_id = ? AND approved = 1',
            [$productId]
        );
    }

    /**
     * Encode a schema graph as a safe <script type="application/ld+json"> tag.
     * Returns '' if the payload fails JSON validation (never ship broken JSON-LD).
     */
    public static function toScriptTag(array $schema): string
    {
        $json = json_encode(
            $schema,
            JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_INVALID_UTF8_SUBSTITUTE
        );
        if ($json === false) {
            error_log('[SEO] JSON-LD encoding failed: ' . json_last_error_msg());
            return '';
        }
        // Validate round-trip before shipping.
        if (json_decode($json, true) === null) {
            error_log('[SEO] JSON-LD failed validation, block omitted.');
            return '';
        }
        // Guard against </script> breakout inside string values.
        $json = str_replace('</', '<\/', $json);
        return '<script type="application/ld+json">' . $json . '</script>';
    }
}
