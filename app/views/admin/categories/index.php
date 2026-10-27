<?php
/**
 * Admin — categories list.
 *
 * @var array $rows
 *
 * The table collapses into stacked cards under lg via .data-table.
 */
?>
<div class="flex flex-wrap items-center justify-between gap-3">
    <div>
        <p class="text-[12px] font-extrabold uppercase tracking-[0.18em] text-electric-600">Catalogue</p>
        <h2 class="mt-1 text-[24px] font-extrabold tracking-tight text-ink">Categories</h2>
    </div>
    <a href="/admin/categories/create" class="btn btn-primary btn-sm">Add category</a>
</div>

<div class="card mt-5 overflow-hidden">
    <?php if ($rows === []): ?>
    <div class="p-12 text-center">
        <p class="text-[15px] font-bold text-ink">No categories yet</p>
        <a href="/admin/categories/create" class="btn btn-primary btn-sm mt-4">Add the first category</a>
    </div>
    <?php else: ?>
    <div class="overflow-x-auto">
        <table class="data-table">
            <caption class="sr-only">All product categories</caption>
            <thead>
                <tr>
                    <th scope="col">Category</th>
                    <th scope="col">Slug</th>
                    <th scope="col">Position</th>
                    <th scope="col">Status</th>
                    <th scope="col"><span class="sr-only">Actions</span></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($rows as $r): ?>
                <tr>
                    <td data-label="Category" class="font-bold text-ink"><?= e($r['name']) ?></td>
                    <td data-label="Slug"><code class="rounded bg-surface-muted px-1.5 py-0.5 text-[12px] text-ink-muted"><?= e($r['slug']) ?></code></td>
                    <td data-label="Position" class="tabular-nums text-ink-muted"><?= (int) ($r['position'] ?? 0) ?></td>
                    <td data-label="Status">
                        <?php if ((string) $r['status'] === 'active'): ?>
                        <span class="badge bg-jade-50 px-2.5 py-1 !text-[11px] text-jade-700">Active</span>
                        <?php else: ?>
                        <span class="badge badge-soft px-2.5 py-1 !text-[11px]">Hidden</span>
                        <?php endif; ?>
                    </td>
                    <td data-label="" class="text-right">
                        <a href="/admin/categories/<?= (int) $r['id'] ?>/edit" class="btn btn-ghost btn-sm">Edit</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>
</div>
