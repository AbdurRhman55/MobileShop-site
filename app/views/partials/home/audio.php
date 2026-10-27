<?php
/** Dedicated audio discovery section populated from the real audio catalog. */
if ( ( $audioProducts ?? [] ) === [] ) {
    return;
}
?>
<section class = 'shell mt-16' aria-labelledby = 'audio-heading'>
<div class = 'relative overflow-hidden rounded-2xl bg-slate-950 px-6 py-8 text-white sm:px-9 md:py-10'>
<div class = 'absolute inset-0 opacity-25' aria-hidden = 'true'><img src = 'https://images.unsplash.com/photo-1608043152269-423dbba4e7e1?auto=format&fit=crop&w=1800&q=82' alt = '' loading = 'lazy' class = 'h-full w-full object-cover'></div>
<div class = 'absolute inset-0 bg-gradient-to-r from-slate-950 via-slate-950/90 to-slate-900/35' aria-hidden = 'true'></div>
<div class = 'relative flex flex-col gap-5 md:flex-row md:items-end md:justify-between'>
<div class = 'max-w-2xl'>
<p class = 'text-xs font-bold uppercase tracking-[0.18em] text-amber-300'>The Audio Edit</p>
<h2 id = 'audio-heading' class = 'mt-2 text-2xl font-extrabold text-white sm:text-3xl'>Sound, made personal.</h2>
<p class = 'mt-2 max-w-xl text-sm leading-relaxed text-white/75'>From focused listening to room-filling sound, find the right audio for the way you live.</p>
</div>
<a href = '/category/audio' class = 'btn btn-primary btn-md w-fit'>Explore Audio <span aria-hidden = 'true'>→</span></a>
</div>
</div>
<ul class = 'product-grid mt-5'>
<?php foreach ( $audioProducts as $product ): ?>
<li class = 'min-w-0'><?= \App\Core\View::partial( 'partials/product-card', [ 'p' => $product, 'ctx' => 'audio', 'lazy' => true ] ) ?></li>
<?php endforeach;
?>
</ul>
</section>
