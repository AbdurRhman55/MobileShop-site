<?php
/**
 * Best sellers — ranked by real units sold. Products with zero recorded
 * sales are skipped so the ranking is never fabricated.
 *
 * @var array $bestSellers  rows incl. units_sold
 * @var array $sectionCfg
 */
$bestSellers = array_values(array_filter(
    $bestSellers ?? [],
    static fn(array $p): bool => (int)($p['units_sold'] ?? 0) > 0
));
if ($bestSellers === []) { return; }
?>
<section class="shell mt-14" aria-labelledby="bestsellers-heading">
    <?php
// Best sellers are ranked by real units_sold. There is no dedicated
// /bestsellers archive route on this site, so no "View all" link is rendered
// rather than pointing at a URL that does not exist.
$viewAllUrl = null;
?>
    <?= \App\Core\View::partial('partials/section-head', [
        'key' => 'bestsellers', 'fallback' => 'Best Sellers',
        'sectionCfg' => $sectionCfg, 'viewAllUrl' => $viewAllUrl,
    ]) ?>
    <?= \App\Core\View::partial('partials/product-row', [
        'products' => array_slice($bestSellers, 0, 8), 'ctx' => 'bestsellers', 'carousel' => true,
    ]) ?>
</section>
