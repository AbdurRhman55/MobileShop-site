<?php
/**
 * Admin — blog posts list.
 *
 * @var array $rows
 */
?>
<div class="flex flex-wrap items-center justify-between gap-3">
    <div>
        <p class="text-[12px] font-extrabold uppercase tracking-[0.18em] text-electric-600">Content</p>
        <h2 class="mt-1 text-[24px] font-extrabold tracking-tight text-ink">Blog posts</h2>
    </div>
    <a href="/admin/blog/create" class="btn btn-primary btn-sm">Add post</a>
</div>

<div class="card mt-5 overflow-hidden">
    <?php if ($rows === []): ?>
    <div class="p-12 text-center">
        <p class="text-[15px] font-bold text-ink">No posts yet</p>
        <a href="/admin/blog/create" class="btn btn-primary btn-sm mt-4">Write the first post</a>
    </div>
    <?php else: ?>
    <div class="overflow-x-auto">
        <table class="data-table">
            <caption class="sr-only">All blog posts</caption>
            <thead>
                <tr>
                    <th scope="col">Post</th>
                    <th scope="col">Published</th>
                    <th scope="col">SEO title</th>
                    <th scope="col">Status</th>
                    <th scope="col"><span class="sr-only">Actions</span></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($rows as $r): ?>
                <tr>
                    <td data-label="Post">
                        <p class="font-bold text-ink"><?= e($r['title']) ?></p>
                        <p class="mt-0.5 font-mono text-[12px] text-ink-muted">/blog/<?= e($r['slug']) ?></p>
                    </td>
                    <td data-label="Published" class="whitespace-nowrap text-ink-muted">
                        <?= $r['published_at'] ? e(date('d M Y', strtotime($r['published_at']))) : '—' ?>
                    </td>
                    <td data-label="SEO title">
                        <?php if (!empty($r['seo_title'])): ?>
                        <span class="badge bg-jade-50 px-2.5 py-1 !text-[11px] text-jade-700">Custom</span>
                        <?php else: ?>
                        <span class="badge badge-soft px-2.5 py-1 !text-[11px]">Auto</span>
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
                        <a href="/admin/blog/<?= (int) $r['id'] ?>/edit" class="btn btn-ghost btn-sm">Edit</a>
                        <?php if ((string) $r['status'] === 'published'): ?>
                        <a href="/blog/<?= e($r['slug']) ?>" target="_blank" rel="noopener" class="btn btn-ghost btn-sm">View ↗</a>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>
</div>
