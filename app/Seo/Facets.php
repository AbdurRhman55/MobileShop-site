<?php

declare( strict_types = 1 );

namespace App\Seo;

use App\Core\Database;

/**
* SEO-aware faceted navigation.
*
* INDEXING POLICY ( explicit, documented ):
*  1. Base category/brand pages                     → index, self-canonical
*  2. Paginated pages ( ?page = N )                     → index, self-canonical ( N≤3 ), then noindex
*  3. ANY filter/sort combination ( ad-hoc URLs )     → noindex, follow, canonical → parent base URL
*  4. Filter pages published by an admin            → index, self-canonical ( their own SEO landing page )
*  5. Internal search results                       → noindex, follow
*
* This prevents unlimited crawlable filter combinations while preserving
* customer-facing filtering. Admins promote valuable combinations into
* dedicated /shop/ {
    slug}
    landing pages ( filter_pages table ).
    */
    final class Facets {
        /** Query params that count as 'filtering' for indexing purposes. */
        public const FILTER_PARAMS = [ 'brand', 'storage', 'ram', 'color', 'min_price', 'max_price', 'condition', 'rating', 'in_stock', 'discount' ];

        /** Sort-only params — these never create unique content. */
        public const SORT_PARAMS = [ 'sort' ];

        /** How many pagination pages stay indexable ( deep pages get noindex ). */
        public const MAX_INDEXABLE_PAGES = 3;

        /**
        * Decide robots + canonical for a listing request.
        *
        * @param array $query  the raw query string params
        * @return array {
            robots:string, canonical_path:string, is_filtered:bool, page:int}
            */
            public static function listingSeo( string $basePath, array $query, ?array $matchedFilterPage = null ): array {
                $page = max( 1, ( int ) ( $query[ 'page' ] ?? 1 ) );

                $hasFilter = self::hasFilter( $query );
                $hasSort   = self::hasSort( $query );

                /* Case 4: this request IS a published, admin-designated filter page. */
                if ( $matchedFilterPage !== null ) {
                    return [
                        'robots'         => 'index,follow',
                        'canonical_path' => '/shop/' . $matchedFilterPage[ 'slug' ],
                        'is_filtered'    => true,
                        'page'           => $page,
                    ];
                }

                /* Case 1+2: clean base or paginated page. */
                if ( !$hasFilter && !$hasSort ) {
                    $path = $page > 1 ? $basePath . '?page=' . $page : $basePath;
                    // Deep pages are duplicates in practice — noindex them ( still crawlable ).
                    $robots = $page <= self::MAX_INDEXABLE_PAGES ? 'index,follow' : 'noindex,follow';
                    return [
                        'robots'         => $robots,
                        'canonical_path' => $path,
                        'is_filtered'    => false,
                        'page'           => $page,
                    ];
                }

                /* Case 3: ad-hoc filtered/sorted URL → noindex, canonical to base.
                * NOTE: 'nofollow' would NOT keep these out of the index — only
                * 'noindex' does. Canonical points at the parent as a second signal. */
                return [
                    'robots'         => 'noindex,follow',
                    'canonical_path' => $basePath,
                    'is_filtered'    => true,
                    'page'           => $page,
                ];
            }

            public static function hasFilter( array $query ): bool {
                foreach ( self::FILTER_PARAMS as $p ) {
                    if ( !empty( $query[ $p ] ) && $query[ $p ] !== '' ) {
                        return true;
                    }
                }
                return false;
            }

            public static function hasSort( array $query ): bool {
                foreach ( self::SORT_PARAMS as $p ) {
                    if ( !empty( $query[ $p ] ) && $query[ $p ] !== '' ) {
                        return true;
                    }
                }
                return false;
            }

            /**
            * Find a published filter page whose slug matches the current query
            * combination ( so ad-hoc filter URLs can canonicalise onto the landing page ).
            */
            public static function matchFilterPage( int $categoryId, array $query ): ?array {
                $pages = Database::fetchAll(
                    "SELECT * FROM filter_pages WHERE status='published' AND (category_id = ? OR category_id IS NULL)",
                    [ $categoryId ]
                );
                foreach ( $pages as $fp ) {
                    $filters = json_decode( ( string ) $fp[ 'filters' ], true ) ?: [];
                    if ( self::queryMatchesFilters( $query, $filters ) ) {
                        return $fp;
                    }
                }
                return null;
            }

            /** Does the query-string filter set satisfy a stored filter-page definition? */
            private static function queryMatchesFilters( array $query, array $filters ): bool {
                foreach ( [ 'storage', 'ram', 'color' ] as $dim ) {
                    $wanted  = array_map( 'strval', ( array ) ( $filters[ $dim ] ?? [] ) );
                    $given   = array_filter( ( array ) ( $query[ $dim ] ?? [] ), static fn( $v ) => $v !== '' );
                    sort( $wanted );
                    sort( $given );
                    if ( $wanted !== $given ) {
                        return false;
                    }
                }
                foreach ( [ 'min_price', 'max_price' ] as $band ) {
                    if ( isset( $filters[ $band ] ) && ( string ) $filters[ $band ] !== ( string ) ( $query[ $band ] ?? '' ) ) {
                        return false;
                    }
                }
                if ( isset( $filters[ 'condition' ] ) && ( string ) $filters[ 'condition' ] !== ( string ) ( $query[ 'condition' ] ?? '' ) ) {
                    return false;
                }
                return true;
            }

            /**
            * Build the WHERE clause + params for a filter query against products.
            * All values are bound parameters — no SQL injection surface.
            *
            * @return array {
                0:string, 1:array}
                [ sql_fragment_with_leading_AND, params ]
                */
                public static function productFilterSql( array $query, int|array|null $categoryId = null, ?int $brandId = null ): array {
                    $sql    = '';
                    $params = [];

                    if ( $categoryId !== null ) {
                        $categoryIds = array_values( array_unique( array_map( 'intval', ( array ) $categoryId ) ) );
                        if ( $categoryIds !== [] ) {
                            $sql .= ' AND p.category_id IN (' . implode( ',', array_fill( 0, count( $categoryIds ), '?' ) ) . ')';
                            array_push( $params, ...$categoryIds );
                        }
                    }
                    if ( $brandId !== null ) {
                        $sql .= ' AND p.brand_id = ?';
                        $params[] = $brandId;
                    }
                    if ( !empty( $query[ 'brand' ] ) ) {
                        $slugs = array_values( array_filter( ( array ) $query[ 'brand' ], static fn( $slug ) => is_scalar( $slug ) && ( string ) $slug !== '' ) );
                        if ( $slugs !== [] ) {
                            $in = implode( ',', array_fill( 0, count( $slugs ), '?' ) );
                            $sql .= " AND b.slug IN ($in)";
                            foreach ( $slugs as $s ) {
                                $params[] = ( string ) $s;
                            }
                        }
                    }
                    if ( !empty( $query[ 'condition' ] ) ) {
                        $sql .= ' AND p.condition = ?';
                        $params[] = ( string ) $query[ 'condition' ];
                    }
                    if ( !empty( $query[ 'min_price' ] ) ) {
                        $sql .= ' AND p.price >= ?';
                        $params[] = ( float ) $query[ 'min_price' ];
                    }
                    if ( !empty( $query[ 'max_price' ] ) ) {
                        $sql .= ' AND p.price <= ?';
                        $params[] = ( float ) $query[ 'max_price' ];
                    }
                    if ( !empty( $query[ 'in_stock' ] ) ) {
                        $sql .= ' AND p.stock > 0';
                    }
                    if ( !empty( $query[ 'rating' ] ) && is_numeric( $query[ 'rating' ] ) ) {
                        $sql .= ' AND p.rating_avg >= ?';
                        $params[] = min( 5, max( 1, ( float ) $query[ 'rating' ] ) );
                    }
                    if ( !empty( $query[ 'discount' ] ) ) {
                        $sql .= ' AND p.sale_price IS NOT NULL AND p.sale_price < p.price';
                    }
                    // Storage / RAM / colour live in the variants table.
                    foreach ( [ 'storage' => 'v.storage', 'ram' => 'v.ram', 'color' => 'v.color' ] as $param => $col ) {
                        if ( !empty( $query[ $param ] ) ) {
                            $vals = array_values( array_filter( ( array ) $query[ $param ], static fn( $value ) => is_scalar( $value ) && ( string ) $value !== '' ) );
                            if ( $vals !== [] ) {
                                $in = implode( ',', array_fill( 0, count( $vals ), '?' ) );
                                $sql .= " AND EXISTS (SELECT 1 FROM product_variants v WHERE v.product_id = p.id AND v.is_active=1 AND $col IN ($in))";
                                foreach ( $vals as $v ) {
                                    $params[] = ( string ) $v;
                                }
                            }
                        }
                    }

                    return [ $sql, $params ];
                }

                /** Whitelisted sort expression ( never interpolate user input into SQL ). */
                public static function sortSql( ?string $sort ): string {
                    return match ( $sort ) {
                        'price_asc'  => 'COALESCE(p.sale_price, p.price) ASC',
                        'price_desc' => 'COALESCE(p.sale_price, p.price) DESC',
                        'newest'     => 'p.published_at DESC',
                        'rating'     => 'p.rating_avg DESC',
                        default      => 'p.featured DESC, p.id DESC',
                    }
                    ;
                }

                /** Build the canonical filter-page URL for a filter-page definition. */
                public static function filterPageUrl( array $filterPage ): string {
                    return '/shop/' . $filterPage[ 'slug' ];
                }

                /**
                * Load all published, indexable filter pages ( for sitemap + navigation ).
                */
                public static function publishedFilterPages(): array {
                    return Database::fetchAll(
                        "SELECT * FROM filter_pages WHERE status='published' AND is_indexable=1 ORDER BY name"
                    );
                }
            }
