<?php
/**
 * Customer order detail.
 *
 * @var array $customer @var array $order @var array $items @var array $events
 * @var array $payment @var ?array $flash
 */
$pay        = $payment;
$methodKey  = $pay['method'];
$instructions = \App\Models\Payment::instructions((string) $order['payment_method']);
$bank       = \App\Models\Payment::bankDetails();
$meta       = \App\Models\Order::statusMeta((string) $order['status']);
$payMeta    = $pay['meta'];
$canReport  = in_array($methodKey, ['bank_transfer', 'wallet'], true)
    && $pay['status'] === 'pending'
    && empty($pay['reported']);
$reported   = !empty($pay['reported']);
?>
<div class="shell py-8 sm:py-12">
    <nav aria-label="Breadcrumb" class="text-sm text-ink-muted">
        <ol class="flex flex-wrap items-center gap-1.5">
            <li><a href="/account" class="hover:text-electric-600">My account</a></li>
            <li aria-hidden="true" class="text-line">/</li>
            <li aria-current="page" class="font-medium text-ink">Order <?= e($order['reference']) ?></li>
        </ol>
    </nav>

    <header class="mt-5 flex flex-wrap items-start justify-between gap-4">
        <div>
            <div class="flex items-center gap-3 mb-2">
                <img src="/img/logo.png" alt="Lala Mobiles" class="h-9 w-auto object-contain">
                <span class="text-[11px] font-extrabold uppercase tracking-[0.2em] text-electric-600">Official Store Order</span>
            </div>
            <h1 class="mt-1 flex flex-wrap items-center gap-2.5 text-[28px] font-extrabold tracking-tight text-ink md:text-3xl">
                <?= e($order['reference']) ?>
                <span class="badge <?= e($meta['bg']) ?> <?= e($meta['fg']) ?> px-2.5 py-1 !text-[11px]"><?= e($meta['label']) ?></span>
                <span class="badge <?= e($payMeta['bg']) ?> <?= e($payMeta['fg']) ?> px-2.5 py-1 !text-[11px]">Payment <?= e($pay['label']) ?></span>
            </h1>
            <p class="mt-1.5 text-[13px] text-ink-muted">Placed <?= e(date('d M Y, H:i', strtotime((string) $order['created_at']))) ?> · Account: <?= e($customer['email']) ?></p>
        </div>
        <div class="flex flex-wrap gap-2">
            <a href="/account/orders/<?= (int) $order['id'] ?>/invoice" class="btn btn-primary btn-sm">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="h-4 w-4"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><path d="M14 2v6h6M16 13H8M16 17H8M10 9H8"/></svg>
                Print / save invoice
            </a>
            <a href="/account" class="btn btn-ghost btn-sm">Back to account</a>
        </div>
    </header>

    <?php if (!empty($flash)): ?>
    <div class="mt-5 flex gap-2.5 rounded-xl px-4 py-3 text-[13px] font-semibold <?= ($flash['type'] ?? 'success') === 'error' ? 'border border-danger-200 bg-danger-50 text-danger-800' : 'border border-jade-200 bg-jade-50 text-jade-800' ?>" role="status">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" class="mt-0.5 h-4 w-4 shrink-0"><?= ($flash['type'] ?? 'success') === 'error' ? '<circle cx="12" cy="12" r="9"/><path d="M12 8v5M12 16h.01"/>' : '<path d="M20 6L9 17l-5-5"/>' ?></svg>
        <span><?= e($flash['message']) ?></span>
    </div>
    <?php endif; ?>

    <div class="mt-7 grid gap-5 lg:grid-cols-12">
        <!-- Items + totals -->
        <section class="card overflow-hidden lg:col-span-7">
            <div class="flex items-center justify-between border-b border-line px-6 py-4">
                <h2 class="text-[15px] font-extrabold text-ink">Items</h2>
                <span class="badge badge-soft px-2 py-1 !text-[11px]"><?= count($items) ?> item<?= count($items) === 1 ? '' : 's' ?></span>
            </div>
            <ul class="divide-y divide-line px-6">
                <?php foreach ($items as $it): ?>
                <li class="flex items-start justify-between gap-4 py-3.5">
                    <span class="min-w-0 flex-1">
                        <?php if (!empty($it['product_slug'])): ?>
                        <a href="/product/<?= e($it['product_slug']) ?>" class="text-[14px] font-semibold text-ink hover:text-electric-700 hover:underline"><?= e($it['item_name']) ?></a>
                        <?php else: ?>
                        <span class="block text-[14px] font-semibold text-ink"><?= e($it['item_name']) ?></span>
                        <?php endif; ?>
                        <?php if (!empty($it['sku'])): ?><span class="mt-0.5 block text-[11.5px] text-ink-muted">SKU: <?= e($it['sku']) ?></span><?php endif; ?>
                        <span class="mt-0.5 block text-[12.5px] text-ink-muted"><?= (int) $it['quantity'] ?> × <?= price((float) $it['unit_price']) ?></span>
                    </span>
                    <span class="shrink-0 whitespace-nowrap text-[14px] font-bold tabular-nums text-ink"><?= price((float) $it['unit_price'] * (int) $it['quantity']) ?></span>
                </li>
                <?php endforeach; ?>
            </ul>
            <dl class="space-y-2.5 border-t border-line px-6 py-4 text-[14px]">
                <div class="flex justify-between"><dt class="text-ink-muted">Subtotal</dt><dd class="font-bold tabular-nums"><?= price((float) $order['subtotal']) ?></dd></div>
                <div class="flex justify-between"><dt class="text-ink-muted">Delivery</dt><dd class="font-bold tabular-nums"><?= (float) $order['shipping'] > 0 ? price((float) $order['shipping']) : '<span class="text-jade-600">Free</span>' ?></dd></div>
                <div class="flex items-baseline justify-between border-t border-line pt-3"><dt class="text-[15px] font-bold text-ink">Total</dt><dd class="text-[22px] font-extrabold tabular-nums tracking-tight text-ink"><?= price((float) $order['total']) ?></dd></div>
                <div class="flex items-baseline justify-between"><dt class="text-ink-muted">Paid so far</dt><dd class="font-bold tabular-nums"><?= price($pay['paid']) ?></dd></div>
                <div class="flex items-baseline justify-between"><dt class="text-ink-muted">Balance outstanding</dt><dd class="font-bold tabular-nums"><?= price($pay['balance']) ?></dd></div>
            </dl>
        </section>

        <!-- Payment, delivery, timeline -->
        <aside class="space-y-5 lg:col-span-5">
            <!-- Payment -->
            <section class="card p-6" id="payment">
                <div class="flex items-center justify-between">
                    <h2 class="text-[15px] font-extrabold text-ink">Payment</h2>
                    <span class="badge <?= e($payMeta['bg']) ?> <?= e($payMeta['fg']) ?> px-2.5 py-1 !text-[11px]"><?= e($pay['label']) ?></span>
                </div>
                <dl class="mt-4 space-y-2.5 text-[13.5px]">
                    <div class="flex justify-between"><dt class="text-ink-muted">Method</dt><dd class="font-semibold text-ink"><?= e($pay['method_label']) ?></dd></div>
                    <?php if ($pay['reference'] !== ''): ?><div class="flex justify-between gap-3"><dt class="text-ink-muted">Reference</dt><dd class="break-all font-mono text-[12.5px] font-bold text-ink"><?= e($pay['reference']) ?></dd></div><?php endif; ?>
                    <?php if (!empty($order['payment_screenshot'])): ?><div class="flex justify-between items-center gap-3"><dt class="text-ink-muted">Payment proof</dt><dd><a href="<?= e($order['payment_screenshot']) ?>" target="_blank" rel="noopener noreferrer" class="font-bold text-electric-700 hover:underline">View Screenshot &nearr;</a></dd></div><?php endif; ?>
                    <?php if ($reported): ?><div class="flex justify-between"><dt class="text-ink-muted">Reported</dt><dd class="font-semibold"><?= e(date('d M Y, H:i', strtotime((string) $order['payment_reported_at']))) ?></dd></div><?php endif; ?>
                    <?php if ($pay['paid_at'] !== null): ?><div class="flex justify-between"><dt class="text-ink-muted">Paid / verified</dt><dd class="font-semibold"><?= e(date('d M Y, H:i', strtotime((string) $pay['paid_at']))) ?></dd></div><?php endif; ?>
                </dl>

                <?php
                $waAccMsg = rawurlencode("Salam Lala Mobiles! Regarding Order #{$order['reference']} for " . price((float)$order['total']) . ". Customer: {$order['customer_name']}. Here is my payment verification / query.");
                $waAccUrl = "https://wa.me/923005970504?text={$waAccMsg}";
                ?>
                <a href="<?= e($waAccUrl) ?>" target="_blank" rel="noopener noreferrer"
                   class="mt-3.5 flex w-full items-center justify-center gap-2 rounded-xl bg-[#25D366] px-4 py-2.5 text-[13px] font-extrabold text-white shadow-sm hover:bg-[#20ba59]">
                    <svg viewBox="0 0 24 24" fill="currentColor" class="h-4 w-4"><path d="M12.04 2c-5.46 0-9.91 4.45-9.91 9.91 0 1.75.46 3.45 1.32 4.95L2.05 22l5.25-1.38c1.45.79 3.08 1.21 4.74 1.21 5.46 0 9.91-4.45 9.91-9.91 0-2.65-1.03-5.14-2.9-7.01A9.82 9.82 0 0 0 12.04 2zm.01 1.67c4.54 0 8.24 3.7 8.24 8.24 0 2.2-.86 4.27-2.42 5.82-1.55 1.56-3.62 2.42-5.82 2.42-1.42 0-2.81-.37-4.04-1.08l-.29-.17-3.01.79.8-2.93-.19-.3a8.21 8.21 0 0 1-1.26-4.31c0-4.54 3.7-8.24 8.24-8.24zm4.52 11.66c-.25-.13-1.47-.72-1.7-.81-.23-.08-.39-.13-.56.13-.17.25-.64.81-.79.97-.14.17-.29.19-.54.06-.25-.13-1.06-.39-2.02-1.25-.75-.67-1.26-1.5-1.4-1.76-.15-.25-.02-.39.11-.51.11-.11.25-.29.38-.44.13-.14.17-.25.25-.42.08-.17.04-.31-.02-.44-.06-.13-.56-1.35-.77-1.85-.2-.49-.41-.42-.56-.43h-.48c-.17 0-.44.06-.67.31-.23.25-.88.86-.88 2.1 0 1.24.9 2.44 1.03 2.61.13.17 1.77 2.7 4.29 3.79.6.26 1.07.41 1.43.53.6.19 1.15.16 1.58.1.48-.07 1.47-.6 1.68-1.18.21-.58.21-1.08.15-1.18-.06-.1-.23-.17-.48-.29z"/></svg>
                    Send Proof on WhatsApp (+92 300 5970504)
                </a>

                <?php if ($pay['status'] === 'pending'): ?>
                <div class="mt-4 rounded-xl border border-line bg-surface px-3.5 py-3">
                    <p class="text-[12px] font-bold uppercase tracking-wide text-ink"><?= e($instructions['title']) ?></p>
                    <ul class="mt-2 space-y-2 text-[12.5px] leading-relaxed text-ink">
                        <?php foreach ($instructions['lines'] as $line): ?><li class="flex gap-2"><span class="mt-1 h-1.5 w-1.5 shrink-0 rounded-full bg-electric-600"></span><span><?= e($line) ?></span></li><?php endforeach; ?>
                    </ul>
                    <?php if ($methodKey === 'bank_transfer' && $bank['bank'] !== '' && $bank['account'] !== ''): ?>
                    <dl class="mt-3 space-y-1.5 border-t border-line pt-2.5 text-[12px]">
                        <div class="flex justify-between"><dt class="text-ink-muted">Bank</dt><dd class="font-semibold"><?= e($bank['bank']) ?></dd></div>
                        <div class="flex justify-between"><dt class="text-ink-muted">Title</dt><dd class="font-semibold"><?= e($bank['title']) ?></dd></div>
                        <div class="flex justify-between gap-3"><dt class="text-ink-muted">Account</dt><dd class="break-all font-mono font-bold"><?= e($bank['account']) ?></dd></div>
                    </dl>
                    <?php elseif ($methodKey === 'wallet' && $bank['wallet'] !== ''): ?>
                    <p class="mt-3 border-t border-line pt-2.5 text-center"><span class="text-[11px] text-ink-muted">Wallet</span><br><span class="font-mono text-[14px] font-extrabold"><?= e($bank['wallet']) ?></span></p>
                    <?php endif; ?>
                </div>
                <?php endif; ?>

                <?php if ($canReport): ?>
                <form method="post" action="/account/orders/<?= (int) $order['id'] ?>/payment" class="mt-4 space-y-3 rounded-xl border border-electric-200 bg-electric-50/40 p-3.5">
                    <?= \App\Core\Csrf::field() ?>
                    <p class="text-[12.5px] font-bold text-ink">I have sent the payment — submit for verification</p>
                    <div class="grid gap-2 sm:grid-cols-2">
                        <div>
                            <label class="mb-1 block text-[11.5px] font-bold text-ink">Amount sent (Rs)</label>
                            <input name="amount" type="number" inputmode="decimal" min="1" step="0.01" required class="input h-9" value="<?= e(number_format($pay['balance'], 2, '.', '')) ?>">
                        </div>
                        <div>
                            <label class="mb-1 block text-[11.5px] font-bold text-ink">Transaction / reference <span class="text-flame-500">*</span></label>
                            <input name="reference" type="text" maxlength="120" required class="input h-9" placeholder="e.g. TID123456789">
                        </div>
                    </div>
                    <div>
                        <label class="mb-1 block text-[11.5px] font-bold text-ink">Note (optional)</label>
                        <input name="note" type="text" maxlength="200" class="input h-9" placeholder="Sender name / branch">
                    </div>
                    <button type="submit" class="btn btn-primary btn-sm w-full">Submit for verification</button>
                    <p class="text-center text-[11px] text-ink-muted">This does not mark the order as paid — our team must approve it.</p>
                </form>
                <?php elseif ($reported && $pay['status'] === 'pending'): ?>
                <p class="mt-4 rounded-xl border border-gold-300/70 bg-gold-50 px-3.5 py-2.5 text-[12.5px] font-semibold text-amber-900">
                    Payment reference submitted — awaiting team verification. It stays pending until approved.
                </p>
                <?php elseif ($pay['status'] === 'paid'): ?>
                <p class="mt-4 rounded-xl border border-jade-200 bg-jade-50 px-3.5 py-2.5 text-[12.5px] font-semibold text-jade-800">
                    Payment verified and recorded. Thank you.
                </p>
                <?php endif; ?>
            </section>

            <!-- Delivery -->
            <section class="card p-6">
                <h2 class="text-[15px] font-extrabold text-ink">Delivery</h2>
                <dl class="mt-4 space-y-3 text-[13.5px]">
                    <div><dt class="text-[11px] font-bold uppercase tracking-wide text-ink-muted">Deliver to</dt><dd class="mt-0.5 leading-relaxed text-ink"><?= e($order['customer_name']) ?><br><?= nl2br(e($order['address'])) ?><br><?= e($order['city']) ?></dd></div>
                    <div><dt class="text-[11px] font-bold uppercase tracking-wide text-ink-muted">Phone</dt><dd class="mt-0.5 font-semibold text-ink"><?= e($order['phone']) ?></dd></div>
                    <div><dt class="text-[11px] font-bold uppercase tracking-wide text-ink-muted">Email</dt><dd class="mt-0.5 break-all font-semibold text-ink"><?= e($order['email'] ?? $customer['email']) ?></dd></div>
                </dl>
            </section>

            <!-- Timeline -->
            <section class="card p-6">
                <h2 class="text-[15px] font-extrabold text-ink">Order timeline</h2>
                <ol class="mt-4 space-y-4">
                    <?php if ($events === []): ?>
                    <li class="text-[13px] text-ink-muted">Timeline will appear as the order moves through the workflow.</li>
                    <?php else: foreach ($events as $event): ?>
                    <li class="flex gap-3">
                        <span class="mt-1 grid h-6 w-6 shrink-0 place-items-center rounded-full <?= match ($event['actor']) { 'admin' => 'bg-electric-100 text-electric-700', 'customer' => 'bg-jade-100 text-jade-700', default => 'bg-surface-muted text-ink-muted' } ?>">
                            <?php if ($event['event_type'] === 'payment'): ?><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="h-3.5 w-3.5"><rect x="2" y="5" width="20" height="14" rx="2"/><path d="M2 10h20"/></svg><?php elseif ($event['event_type'] === 'status'): ?><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="h-3.5 w-3.5"><path d="M20 6L9 17l-5-5"/></svg><?php elseif ($event['actor'] === 'customer'): ?><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="h-3.5 w-3.5"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg><?php else: ?><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="h-3.5 w-3.5"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg><?php endif; ?>
                        </span>
                        <span class="min-w-0 flex-1">
                            <span class="block text-[13px] leading-relaxed text-ink"><?= e($event['note'] ?? 'Update') ?></span>
                            <span class="mt-0.5 block text-[11.5px] text-ink-muted"><?= e(date('d M Y, H:i', strtotime((string) $event['created_at']))) ?> · <?= e(ucfirst($event['actor'])) ?></span>
                        </span>
                    </li>
                    <?php endforeach; endif; ?>
                </ol>
            </section>
        </aside>
    </div>
</div>
