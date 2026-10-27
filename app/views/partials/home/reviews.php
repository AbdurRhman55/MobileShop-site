<?php
/**
 * Customer reviews — professional card design with verified badges and avatars.
 *
 * @var array $reviews  rows incl. product_name, product_slug
 */
$reviews = array_values($reviews ?? []);
if ($reviews === []) { return; }

// Generate a consistent avatar color based on author name
$avatarColors = [
    'from-electric-400 to-violet-600',
    'from-jade-400 to-emerald-600',
    'from-flame-400 to-orange-600',
    'from-gold-400 to-amber-600',
    'from-violet-400 to-fuchsia-600',
    'from-blue-400 to-indigo-600',
    'from-teal-400 to-cyan-600',
    'from-rose-400 to-pink-600',
];
?>
<section class="shell mt-14" aria-labelledby="reviews-heading">
    <?= \App\Core\View::partial('partials/section-head', [
        'key' => 'reviews', 'fallback' => 'What Customers Say',
        'sectionCfg' => $sectionCfg,
    ]) ?>

    <ul class="grid min-w-0 gap-5 sm:grid-cols-2 lg:grid-cols-3">
        <?php foreach ($reviews as $idx => $r): $rating = max(1, min(5, (int)$r['rating']));
            $authorInitial = strtoupper(substr(trim((string)$r['author']), 0, 1)) ?: '?';
            $avatarGrad = $avatarColors[crc32((string)$r['author']) % count($avatarColors)];
        ?>
        <li class="group flex h-full min-w-0 flex-col overflow-hidden rounded-2xl border border-line bg-white p-6 transition-all duration-300 hover:-translate-y-0.5 hover:shadow-lg hover:border-electric-200">
            <!-- Stars + quote icon -->
            <div class="flex items-start justify-between gap-3">
                <div class="flex items-center gap-0.5" role="img" aria-label="Rated <?= $rating ?> out of 5">
                    <?php for ($i = 1; $i <= 5; $i++): ?>
                    <svg viewBox="0 0 24 24" class="h-4.5 w-4.5 <?= $i <= $rating ? 'text-gold-400' : 'text-line' ?>"
                         style="width:1.1rem;height:1.1rem"
                         fill="<?= $i <= $rating ? 'currentColor' : 'none' ?>" stroke="currentColor" stroke-width="1.5">
                        <path d="M12 2l2.9 6.3 6.8.8-5 4.7 1.3 6.8L12 17.8 6 20.6l1.3-6.8-5-4.7 6.8-.8L12 2Z"/>
                    </svg>
                    <?php endfor; ?>
                </div>
                <!-- Quote decoration -->
                <svg viewBox="0 0 24 24" fill="currentColor" class="h-8 w-8 shrink-0 text-electric-100">
                    <path d="M3 21c3 0 7-1 7-8V5c0-1.25-.756-2.017-2-2H4c-1.25 0-2 .75-2 1.972V11c0 1.25.75 2 2 2 1 0 1 0 1 1v1c0 1-1 2-2 2s-1 .008-1 1.031V20c0 1 0 1 1 1zm12 0c3 0 7-1 7-8V5c0-1.25-.757-2.017-2-2h-4c-1.25 0-2 .75-2 1.972V11c0 1.25.75 2 2 2h.75c0 2.25.25 4-2.75 4v3c0 1 0 1 1 1z"/>
                </svg>
            </div>

            <?php if (!empty($r['title'])): ?>
            <h3 class="mt-3 text-[15px] font-bold text-ink leading-snug"><?= e($r['title']) ?></h3>
            <?php endif; ?>

            <p class="mt-2.5 flex-1 text-[14px] leading-relaxed text-ink-muted line-clamp-4"><?= e($r['body']) ?></p>

            <div class="mt-5 flex items-center justify-between gap-3 border-t border-line pt-4">
                <div class="flex items-center gap-3 min-w-0">
                    <!-- Avatar -->
                    <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-gradient-to-br <?= $avatarGrad ?> text-[14px] font-extrabold text-white shadow-sm">
                        <?= $authorInitial ?>
                    </span>
                    <div class="min-w-0">
                        <p class="truncate text-[13px] font-bold text-ink"><?= e($r['author']) ?></p>
                        <?php if (!empty($r['product_slug'])): ?>
                        <a href="/product/<?= e($r['product_slug']) ?>" class="truncate text-[12px] text-electric-600 hover:text-electric-700 transition-colors">
                            <?= e($r['product_name']) ?>
                        </a>
                        <?php endif; ?>
                    </div>
                </div>
                <span class="inline-flex shrink-0 items-center gap-1 rounded-full bg-jade-50 border border-jade-100 px-2.5 py-1 text-[11px] font-bold text-jade-700">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6" class="h-3 w-3"><path d="M20 6L9 17l-5-5"/></svg>
                    Verified
                </span>
            </div>
        </li>
        <?php endforeach; ?>
    </ul>
</section>
