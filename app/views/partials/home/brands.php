<?php
/**
 * Trending brands — only brands that actually stock sellable products.
 * Logos fall back to a clean wordmark tile when no logo file is set.
 *
 * @var array $brands   rows incl. product_count
 * @var array $sectionCfg
 */
if ($brands === []) { return; }
?>
<section class="shell mt-14" aria-labelledby="brands-heading">
    <?php
// Every active brand is already rendered here and there is no /brands index
// route, so no "View all" link is shown.
$viewAllUrl = null;
?>
    <?= \App\Core\View::partial('partials/section-head', [
        'key' => 'brands', 'fallback' => 'Trending Brands',
        'sectionCfg' => $sectionCfg, 'viewAllUrl' => $viewAllUrl,
    ]) ?>

    <ul class="grid grid-cols-3 gap-3 sm:grid-cols-4 lg:grid-cols-6">
        <?php foreach ($brands as $b): $logo = (string)($b['logo'] ?? ''); $logoUrl = $logo !== '' ? image_url($logo) : ''; ?>
        <li>
            <a href="/brand/<?= e($b['slug']) ?>"
               class="group flex h-full flex-col items-center justify-center gap-2 rounded-2xl border border-line bg-white p-4 text-center transition-all duration-200 hover:-translate-y-0.5 hover:border-electric-300 hover:shadow-lift">
                <span class="grid h-14 w-full place-items-center overflow-hidden">
                    <?php if ($logoUrl !== ''): ?>
                    <img src="<?= e($logoUrl) ?>" alt="<?= e($b['name']) ?>" width="120" height="56" loading="lazy" decoding="async"
                         class="max-h-12 w-auto object-contain transition-transform duration-200 group-hover:scale-105">
                    <?php else: ?>
                    <span class="text-[17px] font-extrabold tracking-tight text-ink-muted transition-colors group-hover:text-electric-600">
                        <?= e($b['name']) ?>
                    </span>
                    <?php endif; ?>
                </span>
                <span class="text-[12px] font-semibold text-ink-muted">
                    <?= (int)$b['product_count'] ?> product<?= (int)$b['product_count'] === 1 ? '' : 's' ?>
                </span>
            </a>
        </li>
        <?php endforeach; ?>
    </ul>
</section>
