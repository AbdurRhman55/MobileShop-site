<?php
/**
 * Star rating display (no fabricated data).
 *
 * Renders only when the product genuinely has rating data. Empty stars are
 * drawn as an outline so the visual average is honest — we never round up to
 * a full star to look better.
 *
 * @var array  $p            product row (rating_avg, rating_count)
 * @var bool   $showCount    show "(n)" next to the stars
 * @var string $sizeClass    sizing hook, e.g. "h-4 w-4"
 */
$rating   = isset($p['rating_avg']) ? (float) $p['rating_avg'] : 0.0;
$count    = isset($p['rating_count']) ? (int) $p['rating_count'] : 0;
$showCount= $showCount ?? true;
$sizeClass= $sizeClass ?? 'h-4 w-4';

if ($count <= 0 || $rating <= 0) {
    // No genuine reviews → say so plainly rather than showing 0 stars.
    echo '<span class="inline-flex items-center gap-1 text-[12px] text-ink-muted">'
       . '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" class="' . e($sizeClass) . ' opacity-50"><path d="m12 3 2.6 5.6 6 .8-4.4 4.2 1.1 6L12 16.8 6.7 19.6l1.1-6L3.4 9.4l6-.8L12 3Z"/></svg>'
       . '<span>No reviews yet</span></span>';
    return;
}
?>
<span class="inline-flex items-center gap-1.5">
    <span class="stars inline-flex" role="img" aria-label="Rated <?= number_format($rating, 1) ?> out of 5">
        <?php for ($i = 1; $i <= 5; $i++): ?>
        <?php
            // Fill fraction for this star (0, 0.5 via clip, or 1).
            $fill = max(0.0, min(1.0, $rating - ($i - 1)));
        ?>
        <span class="relative inline-block <?= e($sizeClass) ?>">
            <!-- empty base -->
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" class="absolute inset-0 h-full w-full text-line"><path d="m12 3 2.6 5.6 6 .8-4.4 4.2 1.1 6L12 16.8 6.7 19.6l1.1-6L3.4 9.4l6-.8L12 3Z"/></svg>
            <?php if ($fill > 0): ?>
            <!-- filled overlay, clipped to the fraction -->
            <span class="absolute inset-0 overflow-hidden" style="width:<?= (int) round($fill * 100) ?>%">
                <svg viewBox="0 0 24 24" fill="currentColor" class="h-full w-full text-gold-500"><path d="m12 3 2.6 5.6 6 .8-4.4 4.2 1.1 6L12 16.8 6.7 19.6l1.1-6L3.4 9.4l6-.8L12 3Z"/></svg>
            </span>
            <?php endif; ?>
        </span>
        <?php endfor; ?>
    </span>
    <span class="text-[12px] font-semibold text-ink"><?= number_format($rating, 1) ?></span>
    <?php if ($showCount): ?>
    <span class="text-[12px] text-ink-muted">(<?= number_format($count) ?>)</span>
    <?php endif; ?>
</span>
