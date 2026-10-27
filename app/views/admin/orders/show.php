<?php
/**
 * Admin — order detail & payment desk.
 *
 * @var array $order @var array $items @var array $events @var array $payment
 * @var array $paymentRows @var array $paymentMethods @var array $nextStatuses
 * @var string $invoiceNumber
 */
use App\Core\Csrf;
use App\Models\Order;
use App\Models\Payment;

$status = (string) $order['status'];
$meta   = Order::statusMeta($status);
$pay    = $payment ?? Payment::summary($order);
$pmeta  = $pay['meta'];
$methods = $paymentMethods ?? Payment::methods();
$balance = $pay['balance'];
?>
<a href="/admin/orders" class="btn btn-ghost btn-sm">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="h-4 w-4"><path d="M19 12H5M11 18l-6-6 6-6"/></svg>
    All orders
</a>

<header class="mt-5 flex flex-wrap items-start justify-between gap-4">
    <div>
        <p class="text-[12px] font-extrabold uppercase tracking-[0.18em] text-electric-600">Order desk</p>
        <h1 class="mt-1 flex flex-wrap items-center gap-2.5 text-[26px] font-extrabold tracking-tight text-ink">
            <?= e($order['reference']) ?>
            <span class="badge <?= e($meta['bg']) ?> <?= e($meta['fg']) ?> px-2.5 py-1.5 !text-[12px]"><?= e($meta['label']) ?></span>
            <span class="badge <?= e($pmeta['bg']) ?> <?= e($pmeta['fg']) ?> px-2.5 py-1.5 !text-[12px]">Payment <?= e($pay['label']) ?></span>
        </h1>
        <p class="mt-1.5 text-[13px] text-ink-muted">
            Placed <?= e(date('d M Y, H:i', strtotime((string)$order['created_at']))) ?>
            <?php if (!empty($order['account_customer_id'])): ?> · <a href="/admin/customers/<?= (int)$order['account_customer_id'] ?>" class="text-electric-700 hover:underline">Customer account</a><?php endif; ?>
        </p>
    </div>
    <div class="flex flex-wrap gap-2">
        <a href="/admin/orders/<?= (int)$order['id'] ?>/invoice" class="btn btn-ghost btn-sm" target="_blank" rel="noopener">Print invoice</a>
        <?php if ($balance > 0.009 && ($pay['status'] ?? '') !== 'paid'): ?><a href="#payment" class="btn btn-primary btn-sm">Record payment</a><?php endif; ?>
    </div>
</header>

<?php if (!empty($flash)): ?>
<div class="mt-5 flex gap-2.5 rounded-xl px-4 py-3 text-[13px] font-semibold <?= ($flash['type'] ?? 'success') === 'error' ? 'border border-danger-200 bg-danger-50 text-danger-800' : 'border border-jade-200 bg-jade-50 text-jade-800' ?>" role="status">
    <span><?= e($flash['message']) ?></span>
</div>
<?php endif; ?>

<div class="mt-6 grid gap-5 xl:grid-cols-12">
    <section class="card overflow-hidden xl:col-span-8" aria-labelledby="items-heading">
        <div class="flex flex-wrap items-center justify-between gap-3 border-b border-line px-5 py-4 sm:px-6">
            <h2 id="items-heading" class="text-[15px] font-extrabold text-ink">Items</h2>
            <span class="badge badge-soft px-2 py-1 !text-[11px]"><?= count($items) ?></span>
        </div>
        <div class="overflow-x-auto">
            <table class="data-table">
                <thead><tr><th>Item</th><th>Unit price</th><th>Qty</th><th class="text-right">Line total</th></tr></thead>
                <tbody>
                <?php foreach ($items as $it): ?>
                <tr>
                    <td data-label="Item" class="font-semibold text-ink"><?php if (!empty($it['product_slug'])): ?><a class="hover:text-electric-700 hover:underline" href="/product/<?= e($it['product_slug']) ?>" target="_blank" rel="noopener"><?= e($it['item_name']) ?></a><?php else: ?><?= e($it['item_name']) ?><?php endif; ?><?php if (!empty($it['sku'])): ?><br><span class="text-[11.5px] text-ink-muted">SKU: <?= e($it['sku']) ?></span><?php endif; ?></td>
                    <td data-label="Unit price" class="tabular-nums text-ink-muted"><?= price($it['unit_price']) ?></td>
                    <td data-label="Qty" class="tabular-nums"><?= (int)$it['quantity'] ?></td>
                    <td data-label="Line total" class="whitespace-nowrap text-right font-bold tabular-nums text-ink"><?= price((float)$it['unit_price'] * (int)$it['quantity']) ?></td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <dl class="space-y-2.5 border-t border-line px-5 py-4 text-[14px] sm:px-6">
            <div class="flex justify-between"><dt class="text-ink-muted">Subtotal</dt><dd class="font-bold tabular-nums text-ink"><?= price($order['subtotal']) ?></dd></div>
            <div class="flex justify-between"><dt class="text-ink-muted">Delivery</dt><dd class="font-bold tabular-nums text-ink"><?= (float)$order['shipping'] > 0 ? price($order['shipping']) : '<span class="text-jade-600">Free</span>' ?></dd></div>
            <div class="flex justify-between border-t border-line pt-3"><dt class="text-[15px] font-bold text-ink">Total</dt><dd class="text-[22px] font-extrabold tabular-nums tracking-tight text-ink"><?= price($order['total']) ?></dd></div>
            <div class="flex justify-between"><dt class="text-ink-muted">Paid so far</dt><dd class="font-bold tabular-nums text-ink"><?= price($pay['paid']) ?></dd></div>
            <div class="flex justify-between"><dt class="text-ink-muted">Balance</dt><dd class="font-bold tabular-nums <?= $balance > 0.009 ? 'text-flame-700' : 'text-jade-700' ?>"><?= price($balance) ?></dd></div>
        </dl>
    </section>

    <aside class="space-y-5 xl:col-span-4">
        <section class="card p-5 sm:p-6">
            <h2 class="text-[15px] font-extrabold text-ink">Fulfilment</h2>
            <?php if (!empty($nextStatuses)): ?>
            <form method="post" action="/admin/orders/<?= (int)$order['id'] ?>/status" class="mt-4 space-y-2.5">
                <?= Csrf::field() ?>
                <select name="status" class="input">
                    <?php foreach ($nextStatuses as $ns): $nm = Order::statusMeta($ns); ?><option value="<?= e($ns) ?>"><?= e($nm['label']) ?></option><?php endforeach; ?>
                </select>
                <input name="note" class="input" placeholder="Note for timeline (optional)">
                <button class="btn btn-primary btn-sm w-full">Update status</button>
            </form>
            <?php else: ?>
            <p class="mt-3 rounded-xl bg-surface px-3 py-2 text-[12.5px] text-ink-muted">No further transitions.</p>
            <?php endif; ?>
        </section>

        <section class="card p-5 sm:p-6" id="payment">
            <div class="flex items-center justify-between">
                <h2 class="text-[15px] font-extrabold text-ink">Payment</h2>
                <span class="badge <?= e($pmeta['bg']) ?> <?= e($pmeta['fg']) ?> px-2.5 py-1 !text-[11px]"><?= e($pay['label']) ?></span>
            </div>
            <dl class="mt-4 space-y-2.5 text-[13.5px]">
                <div class="flex justify-between"><dt class="text-ink-muted">Method</dt><dd class="font-semibold"><?= e($pay['method_label']) ?></dd></div>
                <div class="flex justify-between"><dt class="text-ink-muted">Total</dt><dd class="font-bold tabular-nums"><?= price($pay['total']) ?></dd></div>
                <div class="flex justify-between"><dt class="text-ink-muted">Received</dt><dd class="font-bold tabular-nums text-jade-700"><?= price($pay['paid']) ?></dd></div>
                <div class="flex justify-between"><dt class="text-ink-muted">Balance</dt><dd class="font-bold tabular-nums <?= $balance > 0.009 ? 'text-flame-700' : 'text-ink' ?>"><?= price($balance) ?></dd></div>
                <?php if ($pay['reference'] !== ''): ?><div class="flex justify-between gap-3"><dt class="text-ink-muted">Customer ref</dt><dd class="break-all font-mono text-[12px]"><?= e($pay['reference']) ?></dd></div><?php endif; ?>
                <?php if (!empty($order['payment_screenshot'])): ?>
                <div class="border-t border-line/70 pt-2.5">
                    <dt class="text-[11px] font-bold uppercase tracking-wider text-ink-muted">Uploaded Payment Screenshot</dt>
                    <dd class="mt-1.5">
                        <a href="<?= e($order['payment_screenshot']) ?>" target="_blank" rel="noopener noreferrer"
                           class="inline-flex items-center gap-2 rounded-lg border border-electric-300 bg-electric-50/60 px-3 py-1.5 text-[12.5px] font-bold text-electric-700 hover:bg-electric-100">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="h-4 w-4"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                            Open Attached Screenshot &nearr;
                        </a>
                    </dd>
                </div>
                <?php endif; ?>
            </dl>

            <?php if (($pay['status'] ?? '') !== 'paid'): ?>
            <form method="post" action="/admin/orders/<?= (int)$order['id'] ?>/payment" class="mt-4 space-y-2.5 rounded-xl border border-line bg-surface px-3.5 py-3.5">
                <?= Csrf::field() ?>
                <p class="text-[12.5px] font-bold text-ink">Record payment received</p>
                <div class="grid grid-cols-2 gap-2">
                    <input name="amount" type="number" step="0.01" min="0.01" required class="input h-9" value="<?= e(number_format($balance > 0.009 ? $balance : $pay['total'], 2, '.', '')) ?>">
                    <select name="method" class="input h-9">
                        <?php foreach ($methods as $mkey => $m): ?><option value="<?= e($mkey) ?>" <?= $mkey === $pay['method'] ? 'selected' : '' ?>><?= e($m['label']) ?></option><?php endforeach; ?>
                    </select>
                </div>
                <input name="reference" class="input h-9" placeholder="Transaction / receipt reference">
                <input name="paid_at" type="datetime-local" class="input h-9">
                <input name="note" class="input h-9" placeholder="Note (optional)">
                <button class="btn btn-primary btn-sm w-full">Mark payment received</button>
                <p class="text-center text-[11px] text-ink-muted">Only recorded by an admin.</p>
            </form>
            <?php else: ?>
            <form method="post" action="/admin/orders/<?= (int)$order['id'] ?>/refund" class="mt-4">
                <?= Csrf::field() ?>
                <button class="btn btn-ghost btn-sm w-full">Mark as refunded</button>
            </form>
            <?php endif; ?>
        </section>

        <section class="card p-5 sm:p-6">
            <h2 class="text-[15px] font-extrabold text-ink">Customer</h2>
            <dl class="mt-4 space-y-2.5 text-[13.5px]">
                <div><dt class="text-[11px] font-bold uppercase tracking-wide text-ink-muted">Name</dt><dd class="mt-0.5 font-bold"><?= e($order['customer_name']) ?></dd></div>
                <div><dt class="text-[11px] font-bold uppercase tracking-wide text-ink-muted">Phone</dt><dd class="mt-0.5"><a class="text-electric-700" href="tel:<?= e(preg_replace('/[^0-9+]/','',$order['phone'])) ?>"><?= e($order['phone']) ?></a></dd></div>
                <?php if (!empty($order['email'])): ?><div><dt class="text-[11px] font-bold uppercase tracking-wide text-ink-muted">Email</dt><dd class="mt-0.5 break-all"><a class="text-electric-700" href="mailto:<?= e($order['email']) ?>"><?= e($order['email']) ?></a></dd></div><?php endif; ?>
                <div><dt class="text-[11px] font-bold uppercase tracking-wide text-ink-muted">Address</dt><dd class="mt-0.5 leading-relaxed"><?= nl2br(e($order['address'])) ?><br><?= e($order['city']) ?></dd></div>
            </dl>
        </section>

        <section class="card p-5 sm:p-6">
            <h2 class="text-[15px] font-extrabold text-ink">Internal note</h2>
            <?php if (!empty($order['admin_note'])): ?><p class="mt-2 rounded-xl bg-surface px-3 py-2 text-[12.5px] leading-relaxed"><?= nl2br(e($order['admin_note'])) ?></p><?php endif; ?>
            <form method="post" action="/admin/orders/<?= (int)$order['id'] ?>/note" class="mt-3 space-y-2">
                <?= Csrf::field() ?>
                <textarea name="note" class="input" rows="3" placeholder="Add internal note"></textarea>
                <button class="btn btn-ghost btn-sm w-full">Save note</button>
            </form>
        </section>
    </aside>
</div>

<section class="card mt-5 p-5 sm:p-6">
    <h2 class="text-[15px] font-extrabold text-ink">Timeline</h2>
    <ol class="mt-4 grid gap-3 md:grid-cols-2 xl:grid-cols-3">
        <?php if (empty($events)): ?>
        <li class="text-[13px] text-ink-muted">No events yet.</li>
        <?php else: foreach ($events as $event): ?>
        <li class="flex gap-3 rounded-xl border border-line px-3.5 py-3">
            <span class="mt-0.5 grid h-7 w-7 shrink-0 place-items-center rounded-full <?= $event['actor']==='admin'?'bg-electric-100 text-electric-700':($event['actor']==='customer'?'bg-jade-100 text-jade-700':'bg-surface-muted text-ink-muted') ?>">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="h-3.5 w-3.5"><circle cx="12" cy="12" r="3"/><path d="M12 1v6M12 17v6M4.22 4.22l4.24 4.24M15.54 15.54l4.24 4.24M1 12h6M17 12h6M4.22 19.78l4.24-4.24M15.54 8.46l4.24-4.24"/></svg>
            </span>
            <span class="min-w-0"><span class="block text-[13px] leading-relaxed"><?= e($event['note'] ?? 'Update') ?></span><span class="mt-0.5 block text-[11.5px] text-ink-muted"><?= e(date('d M Y, H:i', strtotime($event['created_at']))) ?> · <?= e(ucfirst($event['actor'])) ?></span></span>
        </li>
        <?php endforeach; endif; ?>
    </ol>
</section>
