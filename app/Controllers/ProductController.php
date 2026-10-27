<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Http;
use App\Core\HttpRedirect;
use App\Core\View;
use App\Models\Product;
use App\Models\Catalog;
use App\Seo\StructuredData;
use App\Seo\SeoSettings;
use App\Seo\Url;

/**
 * Product detail page.
 *
 * Lifecycle handling:
 *  - active        → 200, full Product schema with Offer
 *  - discontinued  → 200, Offer availability = OutOfStock, page stays indexed
 *  - deleted       → 301 to replacement product IF one is set, else 410 Gone
 *  - unknown slug  → 301 via redirect table, else genuine 404
 *
 * Variant URLs (/product/slug?variant=N) canonicalise to the base product URL
 * — the variant selector stays functional without creating duplicate pages.
 */
final class ProductController extends BaseController
{
    public function show(string $slug): void
    {
        $product = Product::findBySlug($slug);

        if ($product === null) {
            // Slug may have changed: consult the redirect map before 404/410.
            if (\App\Models\Redirect::resolve('/product/' . $slug) !== null) {
                \App\Seo\RedirectManager::redirectIfFound('/product/' . $slug);
            }
            throw new Http(404, 'This product does not exist or has been removed.');
        }

        /* ------------------------------------------------- lifecycle -- */
        if ($product['status'] === 'deleted') {
            // 301 ONLY when a genuine replacement is configured.
            if (!empty($product['redirect_to_id'])) {
                $replacement = Product::find((int) $product['redirect_to_id']);
                if ($replacement !== null && $replacement['status'] === 'active') {
                    HttpRedirect::to('/product/' . $replacement['slug'], 301);
                }
            }
            // No replacement: honest 410 Gone (this product was removed on purpose).
            http_response_code(410);
            echo View::render('errors/error', [
                'status'  => 410,
                'title'   => 'Product Removed',
                'message' => 'This product has been permanently removed from our catalogue.',
            ]);
            return;
        }

        /* ----------------------------------------------- variant -- */
        $variantId = (int) ($this->request->query('variant') ?? 0);
        $variants  = Product::variants((int) $product['id']);
        $selectedVariant = null;
        if ($variantId > 0) {
            foreach ($variants as $v) {
                if ((int) $v['id'] === $variantId) {
                    $selectedVariant = $v;
                    break;
                }
            }
        }

        $images = Product::images((int) $product['id']);
        $specs  = Product::specs((int) $product['id']);
        $reviews = Product::approvedReviews((int) $product['id']);
        $related = Product::related($product, 4);

        // Record for the per-visitor "recently viewed" strip, and load the
        // other recent products (never includes the one being viewed).
        \App\Models\RecentlyViewed::record((int) $product['id']);
        $recent = \App\Models\RecentlyViewed::items((int) $product['id'], 6);

        // True if this product sits in a live flash campaign right now.
        $liveSale = \App\Models\FlashSale::current();
        $salePrice = null;
        if ($liveSale !== null) {
            foreach (\App\Models\FlashSale::items($liveSale, 40) as $sp) {
                if ((int) $sp['id'] === (int) $product['id']) {
                    $salePrice = (float) $sp['sale_price_eff'];
                    break;
                }
            }
        }

        $brand     = $product['brand_slug'] !== null
            ? ['name' => $product['brand_name'], 'slug' => $product['brand_slug']]
            : null;
        $category  = $product['category_slug'] !== null
            ? ['name' => $product['category_name'], 'slug' => $product['category_slug']]
            : null;

        /* ------------------------------------------------- SEO -- */
        $canonicalPath = '/product/' . $product['slug'];
        // Variant URLs canonicalise to the base product (no duplicate content).
        $autoTitle = sprintf('%s Price in Pakistan', $product['name']);
        $autoDesc  = $product['short_desc'] ?: excerpt(strip_tags((string) $product['description']), 155);

        $seo = $this->resolveMeta(
            'product',
            (int) $product['id'],
            $autoTitle,
            $autoDesc,
            $canonicalPath,
            [
                'og_type'  => 'product',
                'og_image' => $product['main_image'] ?? null,
            ],
        );

        /* ------------------------------------------ structured data -- */
        // Product schema reflects the SELECTED variant's price when one is chosen.
        $schemaProduct = $product;
        if ($selectedVariant !== null) {
            $schemaProduct['price']     = (float) $product['price'] + (float) $selectedVariant['price_delta'];
            $schemaProduct['sale_price'] = null;
            $schemaProduct['sku']       = $selectedVariant['sku'] ?: $product['sku'];
            $schemaProduct['gtin']      = $selectedVariant['gtin'] ?: $product['gtin'];
            $schemaProduct['mpn']       = $selectedVariant['mpn'] ?: $product['mpn'];
            $schemaProduct['stock']     = (int) $selectedVariant['stock'];
        }

        $crumbs = [['Home', '/']];
        if ($category !== null) {
            $crumbs[] = [$category['name'], '/category/' . $category['slug']];
        }
        $crumbs[] = [$product['name'], $canonicalPath];

        $schema = [
            StructuredData::product(
                $schemaProduct,
                Url::absolute($canonicalPath),
                $brand,
                $category
            ),
            $this->breadcrumbs($crumbs),
        ];

        $this->renderPage($seo, $schema, 'product', [
            'product'        => $product,
            'images'         => $images,
            'specs'          => $specs,
            'variants'       => $variants,
            'selectedVariant'=> $selectedVariant,
            'reviews'        => $reviews,
            'related'        => $related,
            'recent'         => $recent,
            'breadcrumbs'    => $crumbs,
            'effectivePrice' => Product::effectivePrice($schemaProduct),
            'discontinued'   => $product['status'] === 'discontinued',
            'salePrice'      => $salePrice,
        ]);
    }
}
