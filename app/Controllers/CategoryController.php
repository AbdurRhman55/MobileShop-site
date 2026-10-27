<?php

declare( strict_types = 1 );

namespace App\Controllers;

use App\Core\Http;
use App\Models\Catalog;
use App\Models\Product;
use App\Seo\Facets;
use App\Seo\StructuredData;
use App\Seo\Url;

/**
* Category listing page with SEO-aware faceted navigation.
*
* Indexing policy ( see Seo\Facets ):
*  - base + first few pages: index, self-canonical
*  - ad-hoc filtered/sorted URLs: noindex, canonical → base category
*  - matching published filter page: index, canonical → /shop/ {
    slug}
    */
    final class CategoryController extends BaseController {
        public function show( string $slug ): void {
            $category = Catalog::categoryBySlug( $slug );
            if ( $category === null ) {
                if ( \App\Models\Redirect::resolve( '/category/' . $slug ) !== null ) {
                    \App\Seo\RedirectManager::redirectIfFound( '/category/' . $slug );
                }
                throw new Http( 404, 'This category does not exist.' );
            }

            $query = $this->request->query;
            $matchedFilterPage = Facets::matchFilterPage( ( int ) $category[ 'id' ], $query );
            $categoryIds = Catalog::descendantCategoryIds( ( int ) $category[ 'id' ] );

            $listing = Product::listing(
                $query,
                $categoryIds,
                null,
                max( 1, ( int ) ( $query[ 'page' ] ?? 1 ) )
            );

            $basePath = '/category/' . $category[ 'slug' ];
            $seoRules = Facets::listingSeo( $basePath, $query, $matchedFilterPage );

            /* -------------------------------------------------- SEO -- */
            $page = $seoRules[ 'page' ];
            $titleSuffix = $page > 1 ? " — Page $page" : '';
            $autoTitle = sprintf( '%s in Pakistan — Best Prices%s', $category[ 'name' ], $titleSuffix );
            $autoDesc  = $category[ 'intro' ]
            ? excerpt( strip_tags( $category[ 'intro' ] ), 155 )
            : 'Shop ' . $category[ 'name' ] . ' at Lala Mobiles. Best prices in Pakistan with warranty and nationwide delivery.';

            $seo = $this->resolveMeta(
                'category',
                ( int ) $category[ 'id' ],
                $autoTitle,
                $autoDesc,
                $seoRules[ 'canonical_path' ],
                [
                    'robots' => $seoRules[ 'robots' ],
                    'og_type' => 'website',
                ],
            );

            /* --------------------------------------- structured data -- */
            $trail = Catalog::categoryTrail( $category );
            $crumbs = [ [ 'Home', '/' ] ];
            foreach ( $trail as $node ) {
                if ( ( int ) $node[ 'id' ] === ( int ) $category[ 'id' ] ) {
                    $crumbs[] = [ $node[ 'name' ], $basePath ];
                } else {
                    $crumbs[] = [ $node[ 'name' ], '/category/' . $node[ 'slug' ] ];
                }
            }

            $schema = [
                $this->breadcrumbs( $crumbs ),
            ];
            // ItemList only on the clean first page — not on filtered variants.
            if ( $page === 1 && !$seoRules[ 'is_filtered' ] ) {
                $schema[] = StructuredData::itemList(
                    $category[ 'name' ],
                    Url::absolute( $basePath ),
                    $listing[ 'items' ],
                    $listing[ 'total' ]
                );
            }

            $children = Catalog::childCategories( ( int ) $category[ 'id' ] );

            $this->renderPage( $seo, $schema, 'listing', [
                'listing'        => $listing,
                'heading'        => $category[ 'name' ],
                'category'       => $category,
                'brands'         => Catalog::brandsForCategoryIds( $categoryIds ),
                'intro'          => $category[ 'intro' ],
                'breadcrumbs'    => $crumbs,
                'basePath'       => $basePath,
                'children'       => $children,
                'seoRules'       => $seoRules,
                'filterPageMatch'=> $matchedFilterPage,
                'activeFilters'  => array_intersect_key( $query, array_flip( Facets::FILTER_PARAMS ) ),
                'sort'           => ( string ) ( $query[ 'sort' ] ?? '' ),
            ] );
        }
    }
