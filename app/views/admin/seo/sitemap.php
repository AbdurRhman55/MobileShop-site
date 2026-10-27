<?php
/**
 * Admin — XML sitemap status.
 *
 * @var string $preview @var string $sitemapUrl
 *
 * Views render in the global namespace, so reference Seo classes by FQCN.
 */
use App\Seo\Url;
?>
<div class="flex flex-wrap items-center justify-between gap-3">
    <div>
        <p class="text-[12px] font-extrabold uppercase tracking-[0.18em] text-electric-600">Search</p>
        <h2 class="mt-1 text-[24px] font-extrabold tracking-tight text-ink">XML sitemaps</h2>
    </div>
    <form method="post" action="/admin/seo/sitemap/flush">
        <?= \App\Core\Csrf::field() ?>
        <button type="submit" class="btn btn-primary btn-sm">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.1" class="h-4 w-4"><path d="M3 12a9 9 0 0 1 15.5-6.2M21 12a9 9 0 0 1-15.5 6.2M18 3v5h-5M6 21v-5h5"/></svg>
            Flush &amp; regenerate
        </button>
    </form>
</div>

<div class="mt-5 space-y-5">
    <!-- Submission URL -->
    <section class="card p-5 sm:p-6" aria-labelledby="submit-heading">
        <h3 id="submit-heading" class="text-[15px] font-extrabold tracking-tight text-ink">Submission URL</h3>
        <p class="mt-3 break-all rounded-xl border border-line bg-surface px-4 py-3 font-mono text-[13.5px] font-bold text-electric-700">
            <?= e($sitemapUrl) ?>
        </p>
        <div class="mt-3 flex flex-wrap gap-3">
            <a href="<?= e($sitemapUrl) ?>" target="_blank" rel="noopener" class="btn btn-ghost btn-sm">Open sitemap ↗</a>
            <span class="self-center text-[12.5px] text-ink-muted">Submit in Google Search Console and Bing Webmaster Tools.</span>
        </div>

        <div class="mt-5 grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
            <?php
            $sections = [
                ['/sitemap-products.xml',      'Products'],
                ['/sitemap-categories.xml',    'Categories'],
                ['/sitemap-brands.xml',        'Brands'],
                ['/sitemap-pages.xml',         'Pages'],
                ['/sitemap-blog.xml',          'Blog'],
                ['/sitemap-filter-pages.xml',  'Filter pages'],
            ];
            foreach ($sections as [$path, $label]): ?>
            <a href="<?= e(Url::absolute($path)) ?>" target="_blank" rel="noopener"
               class="flex items-center justify-between gap-3 rounded-xl border border-line px-4 py-2.5 transition hover:border-electric-300 hover:bg-electric-50/40">
                <span class="text-[14px] font-bold text-ink"><?= e($label) ?></span>
                <span class="truncate font-mono text-[11.5px] text-ink-muted"><?= e($path) ?></span>
            </a>
            <?php endforeach; ?>
        </div>

        <p class="mt-4 text-[12.5px] leading-relaxed text-ink-muted">
            Each section sitemap holds up to 50,000 URLs / 50MB; beyond that the generator automatically splits into
            numbered parts referenced from the index. All URLs are absolute and built from the configured canonical
            domain — never the request host.
        </p>
    </section>

    <!-- Live preview -->
    <section class="card overflow-hidden" aria-labelledby="preview-heading">
        <div class="flex flex-wrap items-center justify-between gap-3 border-b border-line px-5 py-3.5">
            <h3 id="preview-heading" class="text-[14px] font-extrabold text-ink">Live index preview</h3>
            <span class="font-mono text-[11.5px] text-ink-muted">Content-Type: application/xml; charset=UTF-8</span>
        </div>
        <pre class="max-h-96 overflow-auto bg-navy-900 p-4 text-[12.5px] leading-relaxed text-navy-100"><?= e($preview) ?></pre>
    </section>

    <!-- How URLs are chosen -->
    <section class="card p-5 sm:p-6" aria-labelledby="choice-heading">
        <h3 id="choice-heading" class="text-[15px] font-extrabold tracking-tight text-ink">How sitemap URLs are chosen</h3>
        <ul class="mt-3 space-y-2.5 text-[13.5px] leading-relaxed text-ink-muted">
            <li class="flex gap-2.5"><span class="mt-2 h-1.5 w-1.5 shrink-0 rounded-full bg-electric-500" aria-hidden="true"></span><span>Only canonical, indexable URLs are included — no <code class="rounded bg-surface-muted px-1 py-0.5 text-ink">?variant=</code>, no filter/sort URLs, no search pages.</span></li>
            <li class="flex gap-2.5"><span class="mt-2 h-1.5 w-1.5 shrink-0 rounded-full bg-electric-500" aria-hidden="true"></span><span>Excluded: meta robots noindex, hidden categories/brands, drafts, and soft-deleted content.</span></li>
            <li class="flex gap-2.5"><span class="mt-2 h-1.5 w-1.5 shrink-0 rounded-full bg-electric-500" aria-hidden="true"></span><span>Products set to <strong class="text-ink">deleted</strong> with a 410 are removed immediately; those with a replacement 301 are dropped too.</span></li>
            <li class="flex gap-2.5"><span class="mt-2 h-1.5 w-1.5 shrink-0 rounded-full bg-electric-500" aria-hidden="true"></span><span><code class="rounded bg-surface-muted px-1 py-0.5 text-ink">&lt;lastmod&gt;</code> reflects the real content update time.</span></li>
        </ul>
    </section>
</div>
