<?php
/**
 * Homepage.
 *
 * Every promotional section is database-driven and admin-reorderable. The
 * render order + enable/disable comes from SiteContent::homepageSections(),
 * so an admin can turn sections on/off and reorder them without touching
 * this template. Sections with no data render nothing (no empty shells).
 *
 * @var array  $slides       hero_slides rows (already date-filtered)
 * @var array  $trust        trust_indicators rows
 * @var array  $categories   categories with product_count
 * @var array  $brands       brands with product_count
 * @var array  $banners      promo_banners rows (location='home')
 * @var ?array $sale         live flash_sales row
 * @var array  $saleItems    flash sale products (priced, discount computed)
 * @var ?int   $saleEndsAt   absolute unix end time for the countdown
 * @var array  $bestSellers  products with units_sold
 * @var array  $featured     featured products
 * @var array  $latest       recent products
 * @var array  $reviews      approved verified-purchase reviews
 * @var array  $blog         published blog_posts rows
 * @var array  $repairs      published repair pages
 * @var array  $sectionOrder ordered section keys (active only)
 * @var array  $sectionCfg   key => {title, subtitle, position, is_active}
 */
$sectionOrder = $sectionOrder ?? [];
$sectionCfg   = $sectionCfg ?? [];

/**
 * Render a named section partial. $files maps a section key to its partial
 * path; keys without a partial are ignored so admins can't fatal the page.
 */
$files = [
    'hero'          => 'partials/home/hero',
    'trust'         => 'partials/home/trust',
    'categories'    => 'partials/home/categories',
    'audio'         => 'partials/home/audio',
    'flash'         => 'partials/home/flash',
    'bestsellers'   => 'partials/home/bestsellers',
    'new-arrivals'  => 'partials/home/new-arrivals',
    'brands'        => 'partials/home/brands',
    'banners'       => 'partials/home/banners',
    'recommended'   => 'partials/home/recommended',
    'repairs'       => 'partials/home/repairs',
    'reviews'       => 'partials/home/reviews',
    'blog'          => 'partials/home/blog',
    'newsletter'    => 'partials/home/newsletter',
];

// Shared data every partial may reference.
$shared = [
    'slides'       => $slides ?? [],
    'trust'        => $trust ?? [],
    'categories'   => $categories ?? [],
    'audioProducts'=> $audioProducts ?? [],
    'brands'       => $brands ?? [],
    'banners'      => $banners ?? [],
    'sale'         => $sale ?? null,
    'saleItems'    => $saleItems ?? [],
    'saleEndsAt'   => $saleEndsAt ?? null,
    'bestSellers'  => $bestSellers ?? [],
    'featured'     => $featured ?? [],
    'latest'       => $latest ?? [],
    'reviews'      => $reviews ?? [],
    'blog'         => $blog ?? [],
    'repairs'      => $repairs ?? [],
    'sectionCfg'   => $sectionCfg,
];

// The hero and trust strip sit above the fold and are intentionally not
// gated behind section ordering — but they still respect is_active via
// $sectionOrder, which already excludes disabled keys.
$rendered = [];
foreach ($sectionOrder as $key) {
    if (!isset($files[$key])) {
        continue;
    }
    $html = \App\Core\View::partial($files[$key], $shared);
    if (trim($html) !== '') {
        $rendered[] = $html;
    }
}

// Fallback: if an admin disabled everything, still show a usable storefront.
if ($rendered === []) {
    foreach (['hero', 'categories', 'bestsellers', 'new-arrivals', 'newsletter'] as $key) {
        if (isset($files[$key])) {
            $html = \App\Core\View::partial($files[$key], $shared);
            if (trim($html) !== '') {
                $rendered[] = $html;
            }
        }
    }
}

echo implode("\n", $rendered);
