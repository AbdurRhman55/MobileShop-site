<?php

declare( strict_types = 1 );

namespace App\Models;

use App\Core\Database;

/**
* Product catalogue model with SEO-aware lifecycle.
*
* Lifecycle rules:
*  - active:        sellable, in sitemap, Offer in schema
*  - discontinued:  page stays live ( 200 ), Offer shows OutOfStock, still indexed
*  - deleted:       page returns 301 to replacement ( redirect_to_id ) when set,
*                   otherwise 410 Gone. Never a blanket redirect to home.
*/
final class Product {
    public const PAGE_SIZE = 12;

    public static function find( int $id ): ?array {
        return Database::fetch(
            'SELECT p.*, b.name AS brand_name, b.slug AS brand_slug, c.name AS category_name, c.slug AS category_slug
             FROM products p
             LEFT JOIN brands b ON b.id = p.brand_id
             LEFT JOIN categories c ON c.id = p.category_id
             WHERE p.id = ?',
            [ $id ]
        );
    }

    public static function findBySlug( string $slug ): ?array {
        return Database::fetch(
            'SELECT p.*, b.name AS brand_name, b.slug AS brand_slug, c.name AS category_name, c.slug AS category_slug
             FROM products p
             LEFT JOIN brands b ON b.id = p.brand_id
             LEFT JOIN categories c ON c.id = p.category_id
             WHERE p.slug = ?',
            [ $slug ]
        );
    }

    public static function images( int $productId ): array {
        return Database::fetchAll(
            'SELECT * FROM product_images WHERE product_id = ? ORDER BY position ASC',
            [ $productId ]
        );
    }

    public static function specs( int $productId ): array {
        return Database::fetchAll(
            'SELECT * FROM product_specs WHERE product_id = ? ORDER BY spec_group ASC, position ASC',
            [ $productId ]
        );
    }

    public static function variants( int $productId ): array {
        return Database::fetchAll(
            'SELECT * FROM product_variants WHERE product_id = ? AND is_active = 1 ORDER BY position ASC',
            [ $productId ]
        );
    }

    public static function approvedReviews( int $productId ): array {
        return Database::fetchAll(
            'SELECT * FROM reviews WHERE product_id = ? AND approved = 1 ORDER BY created_at DESC',
            [ $productId ]
        );
    }

    /** Related products: same category, excluding self;
    falls back to brand. */
    public static function related( array $product, int $limit = 4 ): array {
        if ( !empty( $product[ 'category_id' ] ) ) {
            $rows = Database::fetchAll(
                "SELECT p.*, b.name AS brand_name, b.slug AS brand_slug
                 FROM products p LEFT JOIN brands b ON b.id = p.brand_id
                 WHERE p.category_id = ? AND p.id <> ? AND p.status = 'active'
                 ORDER BY p.featured DESC, p.id DESC LIMIT " . ( int ) $limit,
                [ ( int ) $product[ 'category_id' ], ( int ) $product[ 'id' ] ]
            );
            if ( $rows !== [] ) {
                return $rows;
            }
        }
        if ( !empty( $product[ 'brand_id' ] ) ) {
            return Database::fetchAll(
                "SELECT p.*, b.name AS brand_name, b.slug AS brand_slug
                 FROM products p LEFT JOIN brands b ON b.id = p.brand_id
                 WHERE p.brand_id = ? AND p.id <> ? AND p.status = 'active'
                 ORDER BY p.featured DESC, p.id DESC LIMIT " . ( int ) $limit,
                [ ( int ) $product[ 'brand_id' ], ( int ) $product[ 'id' ] ]
            );
        }
        return [];
    }

    /** Paginated, filterable product query for listing pages. */
    public static function listing( array $query, int|array|null $categoryId = null, ?int $brandId = null, int $page = 1, int $pageSize = self::PAGE_SIZE, ?string $search = null ): array {
        [ $filterSql, $filterParams ] = \App\Seo\Facets::productFilterSql( $query, $categoryId, $brandId );

        // Internal search reuses the same facet pipeline so every control
        // (brand, price, condition, rating, sort) works on /search too.
        $term = $search !== null ? trim( $search ) : '';
        if ( $term !== '' ) {
            $like = '%' . $term . '%';
            $filterSql     .= ' AND (p.name LIKE ? OR p.short_desc LIKE ? OR p.sku LIKE ? OR b.name LIKE ?)';
            $filterParams[] = $like;
            $filterParams[] = $like;
            $filterParams[] = $like;
            $filterParams[] = $like;
        }

        $where = "p.status IN ('active','discontinued') $filterSql";
        $total = ( int ) Database::fetchColumn(
            "SELECT COUNT(*) FROM products p LEFT JOIN brands b ON b.id = p.brand_id WHERE $where",
            $filterParams
        );

        $offset  = max( 0, ( $page - 1 ) * $pageSize );
        $orderBy = \App\Seo\Facets::sortSql( isset( $query[ 'sort' ] ) ? ( string ) $query[ 'sort' ] : null );

        $rows = Database::fetchAll(
            "SELECT p.*, b.name AS brand_name, b.slug AS brand_slug
             FROM products p LEFT JOIN brands b ON b.id = p.brand_id
             WHERE $where
             ORDER BY $orderBy
             LIMIT $pageSize OFFSET $offset",
            $filterParams
        );

        return [ 'items' => $rows, 'total' => $total, 'page' => $page, 'pages' => ( int ) ceil( $total / $pageSize ) ];
    }

    public static function featured( int $limit = 8 ): array {
        return Database::fetchAll(
            "SELECT p.*, b.name AS brand_name, b.slug AS brand_slug
             FROM products p LEFT JOIN brands b ON b.id = p.brand_id
             WHERE p.status = 'active' AND p.featured = 1
             ORDER BY p.id DESC LIMIT " . ( int ) $limit
        );
    }

    public static function latest( int $limit = 8 ): array {
        return Database::fetchAll(
            "SELECT p.*, b.name AS brand_name, b.slug AS brand_slug
             FROM products p LEFT JOIN brands b ON b.id = p.brand_id
             WHERE p.status = 'active'
             ORDER BY p.published_at DESC, p.id DESC LIMIT " . ( int ) $limit
        );
    }

    public static function search( string $term, int $limit = 24 ): array {
        $like = '%' . $term . '%';
        return Database::fetchAll(
            "SELECT p.*, b.name AS brand_name, b.slug AS brand_slug
             FROM products p LEFT JOIN brands b ON b.id = p.brand_id
             WHERE p.status IN ('active','discontinued')
               AND (p.name LIKE ? OR p.short_desc LIKE ? OR p.sku LIKE ?)
             ORDER BY p.featured DESC, p.name ASC LIMIT " . ( int ) $limit,
            [ $like, $like, $like ]
        );
    }

    /** Effective price ( sale price when present ). */
    public static function effectivePrice( array $p ): float {
        return $p[ 'sale_price' ] !== null ? ( float ) $p[ 'sale_price' ] : ( float ) $p[ 'price' ];
    }

    public static function inStock( array $p ): bool {
        return ( int ) $p[ 'stock' ] > 0 && $p[ 'status' ] === 'active';
    }

    /**
    * Upsert with slug-change redirect handling.
    * When the slug changes, a 301 from the old /product/ {
        old}
        to the new URL
        * is recorded automatically.
        */
        public static function save( array $data, ?int $id = null ): int {
            if ( $id !== null ) {
                $existing = Database::fetch( 'SELECT slug FROM products WHERE id = ?', [ $id ] );
                if ( $existing !== null && $existing[ 'slug' ] !== $data[ 'slug' ] ) {
                    \App\Seo\RedirectManager::record(
                        '/product/' . $existing[ 'slug' ],
                        '/product/' . $data[ 'slug' ],
                        'Product slug changed'
                    );
                }
                Database::update( 'products', $id, $data );
                \App\Seo\SitemapGenerator::flushCache();
                return $id;
            }
            $newId = Database::insert( 'products', $data );
            \App\Seo\SitemapGenerator::flushCache();
            return $newId;
        }
    }
