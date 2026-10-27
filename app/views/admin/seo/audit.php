<?php
/**
 * Admin — SEO audit report.
 *
 * @var array $reports  SeoAudit::reports(): [key => ['label','description','rows']]
 *                      each row: ['entity','name','issue','link']
 */
$total = 0;
foreach ($reports as $r) { $total += count($r['rows']); }
$passCount = count(array_filter($reports, fn($r) => $r['rows'] === []));
?>
<div class="flex flex-wrap items-center justify-between gap-3">
    <div>
        <p class="text-[12px] font-extrabold uppercase tracking-[0.18em] text-electric-600">Search</p>
        <h2 class="mt-1 text-[24px] font-extrabold tracking-tight text-ink">SEO audit</h2>
        <p class="mt-1.5 text-[13.5px] text-ink-muted">
            <?= $total ?> open issue<?= $total === 1 ? '' : 's' ?> across <?= count($reports) ?> checks
            (<?= $passCount ?> passing). Runs on every page load — no cron needed.
        </p>
    </div>
    <span class="badge <?= $total > 0 ? 'bg-gold-100 text-navy-900' : 'bg-jade-50 text-jade-700' ?> px-3 py-1.5">
        <?= $total > 0 ? $total . ' to fix' : 'All checks pass' ?>
    </span>
</div>

<p class="mt-4 max-w-3xl rounded-xl border border-line bg-surface px-4 py-3 text-[12.5px] leading-relaxed text-ink-muted">
    Each report offers its own CSV export for spreadsheets / issue tracking. Auto-generated titles and descriptions are
    <em class="text-ink not-italic">not</em> flagged — only genuinely missing or problematic ones.
</p>

<div class="mt-5 space-y-5">
    <?php foreach ($reports as $key => $r): $count = count($r['rows']); ?>
    <section id="<?= e($key) ?>" class="card overflow-hidden scroll-mt-24">
        <header class="flex flex-wrap items-center justify-between gap-3 border-b border-line px-5 py-4 <?= $count === 0 ? 'bg-jade-50/60' : 'bg-gold-50/70' ?>">
            <div class="flex min-w-0 items-center gap-3">
                <span class="grid h-7 w-7 shrink-0 place-items-center rounded-full text-[13px] font-extrabold
                    <?= $count === 0 ? 'bg-jade-100 text-jade-700' : 'bg-gold-100 text-navy-900' ?>"
                    aria-hidden="true">
                    <?php if ($count === 0): ?>
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" class="h-4 w-4"><path d="M20 6 9 17l-5-5"/></svg>
                    <?php else: ?><?= $count ?><?php endif; ?>
                </span>
                <div class="min-w-0">
                    <h3 class="text-[15px] font-extrabold tracking-tight text-ink"><?= e($r['label']) ?></h3>
                    <p class="mt-0.5 text-[12.5px] text-ink-muted"><?= e($r['description']) ?></p>
                </div>
            </div>
            <div class="flex shrink-0 items-center gap-3">
                <span class="text-[11.5px] font-extrabold uppercase tracking-wide <?= $count === 0 ? 'text-jade-700' : 'text-gold-700' ?>">
                    <?= $count === 0 ? 'Pass' : 'Needs work' ?>
                </span>
                <a href="/admin/seo/audit/<?= e($key) ?>/export" class="btn btn-ghost btn-sm">CSV ↓</a>
            </div>
        </header>

        <?php if ($count > 0): ?>
        <ul class="divide-y divide-line">
            <?php foreach (array_slice($r['rows'], 0, 50) as $row): ?>
            <li class="flex flex-wrap items-start justify-between gap-3 px-5 py-3">
                <div class="min-w-0 flex-1">
                    <p class="break-words text-[14px] text-ink">
                        <span class="mr-2 inline-block align-middle rounded bg-surface-muted px-1.5 py-0.5 text-[10.5px] font-bold uppercase tracking-wide text-ink-muted">
                            <?= e($row['entity'] ?? '') ?>
                        </span>
                        <?= e($row['name'] ?? '') ?>
                    </p>
                    <?php if (!empty($row['issue'])): ?>
                    <p class="mt-1 text-[12.5px] text-ink-muted"><?= e($row['issue']) ?></p>
                    <?php endif; ?>
                </div>
                <?php if (!empty($row['link'])): ?>
                <a href="<?= e($row['link']) ?>" class="btn btn-ghost btn-sm shrink-0">Fix →</a>
                <?php endif; ?>
            </li>
            <?php endforeach; ?>
        </ul>
        <?php if ($count > 50): ?>
        <p class="border-t border-line px-5 py-3 text-[12.5px] text-ink-muted">
            Showing the first 50 of <?= $count ?> issues — use the CSV export for the full list.
        </p>
        <?php endif; ?>
        <?php endif; ?>
    </section>
    <?php endforeach; ?>
</div>
