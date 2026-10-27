<?php
/**
* Product detail page.
*
* @var array $product @var array $images @var array $specs @var array $variants
* @var ?array $selectedVariant @var array $reviews @var array $related
* @var array $recent @var array $breadcrumbs @var float $effectivePrice
* @var bool $discontinued @var ?float $salePrice  live campaign price, if any
*
* Integrity notes:
*  - The struck-through reference price is only shown when it is genuinely
*    higher than the price being charged.
*  - 'Only N left' reflects the real stock count;
we never invent scarcity.
*  - Stock/discount state is computed server-side, not trusted from the client.
*/
use App\Models\Product;
use App\Models\Wishlist;

$p = $product;
// stars.php expects this key; alias it so the rating renders.

$hasSchema = $product[ 'rating_count' ] > 0 && ( float ) $product[ 'rating_avg' ] > 0;

// ---- pricing -------------------------------------------------------------
$normalPrice  = ( float ) $product[ 'price' ];
$baseEffective = Product::effectivePrice( $product );
// A live campaign price beats the product's own sale price, but only when it
// is genuinely lower — otherwise we keep the honest lower of the two.
$price = ($salePrice !== null && $salePrice < $baseEffective) ? $salePrice : $baseEffective;
$hasSaving = $price < $normalPrice && $normalPrice > 0;
$saveAmount = $normalPrice - $price;
$savePct = $hasSaving ? (int) round((1 - $price / $normalPrice) * 100) : 0;

// ---- stock ---------------------------------------------------------------
$stock = (int) $product['stock'];
$isOut = $discontinued || $stock <= 0;
$lowStock = !$isOut && $stock <= 3;

// ---- "new" window ---------------------------------------------------------
$isNew = false;
if (!empty($product['published_at'])) {
    $ts = strtotime((string) $product['published_at']);
    $isNew = $ts !== false && $ts >= (time() - 86400 * 30);
}

$wishlisted = Wishlist::has((int) $product['id']);
$viewItemPayload = json_encode([
    'item_id'    => $product['sku'] ?: ('prod-' . $product['id']),
    'item_name'  => $product['name'],
    'value'      => $price,
    'currency'   => 'PKR',
    'category'   => $product['category_name'] ?? '',
], JSON_UNESCAPED_SLASHES);

$addCartPayload = json_encode([
    'item_id'   => $product['sku'] ?: ('prod-' . $product['id']),
    'item_name' => $product['name'],
    'price'     => $price,
], JSON_UNESCAPED_SLASHES);

// All gallery images, main first. image_url() normalizes the stored relative
// path (e.g. "img/products/x.svg") to a root-absolute "/img/products/x.svg".
$gallery = [
    ['path' => image_url($product['main_image']), 'alt' => $product['image_alt'] ?: $product['name']],
];
foreach ($images as $img) {
    if (!empty($img['path'])) {
        $gallery[] = [
            'path' => image_url($img['path']),
            'alt'  => $img['alt'] ?: ($product['name'] . ' — additional image'),
        ];
    }
}

// Group specs by their group label.
$specGroups = [];
foreach ($specs as $spec) {
    $specGroups[$spec['spec_group']][] = $spec;
}
?>
<nav aria-label="Breadcrumb" class="shell pt-5 text-sm text-ink-muted">
    <ol class="flex flex-wrap items-center gap-1.5">
        <?php foreach ($breadcrumbs as $i => $crumb): ?>
        <li class="flex items-center gap-1.5">
            <?php if ($i > 0): ?><span aria-hidden="true" class="text-line">/</span><?php endif; ?>
            <?php if ($i === count($breadcrumbs) - 1): ?>
            <span aria-current="page" class="text-ink font-medium"><?= e($crumb[0]) ?></span>
            <?php else: ?>
            <a href="<?= e($crumb[1]) ?>" class="hover:text-electric-600"><?= e($crumb[0]) ?></a>
            <?php endif; ?>
        </li>
        <?php endforeach; ?>
    </ol>
</nav>

<article class="shell pb-16" data-ga-view-item="<?= e($viewItemPayload) ?>">
    <div class="mt-6 grid min-w-0 gap-8 lg:grid-cols-12 lg:gap-12">

        <!-- ============================ Gallery ============================ -->
        <div class="min-w-0 lg:col-span-7" data-gallery>
            <div class="relative overflow-hidden rounded-2xl border border-line bg-surface-sunken">
                <img id="gallery-main"
                     src="<?= e($gallery[0]['path']) ?>"
                     alt="<?= e($gallery[0]['alt']) ?>"
                     width="800" height="800"
                     fetchpriority="high" decoding="async"
                     class="product-img aspect-square w-full cursor-zoom-in object-cover">

                <span class="absolute top-3 left-3 flex flex-col items-start gap-1.5">
                    <?php if ($savePct > 0): ?>
                    <span class="badge badge-sale px-2.5 py-1">-<?= $savePct ?>%</span>
                    <?php endif; ?>
                    <?php if ($isNew): ?>
                    <span class="badge badge-new px-2.5 py-1">New</span>
                    <?php endif; ?>
                    <?php if ($discontinued): ?>
                    <span class="badge bg-navy-900/90 text-white px-2.5 py-1">Discontinued</span>
                    <?php endif; ?>
                </span>
            </div>

            <?php if (count($gallery) > 1): ?>
            <ul class="mt-3 flex gap-3 overflow-x-auto pb-1">
                <?php foreach ($gallery as $i => $g): ?>
                <li class="shrink-0">
                    <button type="button" class="group block h-20 w-20 overflow-hidden rounded-xl border-2 transition-colors <?= $i === 0 ? 'border-electric-500' : 'border-line hover:border-electric-300' ?>"
                            data-gallery-thumb data-src="<?= e($g['path']) ?>" data-alt="<?= e($g['alt']) ?>"
                            aria-label="View image <?= $i + 1 ?>" aria-pressed="<?= $i === 0 ? 'true' : 'false' ?>">
                        <img src="<?= e($g['path']) ?>" alt="" width="80" height="80" loading="lazy" decoding="async"
                             class="product-img h-full w-full object-cover">
                    </button>
                </li>
                <?php endforeach; ?>
            </ul>
            <?php endif; ?>
        </div>

        <!-- ============================= Details =========================== -->
        <div class="min-w-0 lg:col-span-5">
            <div class="lg:sticky lg:top-32">
                <?php if (!empty($product['brand_name'])): ?>
                <a href="/brand/<?= e($product['brand_slug']) ?>" class="text-[12px] font-bold uppercase tracking-wider text-electric-600 hover:text-electric-700">
                    <?= e($product['brand_name']) ?>
                </a>
                <?php endif; ?>

                <h1 class="mt-1.5 text-[26px] md:text-3xl font-extrabold leading-tight tracking-tight text-ink">
                    <?= e($product['name']) ?>
                </h1>

                <div class="mt-2.5">
                    <?php include BASE_PATH . '/app/views/partials/stars.php'; ?>
                </div>

                <?php if (!empty($product['short_desc'])): ?>
                <p class="mt-4 text-[15px] leading-relaxed text-ink-muted"><?= e($product['short_desc']) ?></p>
                <?php endif; ?>

                <!-- Price -->
                <div class="mt-5 rounded-2xl border border-line bg-white p-4">
                    <div class="flex flex-wrap items-baseline gap-x-3 gap-y-1">
                        <span class="text-[30px] font-extrabold tracking-tight <?= $hasSaving ? 'text-flame-600' : 'text-ink' ?>">
                            <?= price($price) ?>
                        </span>
                        <?php if ($hasSaving): ?>
                        <span class="text-[17px] text-ink-muted line-through"><?= price($normalPrice) ?></span>
                        <span class="rounded-md bg-jade-50 px-2 py-0.5 text-[12px] font-bold text-jade-700">
                            Save <?= price($saveAmount) ?>
                        </span>
                        <?php endif; ?>
                    </div>

                    <p class="mt-3 flex flex-wrap items-center gap-x-3 gap-y-1 text-[13px] font-semibold">
                        <?php if ($discontinued): ?>
                        <span class="inline-flex items-center gap-1.5 text-amber-600">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="h-4 w-4"><circle cx="12" cy="12" r="9"/><path d="M12 8v5M12 16h.01"/></svg>
                            Discontinued — no longer available new
                        </span>
                        <?php elseif ($isOut): ?>
                        <span class="inline-flex items-center gap-1.5 text-flame-600">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="h-4 w-4"><circle cx="12" cy="12" r="9"/><path d="M15 9l-6 6M9 9l6 6"/></svg>
                            Out of stock
                        </span>
                        <?php else: ?>
                        <span class="inline-flex items-center gap-1.5 text-jade-600">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" class="h-4 w-4"><path d="M20 6L9 17l-5-5"/></svg>
                            In stock
                        </span>
                        <?php endif; ?>
                        <?php if ($lowStock): ?>
                        <span class="text-flame-600">Only <?= $stock ?> left in stock</span>
                        <?php endif; ?>
                        <?php if (!empty($product['warranty'])): ?>
                        <span class="text-ink-muted">· <?= e($product['warranty']) ?></span>
                        <?php endif; ?>
                        <?php if (!empty($product['condition'])): ?>
                        <span class="text-ink-muted">· <?= e(ucfirst((string) $product['condition'])) ?></span>
                        <?php endif; ?>
                    </p>
                </div>

                <!-- Variants -->
                <?php if ($variants !== []): ?>
                <div class="mt-5">
                    <label for="variant-select" class="mb-2 block text-[13px] font-bold uppercase tracking-wider text-ink-muted">Configuration</label>
                    <select id="variant-select" data-base-url="/product/<?= e($product['slug']) ?>"
                            class="w-full rounded-xl border border-line bg-white px-3.5 py-3 text-[15px] focus:border-electric-400 focus:outline-none focus:ring-2 focus:ring-electric-500/20">
                        <?php foreach ($variants as $v): ?>
                        <option value="<?= (int) $v['id'] ?>"
                            <?= $selectedVariant !== null && (int) $selectedVariant['id'] === (int) $v['id'] ? 'selected' : '' ?>>
                            <?= e($v['label']) ?><?= $v['price_delta'] > 0 ? ' ( +' . price($v['price_delta']) . ' )' : ($v['price_delta'] < 0 ? ' ( ' . price($v['price_delta']) . ' )' : '') ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                    <?php if ($selectedVariant !== null): ?>
                    <p class="mt-2 text-[13px] text-ink-muted">
                        Viewing a specific configuration · <a href="/product/<?= e($product['slug']) ?>" class="font-semibold text-electric-600 hover:underline">view the standard model</a>
                    </p>
                    <?php endif; ?>
                </div>
                <?php endif; ?>

                <!-- Add to cart / wishlist / WhatsApp -->
                <?php
                $prodBase = (string) \App\Seo\SeoSettings::get('canonical_domain', 'https://www.lalamobiles.pk');
                $prodPageUrl = rtrim($prodBase, '/') . '/product/' . $product['slug'];
                $waPageMsg = rawurlencode("Salam Lala Mobiles! I want to order / inquire about:\n*" . $product['name'] . "*\nPrice: " . number_format($selling, 0) . " PKR" . ($selectedVariant !== null ? "\nVariant: " . $selectedVariant['label'] : '') . "\nLink: {$prodPageUrl}");
                $waProductUrl = "https://wa.me/923005970504?text={$waPageMsg}";
                ?>
                <div class="mt-5 flex flex-wrap items-stretch gap-3">
                    <?php if (!$isOut): ?>
                    <form id="product-cart-form" method="post" action="/cart/add" class="flex min-w-0 flex-1 flex-wrap items-stretch gap-3 sm:flex-nowrap"
                          data-ga-add-to-cart="<?= e($addCartPayload) ?>">
                        <?= \App\Core\Csrf::field() ?>
                        <input type="hidden" name="product_id" value="<?= (int) $product['id'] ?>">
                        <?php if ($selectedVariant !== null): ?>
                        <input type="hidden" name="variant_id" value="<?= (int) $selectedVariant['id'] ?>">
                        <?php endif; ?>

                        <label for="qty" class="sr-only">Quantity</label>
                        <div class="flex items-center rounded-xl border border-line bg-white">
                            <button type="button" class="grid h-12 w-10 place-items-center text-ink-muted hover:text-electric-600" data-qty-dec aria-label="Decrease quantity">−</button>
                            <input type="number" id="qty" name="quantity" value="1" min="1" max="<?= max(1, min(10, $stock)) ?>"
                                   data-qty-input
                                   class="w-12 border-0 bg-transparent py-3 text-center text-[15px] font-bold focus:outline-none">
                            <button type="button" class="grid h-12 w-10 place-items-center text-ink-muted hover:text-electric-600" data-qty-inc aria-label="Increase quantity">+</button>
                        </div>

                        <button type="submit" class="btn btn-dark btn-md flex-1">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="h-4.5 w-4.5" style="width:1.1rem;height:1.1rem"><path d="M3 4h2l2.4 12.3a1 1 0 0 0 1 .8h9.7a1 1 0 0 0 1-.8L21 8H6"/><circle cx="9" cy="20" r="1.4"/><circle cx="18" cy="20" r="1.4"/></svg>
                            Add to Cart
                        </button>
                    </form>
                    <?php else: ?>
                    <button type="button" disabled class="btn btn-ghost btn-md flex-1 opacity-60 cursor-not-allowed">
                        <?= $discontinued ? 'Discontinued' : 'Out of stock' ?>
                    </button>
                    <?php endif; ?>

                    <button type="button"
                            class="grid h-12 w-12 shrink-0 place-items-center rounded-xl border"
                            data-wishlist-toggle data-active="<?= $wishlisted ? 'true' : 'false' ?>"
                            data-product-id="<?= (int) $product['id'] ?>"
                            aria-pressed="<?= $wishlisted ? 'true' : 'false' ?>"
                            aria-label="<?= $wishlisted ? 'Remove from' : 'Add to' ?> wishlist: <?= e($product['name']) ?>">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" class="h-5 w-5 wl-icon"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg>
                    </button>
                </div>

                <!-- Instant WhatsApp Direct Order Button -->
                <a href="<?= e($waProductUrl) ?>" target="_blank" rel="noopener noreferrer"
                   class="mt-3 flex w-full items-center justify-center gap-2.5 rounded-xl bg-[#25D366] px-5 py-3 text-[14.5px] font-extrabold text-white shadow-sm transition-all hover:bg-[#20ba59] hover:shadow-md">
                    <svg viewBox="0 0 24 24" fill="currentColor" class="h-5 w-5"><path d="M12.04 2c-5.46 0-9.91 4.45-9.91 9.91 0 1.75.46 3.45 1.32 4.95L2.05 22l5.25-1.38c1.45.79 3.08 1.21 4.74 1.21 5.46 0 9.91-4.45 9.91-9.91 0-2.65-1.03-5.14-2.9-7.01A9.82 9.82 0 0 0 12.04 2zm.01 1.67c4.54 0 8.24 3.7 8.24 8.24 0 2.2-.86 4.27-2.42 5.82-1.55 1.56-3.62 2.42-5.82 2.42-1.42 0-2.81-.37-4.04-1.08l-.29-.17-3.01.79.8-2.93-.19-.3a8.21 8.21 0 0 1-1.26-4.31c0-4.54 3.7-8.24 8.24-8.24zm4.52 11.66c-.25-.13-1.47-.72-1.7-.81-.23-.08-.39-.13-.56.13-.17.25-.64.81-.79.97-.14.17-.29.19-.54.06-.25-.13-1.06-.39-2.02-1.25-.75-.67-1.26-1.5-1.4-1.76-.15-.25-.02-.39.11-.51.11-.11.25-.29.38-.44.13-.14.17-.25.25-.42.08-.17.04-.31-.02-.44-.06-.13-.56-1.35-.77-1.85-.2-.49-.41-.42-.56-.43h-.48c-.17 0-.44.06-.67.31-.23.25-.88.86-.88 2.1 0 1.24.9 2.44 1.03 2.61.13.17 1.77 2.7 4.29 3.79.6.26 1.07.41 1.43.53.6.19 1.15.16 1.58.1.48-.07 1.47-.6 1.68-1.18.21-.58.21-1.08.15-1.18-.06-.1-.23-.17-.48-.29z"/></svg>
                    Order / Inquire via WhatsApp (+92 300 5970504)
                </a>

                <!-- Reassurance list -->
                <ul class="mt-5 space-y-2.5 rounded-2xl border border-line bg-surface p-4 text-[14px] text-ink-muted">
                    <li class="flex gap-2.5">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="mt-0.5 h-4 w-4 shrink-0 text-jade-600"><path d="M3 10h18M5 10V6l7-3 7 3v4M5 10v8m4-8v8m6-8v8m4-8v8M3 21h18"/></svg>
                        <span>Official <strong class="text-ink">Soneri Bank</strong> transfer (Lala Mobiles - PK62SONE0000320013557234)</span>
                    </li>
                    <li class="flex gap-2.5">
                        <svg viewBox="0 0 24 24" fill="currentColor" class="mt-0.5 h-4 w-4 shrink-0 text-emerald-600"><path d="M12.04 2c-5.46 0-9.91 4.45-9.91 9.91 0 1.75.46 3.45 1.32 4.95L2.05 22l5.25-1.38c1.45.79 3.08 1.21 4.74 1.21 5.46 0 9.91-4.45 9.91-9.91 0-2.65-1.03-5.14-2.9-7.01A9.82 9.82 0 0 0 12.04 2zm.01 1.67c4.54 0 8.24 3.7 8.24 8.24 0 2.2-.86 4.27-2.42 5.82-1.55 1.56-3.62 2.42-5.82 2.42-1.42 0-2.81-.37-4.04-1.08l-.29-.17-3.01.79.8-2.93-.19-.3a8.21 8.21 0 0 1-1.26-4.31c0-4.54 3.7-8.24 8.24-8.24zm4.52 11.66c-.25-.13-1.47-.72-1.7-.81-.23-.08-.39-.13-.56.13-.17.25-.64.81-.79.97-.14.17-.29.19-.54.06-.25-.13-1.06-.39-2.02-1.25-.75-.67-1.26-1.5-1.4-1.76-.15-.25-.02-.39.11-.51.11-.11.25-.29.38-.44.13-.14.17-.25.25-.42.08-.17.04-.31-.02-.44-.06-.13-.56-1.35-.77-1.85-.2-.49-.41-.42-.56-.43h-.48c-.17 0-.44.06-.67.31-.23.25-.88.86-.88 2.1 0 1.24.9 2.44 1.03 2.61.13.17 1.77 2.7 4.29 3.79.6.26 1.07.41 1.43.53.6.19 1.15.16 1.58.1.48-.07 1.47-.6 1.68-1.18.21-.58.21-1.08.15-1.18-.06-.1-.23-.17-.48-.29z"/></svg>
                        <span>Send payment screenshot on WhatsApp: <a href="https://wa.me/923005970504" class="font-bold text-emerald-700 hover:underline">+92 300 5970504</a></span>
                    </li>
                    <li class="flex gap-2.5">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="mt-0.5 h-4 w-4 shrink-0 text-jade-600"><path d="M12 2l7 4v6c0 4.4-3 8.3-7 9-4-0.7-7-4.6-7-9V6l7-4z"/><path d="M9 12l2 2 4-4"/></svg>
                        Tested before dispatch — battery, screen and cameras checked
                    </li>
                    <li class="flex gap-2.5">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="mt-0.5 h-4 w-4 shrink-0 text-jade-600"><path d="M3 12a9 9 0 1 0 3-6.7"/><path d="M3 4v5h5"/></svg>
                        7-day returns in original condition
                    </li>
                    <?php if (!empty($product['category_name'])): ?>
                    <li class="flex gap-2.5">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="mt-0.5 h-4 w-4 shrink-0 text-electric-500"><rect x="6" y="2" width="12" height="20" rx="2.5"/><path d="M11 18.5h2"/></svg>
                        <a href="/category/<?= e($product['category_slug']) ?>" class="hover:text-electric-600">Browse all <?= e($product['category_name']) ?></a>
                    </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </div>

    <!-- ====================== Description + specs ====================== -->
    <div class="mt-14 grid min-w-0 gap-8 lg:grid-cols-12">
        <?php if (!empty($product['description'])): ?>
        <section class="min-w-0 lg:col-span-7" aria-labelledby="desc-heading">
            <h2 id="desc-heading" class="section-title text-ink">Product Description</h2>
            <div class="prose-body mt-4 rounded-2xl border border-line bg-white p-6">
                <?= $product['description'] /* sanitized by sanitizeRichHtml() on save */ ?>
            </div>
        </section>
        <?php endif; ?>

        <?php if ($specGroups !== []): ?>
        <section class="min-w-0 lg:col-span-5" aria-labelledby="specs-heading">
            <h2 id="specs-heading" class="section-title text-ink">Specifications</h2>
            <div class="mt-4 overflow-hidden rounded-2xl border border-line bg-white">
                <table class="w-full text-sm">
                    <caption class="sr-only">Full technical specifications of <?= e($product['name']) ?></caption>
                    <tbody>
                    <?php foreach ($specGroups as $group => $rows): ?>
                    <tr>
                        <th scope="colgroup" colspan="2" class="bg-surface px-4 py-2.5 text-left text-[12px] font-bold uppercase tracking-wider text-ink-muted"><?= e($group) ?></th>
                    </tr>
                    <?php foreach ($rows as $spec): ?>
                    <tr class="border-t border-line">
                        <th scope="row" class="w-2/5 px-4 py-2.5 text-left align-top font-medium text-ink-muted"><?= e($spec['spec_label']) ?></th>
                        <td class="px-4 py-2.5 align-top text-ink"><?= e($spec['spec_value']) ?></td>
                    </tr>
                    <?php endforeach; ?>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </section>
        <?php endif; ?>
    </div>

    <!-- ============================= Reviews =========================== -->
    <section class="mt-14" aria-labelledby="reviews-heading">
        <h2 id="reviews-heading" class="section-title text-ink">Customer Reviews</h2>

        <?php if ($reviews === []): ?>
        <p class="mt-4 rounded-2xl border border-line bg-white p-6 text-ink-muted">
            No reviews yet for this product. Reviews appear once a verified buyer submits one.
        </p>
        <?php else: ?>
        <ul class="mt-5 grid gap-4 sm:grid-cols-2">
            <?php foreach ($reviews as $r): $rating = max(1, min(5, (int) $r['rating'])); ?>
            <li class="flex h-full flex-col rounded-2xl border border-line bg-white p-5">
                <div class="flex items-center gap-1" role="img" aria-label="Rated <?= $rating ?> out of 5">
                    <?php for ($i = 1; $i <= 5; $i++): ?>
                    <svg viewBox="0 0 24 24" class="h-4 w-4 <?= $i <= $rating ? 'text-gold-400' : 'text-line' ?>"
                         fill="<?= $i <= $rating ? 'currentColor' : 'none' ?>" stroke="currentColor" stroke-width="1.6">
                        <path d="M12 2l2.9 6.3 6.8.8-5 4.7 1.3 6.8L12 17.8 6 20.6l1.3-6.8-5-4.7 6.8-.8L12 2Z"/>
                    </svg>
                    <?php endfor; ?>
                </div>
                <?php if (!empty($r['title'])): ?>
                <h3 class="mt-3 text-[15px] font-bold text-ink"><?= e($r['title']) ?></h3>
                <?php endif; ?>
                <?php if (!empty($r['body'])): ?>
                <p class="mt-2 flex-1 text-[14px] leading-relaxed text-ink-muted"><?= e($r['body']) ?></p>
                <?php endif; ?>
                <div class="mt-4 flex items-center justify-between gap-3 border-t border-line pt-3.5">
                    <p class="text-[13px] font-bold text-ink"><?= e($r['author']) ?></p>
                    <?php if (!empty($r['verified_purchase'])): ?>
                    <span class="inline-flex items-center gap-1 rounded-full bg-jade-50 px-2.5 py-1 text-[11px] font-bold text-jade-700">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6" class="h-3 w-3"><path d="M20 6L9 17l-5-5"/></svg>
                        Verified purchase
                    </span>
                    <?php endif; ?>
                </div>
            </li>
            <?php endforeach; ?>
        </ul>
        <?php endif; ?>
    </section>

    <!-- ======================= Related + recent ======================== -->
    <?php if ($related !== []): ?>
    <section class="mt-14" aria-labelledby="related-heading">
        <h2 id="related-heading" class="section-title text-ink">Related Products</h2>
        <div class="mt-5">
            <?= \App\Core\View::partial('partials/product-row', ['products' => $related, 'ctx' => 'related', 'carousel' => false]) ?>
        </div>
    </section>
    <?php endif; ?>

    <?php if ($recent !== []): ?>
    <section class="mt-14" aria-labelledby="recent-heading">
        <h2 id="recent-heading" class="section-title text-ink">Recently Viewed</h2>
        <div class="mt-5">
            <?= \App\Core\View::partial('partials/product-row', ['products' => $recent, 'ctx' => 'recent', 'carousel' => true]) ?>
        </div>
    </section>
    <?php endif; ?>
</article>

<div class="fixed inset-x-0 bottom-0 z-40 border-t border-line bg-white/95 p-3 shadow-lift backdrop-blur md:hidden" data-sticky-buy hidden>
    <div class="mx-auto flex max-w-7xl items-center gap-3">
        <div class="min-w-0 flex-1"><p class="truncate text-xs font-semibold text-ink-muted"><?= e($product['name']) ?></p><p class="text-base font-extrabold text-ink"><?= price($price) ?></p></div>
        <?php if (!$isOut): ?><button type="button" class="btn btn-primary btn-md" data-sticky-buy-submit>Add to cart</button><?php else: ?><button type="button" disabled class="btn btn-ghost btn-md">Out of stock</button><?php endif; ?>
    </div>
</div>

<div id="gallery-lightbox" class="fixed inset-0 z-[95] hidden items-center justify-center bg-slate-950/90 p-4" role="dialog" aria-modal="true" aria-label="Product image preview" data-gallery-lightbox>
    <button type="button" class="absolute right-4 top-4 grid h-11 w-11 place-items-center rounded-full bg-white/10 text-white hover:bg-white/20" data-gallery-lightbox-close aria-label="Close image preview"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="h-6 w-6"><path d="m18 6-12 12M6 6l12 12"/></svg></button>
    <img src="<?= e($gallery[0]['path']) ?>" alt="<?= e($gallery[0]['alt' ] ) ?>' class='max-h-[ 88vh ] max-w-full rounded-xl object-contain" data-gallery-lightbox-image>
</div>
