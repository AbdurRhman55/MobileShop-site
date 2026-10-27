<?php

declare( strict_types = 1 );

namespace App\Controllers;

use App\Models\Catalog;
use App\Models\Product;
use App\Seo\Facets;

/**
 * Deals landing page.
 *
 * Always restricted to products with a genuine sale price, but every facet
 * (brand, price, condition, rating, in-stock, sort) now narrows that set —
 * previously the sidebar submitted params that this controller ignored.
 */
final class DealsController extends BaseController
{
    public function index(): void
    {
        $userQuery = $this->request->query;
        $page      = max( 1, ( int ) ( $userQuery[ 'page' ] ?? 1 ) );

        $query             = $userQuery;
        $query[ 'discount' ] = '1'; // /deals is "on sale" by definition.
        if ( empty( $query[ 'sort' ] ) ) {
            $query[ 'sort' ] = 'price_asc';
        }

        $listing = Product::listing( $query, null, null, $page, 24 );

        // The forced discount is not a user filter, so it must not make the
        // page look "filtered" to search engines.
        $userFilters = array_intersect_key( $userQuery, array_flip( Facets::FILTER_PARAMS ) );
        unset( $userFilters[ 'discount' ] );
        $isFiltered = $userFilters !== [];

        $seo = $this->resolveMeta(
            'home',
            0,
            'Current offers — Lala Mobiles',
            'Browse products with a genuine reduced price at Lala Mobiles.',
            '/deals',
            [ 'robots' => $isFiltered ? 'noindex,follow' : 'index,follow' ]
        );

        $this->renderPage( $seo, [], 'listing', [
            'listing'         => $listing,
            'heading'         => 'Current offers',
            'intro'           => 'Every offer shown here has a live sale price below its regular price.',
            'breadcrumbs'     => [ [ 'Home', '/' ], [ 'Deals', '/deals' ] ],
            'basePath'        => '/deals',
            'children'        => [],
            'category'        => null,
            'brands'          => Catalog::brandsOnSale(),
            'seoRules'        => [
                'is_filtered'    => $isFiltered,
                'page'           => $listing[ 'page' ],
                'canonical_path' => '/deals',
            ],
            'filterPageMatch' => null,
            'activeFilters'   => $userFilters,
            'sort'            => ( string ) $query[ 'sort' ],
        ] );
    }
}
