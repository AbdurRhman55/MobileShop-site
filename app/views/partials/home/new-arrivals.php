<?php
/**
 * New arrivals — recent products. The "New" badge is rendered by the
 * product-card from a real published_at window; no badge is fabricated.
 *
 * @var array $latest
 * @var array $sectionCfg
 */
if ($latest === []) { return; }
?>
<section class="shell mt-14" aria-labelledby="new-arrivals-heading">
    <?php
// Newest products across the catalogue. There is no dedicated /new-arrivals
// archive route, so no "View all" link is rendered rather than pointing at a
// URL that would 404. (A sorted category listing does exist for a single
// category: /category/{slug}?sort=newest.)
$viewAllUrl = null;
?>
    <?= \App\Core\View::partial('partials/section-head', [
        'key' => 'new-arrivals', 'fallback' => 'New Arrivals',
        'sectionCfg' => $sectionCfg, 'viewAllUrl' => $viewAllUrl,
    ]) ?>
    <?= \App\Core\View::partial('partials/product-row', [
        'products' => $latest, 'ctx' => 'new-arrivals', 'carousel' => true,
    ]) ?>
</section>
