<?php
/**
 * Admin — CMS pages & repair services list.
 *
 * @var array $rows
 */
?>
<div class="flex flex-wrap items-center justify-between gap-3">
    <div>
        <p class="text-[12px] font-extrabold uppercase tracking-[0.18em] text-electric-600">Content</p>
        <h2 class="mt-1 text-[24px] font-extrabold tracking-tight text-ink">Pages &amp; repair services</h2>
    </div>
    <a href="/admin/pages/create" class="btn btn-primary btn-sm">Add page</a>
</div>

<div class="card mt-5 overflow-hidden">
    <?php if ($rows === []): ?>
    <div class="p-12 text-center">
        <p class="text-[15px] font-bold text-ink">No pages yet</p>
        <a href="/admin/pages/create" class="btn btn-primary btn-sm mt-4">Add the first page</a>
    </div>
    <?php else: ?>
    <div class="overflow-x-auto">
        <table class="data-table">
            <caption class="sr-only">All CMS pages and repair services</caption>
            <thead>
                <tr>
                    <th scope="col">Page</th>
                    <th scope="col">Type</th>
                    <th scope="col">Status</th>
                    <th scope="col"><span class="sr-only">Actions</span></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($rows as $r):
                    $prefix = (string) $r['page_type'] === 'repair' ? '/repair/' : '/page/';
                ?>
                <tr>
                    <td data-label="Page">
                        <p class="font-bold text-ink"><?= e($r['title']) ?></p>
                        <p class="mt-0.5 font-mono text-[12px] text-ink-muted"><?= e($prefix . $r['slug']) ?></p>
                    </td>
                    <td data-label="Type">
                        <?php if ((string) $r['page_type'] === 'repair'): ?>
                        <span class="badge bg-violet-50 px-2.5 py-1 !text-[11px] text-violet-700">Repair</span>
                        <?php else: ?>
                        <span class="badge badge-soft px-2.5 py-1 !text-[11px]">Page</span>
                        <?php endif; ?>
                    </td>
                    <td data-label="Status">
                        <?php if ((string) $r['status'] === 'published'): ?>
                        <span class="badge bg-jade-50 px-2.5 py-1 !text-[11px] text-jade-700">Published</span>
                        <?php else: ?>
                        <span class="badge badge-soft px-2.5 py-1 !text-[11px]">Draft</span>
                        <?php endif; ?>
                    </td>
                    <td data-label="" class="whitespace-nowrap text-right">
                        <a href="/admin/pages/<?= (int) $r['id'] ?>/edit" class="btn btn-ghost btn-sm">Edit</a>
                        <?php if ((string) $r['status'] === 'published'): ?>
                        <a href="<?= e($prefix . $r['slug']) ?>" target="_blank" rel="noopener" class="btn btn-ghost btn-sm">View ↗</a>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>
</div>
