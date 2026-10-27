<?php
/**
 * Repair services promo.
 *
 * @var array $repairs  published repair pages (service_name, price_from…)
 */
$repairs  = array_values($repairs ?? []);
if ($repairs === []) { return; }

$sectionCfg = $sectionCfg ?? [];
$first = $repairs[0];
$rest  = array_slice($repairs, 1, 5);
$phone = (string) \App\Seo\SeoSettings::get('organization_phone', '');
?>
<section id="repairs" class="shell mt-14" aria-labelledby="repairs-heading">
    <div class="overflow-hidden rounded-2xl border border-line bg-white">
        <div class="grid gap-6 p-6 md:grid-cols-2 md:p-8">
            <div>
                <span class="inline-flex items-center gap-1.5 rounded-full bg-jade-50 px-3 py-1 text-[12px] font-bold uppercase tracking-wide text-jade-700">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="h-3.5 w-3.5"><path d="M14.7 6.3a4 4 0 0 0-5.4 5.4L3 18l3 3 6.3-6.3a4 4 0 0 0 5.4-5.4l-2.5 2.5-2.1-2.1 2.5-2.5Z"/></svg>
                    Certified Repairs
                </span>
                <?php
                // Admin title/subtitle overrides; blank fields keep the real
                // first service's name/intro as the heading copy.
                $repTitle = trim((string) ($sectionCfg['repairs']['title'] ?? ''));
                $repSub   = trim((string) ($sectionCfg['repairs']['subtitle'] ?? ''));
                ?>
                <h2 id="repairs-heading" class="mt-3 text-2xl md:text-3xl font-extrabold tracking-tight text-ink">
                    <?= e($repTitle !== '' ? $repTitle : ($first['service_name'] ?: $first['title'])) ?>
                </h2>
                <?php if ($repSub !== ''): ?>
                <p class="mt-2.5 max-w-md text-[15px] leading-relaxed text-ink-muted"><?= e($repSub) ?></p>
                <?php elseif (!empty($first['intro'])): ?>
                <p class="mt-2.5 max-w-md text-[15px] leading-relaxed text-ink-muted"><?= e($first['intro']) ?></p>
                <?php endif; ?>

                <?php if ($rest !== []): ?>
                <ul class="mt-5 grid gap-2 sm:grid-cols-2">
                    <?php foreach ($rest as $r): ?>
                    <li>
                        <a href="/repair/<?= e($r['slug']) ?>"
                           class="flex items-center justify-between gap-2 rounded-xl border border-line px-3.5 py-2.5 transition-colors hover:border-jade-400 hover:bg-jade-50/40">
                            <span class="text-[14px] font-semibold text-ink"><?= e($r['service_name'] ?: $r['title']) ?></span>
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" class="h-4 w-4 shrink-0 text-jade-600"><path d="M9 6l6 6-6 6"/></svg>
                        </a>
                    </li>
                    <?php endforeach; ?>
                </ul>
                <?php endif; ?>

                <div class="mt-6 flex flex-wrap gap-3">
                    <?php if (!empty($first['price_from'])): ?>
                    <a href="/repair/<?= e($first['slug']) ?>" class="btn btn-dark btn-md">
                        Book a repair
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" class="h-4 w-4"><path d="M5 12h14m-6-6 6 6-6 6"/></svg>
                    </a>
                    <?php endif; ?>
                    <?php if ($phone !== ''): ?>
                    <a href="tel:<?= e(preg_replace('/[^0-9+]/', '', $phone)) ?>" class="btn btn-ghost btn-md">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="h-4 w-4"><path d="M22 16.9v3a2 2 0 0 1-2.2 2 19.8 19.8 0 0 1-8.6-3.1 19.5 19.5 0 0 1-6-6A19.8 19.8 0 0 1 2.1 4.2 2 2 0 0 1 4.1 2h3a2 2 0 0 1 2 1.7c.1 1 .4 1.9.7 2.8a2 2 0 0 1-.5 2.1L8.1 9.9a16 16 0 0 0 6 6l1.3-1.3a2 2 0 0 1 2.1-.4c.9.3 1.8.6 2.8.7a2 2 0 0 1 1.7 2Z"/></svg>
                        Call to book
                    </a>
                    <?php endif; ?>
                </div>
            </div>

            <div class="relative min-h-[220px] overflow-hidden rounded-xl bg-gradient-to-br from-navy-900 via-navy-800 to-violet-900">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1" class="absolute -right-8 -bottom-8 h-64 w-64 text-white/10"><path d="M14.7 6.3a4 4 0 0 0-5.4 5.4L3 18l3 3 6.3-6.3a4 4 0 0 0 5.4-5.4l-2.5 2.5-2.1-2.1 2.5-2.5Z"/></svg>
                <div class="relative flex h-full flex-col justify-end gap-3 p-6 text-white">
                    <?php if (!empty($first['area_served'])): ?>
                    <p class="text-[13px] font-semibold uppercase tracking-wide text-white/70">Serving</p>
                    <p class="text-[15px] font-semibold leading-snug"><?= e($first['area_served']) ?></p>
                    <?php endif; ?>
                    <?php if (!empty($first['price_from'])): ?>
                    <div class="mt-auto rounded-xl bg-white/10 p-4 ring-1 ring-white/15">
                        <p class="text-[13px] font-semibold uppercase tracking-wide text-white/70">Starting from</p>
                        <p class="mt-1 text-3xl font-extrabold tracking-tight">
                            <?= price($first['price_from']) ?>
                            <?php if (!empty($first['price_to'])): ?>
                            <span class="text-[15px] font-semibold text-white/70">– <?= price($first['price_to']) ?></span>
                            <?php endif; ?>
                        </p>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</section>
