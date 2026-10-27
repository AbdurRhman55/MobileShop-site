<?php
/**
* Shop-by-category — professional gradient icon tiles with hover animations.
*
* @var array $categories  categories with product_count
*/
if ( $categories === [] ) {
    return;
}

// Category icon SVG paths for common mobile shop categories
$catIcons = [
    'smartphones'   => 'M8 2h8a2 2 0 0 1 2 2v16a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2zm4 17h.01',
    'accessories'   => 'M4 9V5h16v4M9 21V9m6 12V9M4 15h16',
    'tablets'       => 'M5 3h14a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2zm7 14h.01',
    'chargers'      => 'M5 12h14M12 5l7 7-7 7',
    'headphones'    => 'M3 18v-6a9 9 0 0 1 18 0v6M3 18a2 2 0 0 0 2 2h1a2 2 0 0 0 2-2v-3a2 2 0 0 0-2-2H3m18 3h-4a2 2 0 0 0-2 2v3a2 2 0 0 0 2 2h1a2 2 0 0 0 2-2',
    'cases'         => 'M8 2h8a2 2 0 0 1 2 2v16a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2z',
    'cables'        => 'M4 9h4m-4 6h4m12-6h-4m4 6h-4M12 3v18M8 12h8',
    'earbuds'       => 'M9 19c0 1.1-.9 2-2 2s-2-.9-2-2 .9-2 2-2 2 .9 2 2zm10 0c0 1.1-.9 2-2 2s-2-.9-2-2 .9-2 2-2 2 .9 2 2zM7 17V8a5 5 0 0 1 10 0v9',
    'speakers'      => 'M11 5H6a2 2 0 0 0-2 2v8a2 2 0 0 0 2 2h5m0-12l4 4-4 4m0-8v12',
    'powerbanks'    => 'M7 7H3v10h4V7zm14 3h-8v4h8v-4zM7 12h4',
    'smartwatches'  => 'M9 2h6l1 5H8L9 2zm-1 5v10h8V7H8zm1 14h6l1-4H8l1 4z',
    'used'          => 'M3 12a9 9 0 0 1 9-9 9.75 9.75 0 0 1 6.74 2.74L21 8m0-5v5h-5M21 12a9 9 0 0 1-9 9 9.75 9.75 0 0 1-6.74-2.74L3 16m0 5v-5h5',
    'refurbished'   => 'M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 14.5v-9l6 4.5-6 4.5z',
];
$defaultIcon = 'M8 2h8a2 2 0 0 1 2 2v16a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2zm4 17h.01';

$viewAllUrl = '/category/audio';
?>
<section class = 'shell mt-14 reveal' data-reveal aria-labelledby = 'categories-heading'>
<?= \App\Core\View::partial( 'partials/section-head', [
    'key' => 'categories', 'fallback' => 'Shop by Category',
    'sectionCfg' => $sectionCfg, 'viewAllUrl' => $viewAllUrl,
] ) ?>

<ul class = 'grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-6 gap-4 md:gap-5 stagger' data-stagger>
<?php foreach ( $categories as $c ):
$img    = ( string )( $c[ 'image' ] ?? '' );
$imgUrl = $img !== '' ? image_url( $img ) : '';
$slug   = strtolower( str_replace( [ ' ', '-' ], [ '', '' ], ( string )( $c[ 'slug' ] ?? '' ) ) );
$iconPath = $catIcons[ $slug ] ?? $catIcons[ $c[ 'slug' ] ?? '' ] ?? $defaultIcon;
?>
<li>
<a href = "/category/<?= e($c['slug']) ?>"

class = 'group relative flex h-full flex-col items-center overflow-hidden rounded-xl bg-white border border-line card-hover img-zoom text-center p-5 pt-6'>

<!-- Image or gradient icon -->
<span class = 'relative mb-4 flex h-[70px] w-[70px] shrink-0 items-center justify-center rounded-2xl bg-gradient-to-br from-surface-sunken to-white text-electric-700 transition-transform duration-300 group-hover:scale-110 group-hover:shadow-md'>
<?php if ( $imgUrl !== '' ): ?>
<img src = "<?= e($imgUrl) ?>" alt = "<?= e($c['name']) ?>" width = '56' height = '56'
loading = 'lazy' decoding = 'async'

class = 'h-14 w-14 rounded-xl object-cover transition-transform duration-500 group-hover:scale-110'>
<?php else: ?>
<svg viewBox = '0 0 24 24' fill = 'none' stroke = 'currentColor' stroke-width = '1.7'
stroke-linecap = 'round' stroke-linejoin = 'round' class = 'h-8 w-8'>
<path d = "<?= $iconPath ?>"/>
</svg>
<?php endif;
?>
</span>

<span class = 'text-[14px] font-bold text-ink leading-tight group-hover:text-electric-700 transition-colors duration-200'>
<?= e( $c[ 'name' ] ) ?>
</span>
<span class = 'mt-1.5 text-[12px] font-semibold text-ink-muted'>
<?= ( int )$c[ 'product_count' ] ?> item<?= ( int )$c[ 'product_count' ] === 1 ? '' : 's' ?>
</span>

<!-- Arrow indicator -->
<span class = 'mt-3 flex h-6 w-6 items-center justify-center rounded-full bg-surface-sunken text-ink-muted opacity-0 group-hover:opacity-100 transition-all duration-200 group-hover:bg-electric-600 group-hover:text-white'>
<svg viewBox = '0 0 24 24' fill = 'none' stroke = 'currentColor' stroke-width = '2.5' class = 'h-3.5 w-3.5'><path d = 'M5 12h14m-6-6 6 6-6 6'/></svg>
</span>
</a>
</li>
<?php endforeach;
?>
</ul>
</section>
