<?php
/**
 * Repair service page.
 *
 * @var array $page @var array $breadcrumbs
 *
 * The price range shown is the real configured range for the service — when
 * the admin has not set one, no price line is rendered at all.
 */
$phone      = (string) \App\Seo\SeoSettings::get('organization_phone', '');
$hasRange   = $page['price_from'] !== null && $page['price_to'] !== null;
$hasFrom    = $page['price_from'] !== null;
?>
<nav aria-label="Breadcrumb" class="shell max-w-3xl pt-5 text-sm text-ink-muted">
    <ol class="flex flex-wrap items-center gap-1.5">
        <li><a href="/" class="hover:text-electric-600">Home</a></li>
        <li aria-hidden="true" class="text-line">/</li>
        <li><a href="/#repairs" class="hover:text-electric-600">Repair Services</a></li>
        <li aria-hidden="true" class="text-line">/</li>
        <li aria-current="page" class="text-ink font-medium"><?= e($page['service_name'] ?? $page['title']) ?></li>
    </ol>
</nav>

<article class="shell max-w-3xl pb-16">
    <header class="mt-6">
        <p class="text-[12px] font-extrabold uppercase tracking-[0.18em] text-electric-600">Repair Services</p>
        <h1 class="mt-2 text-[28px] md:text-[38px] font-extrabold leading-tight tracking-tight text-ink"><?= e($page['service_name'] ?? $page['title']) ?></h1>

        <?php if (!empty($page['intro'])): ?>
        <p class="mt-3 text-[17px] leading-relaxed text-ink-muted"><?= e($page['intro']) ?></p>
        <?php endif; ?>

        <div class="mt-5 flex flex-wrap items-center gap-2.5">
            <?php if ($hasRange): ?>
            <span class="inline-flex items-center gap-2 rounded-xl border border-electric-100 bg-electric-50 px-3.5 py-2 text-[14px] font-bold text-electric-700">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" class="h-4 w-4"><path d="M12 2v20M17 6H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
                Price range: <?= price((float) $page['price_from']) ?> – <?= price((float) $page['price_to']) ?>
            </span>
            <?php elseif ($hasFrom): ?>
            <span class="inline-flex items-center gap-2 rounded-xl border border-electric-100 bg-electric-50 px-3.5 py-2 text-[14px] font-bold text-electric-700">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" class="h-4 w-4"><path d="M12 2v20M17 6H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
                From <?= price((float) $page['price_from']) ?>
            </span>
            <?php endif; ?>

            <?php if (!empty($page['area_served'])): ?>
            <span class="inline-flex items-center gap-2 rounded-xl border border-line bg-white px-3.5 py-2 text-[13px] font-semibold text-ink-muted">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" class="h-4 w-4"><path d="M12 21s-7-5.6-7-11a7 7 0 1 1 14 0c0 5.4-7 11-7 11Z"/><circle cx="12" cy="10" r="2.5"/></svg>
                Serving: <?= e($page['area_served']) ?>
            </span>
            <?php endif; ?>
        </div>
    </header>

    <div class="prose-body mt-8 text-[16px]">
        <?= $page['body'] /* sanitized on save */ ?>
    </div>

    <!-- Booking panel -->
    <aside class="mt-10 overflow-hidden rounded-2xl bg-navy-900 p-6 text-white sm:p-8">
        <div class="flex flex-wrap items-start justify-between gap-6">
            <div class="min-w-0 flex-1">
                <h2 class="text-[20px] font-extrabold tracking-tight">Book this repair</h2>
                <p class="mt-2 max-w-md text-[14px] leading-relaxed text-navy-200">
                    Call or WhatsApp us to confirm parts availability and book a slot. We'll quote the final price before any work starts.
                </p>
            </div>

            <?php if ($phone !== ''): ?>
            <a href="tel:<?= e(preg_replace('/[^0-9+]/', '', $phone)) ?>" class="btn btn-primary btn-lg shrink-0">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="h-4 w-4"><path d="M22 16.9v3a2 2 0 0 1-2.2 2 19.8 19.8 0 0 1-8.6-3.1 19.5 19.5 0 0 1-6-6A19.8 19.8 0 0 1 2.1 4.2 2 2 0 0 1 4.1 2h3a2 2 0 0 1 2 1.7c.1 1 .4 1.9.7 2.8a2 2 0 0 1-.5 2.1L8.1 9.9a16 16 0 0 0 6 6l1.3-1.2a2 2 0 0 1 2.1-.5c.9.3 1.8.6 2.8.7a2 2 0 0 1 1.7 2Z"/></svg>
                Call <?= e($phone) ?>
            </a>
            <?php endif; ?>
        </div>

        <ul class="mt-6 grid gap-3 border-t border-white/10 pt-5 text-[13px] text-navy-200 sm:grid-cols-3">
            <li class="flex gap-2.5">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="mt-0.5 h-4 w-4 shrink-0 text-gold-400"><path d="M20 6L9 17l-5-5"/></svg>
                No work starts without your go-ahead
            </li>
            <li class="flex gap-2.5">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="mt-0.5 h-4 w-4 shrink-0 text-gold-400"><path d="M20 6L9 17l-5-5"/></svg>
                Genuine parts, checked before fitting
            </li>
            <li class="flex gap-2.5">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="mt-0.5 h-4 w-4 shrink-0 text-gold-400"><path d="M20 6L9 17l-5-5"/></svg>
                Repairs carry a written warranty
            </li>
        </ul>
    </aside>
</article>
