<?php /** @var string $xml @var array $errors @var bool $enabled @var string $feedUrl @var int $count */ ?>
<div class="flex flex-wrap items-center justify-between gap-3">
    <div>
        <p class="text-[12px] font-extrabold uppercase tracking-[0.18em] text-electric-600">Search</p>
        <h2 class="mt-1 text-[24px] font-extrabold tracking-tight text-ink">Merchant feed</h2>
    </div>
    <a href="/admin/seo/settings" class="btn btn-ghost btn-sm">Feed settings →</a>
</div>

<div class="mt-5 space-y-5">
    <!-- Status -->
    <section class="card p-5 sm:p-6" aria-labelledby="feed-status">
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div class="min-w-0">
                <h3 id="feed-status" class="text-[16px] font-extrabold tracking-tight text-ink">Google Merchant Center product feed</h3>
                <p class="mt-2 flex flex-wrap items-center gap-2 text-[14px] text-ink-muted">
                    <span class="text-[11.5px] font-bold uppercase tracking-wide text-ink-muted">Status:</span>
                    <?php if ($enabled): ?>
                    <span class="badge bg-jade-50 px-2.5 py-1 !text-[11.5px] text-jade-700">Enabled</span>
                    <span class="text-ink"><?= (int) $count ?> active product<?= (int) $count === 1 ? '' : 's' ?> exported</span>
                    <?php else: ?>
                    <span class="badge bg-gold-100 px-2.5 py-1 !text-[11.5px] text-navy-900">Disabled</span>
                    <span class="text-ink">enable it in SEO settings</span>
                    <?php endif; ?>
                </p>
            </div>
        </div>

        <p class="mt-4 text-[13px] text-ink-muted">Feed URL</p>
        <p class="mt-1.5 break-all rounded-xl border border-line bg-surface px-4 py-3 font-mono text-[13.5px] font-bold text-electric-700">
            <a href="<?= e($feedUrl) ?>" target="_blank" rel="noopener" class="hover:text-electric-800"><?= e($feedUrl) ?></a>
        </p>

        <!-- Validation -->
        <div class="mt-4 rounded-xl border px-4 py-3.5 <?= $errors === [] ? 'border-jade-200 bg-jade-50' : 'border-gold-200 bg-gold-50' ?>">
            <p class="flex items-start gap-2 text-[13.5px] font-bold <?= $errors === [] ? 'text-jade-800' : 'text-gold-800' ?>">
                <?php if ($errors === []): ?>
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6" class="mt-0.5 h-4 w-4 shrink-0"><path d="M20 6 9 17l-5-5"/></svg>
                Feed XML passes validation (well-formed, required Commerce fields present).
                <?php else: ?>
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" class="mt-0.5 h-4 w-4 shrink-0"><path d="M12 9v4M12 17h.01M10.3 3.9 1.8 18a2 2 0 0 0 1.7 3h17a2 2 0 0 0 1.7-3L13.7 3.9a2 2 0 0 0-3.4 0z"/></svg>
                <?= count($errors) ?> validation warning<?= count($errors) === 1 ? '' : 's' ?>:
                <?php endif; ?>
            </p>
            <?php if ($errors !== []): ?>
            <ul class="mt-2 space-y-1 pl-6 text-[13px] text-gold-800">
                <?php foreach ($errors as $err): ?>
                <li class="list-disc"><?= e($err) ?></li>
                <?php endforeach; ?>
            </ul>
            <?php endif; ?>
        </div>

        <div class="mt-4 space-y-2 text-[12.5px] leading-relaxed text-ink-muted">
            <p><strong class="text-ink">Real data only:</strong> <code class="rounded bg-surface-muted px-1 py-0.5">gtin</code> / <code class="rounded bg-surface-muted px-1 py-0.5">mpn</code> are emitted only when genuinely supplied on the product — never fabricated. Missing identifiers use the SKU with <code class="rounded bg-surface-muted px-1 py-0.5">identifier_exists=false</code>.</p>
            <p>Prices, stock and availability come live from the database (PKR). Set up content API / scheduled fetch in Merchant Center — see docs/SEO-SETUP.md.</p>
        </div>
    </section>

    <!-- Preview -->
    <section class="card overflow-hidden" aria-labelledby="feed-preview">
        <div class="flex flex-wrap items-center justify-between gap-3 border-b border-line px-5 py-3.5">
            <h3 id="feed-preview" class="text-[14px] font-extrabold text-ink">Feed preview <span class="font-medium text-ink-muted">(first 20 items)</span></h3>
            <a href="<?= e($feedUrl) ?>?download=1" class="btn btn-ghost btn-sm">Open full feed ↗</a>
        </div>
        <pre class="max-h-[32rem] overflow-auto bg-navy-900 p-4 text-[12.5px] leading-relaxed text-navy-100"><?= e($xml) ?></pre>
    </section>
</div>
