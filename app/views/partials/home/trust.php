<?php
/**
 * Trust indicators strip — professional icon design.
 *
 * @var array $trust  active trust_indicators rows
 */
if ($trust === []) { return; }

$icons = [
    'shield'  => 'M12 2l7 4v6c0 4.4-3 8.3-7 9-4-.7-7-4.6-7-9V6l7-4zM9 12l2 2 4-4',
    'truck'   => 'M3 7h11v9H3zM14 10h4l3 3v3h-7zM7 18a2 2 0 1 0 0-4 2 2 0 0 0 0 4zm10.5 0a2 2 0 1 0 0-4 2 2 0 0 0 0 4',
    'return'  => 'M3 12a9 9 0 1 0 3-6.7M3 4v5h5',
    'warranty'=> 'M12 2l7 4v6c0 4.4-3 8.3-7 9-4-.7-7-4.6-7-9V6l7-4zM12 8v4l2.5 1.5',
    'support' => 'M4 13a8 8 0 0 1 16 0M4 13v3a2 2 0 0 0 2 2h1v-5H6a2 2 0 0 0-2 2zM20 13v3a2 2 0 0 1-2 2h-1v-5h1a2 2 0 0 1 2 2z',
    'cod'     => 'M2 8h20v12a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V8zM2 8l2-4h16l2 4M12 12h.01M8 12h.01M16 12h.01',
    'genuine' => 'M12 2l2.5 5 5.5.8-4 3.9 1 5.5L12 20l-5 2.7 1-5.5-4-3.9L9.5 7z',
    'secure'  => 'M5 11h14v10H5zM8 11V7a4 4 0 0 1 8 0v4',
];
$default = 'M20 6L9 17l-5-5';

$bgColors = [
    'shield'  => 'bg-electric-50 text-electric-600',
    'truck'   => 'bg-jade-50 text-jade-600',
    'return'  => 'bg-violet-50 text-violet-600',
    'warranty'=> 'bg-gold-50 text-gold-600',
    'support' => 'bg-blue-50 text-blue-600',
    'cod'     => 'bg-emerald-50 text-emerald-600',
    'genuine' => 'bg-amber-50 text-amber-600',
    'secure'  => 'bg-indigo-50 text-indigo-600',
];
?>
<section aria-label="Why shop with us" class="bg-white border-y border-line reveal" data-reveal>
    <div class="shell py-8">
        <ul class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-4 md:gap-6 stagger" data-stagger>
            <?php foreach ($trust as $t): $key = strtolower(trim((string)$t['icon'])); ?>
            <li class="group flex items-center gap-3.5 rounded-xl p-3 transition-all duration-300 hover:bg-surface-sunken hover:shadow-sm">
                <span class="mt-0.5 grid h-11 w-11 shrink-0 place-items-center rounded-xl <?= $bgColors[$key] ?? 'bg-electric-50 text-electric-600' ?> transition-transform duration-200 group-hover:scale-110 shadow-sm">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"
                         stroke-linecap="round" stroke-linejoin="round" class="h-5.5 w-5.5" style="width:1.35rem;height:1.35rem">
                        <path d="<?= $icons[$key] ?? $default ?>"/>
                    </svg>
                </span>
                <span>
                    <span class="block text-[14px] font-bold text-ink leading-snug"><?= e($t['label']) ?></span>
                    <?php if (!empty($t['detail'])): ?>
                    <span class="mt-0.5 block text-[12px] text-ink-muted leading-snug"><?= e($t['detail']) ?></span>
                    <?php endif; ?>
                </span>
            </li>
            <?php endforeach; ?>
        </ul>
    </div>
</section>
