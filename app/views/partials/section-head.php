<?php
/**
 * Consistent section heading.
 *
 * @var string $key      section key (matches sectionCfg)
 * @var string $fallback default heading
 * @var array  $sectionCfg
 * @var ?string $viewAllUrl
 * @var ?string $viewAllLabel
 */
$title    = trim((string)($sectionCfg[$key]['title'] ?? '')) ?: $fallback;
$subtitle = trim((string)($sectionCfg[$key]['subtitle'] ?? ''));
$viewAllUrl   = $viewAllUrl ?? null;
$viewAllLabel = $viewAllLabel ?? 'View all';
?>
<div class="mb-6 flex items-end justify-between gap-4">
    <div>
        <h2 id="<?= e($key) ?>-heading" class="section-title text-ink"><?= e($title) ?></h2>
        <?php if ($subtitle !== ''): ?>
        <p class="mt-1.5 text-[15px] text-ink-muted"><?= e($subtitle) ?></p>
        <?php endif; ?>
    </div>
    <?php if ($viewAllUrl !== null): ?>
    <a href="<?= e($viewAllUrl) ?>" class="group inline-flex shrink-0 items-center gap-1 text-[14px] font-bold text-electric-600 hover:text-electric-700">
        <?= e($viewAllLabel) ?>
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" class="h-4 w-4 transition-transform group-hover:translate-x-0.5"><path d="M5 12h14m-6-6 6 6-6 6"/></svg>
    </a>
    <?php endif; ?>
</div>
