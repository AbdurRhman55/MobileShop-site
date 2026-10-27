<?php
/**
* Reusable premium product card — enhanced professional design.
*
* Used by the homepage, shop, category, brand, search, wishlist, flash-sale
* and recently-viewed sections. Everything shown is real data.
*
* @var array  $p        product row
* @var bool   $lazy     lazy-load the image ( default true )
* @var string $ctx      context key
* @var bool   $showWish render the wishlist toggle
*/
use App\Models\Wishlist;

$p        = $p ?? [];
$lazy     = $lazy ?? true;
$ctx      = $ctx ?? 'card';
$showWish = $showWish ?? true;

if ( $p === [] ) {
    return;
}

$name   = ( string ) ( $p[ 'name' ] ?? '' );
$slug   = ( string ) ( $p[ 'slug' ] ?? '' );
$brand  = ( string ) ( $p[ 'brand_name' ] ?? '' );
$href   = '/product/' . $slug;

// ---- pricing -------------------------------------------------------------
$normal  = isset( $p[ 'normal_price' ] )  ? ( float ) $p[ 'normal_price' ]  : ( float ) ( $p[ 'price' ] ?? 0 );
$selling = isset( $p[ 'sale_price_eff' ] )? ( float ) $p[ 'sale_price_eff' ]: ( \App\Models\Product::effectivePrice( $p ) );
$hasSale = $selling < $normal && $normal > 0;
$discPct = isset( $p[ 'discount_pct' ] ) ? ( int ) $p[ 'discount_pct' ]
: ( $hasSale ? ( int ) round( ( 1 - $selling / $normal ) * 100 ) : 0 );

// ---- stock ---------------------------------------------------------------
$stock   = ( int ) ( $p[ 'stock' ] ?? 0 );
$status  = ( string ) ( $p[ 'status' ] ?? 'active' );
$isOut   = $status !== 'active' || $stock <= 0;
$lowLeft = !$isOut && $stock <= 3;

// ---- badges --------------------------------------------------------------
$isNew = false;
if ( !empty( $p[ 'published_at' ] ) ) {
    $ts = strtotime( ( string ) $p[ 'published_at' ] );
    $isNew = $ts !== false && $ts >= ( time() - 86400 * 30 );
}
$image = image_url( $p[ 'main_image' ] ?? null );
$alt   = ( string ) ( $p[ 'image_alt' ] ?: $name );
$wishlisted = $showWish && Wishlist::has( ( int ) ( $p[ 'id' ] ?? 0 ) );
$compareIds = array_map( 'intval', ( array ) ( $_SESSION[ 'compare_ids' ] ?? [] ) );
$isCompared = in_array( ( int ) ( $p[ 'id' ] ?? 0 ), $compareIds, true );

$prodBase   = ( string ) \App\Seo\SeoSettings::get( 'canonical_domain', 'https://www.lalamobiles.pk' );
$prodFullUrl= rtrim( $prodBase, '/' ) . $href;
$waCardMsg  = rawurlencode( "Salam Lala Mobiles! I want to inquire about / buy:\n*{$name}*\nPrice: " . number_format( $selling, 0 ) . " PKR\nLink: {$prodFullUrl}" );
$waCardUrl  = "https://wa.me/923005970504?text={$waCardMsg}";
?>
<article class = 'group relative flex h-full min-w-0 flex-col overflow-hidden rounded-xl border border-line bg-white transition-all duration-220 hover:-translate-y-1 hover:border-electric-200 hover:shadow-lift'
data-product-card data-ctx = "<?= e($ctx) ?>" data-product-id = "<?= (int) ($p['id'] ?? 0) ?>">

<!-- Image + badges -->
<a href = "<?= e($href) ?>" class = 'relative block bg-surface-sunken overflow-hidden aspect-square' aria-label = "<?= e($name) ?>">
<div class = 'relative overflow-hidden aspect-square w-full'>
<img src = "<?= e($image) ?>" alt = "<?= e($alt) ?>" width = '800' height = '800'
<?= $lazy ? 'loading="lazy" decoding="async"' : 'fetchpriority="high"' ?>

class = 'product-img aspect-square w-full h-full object-cover transition-transform duration-500 group-hover:scale-110'>
<!-- Hover overlay -->
<div class = 'absolute inset-0 bg-slate-900/0 group-hover:bg-slate-900/10 transition-all duration-220'></div>
</div>

<!-- Badges top-left -->
<span class = 'absolute top-2.5 left-2.5 flex flex-col gap-1.5 items-start z-10'>
<?php if ( $discPct > 0 ): ?>
<span class = 'badge badge-sale px-2.5 py-1 text-[11px] shadow-sm'>
<svg viewBox = '0 0 24 24' fill = 'none' stroke = 'currentColor' stroke-width = '2.5' class = 'h-3 w-3'><path d = 'M12 2v4m0 12v4M4.93 4.93l2.83 2.83m8.48 8.48 2.83 2.83M2 12h4m12 0h4M4.93 19.07l2.83-2.83m8.48-8.48 2.83-2.83'/></svg>
-<?= $discPct ?>%
</span>
<?php endif;
?>

<?php if ( $isNew ): ?>
<span class = 'badge badge-new px-2.5 py-1 text-[11px] shadow-sm'>✦ New</span>
<?php endif;
?>
<?php if ( $isOut ): ?>
<span class = 'badge badge-out px-2.5 py-1 text-[11px]'>Out of stock</span>
<?php endif;
?>
<?php if ( $lowLeft ): ?>
<span class = 'inline-flex items-center gap-1 rounded-full bg-gold-400 text-navy-900 px-2.5 py-1 text-[11px] font-bold shadow-sm'>
<span class = 'h-1.5 w-1.5 rounded-full bg-navy-900 animate-pulse'></span>Only <?= $stock ?> left
</span>
<?php endif;
?>
</span>

</a>

<?php if ( $showWish ): ?>
<button type = 'button'
class = 'absolute right-2.5 top-2.5 z-20 grid h-9 w-9 place-items-center rounded-full border shadow-sm'
data-wishlist-toggle
data-active = "<?= $wishlisted ? 'true' : 'false' ?>"
data-product-id = "<?= (int) ($p['id'] ?? 0) ?>"
aria-pressed = "<?= $wishlisted ? 'true' : 'false' ?>"
aria-label = "<?= $wishlisted ? 'Remove from' : 'Add to' ?> wishlist: <?= e($name) ?>">
<svg viewBox = '0 0 24 24' fill = 'none' stroke = 'currentColor' stroke-width = '1.9' stroke-linecap = 'round' stroke-linejoin = 'round' class = 'h-[18px] w-[18px] wl-icon'><path d = 'M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z'/></svg>
</button>
<?php endif;
?>
<button type = 'button' class = 'absolute bottom-2.5 right-2.5 z-20 rounded-full border border-line bg-white/95 px-2.5 py-1.5 text-[11px] font-bold text-ink-muted opacity-0 shadow-sm transition-all duration-150 hover:text-electric-700 focus-visible:opacity-100 group-hover:opacity-100' data-compare-toggle data-product-id = "<?= (int) ($p['id'] ?? 0) ?>" aria-pressed = "<?= $isCompared ? 'true' : 'false' ?>">
<?= $isCompared ? 'Compared' : 'Compare' ?>
</button>

<!-- Body -->
<div class = 'flex flex-1 flex-col p-3.5 sm:p-4'>
<div class = 'h-4 flex items-center mb-1 overflow-hidden'>
<?php if ( $brand !== '' ): ?>
<a href = "/brand/<?= e($p['brand_slug'] ?? '') ?>" class = 'text-[11px] font-bold uppercase tracking-widest text-electric-600 hover:text-electric-700 transition-colors truncate'><?= e( $brand ) ?></a>
<?php else: ?>
<span class = 'text-[11px] select-none text-transparent'>&nbsp;</span>
<?php endif; ?>
</div>

<h3 class = 'text-[13.5px] sm:text-[14px] font-semibold leading-snug text-ink line-clamp-2 h-10 overflow-hidden'>
<a href = "<?= e($href) ?>" class = 'hover:text-electric-600 transition-colors'><?= e( $name ) ?></a>
</h3>

<div class = 'mt-1.5 h-4 flex items-center'><?php include BASE_PATH . '/app/views/partials/stars.php'; ?></div>

<!-- Price block -->
<div class = 'mt-2 h-7 flex items-baseline gap-x-2 gap-y-0.5 overflow-hidden'>
<span class = "text-[17px] sm:text-[18px] font-extrabold tracking-tight truncate <?= $hasSale ? 'text-flame-600' : 'text-ink' ?>">
<?= price( $selling ) ?>
</span>
<?php if ( $hasSale ): ?>
<span class = 'text-[12.5px] text-ink-muted line-through truncate'><?= price( $normal ) ?></span>
<?php if ( $discPct > 0 ): ?>
<span class = 'inline-flex items-center rounded-full bg-jade-50 px-1.5 py-0.5 text-[10.5px] font-bold text-jade-700'>
-<?= $discPct ?>%
</span>
<?php endif; ?>
<?php endif; ?>
</div>

<!-- Flash countdown strip -->
<?php if ( !empty( $p[ 'flash_end' ] ) ): ?>
<div class = 'mt-2 flex items-center gap-1.5 rounded-lg bg-flame-50 border border-flame-100 px-2 py-1 text-[11px] font-bold text-flame-600' data-flash-strip data-end = "<?= e(date('c', strtotime((string) $p['flash_end']))) ?>">
<svg viewBox = '0 0 24 24' fill = 'currentColor' class = 'h-3 w-3 animate-pulse'><path d = 'M13 2 4 14h6l-1 8 9-12h-6l1-8Z'/></svg>
<span>Ends: <span data-countdown class = 'font-mono'>--:--:--</span></span>
</div>
<?php endif;
?>

<div class = 'product-card-actions mt-auto pt-3'>
<?php if ( $isOut ): ?>
<button type = 'button' disabled class = 'btn btn-ghost btn-sm opacity-60 cursor-not-allowed' aria-label = "<?= e($name) ?> is out of stock">
<svg viewBox = '0 0 24 24' fill = 'none' stroke = 'currentColor' stroke-width = '2' class = 'h-4 w-4'><circle cx = '12' cy = '12' r = '10'/><path d = 'm4.9 4.9 14.2 14.2'/></svg>
<span class = 'product-card-cart-label'>Out of Stock</span>
</button>
<?php else: ?>
<button type = 'button'
class = 'btn btn-dark btn-sm group/btn hover:bg-electric-600 transition-colors'
data-add-to-cart data-product-id = "<?= (int) ($p['id'] ?? 0) ?>"
data-ctx = "<?= e($ctx) ?>" aria-label = "Add <?= e($name) ?> to cart">
<svg viewBox = '0 0 24 24' fill = 'none' stroke = 'currentColor' stroke-width = '2' class = 'h-4 w-4 transition-transform group/btn-hover:scale-110'><path d = 'M3 4h2l2.4 12.3a1 1 0 0 0 1 .8h9.7a1 1 0 0 0 1-.8L21 8H6'/><circle cx = '9' cy = '20' r = '1.4'/><circle cx = '18' cy = '20' r = '1.4'/></svg>
<span class = 'product-card-cart-label'>Add to Cart</span>
</button>
<?php endif; ?>

<a href = "<?= e($waCardUrl) ?>" target = "_blank" rel = "noopener noreferrer"
   class = "grid h-9 w-9 shrink-0 place-items-center rounded-lg bg-emerald-50 text-emerald-600 border border-emerald-200 hover:bg-[#25D366] hover:text-white hover:border-[#25D366] transition-all shadow-sm"
   title = "Inquire / Order via WhatsApp (+92 300 5970504)"
   aria-label = "Order <?= e($name) ?> via WhatsApp">
<svg viewBox = "0 0 24 24" fill = "currentColor" class = "h-4.5 w-4.5"><path d = "M12.04 2c-5.46 0-9.91 4.45-9.91 9.91 0 1.75.46 3.45 1.32 4.95L2.05 22l5.25-1.38c1.45.79 3.08 1.21 4.74 1.21 5.46 0 9.91-4.45 9.91-9.91 0-2.65-1.03-5.14-2.9-7.01A9.82 9.82 0 0 0 12.04 2zm.01 1.67c4.54 0 8.24 3.7 8.24 8.24 0 2.2-.86 4.27-2.42 5.82-1.55 1.56-3.62 2.42-5.82 2.42-1.42 0-2.81-.37-4.04-1.08l-.29-.17-3.01.79.8-2.93-.19-.3a8.21 8.21 0 0 1-1.26-4.31c0-4.54 3.7-8.24 8.24-8.24zm4.52 11.66c-.25-.13-1.47-.72-1.7-.81-.23-.08-.39-.13-.56.13-.17.25-.64.81-.79.97-.14.17-.29.19-.54.06-.25-.13-1.06-.39-2.02-1.25-.75-.67-1.26-1.5-1.4-1.76-.15-.25-.02-.39.11-.51.11-.11.25-.29.38-.44.13-.14.17-.25.25-.42.08-.17.04-.31-.02-.44-.06-.13-.56-1.35-.77-1.85-.2-.49-.41-.42-.56-.43h-.48c-.17 0-.44.06-.67.31-.23.25-.88.86-.88 2.1 0 1.24.9 2.44 1.03 2.61.13.17 1.77 2.7 4.29 3.79.6.26 1.07.41 1.43.53.6.19 1.15.16 1.58.1.48-.07 1.47-.6 1.68-1.18.21-.58.21-1.08.15-1.18-.06-.1-.23-.17-.48-.29z"/></svg>
</a>
</div>
</div>
</article>
