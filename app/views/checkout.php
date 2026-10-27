<?php
/**
 * Checkout — delivery details, payment method and live order summary.
 *
 * @var array $items @var float $subtotal @var float $shipping @var float $total
 * @var array $errors @var array $old @var array $customer @var array $methods
 *
 * Ordering requires a signed-in customer. The account email is shown but not
 * editable, so the address that receives order updates is the verified one.
 * No payment is taken here: every order starts with payment pending and is
 * approved manually once the money is actually received.
 */
$checkoutItems = [];
foreach ($items as $item) {
    $checkoutItems[] = [
        'item_id'   => $item['sku'] ?? '',
        'item_name' => $item['name'],
        'price'     => $item['unit_price'],
        'quantity'  => $item['quantity'],
    ];
}
$payload = json_encode(['value' => $total, 'items' => $checkoutItems], JSON_UNESCAPED_SLASHES);

$selectedMethod = (string) ($old['payment_method'] ?? 'cod');
if (!isset($methods[$selectedMethod])) {
    $selectedMethod = 'cod';
}

/** Mark a field invalid and return its error message, if any. */
$errFor = static function (string $field) use ($errors): ?string {
    foreach ((array) $errors as $err) {
        if (stripos((string) $err, strtolower($field)) !== false) {
            return (string) $err;
        }
    }
    return null;
};

$methodIcon = static function (string $icon): string {
    return match ($icon) {
        'bank'   => '<path d="M3 10h18M5 10V6l7-3 7 3v4M5 10v8m4-8v8m6-8v8m4-8v8M3 21h18"/>',
        'wallet' => '<rect x="3" y="6" width="18" height="13" rx="2.5"/><path d="M3 10h18M16 14h2"/>',
        default  => '<rect x="2" y="6" width="20" height="12" rx="2"/><circle cx="12" cy="12" r="2.5"/><path d="M6 12h.01M18 12h.01"/>',
    };
};
?>
<div class="shell pb-16 pt-5" data-ga-begin-checkout="<?= e($payload) ?>">
    <nav aria-label="Breadcrumb" class="text-sm text-ink-muted">
        <ol class="flex flex-wrap items-center gap-1.5">
            <li><a href="/cart" class="hover:text-electric-600">Cart</a></li>
            <li aria-hidden="true" class="text-line">/</li>
            <li aria-current="page" class="font-medium text-ink">Checkout</li>
        </ol>
    </nav>

    <div class="mt-5 flex flex-wrap items-end justify-between gap-3">
        <div>
            <h1 class="text-[28px] font-extrabold tracking-tight text-ink md:text-4xl">Secure checkout</h1>
            <p class="mt-1.5 text-[13.5px] text-ink-muted">You are ordering as a signed-in customer, so your invoice and order updates are tied to your account.</p>
        </div>
        <div class="flex items-center gap-2 rounded-full border border-line bg-white px-3 py-1.5 text-[12px] font-semibold text-ink">
            <span class="grid h-6 w-6 place-items-center rounded-full bg-jade-100 text-jade-700">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" class="h-3.5 w-3.5"><path d="M20 6L9 17l-5-5"/></svg>
            </span>
            <?= e($customer['name']) ?>
            <span class="text-ink-muted">·</span>
            <a href="/account" class="text-electric-700 hover:underline">Account</a>
        </div>
    </div>

    <?php if (!empty($errors)): ?>
    <div class="mt-6 rounded-2xl border border-flame-200 bg-flame-50 p-4" role="alert" tabindex="-1" data-error-summary>
        <div class="flex gap-3">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="mt-0.5 h-5 w-5 shrink-0 text-flame-600"><circle cx="12" cy="12" r="9"/><path d="M12 8v5M12 16h.01"/></svg>
            <div>
                <p class="text-[14px] font-bold text-flame-700">Please check the following before placing your order</p>
                <ul class="mt-2 list-disc space-y-1 pl-5 text-[13px] font-semibold text-flame-700">
                    <?php foreach ($errors as $err): ?><li><?= e($err) ?></li><?php endforeach; ?>
                </ul>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <div class="mt-7 grid gap-7 lg:grid-cols-12 lg:gap-8">
        <!-- ============================ The form ============================ -->
        <form method="post" action="/checkout" id="checkout-form" enctype="multipart/form-data" class="lg:col-span-7">
            <?= \App\Core\Csrf::field() ?>

            <section class="card p-6" aria-labelledby="delivery-heading">
                <h2 id="delivery-heading" class="flex items-center gap-2.5 text-[17px] font-extrabold text-ink">
                    <span class="grid h-7 w-7 place-items-center rounded-lg bg-electric-600 text-[13px] font-extrabold text-white">1</span>
                    Delivery details
                </h2>

                <div class="mt-5 space-y-4">
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <label for="name" class="mb-1.5 block text-[13px] font-bold text-ink">Full name <span class="text-flame-500">*</span></label>
                            <input type="text" id="name" name="name" required minlength="3" maxlength="160" autocomplete="name"
                                   value="<?= e((string) ($old['name'] ?? '')) ?>"
                                   class="input <?= $errFor('name') !== null ? 'field-error' : '' ?>"
                                   <?= $errFor('name') !== null ? 'aria-invalid="true"' : '' ?>>
                            <?php if ($errFor('name') !== null): ?><p class="field-hint !font-semibold !text-flame-600"><?= e($errFor('name')) ?></p><?php endif; ?>
                        </div>
                        <div>
                            <label for="phone" class="mb-1.5 block text-[13px] font-bold text-ink">Phone number <span class="text-flame-500">*</span></label>
                            <input type="tel" id="phone" name="phone" required maxlength="20" inputmode="tel"
                                   autocomplete="tel" placeholder="03XX-XXXXXXX" value="<?= e((string) ($old['phone'] ?? '')) ?>"
                                   class="input <?= $errFor('phone') !== null ? 'field-error' : '' ?>"
                                   <?= $errFor('phone') !== null ? 'aria-invalid="true"' : '' ?>>
                            <?php if ($errFor('phone') !== null): ?><p class="field-hint !font-semibold !text-flame-600"><?= e($errFor('phone')) ?></p><?php endif; ?>
                        </div>
                    </div>

                    <div class="rounded-xl border border-line bg-surface px-4 py-3">
                        <div class="flex flex-wrap items-center justify-between gap-2">
                            <div>
                                <p class="text-[11px] font-bold uppercase tracking-wide text-ink-muted">Account email (order updates &amp; invoice)</p>
                                <p class="mt-0.5 break-all text-[13.5px] font-semibold text-ink"><?= e($customer['email']) ?></p>
                            </div>
                            <a href="/account" class="text-[12px] font-bold text-electric-700 hover:underline">Manage account</a>
                        </div>
                    </div>

                    <div>
                        <label for="address" class="mb-1.5 block text-[13px] font-bold text-ink">Delivery address <span class="text-flame-500">*</span></label>
                        <textarea id="address" name="address" required rows="3" maxlength="512" autocomplete="street-address"
                                  class="input <?= $errFor('address') !== null ? 'field-error' : '' ?>"
                                  <?= $errFor('address') !== null ? 'aria-invalid="true"' : '' ?>><?= e((string) ($old['address'] ?? '')) ?></textarea>
                        <p class="field-hint">House / flat, street and area.</p>
                        <?php if ($errFor('address') !== null): ?><p class="field-hint !font-semibold !text-flame-600"><?= e($errFor('address')) ?></p><?php endif; ?>
                    </div>

                    <div>
                        <label for="city" class="mb-1.5 block text-[13px] font-bold text-ink">City <span class="text-flame-500">*</span></label>
                        <input type="text" id="city" name="city" required minlength="2" maxlength="120" autocomplete="address-level2"
                               value="<?= e((string) ($old['city'] ?? '')) ?>"
                               class="input sm:max-w-xs <?= $errFor('city') !== null ? 'field-error' : '' ?>"
                               <?= $errFor('city') !== null ? 'aria-invalid="true"' : '' ?>>
                        <?php if ($errFor('city') !== null): ?><p class="field-hint !font-semibold !text-flame-600"><?= e($errFor('city')) ?></p><?php endif; ?>
                    </div>
                </div>
            </section>

            <!-- Payment method — Exclusively Soneri Bank (No COD) -->
            <section class="card mt-5 p-6" aria-labelledby="payment-heading">
                <input type="hidden" name="payment_method" value="bank_transfer">

                <div class="flex flex-wrap items-center justify-between gap-2">
                    <h2 id="payment-heading" class="flex items-center gap-2.5 text-[17px] font-extrabold text-ink">
                        <span class="grid h-7 w-7 place-items-center rounded-lg bg-electric-600 text-[13px] font-extrabold text-white">2</span>
                        Payment Method — Soneri Bank
                    </h2>
                    <span class="inline-flex items-center gap-1.5 rounded-full bg-jade-50 px-3 py-1 text-[11.5px] font-bold text-jade-700">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" class="h-3.5 w-3.5"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                        Direct Bank Transfer
                    </span>
                </div>

                <p class="mt-2 text-[13px] leading-relaxed text-ink-muted">
                    We only accept official bank transfers via <strong class="text-ink">Soneri Bank</strong>. Cash on Delivery (COD) is not accepted. Please transfer the order amount to the official bank account below and upload your payment screenshot.
                </p>

                <!-- Official Soneri Bank Account Box -->
                <div class="mt-4 overflow-hidden rounded-2xl border-2 border-electric-500/40 bg-gradient-to-br from-electric-50/70 via-white to-surface p-5 shadow-sm">
                    <div class="flex flex-wrap items-center justify-between gap-3 border-b border-electric-100 pb-3.5">
                        <div class="flex items-center gap-3">
                            <span class="grid h-10 w-10 place-items-center rounded-xl bg-electric-600 text-white shadow-sm">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="h-5 w-5"><path d="M3 10h18M5 10V6l7-3 7 3v4M5 10v8m4-8v8m6-8v8m4-8v8M3 21h18"/></svg>
                            </span>
                            <div>
                                <h3 class="text-[15px] font-extrabold text-ink">Soneri Bank Limited</h3>
                                <p class="text-[12px] font-medium text-ink-muted">Official Store Account</p>
                            </div>
                        </div>
                        <div class="text-right">
                            <span class="text-[11px] font-bold uppercase tracking-wider text-ink-muted">Payable Amount</span>
                            <p class="text-[18px] font-black text-electric-700"><?= price($total) ?></p>
                        </div>
                    </div>

                    <div class="mt-4 grid gap-3 sm:grid-cols-2">
                        <div class="rounded-xl border border-line bg-white p-3.5 shadow-sm">
                            <span class="text-[11px] font-bold uppercase tracking-wide text-ink-muted">Account Title</span>
                            <p class="mt-0.5 text-[15px] font-extrabold text-ink">Lala Mobiles</p>
                        </div>

                        <div class="rounded-xl border border-line bg-white p-3.5 shadow-sm">
                            <span class="text-[11px] font-bold uppercase tracking-wide text-ink-muted">Bank Name</span>
                            <p class="mt-0.5 text-[15px] font-extrabold text-ink">Soneri Bank</p>
                        </div>
                    </div>

                    <!-- Account / IBAN with Copy Button -->
                    <div class="mt-3 flex flex-wrap items-center justify-between gap-3 rounded-xl border-2 border-dashed border-electric-300 bg-white p-3.5">
                        <div class="min-w-0 flex-1">
                            <span class="text-[11px] font-bold uppercase tracking-wide text-ink-muted">Account Number / IBAN</span>
                            <p id="iban-text" class="mt-0.5 select-all font-mono text-[14.5px] font-extrabold tracking-wide text-electric-900 break-all">PK62SONE0000320013557234</p>
                        </div>
                        <button type="button" id="copy-iban-btn"
                                onclick="navigator.clipboard.writeText('PK62SONE0000320013557234').then(()=>{ this.innerText='Copied!'; setTimeout(()=>this.innerText='Copy IBAN', 2000); })"
                                class="btn btn-dark btn-sm shrink-0 rounded-lg text-[12px] font-bold">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="h-3.5 w-3.5"><rect x="9" y="9" width="13" height="13" rx="2" ry="2"/><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/></svg>
                            Copy IBAN
                        </button>
                    </div>

                    <!-- Steps notice -->
                    <div class="mt-3.5 flex items-start gap-2.5 text-[12.5px] leading-relaxed text-ink-muted">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="mt-0.5 h-4 w-4 shrink-0 text-electric-600"><circle cx="12" cy="12" r="9"/><path d="M12 8v5M12 16h.01"/></svg>
                        <span>Transfer from any bank app or digital wallet (Easypaisa/JazzCash/1Link) to the Soneri Bank account above.</span>
                    </div>
                </div>

                <!-- Proof Upload Field -->
                <div class="mt-6 rounded-2xl border-2 border-line bg-surface p-5">
                    <label for="payment_screenshot" class="block text-[14px] font-bold text-ink">
                        Upload Online Payment Screenshot <span class="text-flame-500">*</span>
                    </label>
                    <p class="mt-1 text-[12.5px] text-ink-muted">
                        Attach a clear screenshot or receipt of your completed bank transfer (JPG, PNG, WEBP or PDF under 10MB).
                    </p>

                    <div class="mt-3">
                        <input type="file" id="payment_screenshot" name="payment_screenshot" required accept="image/*,.pdf"
                               class="block w-full text-sm text-ink file:mr-4 file:rounded-xl file:border-0 file:bg-electric-600 file:px-4 file:py-2.5 file:text-sm file:font-bold file:text-white hover:file:bg-electric-700 cursor-pointer <?= $errFor('screenshot') !== null || $errFor('receipt') !== null ? 'field-error' : '' ?>">
                    </div>

                    <?php if ($errFor('screenshot') !== null): ?>
                    <p class="field-hint mt-1.5 !font-semibold !text-flame-600"><?= e($errFor('screenshot')) ?></p>
                    <?php endif; ?>
                    <?php if ($errFor('receipt') !== null): ?>
                    <p class="field-hint mt-1.5 !font-semibold !text-flame-600"><?= e($errFor('receipt')) ?></p>
                    <?php endif; ?>

                    <!-- Optional Transaction Ref -->
                    <div class="mt-4 pt-4 border-t border-line">
                        <label for="payment_reference" class="block text-[13px] font-bold text-ink">
                            Transaction ID / Reference Number <span class="text-ink-muted font-normal">(optional)</span>
                        </label>
                        <input type="text" id="payment_reference" name="payment_reference" maxlength="120"
                               placeholder="e.g. SONE-1234567 or Bank Reference"
                               value="<?= e((string) ($old['payment_reference'] ?? '')) ?>"
                               class="input mt-1.5">
                    </div>
                </div>

                <!-- WhatsApp verification reminder -->
                <div class="mt-4 flex items-center justify-between gap-3 rounded-xl border border-jade-200 bg-jade-50/70 p-3.5 text-[13px] text-jade-900">
                    <div class="flex items-center gap-2.5">
                        <span class="grid h-8 w-8 place-items-center rounded-lg bg-[#25d366] text-white shadow-sm shrink-0">
                            <svg viewBox="0 0 24 24" fill="currentColor" class="h-4.5 w-4.5"><path d="M12.04 2c-5.46 0-9.91 4.45-9.91 9.91 0 1.75.46 3.45 1.32 4.95L2.05 22l5.25-1.38c1.45.79 3.08 1.21 4.74 1.21 5.46 0 9.91-4.45 9.91-9.91 0-2.65-1.03-5.14-2.9-7.01A9.82 9.82 0 0 0 12.04 2zm.01 1.67c4.54 0 8.24 3.7 8.24 8.24 0 2.2-.86 4.27-2.42 5.82-1.55 1.56-3.62 2.42-5.82 2.42-1.42 0-2.81-.37-4.04-1.08l-.29-.17-3.01.79.8-2.93-.19-.3a8.21 8.21 0 0 1-1.26-4.31c0-4.54 3.7-8.24 8.24-8.24zm4.52 11.66c-.25-.13-1.47-.72-1.7-.81-.23-.08-.39-.13-.56.13-.17.25-.64.81-.79.97-.14.17-.29.19-.54.06-.25-.13-1.06-.39-2.02-1.25-.75-.67-1.26-1.5-1.4-1.76-.15-.25-.02-.39.11-.51.11-.11.25-.29.38-.44.13-.14.17-.25.25-.42.08-.17.04-.31-.02-.44-.06-.13-.56-1.35-.77-1.85-.2-.49-.41-.42-.56-.43h-.48c-.17 0-.44.06-.67.31-.23.25-.88.86-.88 2.1 0 1.24.9 2.44 1.03 2.61.13.17 1.77 2.7 4.29 3.79.6.26 1.07.41 1.43.53.6.19 1.15.16 1.58.1.48-.07 1.47-.6 1.68-1.18.21-.58.21-1.08.15-1.18-.06-.1-.23-.17-.48-.29z"/></svg>
                        </span>
                        <div>
                            <span class="font-bold text-jade-950">WhatsApp Verification (+92 300 5970504)</span>
                            <p class="text-[12px] text-jade-800">After placing your order, send your payment screenshot on WhatsApp for instant approval.</p>
                        </div>
                    </div>
                </div>
            </section>

            <div class="mt-5 lg:hidden">
                <button type="submit" class="btn btn-primary btn-lg btn-block">Place Order — <?= price($total) ?></button>
            </div>
        </form>

        <!-- ============================ Summary ============================ -->
        <aside class="lg:col-span-5">
            <div class="card sticky top-32 p-6">
                <h2 class="text-[17px] font-extrabold text-ink">Order summary</h2>

                <ul class="mt-4 max-h-72 space-y-3 overflow-y-auto pr-1">
                    <?php foreach ($items as $item): ?>
                    <li class="flex items-start justify-between gap-3">
                        <span class="min-w-0 flex-1">
                            <span class="block text-[13.5px] font-semibold leading-snug text-ink"><?= e($item['name']) ?></span>
                            <span class="mt-0.5 block text-[12px] text-ink-muted"><?= (int) $item['quantity'] ?> × <?= price((float) $item['unit_price']) ?></span>
                        </span>
                        <span class="shrink-0 whitespace-nowrap text-[13.5px] font-bold tabular-nums text-ink">
                            <?= price((float) $item['unit_price'] * (int) $item['quantity']) ?>
                        </span>
                    </li>
                    <?php endforeach; ?>
                </ul>

                <dl class="mt-4 space-y-2.5 border-t border-line pt-4 text-[14px]">
                    <div class="flex items-baseline justify-between gap-3">
                        <dt class="text-ink-muted">Subtotal</dt>
                        <dd class="font-bold tabular-nums text-ink"><?= price($subtotal) ?></dd>
                    </div>
                    <div class="flex items-baseline justify-between gap-3">
                        <dt class="text-ink-muted">Delivery</dt>
                        <dd class="font-bold tabular-nums text-ink">
                            <?= $shipping > 0 ? price($shipping) : '<span class="text-jade-600">Free</span>' ?>
                        </dd>
                    </div>
                </dl>

                <div class="mt-4 flex items-baseline justify-between gap-3 border-t border-line pt-4">
                    <span class="text-[15px] font-bold text-ink">Total</span>
                    <span class="text-[24px] font-extrabold tabular-nums tracking-tight text-ink"><?= price($total) ?></span>
                </div>

                <button type="submit" form="checkout-form" class="btn btn-primary btn-lg btn-block mt-5 hidden lg:inline-flex">
                    Place Order — <?= price($total) ?>
                </button>

                <p class="mt-3 text-center text-[11.5px] font-semibold text-ink-muted">
                    Payment remains pending until verified by our team.
                </p>

                <ul class="mt-5 space-y-2.5 border-t border-line pt-4 text-[13px] text-ink-muted">
                    <li class="flex gap-2.5">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="mt-0.5 h-4 w-4 shrink-0 text-jade-600"><path d="M12 2l7 4v6c0 4.4-3 8.3-7 9-4-.7-7-4.6-7-9V6l7-4z"/><path d="M9 12l2 2 4-4"/></svg>
                        Every device is tested before it ships
                    </li>
                    <li class="flex gap-2.5">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="mt-0.5 h-4 w-4 shrink-0 text-jade-600"><path d="M3 12a9 9 0 1 0 3-6.7"/><path d="M3 4v5h5"/></svg>
                        7-day returns in original condition
                    </li>
                    <li class="flex gap-2.5">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="mt-0.5 h-4 w-4 shrink-0 text-jade-600"><path d="M4 4h16v12H4z"/><path d="M2 20h20"/></svg>
                        Printable invoice with every order
                    </li>
                </ul>
            </div>
        </aside>
    </div>
</div>
