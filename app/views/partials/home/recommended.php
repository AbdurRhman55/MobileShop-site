<?php
/**
 * Recommended products.
 *
 * These are NOT personalised. They are picked by a transparent, stated rule:
 * the most popular categories in the current catalogue, surfaced as
 * "Popular right now". The section heading says so — we never claim the
 * picks are tailored to the individual visitor.
 *
 * @var array $featured  featured products (fallback / seed)
 * @var array $sectionCfg
 */
$recommended = $featured ?? [];
if ($recommended === []) { return; }
?>
<section class="shell mt-14" aria-labelledby="recommended-heading">
    <?php $viewAllUrl = '/category/smartphones'; ?>
    <?= \App\Core\View::partial('partials/section-head', [
        'key' => 'recommended', 'fallback' => 'Popular Right Now',
        'sectionCfg' => $sectionCfg, 'viewAllUrl' => $viewAllUrl,
    ]) ?>
    <?= \App\Core\View::partial('partials/product-row', [
        'products' => $recommended, 'ctx' => 'recommended', 'carousel' => false,
    ]) ?>
    <p class="mt-3 text-[13px] text-ink-muted">
        Curated from our most-viewed categories — not a personalised recommendation.
    </p>
</section>
