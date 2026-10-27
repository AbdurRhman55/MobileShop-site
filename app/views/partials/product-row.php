<?php
/**
 * Responsive product grid / carousel.
 *
 * @var array  $products   product rows (brand_name, brand_slug joined)
 * @var string $ctx        analytics context key
 * @var bool   $carousel   enable horizontal snap-scroll on large screens
 * @var string $viewAllUrl
 */
$products = $products ?? [];
$ctx      = $ctx ?? 'row';
$carousel = $carousel ?? true;

if ($products === []) { return; }
?>
<div class="relative" data-carousel>
    <ul class="<?= $carousel
        ? 'flex snap-x snap-mandatory gap-4 overflow-x-auto pb-2 no-scrollbar [-ms-overflow-style:none] [scrollbar-width:none] md:grid md:grid-cols-4 md:overflow-visible md:pb-0'
        : 'product-grid' ?>">
        <?php foreach ($products as $p): ?>
        <li class="<?= $carousel ? 'w-[62%] shrink-0 snap-start sm:w-[42%] md:w-auto h-full flex flex-col' : 'h-full flex flex-col' ?>">
            <?= \App\Core\View::partial('partials/product-card', ['p' => $p, 'ctx' => $ctx]) ?>
        </li>
        <?php endforeach; ?>
    </ul>

    <?php if ($carousel): ?>
    <button type="button" data-carousel-prev aria-label="Scroll products left"
            class="absolute -left-3 top-1/2 hidden h-10 w-10 -translate-y-1/2 place-items-center rounded-full border border-line bg-white text-ink shadow-card hover:border-electric-400 hover:text-electric-600 md:grid">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" class="h-5 w-5"><path d="M15 18l-6-6 6-6"/></svg>
    </button>
    <button type="button" data-carousel-next aria-label="Scroll products right"
            class="absolute -right-3 top-1/2 hidden h-10 w-10 -translate-y-1/2 place-items-center rounded-full border border-line bg-white text-ink shadow-card hover:border-electric-400 hover:text-electric-600 md:grid">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" class="h-5 w-5"><path d="M9 6l6 6-6 6"/></svg>
    </button>
    <?php endif; ?>
</div>
