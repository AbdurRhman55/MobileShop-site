<?php
/**
 * Admin — customer reviews queue.
 *
 * @var array $rows
 *
 * Only genuinely-submitted reviews reach this queue (rows come from the
 * reviews table joined to products). Approving one publishes it on the product
 * page and in Review/AggregateRating structured data; rejecting hides it.
 * Nothing here is generated.
 *
 * Actions hit the real POST /admin/reviews/{id}/approve|reject routes.
 */
?>
<div class="flex flex-wrap items-center justify-between gap-3">
    <div>
        <p class="text-[12px] font-extrabold uppercase tracking-[0.18em] text-electric-600">Moderation</p>
        <h2 class="mt-1 text-[24px] font-extrabold tracking-tight text-ink">Customer reviews</h2>
    </div>
    <?php
    $pending = 0;
    foreach ($rows as $r) { if ((int) $r['approved'] !== 1) { $pending++; } }
    ?>
    <span class="badge <?= $pending > 0 ? 'bg-gold-100 text-navy-900' : 'badge-soft' ?> px-3 py-1.5">
        <?= $pending ?> awaiting approval
    </span>
</div>

<div class="card mt-5 overflow-hidden">
    <?php if ($rows === []): ?>
    <div class="p-12 text-center">
        <span class="mx-auto grid h-14 w-14 place-items-center rounded-2xl bg-surface-muted text-ink-muted">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" class="h-7 w-7"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
        </span>
        <p class="mt-4 text-[15px] font-bold text-ink">No reviews yet</p>
        <p class="mx-auto mt-1.5 max-w-sm text-[13.5px] text-ink-muted">Reviews submitted by customers appear here for approval.</p>
    </div>
    <?php else: ?>
    <div class="overflow-x-auto">
        <table class="data-table">
            <caption class="sr-only">Customer reviews awaiting moderation</caption>
            <thead>
                <tr>
                    <th scope="col">Review</th>
                    <th scope="col">Product</th>
                    <th scope="col">Rating</th>
                    <th scope="col">Date</th>
                    <th scope="col">State</th>
                    <th scope="col"><span class="sr-only">Actions</span></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($rows as $r): ?>
                <tr>
                    <td data-label="Review" class="min-w-[18rem] max-w-[34rem]">
                        <p class="line-clamp-2 text-[14px] leading-snug text-ink"><?= e($r['body']) ?></p>
                        <p class="mt-1 text-[12px] font-semibold text-ink-muted"><?= e($r['customer_name'] ?: 'Anonymous') ?></p>
                    </td>
                    <td data-label="Product" class="text-ink-muted">
                        <a href="/product/<?= e($r['product_slug']) ?>" target="_blank" rel="noopener" class="hover:text-electric-600"><?= e($r['product_name']) ?></a>
                    </td>
                    <td data-label="Rating">
                        <span class="inline-flex items-center gap-1 font-bold tabular-nums text-ink">
                            <span class="text-gold-500" aria-hidden="true">★</span><?= number_format((float) $r['rating'], 1) ?>
                            <span class="sr-only">out of 5</span>
                        </span>
                    </td>
                    <td data-label="Date" class="whitespace-nowrap text-ink-muted"><?= e(date('d M Y', strtotime($r['created_at']))) ?></td>
                    <td data-label="State">
                        <?php if ((int) $r['approved'] === 1): ?>
                        <span class="badge bg-jade-50 px-2.5 py-1 !text-[11px] text-jade-700">Approved</span>
                        <?php else: ?>
                        <span class="badge bg-gold-100 px-2.5 py-1 !text-[11px] text-navy-900">Pending</span>
                        <?php endif; ?>
                    </td>
                    <td data-label="" class="whitespace-nowrap text-right">
                        <form method="post" action="/admin/reviews/<?= (int) $r['id'] ?>/approve" class="inline">
                            <?= \App\Core\Csrf::field() ?>
                            <button type="submit" <?= (int) $r['approved'] === 1 ? 'hidden' : '' ?> class="btn btn-tech btn-sm">Approve</button>
                        </form>
                        <form method="post" action="/admin/reviews/<?= (int) $r['id'] ?>/reject" class="inline">
                            <?= \App\Core\Csrf::field() ?>
                            <button type="submit" class="btn btn-ghost btn-sm"><?= (int) $r['approved'] === 1 ? 'Hide' : 'Reject' ?></button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>
</div>
