<?php
/**
 * Cart — server-side session cart.
 *
 * @var array $items @var float $subtotal @var float $shipping
 *
 * Item shape (from Cart::add): product_id, variant_id, name, sku,
 * unit_price, quantity, image. No slug or stock is stored here, so the line
 * links by product_id lookup below only when a slug is available.
 *
 * This page is noindex (private area) — set by the controller.
 */
use App\Models\Product;

// Resolve a linkable slug + live stock per line (cheap: one query per unique id).
$links = [];
foreach ($items as $item) {
    $pid = (int) ($item['product_id'] ?? 0);
    if ($pid === 0 || isset($links[$pid])) {
        continue;
    }
    $row = Product::find($pid);
    $links[$pid] = $row !== null
        ? ['slug' => (string) $row['slug'], 'stock' => (int) $row['stock'], 'status' => (string) $row['status']]
        : null;
}

$hasLowStock = false;
foreach ($items as $item) {
    $info = $links[(int) ($item['product_id'] ?? 0)] ?? null;
    if ($info !== null && $info['status'] === 'active' && $info['stock'] > 0 && $info['stock'] <= 3) {
        $hasLowStock = true;
        break;
    }
}

$total = $subtotal + $shipping;
$freeThreshold = 5000.0;
?>
<div class="shell pb-16 pt-5">
    <nav aria-label="Breadcrumb" class="text-sm text-ink-muted">
        <ol class="flex flex-wrap items-center gap-1.5">
            <li><a href="/" class="hover:text-electric-600">Home</a></li>
            <li aria-hidden="true" class="text-line">/</li>
            <li aria-current="page" class="text-ink font-medium">Cart</li>
        </ol>
    </nav>

    <h1 class="mt-5 text-[28px] md:text-4xl font-extrabold tracking-tight text-ink">Your Cart</h1>

    <?php if ($items === []): ?>
    <!-- Empty state -->
    <div class="mt-8 rounded-2xl border border-dashed border-line bg-white p-14 text-center">
        <span class="mx-auto grid h-16 w-16 place-items-center rounded-2xl bg-surface-muted text-ink-muted">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" class="h-8 w-8"><path d="M3 4h2l2.4 12.3a1 1 0 0 0 1 .8h9.7a1 1 0 0 0 1-.8L21 8H6"/><circle cx="9" cy="20" r="1.5"/><circle cx="18" cy="20" r="1.5"/></svg>
        </span>
        <h2 class="mt-5 text-xl font-extrabold text-ink">Your cart is empty</h2>
        <p class="mx-auto mt-2 max-w-md text-sm text-ink-muted">Browse the full range — new, used and refurbished smartphones, plus accessories.</p>
        <div class="mt-6 flex flex-wrap justify-center gap-3">
            <a href="/category/smartphones" class="btn btn-primary btn-md">Shop smartphones</a>
            <a href="/" class="btn btn-ghost btn-md">Back to home</a>
        </div>
    </div>

    <?php else: ?>
    <div class="mt-7 grid gap-7 lg:grid-cols-12 lg:gap-8">
        <!-- ============================ Line items ============================ -->
        <div class="lg:col-span-8">
            <ul class="space-y-4">
                <?php foreach ($items as $key => $item):
                    $pid  = (int) ($item['product_id'] ?? 0);
                    $info = $links[$pid] ?? null;
                    $qty  = max(1, (int) $item['quantity']);
                    $line = (float) $item['unit_price'] * $qty;
                    $out  = $info === null || $info['status'] !== 'active';
                    $low  = !$out && $info['stock'] > 0 && $info['stock'] <= 3 && $info['stock'] < $qty + 10;
                    $href = ($info !== null && $info['slug'] !== '') ? '/product/' . $info['slug'] : '#';
                ?>
                <li class="card overflow-hidden">
                    <div class="flex flex-col gap-4 p-4 sm:flex-row sm:items-center">
                        <!-- Thumb -->
                        <a href="<?= e($href) ?>" class="shrink-0 self-start">
                            <span class="block h-24 w-24 overflow-hidden rounded-xl border border-line bg-surface-sunken">
                                <img src="<?= e(image_url($item['image'] ?? null)) ?>" alt="<?= e($item['name']) ?>"
                                     width="96" height="96" loading="lazy" decoding="async" class="product-img h-full w-full object-cover">
                            </span>
                        </a>

                        <!-- Name + unit price -->
                        <div class="min-w-0 flex-1">
                            <a href="<?= e($href) ?>" class="text-[15px] font-bold leading-snug text-ink hover:text-electric-600 line-clamp-2"><?= e($item['name']) ?></a>
                            <?php if (!empty($item['sku'])): ?>
                            <p class="mt-1 text-[12px] font-semibold uppercase tracking-wide text-ink-muted">SKU <?= e($item['sku']) ?></p>
                            <?php endif; ?>
                            <p class="mt-1.5 text-[13px] text-ink-muted"><?= price((float) $item['unit_price']) ?> each</p>

                            <?php if ($out): ?>
                            <p class="mt-2 inline-flex items-center gap-1.5 rounded-lg bg-surface-muted px-2 py-1 text-[12px] font-bold text-ink-muted">
                                No longer available — remove to continue
                            </p>
                            <?php elseif ($low): ?>
                            <p class="mt-2 inline-flex items-center gap-1.5 rounded-lg bg-flame-50 px-2 py-1 text-[12px] font-bold text-flame-600">
                                Only <?= (int) $info['stock'] ?> left in stock
                            </p>
                            <?php endif; ?>
                        </div>

                        <!-- Quantity stepper -->
                        <form method="post" action="/cart/update" class="flex items-center gap-2">
                            <?= \App\Core\Csrf::field() ?>
                            <input type="hidden" name="key" value="<?= e($key) ?>">
                            <div class="flex items-center rounded-xl border border-line bg-white">
                                <button type="submit" name="quantity" value="<?= $qty - 1 ?>"
                                        class="grid h-10 w-9 place-items-center text-ink-muted hover:text-electric-600"
                                        aria-label="Decrease quantity of <?= e($item['name']) ?>">−</button>
                                <label class="sr-only" for="qty-<?= e($key) ?>">Quantity for <?= e($item['name']) ?></label>
                                <input type="number" id="qty-<?= e($key) ?>" name="quantity" value="<?= $qty ?>"
                                       min="0" max="10" inputmode="numeric"
                                       class="w-11 border-0 bg-transparent py-2 text-center text-[14px] font-bold focus:outline-none">
                                <button type="submit" name="quantity" value="<?= $qty + 1 ?>"
                                        class="grid h-10 w-9 place-items-center text-ink-muted hover:text-electric-600"
                                        aria-label="Increase quantity of <?= e($item['name']) ?>">+</button>
                            </div>
                        </form>

                        <!-- Line total + remove -->
                        <div class="flex items-center justify-between gap-3 sm:flex-col sm:items-end sm:gap-2">
                            <p class="text-[16px] font-extrabold tabular-nums text-ink"><?= price($line) ?></p>
                            <form method="post" action="/cart/remove">
                                <?= \App\Core\Csrf::field() ?>
                                <input type="hidden" name="key" value="<?= e($key) ?>">
                                <button type="submit"
                                        class="inline-flex items-center gap-1.5 rounded-lg px-2 py-1.5 text-[13px] font-semibold text-ink-muted transition-colors hover:bg-flame-50 hover:text-flame-600"
                                        aria-label="Remove <?= e($item['name']) ?> from cart">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" class="h-4 w-4"><path d="M4 7h16M10 11v6M14 11v6M6 7l1 13a1 1 0 0 0 1 1h8a1 1 0 0 0 1-1l1-13M9 7V4h6v3"/></svg>
                                    Remove
                                </button>
                            </form>
                        </div>
                    </div>
                </li>
                <?php endforeach; ?>
            </ul>

            <div class="mt-5 flex flex-wrap items-center justify-between gap-3">
                <a href="/" class="btn btn-ghost btn-sm">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="h-4 w-4"><path d="M19 12H5M11 18l-6-6 6-6"/></svg>
                    Continue shopping
                </a>
                <?php if ($hasLowStock): ?>
                <p class="text-[13px] font-semibold text-flame-600">Some items are running low — checkout soon to be sure of availability.</p>
                <?php endif; ?>
            </div>
        </div>

        <!-- ============================= Summary ============================= -->
        <aside class="lg:col-span-4">
            <div class="card sticky top-32 p-5">
                <h2 class="text-[17px] font-extrabold text-ink">Order summary</h2>

                <dl class="mt-4 space-y-3 text-[14px]">
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
                    <?php if ($shipping <= 0 && $subtotal > 0): ?>
                    <div class="flex items-start gap-2.5 rounded-xl bg-jade-50 px-3 py-2.5 text-[13px] font-semibold text-jade-700">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" class="mt-0.5 h-4 w-4 shrink-0"><path d="M20 6L9 17l-5-5"/></svg>
                        Free nationwide delivery applied.
                    </div>
                    <?php elseif ($subtotal > 0 && $subtotal < $freeThreshold): ?>
                    <div class="rounded-xl border border-line bg-surface px-3 py-2.5 text-[13px] text-ink-muted">
                        Free delivery on orders over <?= price($freeThreshold) ?>.
                    </div>
                    <?php endif; ?>
                </dl>

                <div class="mt-4 flex items-baseline justify-between gap-3 border-t border-line pt-4">
                    <span class="text-[15px] font-bold text-ink">Total</span>
                    <span class="text-[24px] font-extrabold tabular-nums tracking-tight text-ink"><?= price($total) ?></span>
                </div>

                <a href="/checkout" class="btn btn-primary btn-lg btn-block mt-5">
                    Proceed to Checkout
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" class="h-4 w-4"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
                </a>

                <ul class="mt-5 space-y-2.5 border-t border-line pt-4 text-[13px] text-ink-muted">
                    <li class="flex gap-2.5">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="mt-0.5 h-4 w-4 shrink-0 text-jade-600"><path d="M3 10h18M5 10V6l7-3 7 3v4M5 10v8m4-8v8m6-8v8m4-8v8M3 21h18"/></svg>
                        Official Soneri Bank transfer (Account: Lala Mobiles)
                    </li>
                    <li class="flex gap-2.5">
                        <svg viewBox="0 0 24 24" fill="currentColor" class="mt-0.5 h-4 w-4 shrink-0 text-emerald-600"><path d="M12.04 2c-5.46 0-9.91 4.45-9.91 9.91 0 1.75.46 3.45 1.32 4.95L2.05 22l5.25-1.38c1.45.79 3.08 1.21 4.74 1.21 5.46 0 9.91-4.45 9.91-9.91 0-2.65-1.03-5.14-2.9-7.01A9.82 9.82 0 0 0 12.04 2zm.01 1.67c4.54 0 8.24 3.7 8.24 8.24 0 2.2-.86 4.27-2.42 5.82-1.55 1.56-3.62 2.42-5.82 2.42-1.42 0-2.81-.37-4.04-1.08l-.29-.17-3.01.79.8-2.93-.19-.3a8.21 8.21 0 0 1-1.26-4.31c0-4.54 3.7-8.24 8.24-8.24zm4.52 11.66c-.25-.13-1.47-.72-1.7-.81-.23-.08-.39-.13-.56.13-.17.25-.64.81-.79.97-.14.17-.29.19-.54.06-.25-.13-1.06-.39-2.02-1.25-.75-.67-1.26-1.5-1.4-1.76-.15-.25-.02-.39.11-.51.11-.11.25-.29.38-.44.13-.14.17-.25.25-.42.08-.17.04-.31-.02-.44-.06-.13-.56-1.35-.77-1.85-.2-.49-.41-.42-.56-.43h-.48c-.17 0-.44.06-.67.31-.23.25-.88.86-.88 2.1 0 1.24.9 2.44 1.03 2.61.13.17 1.77 2.7 4.29 3.79.6.26 1.07.41 1.43.53.6.19 1.15.16 1.58.1.48-.07 1.47-.6 1.68-1.18.21-.58.21-1.08.15-1.18-.06-.1-.23-.17-.48-.29z"/></svg>
                        Instant WhatsApp help &amp; verification: <a href="https://wa.me/923005970504" target="_blank" class="font-bold text-emerald-700 hover:underline">+92 300 5970504</a>
                    </li>
                    <li class="flex gap-2.5">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="mt-0.5 h-4 w-4 shrink-0 text-jade-600"><path d="M12 2l7 4v6c0 4.4-3 8.3-7 9-4-.7-7-4.6-7-9V6l7-4z"/><path d="M9 12l2 2 4-4"/></svg>
                        Tested before dispatch, with a written warranty
                    </li>
                    <li class="flex gap-2.5">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="mt-0.5 h-4 w-4 shrink-0 text-jade-600"><path d="M3 12a9 9 0 1 0 3-6.7"/><path d="M3 4v5h5"/></svg>
                        7-day returns in original condition
                    </li>
                </ul>
            </div>
        </aside>
    </div>
    <?php endif; ?>
</div>
