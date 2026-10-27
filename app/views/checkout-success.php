<?php
/**
 * Order confirmation — Soneri Bank only with WhatsApp proof submission & Modal.
 *
 * @var array $order @var array $items @var array $payment
 */
$gaItems = [];
foreach ($items as $item) {
    $gaItems[] = [
        'item_id'   => $item['sku'] ?? '',
        'item_name' => $item['item_name'],
        'price'     => (float) $item['unit_price'],
        'quantity'  => (int) $item['quantity'],
    ];
}
$payload = json_encode([
    'transaction_id' => $order['reference'],
    'value'          => (float) $order['total'],
    'shipping'       => (float) $order['shipping'],
    'items'          => $gaItems,
], JSON_UNESCAPED_SLASHES);

$pay = $payment ?? \App\Models\Payment::summary($order);
$bank = \App\Models\Payment::bankDetails();
$instructions = \App\Models\Payment::instructions('bank_transfer');

$waMessage = rawurlencode(
    "Salam Lala Mobiles! I have placed Order #{$order['reference']} for " . price((float) $order['total']) . ".\n"
    . "Customer: {$order['customer_name']}\n"
    . "Phone: {$order['phone']}\n"
    . "Payment: Soneri Bank Transfer (PK62SONE0000320013557234)\n"
    . "I am sharing my payment screenshot here for priority verification."
);
$waUrl = "https://wa.me/923005970504?text={$waMessage}";
?>
<div class="shell pb-24 pt-10" data-ga-purchase="<?= e($payload) ?>">
    <div class="mx-auto max-w-3xl text-center">
        <!-- Official Logo Header -->
        <div class="mb-5 flex justify-center">
            <a href="/" title="Lala Mobiles">
                <img src="/img/logo.png" alt="Lala Mobiles" class="h-14 sm:h-16 w-auto object-contain drop-shadow-md transition-transform hover:scale-105">
            </a>
        </div>

        <!-- Success mark -->
        <span class="mx-auto grid h-16 w-16 place-items-center rounded-full bg-jade-50 ring-8 ring-jade-50/60">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6" class="h-8 w-8 text-jade-600" aria-hidden="true"><path d="M20 6L9 17l-5-5"/></svg>
        </span>

        <p class="mt-6 text-[11px] font-extrabold uppercase tracking-[0.22em] text-jade-600">Order Placed Successfully</p>
        <h1 class="mt-2 text-[30px] font-extrabold tracking-tight text-ink md:text-[40px]">Thank you, <?= e($order['customer_name']) ?>!</h1>

        <p class="mx-auto mt-4 max-w-xl text-[15px] leading-relaxed text-ink-muted">
            Order <span class="rounded-lg bg-surface-muted px-2.5 py-1 font-mono text-[14px] font-bold text-ink">#<?= e($order['reference']) ?></span>
            has been received. We have sent a detailed order confirmation and invoice to <strong class="text-ink"><?= e($order['email']) ?></strong>.
        </p>

        <!-- Prominent WhatsApp Action Banner -->
        <div class="mx-auto mt-6 max-w-xl rounded-2xl border-2 border-jade-300 bg-gradient-to-r from-emerald-50 via-teal-50 to-jade-50 p-5 shadow-sm">
            <div class="flex flex-col sm:flex-row items-center justify-between gap-4">
                <div class="text-left">
                    <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-600 px-2.5 py-0.5 text-[11px] font-extrabold text-white">
                        <span>⚡ Priority Verification</span>
                    </span>
                    <h3 class="mt-1 text-[16px] font-extrabold text-slate-900">Send Screenshot on WhatsApp</h3>
                    <p class="text-[12.5px] text-slate-600">Share your bank receipt on <strong>+92 300 5970504</strong> for swift dispatch.</p>
                </div>
                <a href="<?= e($waUrl) ?>" target="_blank" rel="noopener noreferrer"
                   class="inline-flex shrink-0 items-center gap-2 rounded-xl bg-[#25D366] px-5 py-3 text-[14px] font-extrabold text-white shadow-md transition-all hover:bg-[#20ba59] hover:shadow-lg">
                    <svg viewBox="0 0 24 24" fill="currentColor" class="h-5 w-5"><path d="M12.04 2c-5.46 0-9.91 4.45-9.91 9.91 0 1.75.46 3.45 1.32 4.95L2.05 22l5.25-1.38c1.45.79 3.08 1.21 4.74 1.21 5.46 0 9.91-4.45 9.91-9.91 0-2.65-1.03-5.14-2.9-7.01A9.82 9.82 0 0 0 12.04 2zm.01 1.67c4.54 0 8.24 3.7 8.24 8.24 0 2.2-.86 4.27-2.42 5.82-1.55 1.56-3.62 2.42-5.82 2.42-1.42 0-2.81-.37-4.04-1.08l-.29-.17-3.01.79.8-2.93-.19-.3a8.21 8.21 0 0 1-1.26-4.31c0-4.54 3.7-8.24 8.24-8.24zm4.52 11.66c-.25-.13-1.47-.72-1.7-.81-.23-.08-.39-.13-.56.13-.17.25-.64.81-.79.97-.14.17-.29.19-.54.06-.25-.13-1.06-.39-2.02-1.25-.75-.67-1.26-1.5-1.4-1.76-.15-.25-.02-.39.11-.51.11-.11.25-.29.38-.44.13-.14.17-.25.25-.42.08-.17.04-.31-.02-.44-.06-.13-.56-1.35-.77-1.85-.2-.49-.41-.42-.56-.43h-.48c-.17 0-.44.06-.67.31-.23.25-.88.86-.88 2.1 0 1.24.9 2.44 1.03 2.61.13.17 1.77 2.7 4.29 3.79.6.26 1.07.41 1.43.53.6.19 1.15.16 1.58.1.48-.07 1.47-.6 1.68-1.18.21-.58.21-1.08.15-1.18-.06-.1-.23-.17-.48-.29z"/></svg>
                    Send on WhatsApp
                </a>
            </div>
        </div>
    </div>

    <div class="mx-auto mt-10 grid max-w-4xl gap-5 lg:grid-cols-12">
        <!-- Order summary -->
        <div class="card overflow-hidden lg:col-span-7">
            <div class="flex flex-wrap items-center justify-between gap-3 border-b border-line bg-surface px-6 py-4">
                <h2 class="text-[15px] font-extrabold text-ink">Order Summary</h2>
                <span class="badge badge-soft px-2.5 py-1 !text-[11.5px]">Soneri Bank Transfer</span>
            </div>

            <ul class="divide-y divide-line px-6">
                <?php foreach ($items as $item): ?>
                <li class="flex items-start justify-between gap-4 py-3.5">
                    <span class="min-w-0 flex-1">
                        <span class="block text-[14px] font-semibold leading-snug text-ink"><?= e($item['item_name']) ?></span>
                        <span class="mt-0.5 block text-[12.5px] text-ink-muted"><?= (int) $item['quantity'] ?> × <?= price((float) $item['unit_price']) ?></span>
                    </span>
                    <span class="shrink-0 whitespace-nowrap text-[14px] font-bold tabular-nums text-ink">
                        <?= price((float) $item['unit_price'] * (int) $item['quantity']) ?>
                    </span>
                </li>
                <?php endforeach; ?>
            </ul>

            <dl class="space-y-2.5 border-t border-line px-6 py-4 text-[14px]">
                <div class="flex items-baseline justify-between gap-3">
                    <dt class="text-ink-muted">Subtotal</dt>
                    <dd class="font-bold tabular-nums text-ink"><?= price((float) $order['subtotal']) ?></dd>
                </div>
                <div class="flex items-baseline justify-between gap-3">
                    <dt class="text-ink-muted">Delivery</dt>
                    <dd class="font-bold tabular-nums text-ink">
                        <?= (float) $order['shipping'] > 0 ? price((float) $order['shipping']) : '<span class="text-jade-600">Free</span>' ?>
                    </dd>
                </div>
                <div class="flex items-baseline justify-between gap-3 border-t border-line pt-3">
                    <dt class="text-[15px] font-bold text-ink">Total Payable</dt>
                    <dd class="text-[22px] font-extrabold tabular-nums tracking-tight text-ink"><?= price((float) $order['total']) ?></dd>
                </div>
                <div class="flex items-center justify-between gap-3 pt-1">
                    <dt class="text-ink-muted">Payment status</dt>
                    <dd class="flex items-center gap-2">
                        <span class="badge <?= e($pay['meta']['bg']) ?> <?= e($pay['meta']['fg']) ?> px-2.5 py-1 !text-[11px]"><?= e($pay['label']) ?></span>
                        <span class="text-[12px] text-ink-muted">Under Verification</span>
                    </dd>
                </div>
            </dl>

            <?php if (!empty($order['payment_screenshot'])): ?>
            <div class="border-t border-line bg-surface/50 p-4">
                <p class="text-[12px] font-bold text-ink-muted uppercase tracking-wider">Payment Proof Attached</p>
                <div class="mt-2 flex items-center gap-3">
                    <a href="<?= e($order['payment_screenshot']) ?>" target="_blank" rel="noopener noreferrer"
                       class="inline-flex items-center gap-2 text-[13px] font-bold text-electric-700 hover:underline">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="h-4 w-4"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                        View Attached Screenshot
                    </a>
                    <span class="text-xs text-jade-700 font-semibold bg-jade-100 rounded-full px-2 py-0.5">Uploaded</span>
                </div>
            </div>
            <?php endif; ?>
        </div>

        <!-- Payment instructions & details -->
        <aside class="space-y-5 lg:col-span-5">
            <section class="card p-6">
                <div class="flex items-center justify-between">
                    <h2 class="text-[15px] font-extrabold text-ink">Soneri Bank Transfer</h2>
                    <span class="badge bg-gold-400 px-2 py-1 text-[11px] text-navy-900">Awaiting Verification</span>
                </div>

                <div class="mt-4 rounded-xl border border-line bg-surface p-4 text-[13px] leading-relaxed text-ink">
                    <dl class="space-y-2">
                        <div class="flex justify-between gap-3"><dt class="text-ink-muted">Bank</dt><dd class="font-bold text-ink">Soneri Bank</dd></div>
                        <div class="flex justify-between gap-3"><dt class="text-ink-muted">Account Title</dt><dd class="font-bold text-ink">Lala Mobiles</dd></div>
                        <div class="flex justify-between gap-3"><dt class="text-ink-muted">Account / IBAN</dt><dd class="break-all font-mono font-bold text-electric-800">PK62SONE0000320013557234</dd></div>
                        <div class="flex justify-between gap-3 border-t border-line/60 pt-2"><dt class="text-ink-muted">WhatsApp Support</dt><dd class="font-bold text-emerald-700">+92 300 5970504</dd></div>
                    </dl>
                </div>

                <a href="<?= e($waUrl) ?>" target="_blank" rel="noopener noreferrer"
                   class="btn btn-primary btn-md btn-block mt-4 flex items-center justify-center gap-2 !bg-[#25D366] hover:!bg-[#20ba59] !text-white border-none shadow-sm">
                    <svg viewBox="0 0 24 24" fill="currentColor" class="h-4.5 w-4.5"><path d="M12.04 2c-5.46 0-9.91 4.45-9.91 9.91 0 1.75.46 3.45 1.32 4.95L2.05 22l5.25-1.38c1.45.79 3.08 1.21 4.74 1.21 5.46 0 9.91-4.45 9.91-9.91 0-2.65-1.03-5.14-2.9-7.01A9.82 9.82 0 0 0 12.04 2zm.01 1.67c4.54 0 8.24 3.7 8.24 8.24 0 2.2-.86 4.27-2.42 5.82-1.55 1.56-3.62 2.42-5.82 2.42-1.42 0-2.81-.37-4.04-1.08l-.29-.17-3.01.79.8-2.93-.19-.3a8.21 8.21 0 0 1-1.26-4.31c0-4.54 3.7-8.24 8.24-8.24zm4.52 11.66c-.25-.13-1.47-.72-1.7-.81-.23-.08-.39-.13-.56.13-.17.25-.64.81-.79.97-.14.17-.29.19-.54.06-.25-.13-1.06-.39-2.02-1.25-.75-.67-1.26-1.5-1.4-1.76-.15-.25-.02-.39.11-.51.11-.11.25-.29.38-.44.13-.14.17-.25.25-.42.08-.17.04-.31-.02-.44-.06-.13-.56-1.35-.77-1.85-.2-.49-.41-.42-.56-.43h-.48c-.17 0-.44.06-.67.31-.23.25-.88.86-.88 2.1 0 1.24.9 2.44 1.03 2.61.13.17 1.77 2.7 4.29 3.79.6.26 1.07.41 1.43.53.6.19 1.15.16 1.58.1.48-.07 1.47-.6 1.68-1.18.21-.58.21-1.08.15-1.18-.06-.1-.23-.17-.48-.29z"/></svg>
                    Send Receipt on WhatsApp
                </a>
                <p class="mt-2 text-center text-[11.5px] text-ink-muted">Verification is done swiftly during business hours.</p>
            </section>

            <section class="card p-6">
                <h2 class="text-[15px] font-extrabold text-ink">What happens next</h2>
                <ol class="mt-4 space-y-4">
                    <?php
                    $steps = [
                        ['Payment verification', 'Our team checks your Soneri Bank deposit or online receipt.'],
                        ['Testing & packing', 'Your handset is tested, sanitised and securely packed with warranty documentation.'],
                        ['Dispatch & tracking', 'Shipped via insured courier and tracking number sent to your phone/WhatsApp.'],
                    ];
                    foreach ($steps as $i => [$title, $body]): ?>
                    <li class="flex gap-3.5">
                        <span class="mt-0.5 grid h-7 w-7 shrink-0 place-items-center rounded-lg bg-electric-600 text-[13px] font-extrabold text-white"><?= $i + 1 ?></span>
                        <span>
                            <span class="block text-[14px] font-bold text-ink"><?= e($title) ?></span>
                            <span class="mt-0.5 block text-[13px] leading-relaxed text-ink-muted"><?= e($body) ?></span>
                        </span>
                    </li>
                    <?php endforeach; ?>
                </ol>
            </section>
        </aside>
    </div>

    <div class="mt-10 flex flex-wrap justify-center gap-3">
        <a href="/account/orders/<?= (int) $order['id'] ?>" class="btn btn-primary btn-md">View order &amp; invoice</a>
        <a href="/account" class="btn btn-ghost btn-md">Go to my account</a>
        <a href="/" class="btn btn-ghost btn-md">Continue shopping</a>
    </div>

    <p class="mx-auto mt-6 max-w-md text-center text-[12.5px] text-ink-muted">
        Need assistance? WhatsApp or call us anytime at <a href="tel:+923005970504" class="font-bold text-electric-700 hover:underline">+92 300 5970504</a>.
    </p>
</div>

<!-- ========================================================================= -->
<!-- Professional Order Success Popup Modal                                     -->
<!-- ========================================================================= -->
<div id="order-success-modal" class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-slate-950/70 backdrop-blur-sm transition-opacity duration-300">
    <div class="relative w-full max-w-lg overflow-hidden rounded-3xl bg-white p-6 shadow-2xl sm:p-8 transform transition-all duration-300 scale-100">
        <!-- Close button -->
        <button type="button" onclick="document.getElementById('order-success-modal').remove()"
                class="absolute right-4 top-4 grid h-9 w-9 place-items-center rounded-full bg-surface-muted text-ink-muted hover:bg-surface-sunken hover:text-ink transition-colors"
                aria-label="Close modal">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" class="h-4.5 w-4.5"><path d="m18 6-12 12M6 6l12 12"/></svg>
        </button>

        <!-- Animated Check Badge & Logo -->
        <div class="text-center">
            <div class="mb-3 flex justify-center">
                <img src="/img/logo.png" alt="Lala Mobiles" class="h-12 sm:h-14 w-auto object-contain drop-shadow-sm">
            </div>
            <div class="mx-auto grid h-16 w-16 place-items-center rounded-full bg-emerald-100 ring-8 ring-emerald-50 text-emerald-600 animate-bounce">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.8" class="h-8 w-8"><path d="M20 6L9 17l-5-5"/></svg>
            </div>

            <span class="mt-4 inline-block text-[11px] font-extrabold uppercase tracking-widest text-emerald-600">Order Placed Successfully</span>
            <h2 class="mt-1 text-2xl font-black text-slate-900 sm:text-3xl">Thank You for Your Order!</h2>
            <p class="mt-1.5 font-mono text-[14px] font-extrabold text-electric-600">Order Reference: #<?= e($order['reference']) ?></p>
        </div>

        <!-- Big Email Notification Box -->
        <div class="mt-5 rounded-2xl border border-blue-200 bg-blue-50/70 p-4 text-left">
            <div class="flex gap-3 items-start">
                <span class="grid h-8 w-8 place-items-center rounded-lg bg-blue-600 text-white shrink-0 mt-0.5">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="h-4 w-4"><rect x="2" y="4" width="20" height="16" rx="2"/><path d="m2 7 10 6 10-6"/></svg>
                </span>
                <div>
                    <h4 class="text-[13.5px] font-extrabold text-blue-950">Email Confirmation Sent</h4>
                    <p class="mt-0.5 text-[12px] text-blue-800 leading-snug">
                        A detailed order receipt and bank transfer invoice has been emailed to <strong class="text-blue-950"><?= e($order['email']) ?></strong>.
                    </p>
                </div>
            </div>
        </div>

        <!-- Soneri Bank Reminder Box -->
        <div class="mt-3.5 rounded-2xl border border-slate-200 bg-slate-50 p-4 text-left">
            <div class="flex items-center justify-between border-b border-slate-200/80 pb-2.5">
                <span class="text-[12px] font-extrabold uppercase tracking-wide text-slate-700">Soneri Bank Limited</span>
                <span class="text-[14px] font-black text-slate-900"><?= price((float)$order['total']) ?></span>
            </div>
            <div class="mt-2 text-[12.5px] space-y-1 text-slate-700">
                <p><strong>Account Title:</strong> Lala Mobiles</p>
                <p class="font-mono font-bold text-electric-700 break-all"><strong>IBAN:</strong> PK62SONE0000320013557234</p>
            </div>
        </div>

        <!-- Prominent WhatsApp Action CTA -->
        <div class="mt-5 text-center">
            <p class="text-[13px] font-semibold text-slate-700 mb-3">
                For fastest order dispatch, please send your payment screenshot on WhatsApp:
            </p>
            <a href="<?= e($waUrl) ?>" target="_blank" rel="noopener noreferrer"
               class="flex w-full items-center justify-center gap-2.5 rounded-2xl bg-[#25D366] py-3.5 px-6 text-[15px] font-extrabold text-white shadow-lg transition-transform hover:scale-[1.02] hover:bg-[#20ba59]">
                <svg viewBox="0 0 24 24" fill="currentColor" class="h-5 w-5"><path d="M12.04 2c-5.46 0-9.91 4.45-9.91 9.91 0 1.75.46 3.45 1.32 4.95L2.05 22l5.25-1.38c1.45.79 3.08 1.21 4.74 1.21 5.46 0 9.91-4.45 9.91-9.91 0-2.65-1.03-5.14-2.9-7.01A9.82 9.82 0 0 0 12.04 2zm.01 1.67c4.54 0 8.24 3.7 8.24 8.24 0 2.2-.86 4.27-2.42 5.82-1.55 1.56-3.62 2.42-5.82 2.42-1.42 0-2.81-.37-4.04-1.08l-.29-.17-3.01.79.8-2.93-.19-.3a8.21 8.21 0 0 1-1.26-4.31c0-4.54 3.7-8.24 8.24-8.24zm4.52 11.66c-.25-.13-1.47-.72-1.7-.81-.23-.08-.39-.13-.56.13-.17.25-.64.81-.79.97-.14.17-.29.19-.54.06-.25-.13-1.06-.39-2.02-1.25-.75-.67-1.26-1.5-1.4-1.76-.15-.25-.02-.39.11-.51.11-.11.25-.29.38-.44.13-.14.17-.25.25-.42.08-.17.04-.31-.02-.44-.06-.13-.56-1.35-.77-1.85-.2-.49-.41-.42-.56-.43h-.48c-.17 0-.44.06-.67.31-.23.25-.88.86-.88 2.1 0 1.24.9 2.44 1.03 2.61.13.17 1.77 2.7 4.29 3.79.6.26 1.07.41 1.43.53.6.19 1.15.16 1.58.1.48-.07 1.47-.6 1.68-1.18.21-.58.21-1.08.15-1.18-.06-.1-.23-.17-.48-.29z"/></svg>
                Send Screenshot on WhatsApp (+92 300 5970504)
            </a>

            <button type="button" onclick="document.getElementById('order-success-modal').remove()"
                    class="mt-3 text-[13px] font-bold text-slate-500 hover:text-slate-800">
                View Full Order Details &rarr;
            </button>
        </div>
    </div>
</div>
