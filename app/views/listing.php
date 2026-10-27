<?php
/**
* Listing page ( category / brand / filter landing page ).
*
* @var array $listing @var string $heading @var ?string $intro
* @var array $breadcrumbs @var string $basePath @var array $children
* @var array $seoRules @var ?array $filterPageMatch @var array $activeFilters @var string $sort
*
* SEO notes ( unchanged behaviour ):
*  - Filtered combinations are noindexed server-side;
this block only informs
*    the operator/visitor and links to the curated indexable page if one exists.
*  - Pagination links preserve active filters and sort via $buildUrl.
*/
use App\Seo\Facets;

$searchTerm = (string) ( $searchTerm ?? '' );
// Extra params that must survive filtering and pagination (internal search).
$keepExtra = $searchTerm !== '' ? [ 'q' => $searchTerm ] : [];

$buildUrl = function ( array $overrides ) use ( $basePath, $activeFilters, $sort, $keepExtra ) {
    $params = array_merge( $keepExtra, $activeFilters, $overrides );
    if ( $sort !== '' && !isset( $overrides[ 'sort' ] ) ) {
        $params[ 'sort' ] = $sort;
    }
    $params = array_filter( $params, static fn( $v ) => $v !== '' && $v !== null && $v !== [] );
    return $basePath . ( $params !== [] ? '?' . http_build_query( $params ) : '' );
}
;

$total = ( int ) $listing[ 'total' ];
$category = $category ?? null;
$categoryBanner = is_array( $category ) ? ( $category[ 'banner_image' ] ?? $category[ 'image' ] ?? null ) : null;

// "Clear all" must keep an internal search term but drop every facet.
$clearAllUrl = $searchTerm !== ''
    ? $buildUrl( [ 'brand' => '', 'condition' => '', 'rating' => '', 'in_stock' => '', 'discount' => '', 'min_price' => '', 'max_price' => '', 'sort' => '', 'page' => null ] )
    : $basePath;

// Which filters are actually narrowing the set right now ( for the summary row ).
$activeChips = [];
foreach ( [ 'condition', 'min_price', 'max_price' ] as $fk ) {
    $fv = ( string ) ( $activeFilters[ $fk ] ?? '' );
    if ( $fv !== '' ) {
        $activeChips[ $fk ] = $fv;
    }
}
$selectedBrandSlugs = array_values( array_filter( ( array ) ( $activeFilters[ 'brand' ] ?? [] ), static fn( $value ) => is_string( $value ) && $value !== '' ) );
?>
<nav aria-label = 'Breadcrumb' class = 'shell pt-5 text-sm text-ink-muted'>
<ol class = 'flex flex-wrap items-center gap-1.5'>
<?php foreach ( $breadcrumbs as $i => $crumb ): ?>
<li class = 'flex items-center gap-1.5'>
<?php if ( $i > 0 ): ?><span aria-hidden = 'true' class = 'text-line'>/</span><?php endif;
?>
<?php if ( $i === count( $breadcrumbs ) - 1 ): ?>
<span aria-current = 'page' class = 'text-ink font-medium'><?= e( $crumb[ 0 ] ) ?></span>
<?php else: ?>
<a href = "<?= e($crumb[1]) ?>" class = 'hover:text-electric-600'><?= e( $crumb[ 0 ] ) ?></a>
<?php endif;
?>
</li>
<?php endforeach;
?>
</ol>
</nav>

<div class = 'shell pb-16'>
<header class = 'relative mt-6 min-h-44 overflow-hidden rounded-2xl border border-line bg-slate-900 md:min-h-56'>
<?php if ( !empty( $categoryBanner ) ): ?>
<img src = "<?= e(image_url((string) $categoryBanner)) ?>" alt = '' width = '1600' height = '560' fetchpriority = 'high' class = 'absolute inset-0 h-full w-full object-cover'>
<?php endif;
?>
<div class = 'absolute inset-0 bg-gradient-to-r from-slate-950/90 via-slate-900/70 to-slate-900/15'></div>
<div class = 'relative z-10 flex min-h-44 max-w-3xl flex-col justify-center px-6 py-8 text-white md:min-h-56 md:px-10'>
<h1 class = 'text-[28px] font-extrabold tracking-tight text-white md:text-4xl'><?= e( $heading ) ?></h1>
<?php if ( $intro ): ?>
<div class = 'mt-3 max-w-3xl text-[15px] leading-relaxed text-white/80'><?= $intro /* sanitized on save */ ?></div>
<?php endif;
?>
</div>
<?php if ( $seoRules[ 'is_filtered' ] ): ?>
<p class = 'mt-4 inline-flex flex-wrap items-center gap-2 rounded-xl border border-gold-200 bg-gold-50 px-3.5 py-2.5 text-[13px] font-semibold text-navy-900'>
<svg viewBox = '0 0 24 24' fill = 'none' stroke = 'currentColor' stroke-width = '2' class = 'h-4 w-4 shrink-0'><path d = 'M3 5h18M6 12h12M10 19h4'/></svg>
Filtered view — hidden from search engines.
<?php if ( $filterPageMatch !== null ): ?>
<a class = 'underline decoration-electric-400 underline-offset-2 hover:text-electric-700' href = "<?= e(Facets::filterPageUrl($filterPageMatch)) ?>">View the indexable page</a>.
<?php endif;
?>
</p>
<?php endif;
?>
</header>

<?php if ( $children !== [] ): ?>
<nav aria-label = 'Subcategories' class = 'mt-6 flex flex-wrap gap-2'>
<?php foreach ( $children as $child ): ?>
<a href = "/category/<?= e($child['slug']) ?>"

class = 'btn btn-ghost btn-sm !rounded-full'><?= e( $child[ 'name' ] ) ?></a>
<?php endforeach;
?>
</nav>
<?php endif;
?>

<div class = 'mt-8 grid gap-7 lg:grid-cols-12'>
<!-- ===  ===  ===  ===  ===  ===  ===  ===  ===  = Facets sidebar ===  ===  ===  ===  ===  ===  ===  ===  ===  = -->
<aside class = 'lg:col-span-3'>
<!-- Collapsible on small screens, sticky on desktop. -->
<details class = 'group overflow-hidden rounded-2xl border border-line bg-white lg:open:static' data-facet-panel
<?= $activeChips !== [] || $sort !== '' ? 'open' : '' ?>>
<summary class = 'flex cursor-pointer list-none items-center justify-between gap-3 px-5 py-4 text-[15px] font-bold text-ink marker:hidden'>
<span class = 'inline-flex items-center gap-2'>
<svg viewBox = '0 0 24 24' fill = 'none' stroke = 'currentColor' stroke-width = '2' class = 'h-4 w-4 text-electric-600'><path d = 'M3 6h18M7 12h10M10 18h4'/></svg>
Filter &amp;
sort
</span>
<svg viewBox = '0 0 24 24' fill = 'none' stroke = 'currentColor' stroke-width = '2.2'

class = 'h-4 w-4 text-ink-muted transition-transform group-open:rotate-180'><path d = 'm6 9 6 6 6-6'/></svg>
</summary>

<form method = 'get' action = "<?= e($basePath) ?>" class = 'space-y-5 border-t border-line px-5 pb-5 pt-4' data-filter-form>
<?php if ( $searchTerm !== '' ): ?>
<input type = 'hidden' name = 'q' value = "<?= e( $searchTerm ) ?>">
<?php endif; ?>
<?php if ( ( $brands ?? [] ) !== [] ): ?>
<fieldset>
<legend class = 'mb-2 text-[13px] font-bold text-ink'>Brand</legend>
<div class = 'max-h-44 space-y-2 overflow-y-auto'>
<?php foreach ( ( $brands ?? [] ) as $brand ): $brandSlug = ( string ) $brand[ 'slug' ];
$selectedBrands = ( array ) ( $activeFilters[ 'brand' ] ?? [] );
$brandCount = ( int ) ( $brand[ 'product_count' ] ?? 0 );
?>
<label class = 'flex items-center gap-2 text-sm text-ink-muted'>
<input type = 'checkbox' name = 'brand[]' value = "<?= e($brandSlug) ?>" <?= in_array( $brandSlug, $selectedBrands, true ) ? 'checked' : '' ?> class = 'h-4 w-4 rounded border-line accent-electric-600'>
<span class = 'min-w-0 flex-1 truncate'><?= e( $brand[ 'name' ] ) ?></span>
<?php if ( $brandCount > 0 ): ?><span class = 'shrink-0 rounded-full bg-surface-muted px-1.5 py-0.5 text-[10.5px] font-bold text-ink-muted'><?= $brandCount ?></span><?php endif; ?>
</label>
<?php endforeach;
?>
</div>
</fieldset>
<?php endif;
?>

<div>
<label for = 'f-condition' class = 'mb-1.5 block text-[13px] font-bold text-ink'>Condition</label>
<select id = 'f-condition' name = 'condition' class = 'input' data-filter-auto>
<option value = ''>All conditions</option>
<?php foreach ( [ 'new' => 'New', 'used' => 'Used', 'refurbished' => 'Refurbished' ] as $val => $label ): ?>
<option value = "<?= $val ?>" <?= ( $activeFilters[ 'condition' ] ?? '' ) === $val ? 'selected' : '' ?>><?= $label ?></option>
<?php endforeach;
?>
</select>
</div>

<fieldset>
<legend class = 'mb-2 text-[13px] font-bold text-ink'>Availability and rating</legend>
<label class = 'flex items-center gap-2 text-sm text-ink-muted'>
<input type = 'checkbox' name = 'in_stock' value = '1' <?= !empty( $activeFilters[ 'in_stock' ] ) ? 'checked' : '' ?> class = 'h-4 w-4 rounded border-line accent-electric-600'> In stock
</label>
<label for = 'f-rating' class = 'mb-1.5 mt-3 block text-[13px] font-semibold text-ink-muted'>Minimum rating</label>
<select id = 'f-rating' name = 'rating' class = 'input' data-filter-auto>
<option value = ''>Any rating</option>
<?php foreach ( [ 4, 3, 2 ] as $rating ): ?><option value = "<?= $rating ?>" <?= ( string ) ( $activeFilters[ 'rating' ] ?? '' ) === ( string ) $rating ? 'selected' : '' ?>><?= $rating ?> stars and up</option><?php endforeach;
?>
</select>
<label class = 'mt-3 flex items-center gap-2 text-sm text-ink-muted'>
<input type = 'checkbox' name = 'discount' value = '1' <?= !empty( $activeFilters[ 'discount' ] ) ? 'checked' : '' ?> class = 'h-4 w-4 rounded border-line accent-electric-600'> On sale
</label>
</fieldset>

<fieldset>
<legend class = 'mb-1.5 text-[13px] font-bold text-ink'>Price range ( Rs )</legend>
<div class = 'flex items-center gap-2'>
<label for = 'f-min' class = 'sr-only'>Minimum price</label>
<input type = 'number' id = 'f-min' name = 'min_price' min = '0' step = '1000' inputmode = 'numeric'
placeholder = 'Min' value = "<?= e((string) ($activeFilters['min_price'] ?? '')) ?>" class = 'input !px-3'>
<span aria-hidden = 'true' class = 'text-ink-muted'>–</span>
<label for = 'f-max' class = 'sr-only'>Maximum price</label>
<input type = 'number' id = 'f-max' name = 'max_price' min = '0' step = '1000' inputmode = 'numeric'
placeholder = 'Max' value = "<?= e((string) ($activeFilters['max_price'] ?? '')) ?>" class = 'input !px-3'>
</div>
</fieldset>

<div>
<label for = 'f-sort' class = 'mb-1.5 block text-[13px] font-bold text-ink'>Sort by</label>
<select id = 'f-sort' name = 'sort' class = 'input' data-filter-auto>
<option value = ''>Featured</option>
<?php foreach ( [ 'price_asc' => 'Price: low to high', 'price_desc' => 'Price: high to low', 'newest' => 'Newest first', 'rating' => 'Top rated' ] as $val => $label ): ?>
<option value = "<?= $val ?>" <?= $sort === $val ? 'selected' : '' ?>><?= $label ?></option>
<?php endforeach;
?>
</select>
</div>

<div class = 'flex gap-2 pt-1'>
<button type = 'submit' class = 'btn btn-tech btn-sm flex-1'>Apply</button>
<a href = "<?= e($basePath) ?>" class = 'btn btn-ghost btn-sm'>Reset</a>
</div>
</form>
</details>
</aside>

<!-- ===  ===  ===  ===  ===  ===  ===  ===  ===  === Results ===  ===  ===  ===  ===  ===  ===  ===  ===  === -->
<div class = 'lg:col-span-9'>
<!-- Summary row -->
<div class = 'mb-5 flex flex-wrap items-center justify-between gap-3 rounded-2xl border border-line bg-white px-4 py-3'>
<p class = 'text-sm font-semibold text-ink'>
<?= number_format( $total ) ?> product<?= $total === 1 ? '' : 's' ?>
<?php if ( $seoRules[ 'page' ] > 1 ): ?> <span class = 'text-ink-muted font-medium'>· Page <?= ( int ) $seoRules[ 'page' ] ?> of <?= ( int ) $listing[ 'pages' ] ?></span><?php endif;
?>
</p>
<?php if ( $activeChips !== [] || $selectedBrandSlugs !== [] || !empty( $activeFilters[ 'rating' ] ) || !empty( $activeFilters[ 'in_stock' ] ) || !empty( $activeFilters[ 'discount' ] ) ): ?>
<div class = 'flex flex-wrap items-center gap-2 text-[12px]'>
<?php
$labels = [ 'condition' => 'Condition', 'min_price' => 'Min', 'max_price' => 'Max' ];
foreach ( $activeChips as $ck => $cv ):
$clean = $buildUrl( [ $ck => '' ] );
?>
<a href = "<?= e($clean) ?>"

class = 'inline-flex items-center gap-1.5 rounded-full bg-electric-50 px-2.5 py-1 font-bold text-electric-700 ring-1 ring-electric-100 hover:bg-electric-100'>
<?= e( $labels[ $ck ] ) ?>: <?= e( $cv === 'new' || $cv === 'used' || $cv === 'refurbished' ? ucfirst( $cv ) : price( ( float ) $cv ) ) ?>
<span aria-hidden = 'true'>×</span><span class = 'sr-only'>Remove this filter</span>
</a>
<?php endforeach;
?>
<?php foreach ( $selectedBrandSlugs as $brandSlug ): ?>
<a href = "<?= e($buildUrl(['brand' => array_values(array_diff($selectedBrandSlugs, [$brandSlug])) ?: ''])) ?>" class = 'rounded-full bg-electric-50 px-2.5 py-1 font-bold text-electric-700'>Brand: <?= e( $brandSlug ) ?> ×</a>
<?php endforeach;
?>
<?php if ( !empty( $activeFilters[ 'rating' ] ) ): ?><a href = "<?= e($buildUrl(['rating' => ''])) ?>" class = 'rounded-full bg-electric-50 px-2.5 py-1 font-bold text-electric-700'>Rating: <?= e( ( string ) $activeFilters[ 'rating' ] ) ?>+ ×</a><?php endif;
?>
<?php if ( !empty( $activeFilters[ 'in_stock' ] ) ): ?><a href = "<?= e($buildUrl(['in_stock' => ''])) ?>" class = 'rounded-full bg-electric-50 px-2.5 py-1 font-bold text-electric-700'>In stock ×</a><?php endif;
?>
<?php if ( !empty( $activeFilters[ 'discount' ] ) ): ?><a href = "<?= e($buildUrl(['discount' => ''])) ?>" class = 'rounded-full bg-electric-50 px-2.5 py-1 font-bold text-electric-700'>On sale ×</a><?php endif;
?>
<a href = "<?= e($clearAllUrl) ?>" class = 'font-bold text-ink-muted underline underline-offset-2'>Clear all</a>
</div>
<?php endif;
?>
</div>

<?php if ( $listing[ 'items' ] === [] ): ?>
<!-- Empty state -->
<div class = 'rounded-2xl border border-dashed border-line bg-white p-12 text-center'>
<span class = 'mx-auto grid h-14 w-14 place-items-center rounded-2xl bg-surface-muted text-ink-muted'>
<svg viewBox = '0 0 24 24' fill = 'none' stroke = 'currentColor' stroke-width = '1.8' class = 'h-7 w-7'><circle cx = '11' cy = '11' r = '7'/><path d = 'm20 20-3.5-3.5'/></svg>
</span>
<h2 class = 'mt-4 text-lg font-extrabold text-ink'><?= $searchTerm !== '' && $activeFilters === [] ? 'No products found for “' . e( $searchTerm ) . '”' : 'No products match these filters' ?></h2>
<p class = 'mx-auto mt-2 max-w-md text-sm text-ink-muted'><?= $searchTerm !== '' && $activeFilters === [] ? 'Try a different spelling, a broader term, or browse the categories.' : 'Try widening the price range or clearing the condition filter.' ?></p>
<a href = "<?= e($clearAllUrl) ?>" class = 'btn btn-tech btn-md mt-5'>Clear all filters</a>
</div>
<?php else: ?>
<ul class = 'product-grid product-grid--catalog'>
<?php foreach ( $listing[ 'items' ] as $p ): ?>
<li class = 'h-full flex flex-col'>
<?= \App\Core\View::partial( 'partials/product-card', [ 'p' => $p, 'ctx' => 'listing', 'lazy' => true ] ) ?>
</li>
<?php endforeach;
?>
</ul>
<?php endif;
?>

<!-- Pagination -->
<?php if ( $listing[ 'pages' ] > 1 ): ?>
<nav aria-label = 'Pagination' class = 'mt-10 flex flex-wrap items-center justify-center gap-2'>
<?php
$current = ( int ) $listing[ 'page' ];
$pages   = ( int ) $listing[ 'pages' ];
// Window of page links around the current page.
$start = max( 1, $current - 2 );
$end   = min( $pages, $current + 2 );
$href  = fn( int $i ): string => $i === 1 && $activeFilters === [] && $keepExtra === []
? $basePath
: $buildUrl( [ 'page' => $i === 1 ? null : $i ] );
?>
<?php if ( $current > 1 ): ?>
<a href = "<?= e($href($current - 1)) ?>" class = 'btn btn-ghost btn-sm' rel = 'prev'>← Prev</a>
<?php endif;
?>

<?php if ( $start > 1 ): ?>
<a href = "<?= e($href(1)) ?>" class = 'pagination-link'>1</a>
<?php if ( $start > 2 ): ?><span class = 'px-1 text-ink-muted' aria-hidden = 'true'>…</span><?php endif;
?>
<?php endif;
?>

<?php for ( $i = $start; $i <= $end; $i++ ): ?>
<?php $isCurrent = $i === $current;
?>
<a href = "<?= e($href($i)) ?>"
<?= $isCurrent ? 'aria-current="page"' : '' ?>

class = "pagination-link <?= $isCurrent ? 'pagination-link-active' : '' ?>">
<?= $i ?>
</a>
<?php endfor;
?>

<?php if ( $end < $pages ): ?>
<?php if ( $end < $pages - 1 ): ?><span class = 'px-1 text-ink-muted' aria-hidden = 'true'>…</span><?php endif;
?>
<a href = "<?= e($href($pages)) ?>" class = 'pagination-link'><?= $pages ?></a>
<?php endif;
?>

<?php if ( $current < $pages ): ?>
<a href = "<?= e($href($current + 1)) ?>" class = 'btn btn-ghost btn-sm' rel = 'next'>Next →</a>
<?php endif;
?>
</nav>
<?php endif;
?>
</div>
</div>
</div>
