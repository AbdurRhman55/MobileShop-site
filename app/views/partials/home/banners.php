<?php
/**
* Promotional banner tiles.
*
* @var array $banners  active, in-window promo_banners rows
*/
if ( $banners === [] ) {
    return;
}

$themes = [
    'navy' => [ 'bg' => 'bg-slate-900', 'text' => 'text-white', 'sub' => 'text-white/80', 'accent' => 'text-amber-300' ],
    'electric' => [ 'bg' => 'bg-indigo-700', 'text' => 'text-white', 'sub' => 'text-white/85', 'accent' => 'text-white' ],
    'violet' => [ 'bg' => 'bg-indigo-700', 'text' => 'text-white', 'sub' => 'text-white/85', 'accent' => 'text-white' ],
    'flame' => [ 'bg' => 'bg-slate-900', 'text' => 'text-white', 'sub' => 'text-white/85', 'accent' => 'text-amber-300' ],
    'jade' => [ 'bg' => 'bg-slate-900', 'text' => 'text-white', 'sub' => 'text-white/85', 'accent' => 'text-amber-300' ],
    'custom' => [ 'bg' => 'bg-slate-900', 'text' => 'text-white', 'sub' => 'text-white/85', 'accent' => 'text-amber-300' ],
];
?>
<section class = 'shell mt-14' aria-label = 'Offers'>
<ul class = 'grid gap-4 sm:grid-cols-2 lg:grid-cols-3'>
<?php foreach ( $banners as $bn ):
$t = $themes[ ( string )$bn[ 'theme' ] ] ?? $themes[ 'electric' ];
$img  = ( string )( $bn[ 'image' ] ?? '' );
$imgUrl = $img !== '' ? image_url( $img ) : '';
$url  = \App\Models\SiteContent::isSafeUrl( ( string )( $bn[ 'url' ] ?? '' ) ) ? ( string )$bn[ 'url' ] : null;
$tag  = $url !== null ? 'a' : 'div';
$attr = $url !== null ? ' href="' . e( $url ) . '"' : '';
?>
<li>
<<?= $tag ?><?= $attr ?>

class = "group relative flex h-full min-h-[180px] flex-col justify-between overflow-hidden rounded-xl p-6 <?= $t['bg'] ?> <?= $t['text'] ?> transition-all duration-200 <?= $url !== null ? 'hover:-translate-y-0.5 hover:shadow-lift' : '' ?>">
<?php if ( $imgUrl !== '' ): ?>
<img src = "<?= e($imgUrl) ?>" alt = '' width = '600' height = '400' loading = 'lazy' decoding = 'async'

class = "absolute inset-0 h-full w-full object-cover opacity-25 transition-transform duration-500 <?= $url !== null ? 'group-hover:scale-105' : '' ?>">
<span class = "absolute inset-0 <?= $t['bg'] ?> opacity-30"></span>
<span class = 'absolute inset-0 bg-gradient-to-t from-slate-950/90 via-slate-900/45 to-slate-900/15'></span>
<?php endif;
?>
<span class = 'relative'>
<span class = 'block text-[20px] font-extrabold leading-tight tracking-tight'><?= e( $bn[ 'title' ] ) ?></span>
<?php if ( !empty( $bn[ 'description' ] ) ): ?>
<span class = "mt-1.5 block max-w-xs text-[14px] leading-snug <?= $t['sub'] ?>"><?= e( $bn[ 'description' ] ) ?></span>
<?php endif;
?>
</span>
<?php if ( $url !== null ): ?>
<span class = "relative mt-4 inline-flex items-center gap-1.5 text-[13px] font-bold <?= $t['accent'] ?>">
Shop now
<svg viewBox = '0 0 24 24' fill = 'none' stroke = 'currentColor' stroke-width = '2.6' class = 'h-4 w-4 transition-transform group-hover:translate-x-0.5'><path d = 'M5 12h14m-6-6 6 6-6 6'/></svg>
</span>
<?php endif;
?>
</<?= $tag ?>>
</li>
<?php endforeach;
?>
</ul>
</section>
