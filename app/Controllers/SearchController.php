<?php

declare( strict_types = 1 );

namespace App\Controllers;

use App\Models\Product;
use App\Core\Database;

/**
* Internal search results.
*
* Always noindex, follow: search result pages are not useful landing pages and
* must never enter the index. The canonical points to /search without the
* query so parameterised variants do not consolidate incorrectly.
*/
final class SearchController extends BaseController {
    public function index(): void {
        $term = trim( ( string ) $this->request->query( 'q', '' ) );

        if ( $term === '' ) {
            $seo = $this->resolveMeta(
                'home',
                0,
                'Search — Lala Mobiles',
                'Search results on Lala Mobiles.',
                '/search',
                [ 'robots' => 'noindex,follow' ],
            );

            $this->renderPage( $seo, [], 'search', [
                'term'    => '',
                'results' => [],
            ] );
            return;
        }

        // Non-empty searches reuse the standard listing UI so brand, price,
        // condition, rating and sort all work — previously /search had no
        // facets at all and ignored every filter control.
        $query = $this->request->query;
        $page  = max( 1, ( int ) ( $query[ 'page' ] ?? 1 ) );
        $listing = Product::listing( $query, null, null, $page, 24, $term );

        $seo = $this->resolveMeta(
            'home',
            0,
            'Search: ' . $term . ' — Lala Mobiles',
            'Search results for “' . $term . '” on Lala Mobiles.',
            '/search',
            [ 'robots' => 'noindex,follow' ],
        );

        $this->renderPage( $seo, [], 'listing', [
            'listing'        => $listing,
            'heading'        => 'Results for “' . $term . '”',
            'intro'          => null,
            'breadcrumbs'    => [ [ 'Home', '/' ], [ 'Search', '/search' ] ],
            'basePath'       => '/search',
            'children'       => [],
            'category'       => null,
            'brands'         => \App\Models\Catalog::brandsForSearch( $term ),
            'searchTerm'     => $term,
            'seoRules'       => [
                'robots'         => 'noindex,follow',
                'is_filtered'    => false,
                'page'           => $page,
                'canonical_path' => '/search',
            ],
            'filterPageMatch' => null,
            'activeFilters'  => array_intersect_key( $query, array_flip( \App\Seo\Facets::FILTER_PARAMS ) ),
            'sort'           => ( string ) ( $query[ 'sort' ] ?? '' ),
        ] );
    }

    /**
    * Live-search suggestions ( JSON, consumed by assets/js/ui.js ).
    * Intentionally minimal payload and noindex — this is a UX endpoint, not
    * a landing page.
    */

    public function suggest(): void {
        $term    = trim( ( string ) $this->request->query( 'q', '' ) );
        $results = [];

        if ( mb_strlen( $term ) >= 2 ) {
            foreach ( Product::search( $term, 8 ) as $p ) {
                $results[] = [
                    'id'         => ( int ) $p[ 'id' ],
                    'slug'       => ( string ) $p[ 'slug' ],
                    'name'       => ( string ) $p[ 'name' ],
                    'price'      => Product::effectivePrice( $p ),
                    'main_image' => ( string ) $p[ 'main_image' ],
                ];
            }
            $like = '%' . $term . '%';
            foreach ( Database::fetchAll( "SELECT slug, name FROM categories WHERE status='active' AND name LIKE ? ORDER BY name LIMIT 4", [ $like ] ) as $category ) {
                $results[] = [ 'type' => 'category', 'slug' => ( string ) $category[ 'slug' ], 'name' => ( string ) $category[ 'name' ], 'url' => '/category/' . rawurlencode( ( string ) $category[ 'slug' ] ) ];
            }
            foreach ( Database::fetchAll( "SELECT slug, name FROM brands WHERE status='active' AND name LIKE ? ORDER BY name LIMIT 3", [ $like ] ) as $brand ) {
                $results[] = [ 'type' => 'brand', 'slug' => ( string ) $brand[ 'slug' ], 'name' => ( string ) $brand[ 'name' ], 'url' => '/brand/' . rawurlencode( ( string ) $brand[ 'slug' ] ) ];
            }
        }

        header( 'Content-Type: application/json; charset=utf-8' );
        header( 'X-Content-Type-Options: nosniff' );
        header( 'Cache-Control: no-store' );
        echo json_encode( [ 'results' => $results ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE );
        exit;
    }
}
