<?php
/**
* Flash-sale campaign section.
*
* @var ?array $sale        live campaign row ( null → skipped )
* @var array  $saleItems   products with normal_price/sale_price_eff/discount_pct/flash_end
* @var ?int   $saleEndsAt  absolute unix end time ( null → open-ended )
* @var array  $sectionCfg
*/
if ( $sale === null || $saleItems === [] ) {
    return;
}

$themeBg = 'from-slate-950 via-slate-900 to-slate-800';
$subtitle = trim( ( string )( $sectionCfg[ 'flash' ][ 'subtitle' ] ?? '' ) );
?>
<section id = 'flash-sale' class = 'shell mt-14' aria-labelledby = 'flash-heading'>
<div class = "overflow-hidden rounded-2xl bg-gradient-to-br <?= $themeBg ?> text-white">
<div class = 'flex flex-col gap-4 p-5 md:flex-row md:items-center md:justify-between md:p-7'>
<div class = 'flex items-start gap-4'>
<span class = 'grid h-12 w-12 shrink-0 place-items-center rounded-xl bg-amber-400 text-slate-950 ring-1 ring-amber-300'>
<svg viewBox = '0 0 24 24' fill = 'currentColor' class = 'h-6 w-6'><path d = 'M13 2 4.5 13.5H11l-1 8.5 9-12h-6.5L13 2Z'/></svg>
</span>
<div>
<p class = 'text-[12px] font-bold uppercase tracking-widest text-amber-300'>Flash Sale</p>
<h2 id = 'flash-heading' class = 'mt-0.5 text-2xl md:text-3xl font-extrabold tracking-tight'>
<?= e( $sale[ 'title' ] ?? 'Limited-Time Deals' ) ?>
</h2>
<?php if ( $subtitle !== '' ): ?>
<p class = 'mt-1 max-w-xl text-[14px] text-white/85'><?= e( $subtitle ) ?></p>
<?php endif;
?>
</div>
</div>

<?php if ( $saleEndsAt !== null ): ?>
<div class = 'shrink-0' data-flash-countdown data-end = "<?= (int)$saleEndsAt ?>"
aria-live = 'off' aria-label = 'Time remaining'>
<p class = 'text-[12px] font-bold uppercase tracking-widest text-white/80'>Ends in</p>
<div class = 'mt-1.5 flex gap-2' data-clock>
<?php foreach ( [ 'h' => 'Hours', 'm' => 'Mins', 's' => 'Secs' ] as $u => $lbl ): ?>
<div class = 'min-w-[62px] rounded-xl bg-white/15 px-3 py-2 text-center ring-1 ring-white/25'>
<div class = 'text-2xl font-extrabold tabular-nums leading-none' data-unit = "<?= $u ?>">00</div>
<div class = 'mt-1 text-[11px] font-semibold uppercase tracking-wide text-white/70'><?= $lbl ?></div>
</div>
<?php endforeach;
?>
</div>
<!-- Shown by JS only once the stored end time has actually passed. -->
<p class = 'mt-1.5 hidden rounded-xl bg-white/15 px-3 py-2.5 text-center text-[13px] font-bold ring-1 ring-white/25'
data-expired-label>This offer has ended</p>
</div>
<?php endif;
?>
</div>

<div class = 'px-5 pb-6 md:px-7 md:pb-7'>
<ul class = 'product-grid product-grid--flash'>
<?php foreach ( $saleItems as $p ): ?>
<li class = 'min-w-0 overflow-hidden rounded-xl bg-white/10 ring-1 ring-white/15 backdrop-blur-sm'>
<?php $ctx = 'flash';
?>
<?= \App\Core\View::partial( 'partials/product-card', [ 'p' => $p, 'ctx' => $ctx ] ) ?>
</li>
<?php endforeach;
?>
</ul>
<div class = 'mt-5'>
<a href = '/#flash-sale' class = 'btn btn-primary btn-md'>
View all deals
<svg viewBox = '0 0 24 24' fill = 'none' stroke = 'currentColor' stroke-width = '2.4' class = 'h-4 w-4'><path d = 'M5 12h14m-6-6 6 6-6 6'/></svg>
</a>
</div>
</div>
</div>
</section>
