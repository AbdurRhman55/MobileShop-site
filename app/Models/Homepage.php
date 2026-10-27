<?php

declare( strict_types = 1 );

namespace App\Models;

use App\Core\Database;
use App\Core\Config;

/**
* Homepage section data.
*
* Every section is database-driven and degrades gracefully: when a promo
* table has no rows ( fresh install ) the caller gets an empty array and the
* view simply skips the section. Nothing is ever fabricated.
*
* Ordering + enable/disable comes from homepage_sections ( section_key ). A
* section with no row there still renders, at the end, unless the whole
* section returns no items.
*/
final class Homepage {
    /** Default order for sections that have no homepage_sections row. */
    private const DEFAULT_ORDER = [
        'hero' => 0, 'trust' => 10, 'categories' => 20, 'audio' => 25, 'flash' => 30,
        'bestsellers' => 40, 'new-arrivals' => 50, 'brands' => 60,
        'banners' => 70, 'recommended' => 80, 'repairs' => 90,
        'reviews' => 100, 'newsletter' => 110,
    ];

    /**
    * Active, in-window hero slides ordered for display.
    *
    * @return array<int, array>
    */
    public static function heroSlides(): array {
        $now = self::nowSql();
        return Database::fetchAll(
            "SELECT * FROM hero_slides
             WHERE is_active = 1
               AND (starts_at IS NULL OR starts_at <= ?)
               AND (ends_at   IS NULL OR ends_at   >  ?)
             ORDER BY position ASC, id ASC",
            [ $now, $now ]
        );
    }

    /**
    * Active trust indicators.
    *
    * @return array<int, array>
    */
    public static function trustIndicators(): array {
        return Database::fetchAll(
            'SELECT * FROM trust_indicators WHERE is_active = 1 ORDER BY position ASC, id ASC'
        );
    }

    /**
    * Category tiles for the 'Shop by category' grid.
    *
    * Only categories that have at least one sellable product are returned,
    * so the grid never links to an empty page.
    *
    * @return array<int, array>
    */
    public static function categoryTiles( int $limit = 12 ): array {
        return Database::fetchAll(
            "WITH RECURSIVE category_tree AS (
                SELECT id, id AS root_id FROM categories WHERE parent_id IS NULL AND status = 'active'
                UNION ALL
                SELECT child.id, tree.root_id FROM categories child
                JOIN category_tree tree ON child.parent_id = tree.id
                WHERE child.status = 'active'
             )
             SELECT root.*, COUNT(DISTINCT p.id) AS product_count
             FROM categories root
             JOIN category_tree tree ON tree.root_id = root.id
             LEFT JOIN products p ON p.category_id = tree.id AND p.status = 'active' AND p.stock > 0
             WHERE root.parent_id IS NULL AND root.status = 'active' AND root.is_active = 1
             GROUP BY root.id
             HAVING product_count > 0
             ORDER BY root.sort_order ASC, root.position ASC, product_count DESC, root.name ASC
             LIMIT " . max( 1, $limit )
        );
    }

    /** Audio products for the dedicated audio discovery strip. */
    public static function productsForCategory( string $slug, int $limit = 8 ): array {
        return Database::fetchAll(
            "WITH RECURSIVE category_tree AS (
                SELECT id FROM categories WHERE slug = ? AND status = 'active'
                UNION ALL
                SELECT child.id FROM categories child JOIN category_tree tree ON child.parent_id = tree.id
                WHERE child.status = 'active'
             )
             SELECT p.*, b.name AS brand_name, b.slug AS brand_slug
             FROM products p JOIN category_tree tree ON tree.id = p.category_id
             LEFT JOIN brands b ON b.id = p.brand_id
             WHERE p.status = 'active'
             ORDER BY p.featured DESC, p.published_at DESC, p.id DESC
             LIMIT " . max( 1, $limit ),
            [ $slug ]
        );
    }

    /**
    * Brands that actually stock products, for the trending-brands strip.
    *
    * @return array<int, array>
    */
    public static function brands( int $limit = 12 ): array {
        return Database::fetchAll(
            "SELECT b.*, COUNT(p.id) AS product_count
             FROM brands b
             JOIN products p
               ON p.brand_id = b.id
              AND p.status = 'active'
              AND p.stock > 0
             WHERE b.status = 'active'
             GROUP BY b.id
             ORDER BY product_count DESC, b.name ASC
             LIMIT " . max( 1, $limit )
        );
    }

    /**
    * Active promo banners for a location ( 'home', 'shop', … ), respecting the
    * campaign date window. Expired or scheduled-away banners never render.
    *
    * @return array<int, array>
    */
    public static function banners( string $location = 'home', int $limit = 6 ): array {
        $now = self::nowSql();
        return Database::fetchAll(
            "SELECT * FROM promo_banners
             WHERE is_active = 1
               AND location = ?
               AND (starts_at IS NULL OR starts_at <= ?)
               AND (ends_at   IS NULL OR ends_at   >  ?)
             ORDER BY position ASC, id ASC
             LIMIT " . max( 1, $limit ),
            [ $location, $now, $now ]
        );
    }

    /**
    * Blog posts that are published and recent.
    *
    * @return array<int, array>
    */
    public static function blogPosts( int $limit = 3 ): array {
        return Database::fetchAll(
            "SELECT * FROM blog_posts
             WHERE status = 'published'
             ORDER BY published_at DESC, id DESC
             LIMIT " . max( 1, $limit )
        );
    }

    /**
    * Ordered section config ( key -> row ) merged over DEFAULT_ORDER.
    *
    * @return array<string, array {
        title:?string, subtitle:?string, position:int, is_active:bool}
        >
        */
        public static function sectionConfig(): array {
            $rows = Database::fetchAll( 'SELECT * FROM homepage_sections' );
            $byKey = [];
            foreach ( $rows as $r ) {
                $byKey[ $r[ 'section_key' ] ] = [
                    'title'    => $r[ 'title' ],
                    'subtitle' => $r[ 'subtitle' ],
                    'position' => ( int ) $r[ 'position' ],
                    'is_active'=> ( bool ) $r[ 'is_active' ],
                ];
            }
            // Ensure every known section has an entry.
            foreach ( self::DEFAULT_ORDER as $key => $pos ) {
                if ( !isset( $byKey[ $key ] ) ) {
                    $byKey[ $key ] = [ 'title' => null, 'subtitle' => null, 'position' => $pos, 'is_active' => true ];
                }
            }
            return $byKey;
        }

        /**
        * Section keys in display order, filtered to the active ones.
        *
        * @return string[]
        */
        public static function activeSectionOrder(): array {
            $cfg = self::sectionConfig();
            $keys = [];
            foreach ( $cfg as $key => $c ) {
                if ( $c[ 'is_active' ] ) {
                    $keys[] = $key;
                }
            }
            usort( $keys, static function ( string $a, string $b ) use ( $cfg ): int {
                $d = $cfg[ $a ][ 'position' ] <=> $cfg[ $b ][ 'position' ];
                return $d !== 0 ? $d : ( self::DEFAULT_ORDER[ $a ] ?? 999 ) <=> ( self::DEFAULT_ORDER[ $b ] ?? 999 );
            }
        );
        return $keys;
    }

    /** Current time as a MySQL datetime in the app timezone. */
    private static function nowSql(): string {
        $tz = ( string ) Config::get( 'app.timezone', 'UTC' );
        try {
            $now = new \DateTimeImmutable( 'now', new \DateTimeZone( $tz ) );
            return $now->format( 'Y-m-d H:i:s' );
        } catch ( \Throwable ) {
            return date( 'Y-m-d H:i:s' );
        }
    }
}
