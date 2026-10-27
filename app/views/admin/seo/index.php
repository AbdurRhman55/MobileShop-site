<?php
/**
 * Admin — SEO overview.
 *
 * @var array $settings @var array $auditSummary @var int $redirectCount
 * @var int $filterPageCount @var bool $feedEnabled @var string $sitemapUrl
 * @var string $robotsTxt
 */
$issues = 0;
foreach ($auditSummary as $r) { $issues += (int) $r['count']; }
?>
<div class="flex flex-wrap items-center justify-between gap-3">
    <div>
        <p class="text-[12px] font-extrabold uppercase tracking-[0.18em] text-electric-600">Search</p>
        <h2 class="mt-1 text-[24px] font-extrabold tracking-tight text-ink">SEO control centre</h2>
    </div>
    <a href="/admin/seo/audit" class="btn btn-primary btn-sm">Open audit</a>
</div>

<!-- ============================== Health tiles ============================== -->
<div class="mt-5 grid gap-4 md:grid-cols-3">
    <div class="card p-5">
        <p class="text-[11.5px] font-bold uppercase tracking-wide text-ink-muted">SEO health</p>
        <p class="mt-2 text-[34px] font-extrabold leading-none tracking-tight <?= $issues > 0 ? 'text-gold-500' : 'text-jade-600' ?>">
            <?= $issues ?>
        </p>
        <p class="mt-1.5 text-[13px] text-ink-muted">open audit issue<?= $issues === 1 ? '' : 's' ?></p>
        <a href="/admin/seo/audit" class="btn btn-ghost btn-sm mt-4">Review issues →</a>
    </div>

    <div class="card p-5">
        <p class="text-[11.5px] font-bold uppercase tracking-wide text-ink-muted">Canonical domain</p>
        <p class="mt-2 break-all font-mono text-[14px] font-bold text-ink"><?= e($settings['canonical_domain'] ?? 'NOT SET') ?></p>
        <p class="mt-2 text-[12.5px] leading-relaxed text-ink-muted">Every canonical URL, sitemap and feed is built from this domain — never the request host.</p>
        <a href="/admin/seo/settings" class="btn btn-ghost btn-sm mt-4">Edit settings →</a>
    </div>

    <div class="card p-5">
        <p class="text-[11.5px] font-bold uppercase tracking-wide text-ink-muted">Indexing</p>
        <?php if (($settings['robots_index_site'] ?? '1') === '1'): ?>
        <p class="mt-2 flex items-center gap-2 text-[15px] font-extrabold text-jade-600">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" class="h-4.5 w-4.5"><path d="M20 6 9 17l-5-5"/></svg>
            Site is indexable
        </p>
        <?php else: ?>
        <p class="mt-2 flex items-center gap-2 text-[15px] font-extrabold text-gold-600">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" class="h-4.5 w-4.5"><path d="M12 9v4M12 17h.01M10.3 3.9 1.8 18a2 2 0 0 0 1.7 3h17a2 2 0 0 0 1.7-3L13.7 3.9a2 2 0 0 0-3.4 0z"/></svg>
            Staging guard active
        </p>
        <p class="mt-1.5 text-[12.5px] text-ink-muted">The whole site is noindexed.</p>
        <?php endif; ?>
        <p class="mt-2 text-[12.5px] text-ink-muted">Sitemap: <a href="<?= e($sitemapUrl) ?>" target="_blank" rel="noopener" class="break-all font-semibold text-electric-600 hover:text-electric-700"><?= e($sitemapUrl) ?></a></p>
        <a href="/admin/seo/sitemap" class="btn btn-ghost btn-sm mt-4">Sitemap status →</a>
    </div>
</div>

<!-- ============================== Tool tiles ============================== -->
<div class="mt-5 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
    <?php
    $tiles = [
        ['/admin/seo/settings',        'Global SEO settings',    'Homepage meta, defaults, organization, verification codes, GA4.'],
        ['/admin/seo/audit',           'SEO audit reports',      $issues . ' open issue' . ($issues === 1 ? '' : 's') . ' across metadata, images, links and product data.'],
        ['/admin/seo/redirects',       'Redirect manager',       $redirectCount . ' active redirect' . ($redirectCount === 1 ? '' : 's') . '. Slug changes create 301s automatically.'],
        ['/admin/seo/filter-pages',    'Filter landing pages',   $filterPageCount . ' published indexable filter page' . ($filterPageCount === 1 ? '' : 's') . '.'],
        ['/admin/seo/sitemap',         'XML sitemaps',           'Preview the sitemap index, flush the cache, grab the submission URL.'],
        ['/admin/seo/feed',            'Merchant feed',          $feedEnabled ? 'Product feed is enabled.' : 'Product feed is disabled.'],
    ];
    foreach ($tiles as [$href, $title, $desc]): ?>
    <a href="<?= e($href) ?>" class="card group block p-5 transition hover:border-electric-300 hover:shadow-soft">
        <div class="flex items-start justify-between gap-3">
            <h3 class="text-[15px] font-extrabold tracking-tight text-ink"><?= e($title) ?></h3>
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" aria-hidden="true"
                 class="h-4 w-4 shrink-0 text-ink-muted transition group-hover:translate-x-0.5 group-hover:text-electric-600">
                <path d="M5 12h14M13 6l6 6-6 6"/>
            </svg>
        </div>
        <p class="mt-1.5 text-[13px] leading-relaxed text-ink-muted"><?= e($desc) ?></p>
    </a>
    <?php endforeach; ?>
</div>

<!-- ============================ robots.txt ============================ -->
<section class="card mt-5 overflow-hidden" aria-labelledby="robots-heading">
    <div class="flex flex-wrap items-center justify-between gap-3 border-b border-line px-5 py-4">
        <h2 id="robots-heading" class="text-[15px] font-extrabold text-ink">robots.txt preview</h2>
        <span class="badge badge-soft px-2.5 py-1">Guidance, not access control</span>
    </div>
    <pre class="overflow-x-auto bg-navy-900 p-4 text-[12.5px] leading-relaxed text-navy-100"><?= e($robotsTxt) ?></pre>
    <p class="border-t border-line px-5 py-3.5 text-[12.5px] leading-relaxed text-ink-muted">
        robots.txt guides crawlers but is <strong class="text-ink">not</strong> access control — private areas are also
        protected by authentication and <code class="rounded bg-surface-muted px-1 py-0.5">noindex</code> directives.
    </p>
</section>
