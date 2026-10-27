<?php
/**
 * Wishlist page.
 *
 * Anonymous and cookie-backed — there is no customer account requirement, so
 * the copy says "this device" rather than implying a login.
 *
 * Both the empty state and the grid are ALWAYS rendered; only one is visible.
 * That lets the AJAX heart remove the last card and reveal the empty state
 * without a page reload (see syncWishlistPage() in ui.js).
 *
 * Every action here is real:
 *   - the heart on each card   → POST /wishlist/toggle (AJAX, card animates out)
 *   - Add to cart on each card → POST /cart/add
 *   - Empty-state links        → real category / deals routes
 *
 * @var array $items  product rows for the visitor's saved items
 */
$wishlistCount = count($items);
$hasItems      = $wishlistCount > 0;
?>
<div class="shell py-8 md:py-10">

    <nav aria-label="Breadcrumb" class="mb-5 text-sm text-ink-muted">
        <ol class="flex flex-wrap items-center gap-1.5">
            <li><a href="/" class="transition-colors hover:text-electric-600">Home</a></li>
            <li aria-hidden="true">/</li>
            <li><span aria-current="page" class="font-medium text-ink">Wishlist</span></li>
        </ol>
    </nav>

    <!-- ============================== Page header ============================== -->
    <header class="flex flex-wrap items-center justify-between gap-4">
        <div class="flex items-center gap-4">
            <span class="grid h-12 w-12 shrink-0 place-items-center rounded-2xl bg-gradient-to-br from-flame-500 to-flame-600 text-white shadow-[0_10px_24px_-10px_rgba(239,68,68,0.7)]"
                  aria-hidden="true">
                <svg viewBox="0 0 24 24" fill="currentColor" class="h-6 w-6">
                    <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/>
                </svg>
            </span>
            <div>
                <h1 class="text-2xl font-extrabold tracking-tight text-ink md:text-3xl">My Wishlist</h1>
                <p class="mt-1 text-sm text-ink-muted" data-wishlist-count-text>
                    <?= $hasItems
                        ? number_format($wishlistCount) . ' saved item' . ($wishlistCount === 1 ? '' : 's')
                        : 'Nothing saved yet' ?>
                </p>
            </div>
        </div>

        <div class="flex flex-wrap items-center gap-2.5">
            <a href="/cart" class="btn btn-ghost btn-md">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" class="h-4 w-4" aria-hidden="true">
                    <path d="M3 4h2l2.4 12.3a1 1 0 0 0 1 .8h9.7a1 1 0 0 0 1-.8L21 8H6"/>
                    <circle cx="9" cy="20" r="1.4"/><circle cx="18" cy="20" r="1.4"/>
                </svg>
                View cart
            </a>
            <a href="/category/smartphones" class="btn btn-dark btn-md">Continue shopping</a>
        </div>
    </header>

    <!-- ================================ Toolbar =============================== -->
    <div class="mt-6 flex flex-wrap items-center justify-between gap-2 rounded-xl border border-line bg-surface-muted px-4 py-2.5 <?= $hasItems ? '' : 'hidden' ?>"
         data-wishlist-toolbar>
        <p class="flex items-center gap-2 text-[13px] text-ink-muted">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" class="h-4 w-4 shrink-0 text-jade-600" aria-hidden="true">
                <path d="M12 3a9 9 0 1 0 9 9"/><path d="M12 7v5l3 2"/><path d="M16 3h5v5"/>
            </svg>
            Saved on this device — tap a heart to remove an item.
        </p>
        <span class="inline-flex items-center gap-1.5 rounded-full bg-white px-2.5 py-1 text-[11.5px] font-bold text-ink-muted ring-1 ring-line">
            <span class="h-1.5 w-1.5 rounded-full bg-jade-500" aria-hidden="true"></span>
            Kept until you remove it
        </span>
    </div>

    <!-- ============================== Empty state ============================= -->
    <div class="mt-6 <?= $hasItems ? 'hidden' : '' ?>" data-wishlist-empty>
        <div class="card mx-auto max-w-2xl overflow-hidden p-0 text-center">
            <div class="relative bg-gradient-to-br from-flame-50 via-white to-electric-50 px-6 py-12 md:px-10">
                <div class="mx-auto grid h-20 w-20 place-items-center rounded-full bg-white text-flame-500 shadow-lift ring-1 ring-flame-100" aria-hidden="true">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" class="h-9 w-9">
                        <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/>
                    </svg>
                </div>

                <h2 tabindex="-1" class="mt-5 text-xl font-extrabold tracking-tight text-ink outline-none md:text-2xl">
                    Your wishlist is empty
                </h2>
                <p class="mx-auto mt-2 max-w-md text-[14.5px] leading-relaxed text-ink-muted">
                    Tap the heart on any product and it will wait for you here — even if you close the browser.
                </p>

                <div class="mt-7 flex flex-wrap justify-center gap-3">
                    <a href="/category/smartphones" class="btn btn-primary btn-md">Browse smartphones</a>
                    <a href="/deals" class="btn btn-ghost btn-md">See today's deals</a>
                </div>
            </div>

            <div class="border-t border-line px-6 py-5 md:px-10">
                <p class="text-[11.5px] font-bold uppercase tracking-[0.14em] text-ink-muted">Popular destinations</p>
                <div class="mt-3 flex flex-wrap items-center justify-center gap-2">
                    <a href="/category/used-phones" class="badge badge-soft px-3 py-1.5 transition-colors hover:border-electric-300 hover:text-electric-600">Certified used</a>
                    <a href="/category/accessories" class="badge badge-soft px-3 py-1.5 transition-colors hover:border-electric-300 hover:text-electric-600">Accessories</a>
                    <a href="/repair/screen-replacement" class="badge badge-soft px-3 py-1.5 transition-colors hover:border-electric-300 hover:text-electric-600">Repairs</a>
                    <a href="/blog" class="badge badge-soft px-3 py-1.5 transition-colors hover:border-electric-300 hover:text-electric-600">Buying guides</a>
                </div>
            </div>
        </div>
    </div>

    <!-- ================================ Grid ================================== -->
    <div class="product-grid mt-5 <?= $hasItems ? '' : 'hidden' ?>"
         data-wishlist-grid>
        <?php foreach ($items as $p):
            $p = $p + [
                'brand_name' => $p['brand_name'] ?? '',
                'brand_slug' => $p['brand_slug'] ?? '',
            ];
            $ctx = 'wishlist';
            include BASE_PATH . '/app/views/partials/product-card.php';
        endforeach; ?>
    </div>

    <div class="mt-8 flex flex-wrap items-center justify-between gap-3 rounded-xl border border-line bg-surface p-4 <?= $hasItems ? '' : 'hidden' ?>"
         data-wishlist-foot>
        <p class="text-[13px] text-ink-muted">
            Looking for something else? Browse the full catalogue or check today's live offers.
        </p>
        <div class="flex flex-wrap gap-2">
            <a href="/category/smartphones" class="btn btn-ghost btn-sm">All smartphones</a>
            <a href="/deals" class="btn btn-dark btn-sm">Today's deals</a>
        </div>
    </div>
</div>
