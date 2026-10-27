<?php
/**
 * Admin — brands list.
 *
 * @var array $rows
 */
?>
<div class="flex flex-wrap items-center justify-between gap-3">
    <div>
        <p class="text-[12px] font-extrabold uppercase tracking-[0.18em] text-electric-600">Catalogue</p>
        <h2 class="mt-1 text-[24px] font-extrabold tracking-tight text-ink">Brands</h2>
    </div>
    <a href="/admin/brands/create" class="btn btn-primary btn-sm">Add brand</a>
</div>

<div class="card mt-5 overflow-hidden">
    <?php if ($rows === []): ?>
    <div class="p-12 text-center">
        <p class="text-[15px] font-bold text-ink">No brands yet</p>
        <a href="/admin/brands/create" class="btn btn-primary btn-sm mt-4">Add the first brand</a>
    </div>
    <?php else: ?>
    <div class="overflow-x-auto">
        <table class="data-table">
            <caption class="sr-only">All brands</caption>
            <thead>
                <tr>
                    <th scope="col">Brand</th>
                    <th scope="col">SEO title</th>
                    <th scope="col">Status</th>
                    <th scope="col"><span class="sr-only">Actions</span></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($rows as $r): ?>
                <tr>
                    <td data-label="Brand">
                        <p class="font-bold text-ink"><?= e($r['name']) ?></p>
                        <p class="mt-0.5 font-mono text-[12px] text-ink-muted">/brand/<?= e($r['slug']) ?></p>
                    </td>
                    <td data-label="SEO title">
                        <?php if (!empty($r['seo_title'])): ?>
                        <span class="badge bg-jade-50 px-2.5 py-1 !text-[11px] text-jade-700">Custom</span>
                        <?php else: ?>
                        <span class="badge badge-soft px-2.5 py-1 !text-[11px]">Auto</span>
                        <?php endif; ?>
                    </td>
                    <td data-label="Status">
                        <?php if ((string) $r['status'] === 'active'): ?>
                        <span class="badge bg-jade-50 px-2.5 py-1 !text-[11px] text-jade-700">Active</span>
                        <?php else: ?>
                        <span class="badge badge-soft px-2.5 py-1 !text-[11px]">Hidden</span>
                        <?php endif; ?>
                    </td>
                    <td data-label="" class="whitespace-nowrap text-right">
                        <a href="/admin/brands/<?= (int) $r['id'] ?>/edit" class="btn btn-ghost btn-sm">Edit</a>
                        <a href="/brand/<?= e($r['slug']) ?>" target="_blank" rel="noopener" class="btn btn-ghost btn-sm">View ↗</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>
</div>
