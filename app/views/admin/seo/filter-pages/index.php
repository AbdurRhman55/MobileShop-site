<?php
/**
 * Admin — curated filter landing pages.
 *
 * @var array $rows
 *
 * Ad-hoc filter/sort URLs on the shop are noindex with canonical → parent.
 * Only these curated /shop/{slug} pages are eligible for indexing.
 */
use App\Core\Database;

// One-shot lookups so the table can show human-readable names.
$catNames   = [];
$brandNames = [];
foreach (Database::fetchAll('SELECT id, name FROM categories') as $c) { $catNames[(int) $c['id']] = $c['name']; }
foreach (Database::fetchAll('SELECT id, name FROM brands') as $b)     { $brandNames[(int) $b['id']] = $b['name']; }
?>
<div class="flex flex-wrap items-center justify-between gap-3">
    <div>
        <p class="text-[12px] font-extrabold uppercase tracking-[0.18em] text-electric-600">Search</p>
        <h2 class="mt-1 text-[24px] font-extrabold tracking-tight text-ink">Filter landing pages</h2>
    </div>
    <a href="/admin/seo/filter-pages/create" class="btn btn-primary btn-sm">Add filter page</a>
</div>

<p class="mt-4 max-w-3xl rounded-xl border border-line bg-surface px-4 py-3 text-[12.5px] leading-relaxed text-ink-muted">
    Ad-hoc filter/sort URLs on the shop are <strong class="text-ink">noindex with canonical → parent</strong>. Only these
    curated <code class="rounded bg-surface-muted px-1 py-0.5">/shop/{slug}</code> pages are eligible for indexing.
</p>

<div class="card mt-5 overflow-hidden">
    <?php if ($rows === []): ?>
    <div class="p-12 text-center">
        <span class="mx-auto grid h-14 w-14 place-items-center rounded-2xl bg-surface-muted text-ink-muted">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" class="h-7 w-7"><path d="M3 5h18M6 12h12M10 19h4"/></svg>
        </span>
        <p class="mt-4 text-[15px] font-bold text-ink">No filter pages yet</p>
        <a href="/admin/seo/filter-pages/create" class="btn btn-primary btn-sm mt-4">Add the first filter page</a>
    </div>
    <?php else: ?>
    <div class="overflow-x-auto">
        <table class="data-table">
            <caption class="sr-only">Curated filter landing pages</caption>
            <thead>
                <tr>
                    <th scope="col">Page</th>
                    <th scope="col">Scope</th>
                    <th scope="col">Filters</th>
                    <th scope="col">Indexing</th>
                    <th scope="col">Status</th>
                    <th scope="col"><span class="sr-only">Actions</span></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($rows as $r): ?>
                <tr>
                    <td data-label="Page">
                        <p class="font-bold text-ink"><?= e($r['name']) ?></p>
                        <p class="mt-0.5 font-mono text-[12px] text-ink-muted">/shop/<?= e($r['slug']) ?></p>
                    </td>
                    <td data-label="Scope" class="text-[13px] text-ink-muted">
                        <?= e($catNames[(int) ($r['category_id'] ?? 0)] ?? 'All categories') ?>
                        <?php if (!empty($r['brand_id'])): ?>
                        <br><span class="text-[12px]">+ <?= e($brandNames[(int) $r['brand_id']] ?? 'brand') ?></span>
                        <?php endif; ?>
                    </td>
                    <td data-label="Filters">
                        <?php
                        $filters = json_decode((string) $r['filters'], true) ?: [];
                        $bits = [];
                        foreach ($filters as $k => $v) {
                            $bits[] = $k . '=' . (is_array($v) ? implode('|', $v) : $v);
                        }
                        ?>
                        <div class="flex max-w-[14rem] flex-wrap gap-1">
                            <?php foreach (array_slice($bits, 0, 4) as $bit): ?>
                            <span class="rounded bg-surface-muted px-1.5 py-0.5 font-mono text-[11px] text-ink-muted"><?= e($bit) ?></span>
                            <?php endforeach; ?>
                            <?php if (count($bits) > 4): ?>
                            <span class="text-[11px] text-ink-muted">+<?= count($bits) - 4 ?></span>
                            <?php endif; ?>
                        </div>
                    </td>
                    <td data-label="Indexing">
                        <?php if ((int) $r['is_indexable'] === 1 && $r['status'] === 'published'): ?>
                        <span class="badge bg-jade-50 px-2.5 py-1 !text-[11px] text-jade-700">index,follow</span>
                        <?php else: ?>
                        <span class="badge bg-gold-100 px-2.5 py-1 !text-[11px] text-navy-900">noindex</span>
                        <?php endif; ?>
                    </td>
                    <td data-label="Status">
                        <?php if ($r['status'] === 'published'): ?>
                        <span class="badge bg-jade-50 px-2.5 py-1 !text-[11px] text-jade-700">Published</span>
                        <?php else: ?>
                        <span class="badge badge-soft px-2.5 py-1 !text-[11px]">Draft</span>
                        <?php endif; ?>
                    </td>
                    <td data-label="" class="whitespace-nowrap text-right">
                        <a href="/admin/seo/filter-pages/<?= (int) $r['id'] ?>/edit" class="btn btn-ghost btn-sm">Edit</a>
                        <?php if ($r['status'] === 'published'): ?>
                        <a href="/shop/<?= e($r['slug']) ?>" target="_blank" rel="noopener" class="btn btn-ghost btn-sm">View ↗</a>
                        <?php endif; ?>
                        <form method="post" action="/admin/seo/filter-pages/<?= (int) $r['id'] ?>/delete" class="inline"
                              onsubmit="return confirm('Delete this filter page? Its URL will be redirected.');">
                            <?= \App\Core\Csrf::field() ?>
                            <button type="submit" class="btn btn-ghost btn-sm text-flame-600 hover:bg-flame-50">Delete</button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>
</div>
