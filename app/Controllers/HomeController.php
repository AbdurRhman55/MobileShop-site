<?php

declare( strict_types = 1 );

namespace App\Controllers;

use App\Models\Product;
use App\Models\Content;
use App\Models\Homepage;
use App\Models\FlashSale;
use App\Models\SiteContent;
use App\Seo\StructuredData;

final class HomeController extends BaseController {
    public function index(): void {
        // --- catalog -------------------------------------------------------
        $featured   = Product::featured( 8 );
        $latest     = Product::latest( 8 );
        $blog       = Content::blogIndex( 1, 3 );
        $repairs    = Content::publishedRepairPages();

        // --- dynamic promo sections ---------------------------------------
        $slides      = Homepage::heroSlides();
        $trust       = Homepage::trustIndicators();
        $categories  = Homepage::categoryTiles( 12 );
        $audioProducts = Homepage::productsForCategory( 'audio', 8 );
        $brands      = Homepage::brands( 12 );
        $banners     = Homepage::banners( 'home', 6 );
        $blogPosts   = Homepage::blogPosts( 3 );
        $sectionOrder = Homepage::activeSectionOrder();
        $sectionCfg  = Homepage::sectionConfig();

        // Flash sale ( live campaign only, otherwise null → section skipped ).
        $sale       = FlashSale::current();
        $saleItems  = $sale ? FlashSale::items( $sale, 8 ) : [];
        $saleEndsAt = FlashSale::endsAtUnix( $sale );

        // Engagement sections.
        $bestSellers = SiteContent::bestSellers( 8 );
        $reviews     = SiteContent::verifiedReviews( 6 );

        // Homepage SEO: stored overrides win, else admin-configured homepage meta.
        $seo = $this->resolveMeta(
            'home',
            0,
            ( string ) \App\Seo\SeoSettings::get( 'homepage_seo_title', 'Lala Mobiles — Mobile Phones in Pakistan' ),
            ( string ) \App\Seo\SeoSettings::get( 'homepage_seo_description', '' ),
            '/',
            [ 'og_type' => 'website' ],
        );

        $schema = [
            StructuredData::organization(),
            StructuredData::website(),
            StructuredData::localBusiness(),
            $this->breadcrumbs( [ [ 'Home', '/' ] ] ),
        ];

        $this->renderPage( $seo, $schema, 'home', [
            'featured'     => $featured,
            'latest'       => $latest,
            'blog'         => $blog[ 'items' ],
            'repairs'      => $repairs,
            'slides'       => $slides,
            'trust'        => $trust,
            'categories'   => $categories,
            'audioProducts'=> $audioProducts,
            'brands'       => $brands,
            'banners'      => $banners,
            'blogPosts'    => $blogPosts,
            'sectionOrder' => $sectionOrder,
            'sectionCfg'   => $sectionCfg,
            'sale'         => $sale,
            'saleItems'    => $saleItems,
            'saleEndsAt'   => $saleEndsAt,
            'bestSellers'  => $bestSellers,
            'reviews'      => $reviews,
        ] );
    }
}
