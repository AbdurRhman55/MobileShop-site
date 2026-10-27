<?php
/**
 * Shared storefront header.
 *
 * Everything here is database- and settings-driven:
 *   - announcement bar items  → SiteContent::announcementItems()
 *   - search suggestions      → Product::searchSuggestions() (fetched by JS)
 *   - main categories + their children → Catalog::navCategories()
 *   - cart / wishlist counts  → Cart::count(), SiteContent::wishlistCount()
 *   - contact details         → SeoSettings (organization_phone, email)
 *
 * Layout: the whole chrome (announcement, main bar, category bar) is
 * FULL-BLEED via `.nav-shell` — no max-width container — so the navbar spans
 * the viewport at every breakpoint on every page.
 *
 * @var array  $navCategories  top-level categories (with 'children')
 * @var int    $cartCount
 * @var int    $wishlistCount
 * @var string $siteName
 * @var string $siteTagline
 * @var string $canonicalBase  canonical origin (never from Host header)
 */
use App\Models\SiteContent;

$navCategories = $navCategories ?? [];
$cartCount     = (int) ($cartCount ?? 0);
$wishlistCount = (int) ($wishlistCount ?? 0);
$siteName      = $siteName ?? 'Lala Mobiles';
$siteTagline   = $siteTagline ?? 'Your Mobile, Our Priority';
$canonicalBase = $canonicalBase ?? '';

$announcements = SiteContent::announcementItems();
$navBrands     = \App\Models\Catalog::allActiveBrands();
$phone         = (string) \App\Seo\SeoSettings::get('organization_phone', '');
$email         = (string) \App\Seo\SeoSettings::get('organization_email', '');
$csrf          = \App\Core\Csrf::field();

/**
 * Navigation scope set by the shop owner.
 *
 * Computers, Cameras & Drones and Smart Home were removed from the storefront
 * navigation and replaced by a single, clearly-labelled Headphones tab.
 * Their /category/... URLs, products, sitemap entries and footer links are
 * untouched — only the primary bar and drawer changed.
 */
$navHiddenSlugs = ['computers-laptops', 'cameras-drones', 'home-smart-devices'];
$navCategories  = array_values(array_filter(
    $navCategories,
    static fn (array $c): bool => !in_array((string) ($c['slug'] ?? ''), $navHiddenSlugs, true)
));

// Categories that deserve a mega-menu (have children or are primary).
$megaParents = [];
foreach ($navCategories as $cat) {
    if (!empty($cat['children'])) {
        $megaParents[(int) $cat['id']] = $cat;
    }
}
$currentPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';

/**
 * The desktop bar gets one link per top-level category EXCEPT repair-services:
 * it duplicates the dedicated "Repairs" link and was the item that pushed the
 * bar into a second line on 1024–1440px screens. The category itself is still
 * in the mobile drawer and in the footer, so nothing becomes unreachable.
 */
$desktopCategories = array_values(array_filter(
    $navCategories,
    static fn (array $c): bool => (string) ($c['slug'] ?? '') !== 'repair-services'
));

/** Build a category tile icon from its image or fall back to a generic glyph. */
$catIcon = function (array $c): string {
    if (!empty($c['image'])) {
        return '<img src="' . e(image_url($c['image'])) . '" alt="" width="48" height="48" loading="lazy" decoding="async" class="h-12 w-12 rounded-xl object-cover bg-surface-sunken">';
    }
    return '<span class="h-12 w-12 rounded-xl bg-gradient-to-br from-electric-500/15 to-violet-600/15 grid place-items-center text-electric-600" aria-hidden="true">'
        . '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="h-6 w-6"><rect x="6" y="2" width="12" height="20" rx="2.5"/><path d="M11 18h2"/></svg>'
        . '</span>';
};

/**
 * Compact labels for the primary bar only.
 *
 * The full category names are long ("Smart Watches and Wearables") and would
 * wrap the desktop bar onto three lines. These are presentation-only strings
 * keyed by slug; anything not listed keeps its real name, so a new category
 * still renders correctly.
 */
$navShortLabels = [
    'mobiles-tablets'      => 'Mobiles & Tablets',
    'computers-laptops'    => 'Computers',
    'wearables'            => 'Wearables',
    'cameras-drones'       => 'Cameras',
    'home-smart-devices'   => 'Smart Home',
    'audio'                => 'Audio',
    'accessories'          => 'Accessories',
    'repair-services'      => 'Repairs',
];
$navLabel = static function (array $cat) use ($navShortLabels): string {
    return $navShortLabels[(string) ($cat['slug'] ?? '')] ?? (string) ($cat['name'] ?? '');
};

/** Active state helper for primary nav. */
$navActive = static function (string $path) use ($currentPath): string {
    if ($path === '/') {
        return $currentPath === '/' ? 'is-active' : '';
    }
    return str_starts_with($currentPath, $path) ? 'is-active' : '';
};
?>
<a href="#main" class="sr-only focus:not-sr-only focus:absolute focus:z-[100] focus:top-2 focus:left-2 focus:bg-navy-900 focus:text-white focus:px-4 focus:py-2 focus:rounded-lg">Skip to content</a>

<!-- ============================ Announcement bar ============================ -->
<?php if ($announcements !== [] || $email !== ''): ?>
<div class="bg-navy-950 text-navy-100 text-[12.5px]" data-announcement-bar>
    <div class="nav-shell flex min-h-9 items-center justify-between gap-4">
        <div class="flex min-w-0 items-center gap-5 overflow-hidden">
            <?php if ($announcements !== []): ?>
            <ul class="flex min-w-0 items-center gap-5">
                <?php foreach ($announcements as $i => $a): ?>
                <li class="flex min-w-0 items-center gap-1.5 overflow-hidden whitespace-nowrap text-navy-100/90 <?= $i > 0 ? 'max-[420px]:hidden' : '' ?>">
                    <span class="h-1 w-1 shrink-0 rounded-full bg-gold-400" aria-hidden="true"></span>
                    <?php if (!empty($a['url'])): ?>
                    <a href="<?= e($a['url']) ?>" class="min-w-0 truncate transition-colors hover:text-white"><?= e($a['text']) ?></a>
                    <?php else: ?>
                    <span class="min-w-0 truncate"><?= e($a['text']) ?></span>
                    <?php endif; ?>
                </li>
                <?php endforeach; ?>
            </ul>
            <?php endif; ?>
        </div>

        <div class="flex shrink-0 items-center gap-3">
            <?php if ($email !== ''): ?>
            <a href="mailto:<?= e($email) ?>" class="hidden items-center gap-1.5 text-navy-200 transition-colors hover:text-white md:inline-flex">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" class="h-3.5 w-3.5"><rect x="2" y="4" width="20" height="16" rx="2"/><path d="m2 7 10 6 10-6"/></svg>
                <?= e($email) ?>
            </a>
            <?php endif; ?>
            <a href="/page/delivery-information" class="hidden text-navy-200 transition-colors hover:text-white lg:inline">Delivery</a>
            <a href="/page/warranty-return-policy" class="hidden text-navy-200 transition-colors hover:text-white lg:inline">Returns</a>
            <button type="button"
                    class="grid h-7 w-7 place-items-center rounded-md text-navy-300 hover:bg-white/10 hover:text-white"
                    data-announcement-close aria-label="Dismiss announcement">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="h-4 w-4"><path d="m18 6-12 12M6 6l12 12"/></svg>
            </button>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- =============================== Main header =============================== -->
<header class="sticky top-0 z-50 border-b border-line bg-white/95 backdrop-blur-xl" id="site-header">
    <div class="nav-shell flex h-16 items-center gap-3 md:h-[4.75rem] md:gap-5">

        <!-- Mobile menu trigger -->
        <button type="button" id="nav-toggle"
                class="-ml-1 grid h-10 w-10 place-items-center rounded-xl text-ink transition-colors hover:bg-surface-sunken lg:hidden"
                aria-label="Open menu" aria-expanded="false" aria-controls="mobile-drawer">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="h-6 w-6"><path d="M3 6h18M3 12h18M3 18h18"/></svg>
        </button>

        <!-- Logo -->
        <a href="/" class="flex shrink-0 items-center py-1" aria-label="<?= e($siteName) ?> — home">
            <img src="/img/logo.png" alt="<?= e($siteName) ?>" width="180" height="117"
                 class="h-10 w-auto min-[360px]:h-11 md:h-13 object-contain drop-shadow-sm transition-transform hover:scale-105" fetchpriority="high">
        </a>

        <!-- Desktop search -->
        <div class="relative mx-auto hidden w-full max-w-2xl md:block" id="search-wrap">
            <form action="/search" method="get" role="search" class="relative" autocomplete="off">
                <label for="site-search" class="sr-only">Search products</label>
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"
                     class="pointer-events-none absolute left-4 top-1/2 h-4.5 w-4.5 -translate-y-1/2 text-ink-muted" style="width:1.1rem;height:1.1rem">
                    <circle cx="11" cy="11" r="7"/><path d="m21 21-4.3-4.3"/>
                </svg>
                <input type="search" id="site-search" name="q" value="<?= e($_GET['q'] ?? '') ?>"
                       placeholder="Search phones, brands, accessories…"
                       class="w-full rounded-full border border-line bg-surface-sunken py-2.5 pl-11 pr-28 text-sm transition placeholder:text-ink-muted focus:border-electric-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-electric-500/20"
                       data-search-input aria-autocomplete="list" aria-expanded="false" aria-controls="search-suggest">
                <button type="submit" class="btn btn-dark btn-sm absolute right-1.5 top-1/2 -translate-y-1/2 rounded-full">Search</button>
                <div id="search-suggest" role="listbox"
                     class="absolute z-50 mt-2 hidden w-full overflow-hidden rounded-2xl border border-line bg-white shadow-pop"
                     data-search-suggest></div>
            </form>
        </div>

        <!-- Right actions -->
        <div class="ml-auto flex items-center gap-0.5 md:gap-1">

            <!-- Mobile search toggle -->
            <button type="button" id="mobile-search-toggle"
                    class="grid h-10 w-10 place-items-center rounded-xl text-ink transition-colors hover:bg-surface-sunken md:hidden"
                    aria-label="Search">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="h-5 w-5"><circle cx="11" cy="11" r="7"/><path d="m21 21-4.3-4.3"/></svg>
            </button>

            <!-- Compare (lg+) -->
            <a href="/compare" class="hidden items-center gap-2 rounded-xl px-3 py-2 text-[13px] font-bold text-ink transition-colors hover:bg-surface-sunken hover:text-electric-600 lg:flex">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" class="h-5 w-5"><path d="M4 7h10M4 17h10M16 4l4 3-4 3M16 14l4 3-4 3"/></svg>
                <span class="hidden xl:inline">Compare</span>
            </a>

            <!-- Account -->
            <a href="<?= !empty($_SESSION['customer_id']) ? '/account' : '/account/login' ?>"
               class="hidden flex-col items-center rounded-xl px-2.5 py-1.5 text-ink transition-colors hover:bg-surface-sunken hover:text-electric-600 sm:flex"
               aria-label="Customer account">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="h-5 w-5" aria-hidden="true"><circle cx="12" cy="8" r="4"/><path d="M4 21a8 8 0 0 1 16 0"/></svg>
                <span class="mt-0.5 hidden text-[11px] font-semibold xl:block"><?= !empty($_SESSION['customer_name']) ? e(mb_substr((string) $_SESSION['customer_name'], 0, 12)) : 'Account' ?></span>
            </a>

            <!-- Wishlist -->
            <a href="/wishlist"
               class="relative flex flex-col items-center rounded-xl px-2.5 py-1.5 text-ink transition-colors hover:bg-flame-50 hover:text-flame-600"
               aria-label="Wishlist<?= $wishlistCount > 0 ? ' (' . $wishlistCount . ' items)' : '' ?>"
               data-wishlist-link>
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="h-5 w-5" aria-hidden="true">
                    <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/>
                </svg>
                <span class="mt-0.5 hidden text-[11px] font-semibold xl:block">Wishlist</span>
                <span data-wishlist-count
                      class="absolute -top-0.5 right-0.5 grid h-[18px] min-w-[18px] place-items-center rounded-full bg-flame-500 px-1 text-[10px] font-bold leading-none text-white <?= $wishlistCount > 0 ? '' : 'hidden' ?>"><?= $wishlistCount ?></span>
            </a>

            <!-- Cart -->
            <a href="/cart"
               class="relative flex flex-col items-center rounded-xl px-2.5 py-1.5 text-ink transition-colors hover:bg-electric-50 hover:text-electric-600"
               aria-label="Cart<?= $cartCount > 0 ? ' (' . $cartCount . ' items)' : '' ?>">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="h-5 w-5" aria-hidden="true">
                    <path d="M3 4h2l2.4 12.3a1 1 0 0 0 1 .8h9.7a1 1 0 0 0 1-.8L21 8H6"/>
                    <circle cx="9" cy="20" r="1.4"/><circle cx="18" cy="20" r="1.4"/>
                </svg>
                <span class="mt-0.5 hidden text-[11px] font-semibold xl:block">Cart</span>
                <span data-cart-count
                      class="absolute -top-0.5 right-0.5 grid h-[18px] min-w-[18px] place-items-center rounded-full bg-electric-600 px-1 text-[10px] font-bold leading-none text-white <?= $cartCount > 0 ? '' : 'hidden' ?>"><?= $cartCount ?></span>
            </a>
        </div>
    </div>

    <!-- Mobile search sheet -->
    <div id="mobile-search" class="hidden border-t border-line bg-white md:hidden">
        <div class="nav-shell py-3">
            <form action="/search" method="get" role="search" class="relative">
                <label for="mobile-search-input" class="sr-only">Search products</label>
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"
                     class="pointer-events-none absolute left-4 top-1/2 h-4 w-4 -translate-y-1/2 text-ink-muted">
                    <circle cx="11" cy="11" r="7"/><path d="m21 21-4.3-4.3"/>
                </svg>
                <input type="search" id="mobile-search-input" name="q" placeholder="Search phones, brands, accessories…"
                       class="w-full rounded-full border border-line bg-surface-sunken py-2.5 pl-11 pr-4 text-sm focus:border-electric-400 focus:bg-white focus:outline-none"
                       data-search-input>
            </form>
        </div>
    </div>

    <!-- ============================ Primary nav ============================ -->
    <nav class="hidden border-t border-line/70 bg-white lg:block" aria-label="Primary">
        <div class="nav-shell flex h-13 w-full items-center justify-center gap-0.5 px-3 xl:gap-1 xl:px-4">

            <!-- ⚡ Deals -->
            <a href="/deals"
               class="group flex shrink-0 items-center gap-1.5 whitespace-nowrap rounded-lg px-2.5 py-2 text-[12.5px] font-extrabold text-flame-600 transition-colors hover:bg-flame-50 xl:px-3 xl:text-[13px]">
                <svg viewBox="0 0 24 24" fill="currentColor" class="h-4 w-4 transition-transform group-hover:scale-110" aria-hidden="true"><path d="M13 2 4 14h6l-1 8 9-12h-6l1-8Z"/></svg>
                Hot Deals
            </a>

            <!-- 🎧 Audio mega-menu -->
            <div class="nav-mega relative shrink-0" data-mega>
                <button type="button"
                        class="nav-link flex items-center gap-1 whitespace-nowrap rounded-lg px-2.5 py-2 text-[12.5px] font-semibold text-ink transition-colors hover:bg-electric-50/70 hover:text-electric-600 xl:px-3 xl:text-[13px]"
                        aria-haspopup="true" aria-expanded="false" data-mega-trigger>
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" class="h-4 w-4 text-electric-600" aria-hidden="true">
                        <path d="M4 14v-2a8 8 0 0 1 16 0v2"/><rect x="2.5" y="13" width="4.5" height="7" rx="2"/><rect x="17" y="13" width="4.5" height="7" rx="2"/>
                    </svg>
                    Audio
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" class="h-3 w-3 opacity-40 transition-transform duration-200" aria-hidden="true"><path d="m6 9 6 6 6-6"/></svg>
                </button>
                <div class="mega-panel absolute left-0 top-full z-50 hidden w-[min(76rem,calc(100vw-2rem))] pt-2" data-mega-panel>
                    <div class="overflow-hidden rounded-2xl border border-line bg-white shadow-2xl">
                        <!-- Top promo strip -->
                        <div class="flex items-center gap-3 border-b border-line/60 bg-electric-50 px-6 py-2.5">
                            <span class="text-[11.5px] font-bold uppercase tracking-widest text-electric-600">Audio &amp; Sound</span>
                            <span class="h-3 w-px bg-electric-200"></span>
                            <span class="text-[11.5px] text-ink-muted">Pakistan's widest selection of headphones, earbuds &amp; speakers</span>
                            <a href="/category/audio" class="ml-auto text-[11.5px] font-bold text-electric-600 hover:text-electric-700">View all →</a>
                        </div>
                        <div class="grid grid-cols-12 gap-0">
                            <!-- Categories column -->
                            <div class="col-span-8 grid grid-cols-3 gap-x-6 gap-y-5 p-6">
                                <section>
                                    <p class="mb-2 flex items-center gap-1.5 text-[11px] font-bold uppercase tracking-wider text-ink-muted">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="h-3.5 w-3.5"><path d="M9 18V5l12-2v13"/><circle cx="6" cy="18" r="3"/><circle cx="18" cy="16" r="3"/></svg>
                                        Speakers
                                    </p>
                                    <ul class="space-y-1.5">
                                        <li><a href="/category/bluetooth-speakers" class="text-[12.5px] text-ink-muted transition-colors hover:text-electric-600">Bluetooth Speakers</a></li>
                                        <li><a href="/category/portable-speakers" class="text-[12.5px] text-ink-muted transition-colors hover:text-electric-600">Portable Speakers</a></li>
                                        <li><a href="/category/smart-speakers" class="text-[12.5px] text-ink-muted transition-colors hover:text-electric-600">Smart Speakers</a></li>
                                        <li><a href="/category/soundbars" class="text-[12.5px] text-ink-muted transition-colors hover:text-electric-600">Soundbars</a></li>
                                        <li><a href="/category/home-theatre" class="text-[12.5px] text-ink-muted transition-colors hover:text-electric-600">Home Theatre</a></li>
                                    </ul>
                                </section>
                                <section>
                                    <p class="mb-2 flex items-center gap-1.5 text-[11px] font-bold uppercase tracking-wider text-ink-muted">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="h-3.5 w-3.5"><path d="M4 14v-2a8 8 0 0 1 16 0v2"/><rect x="2.5" y="13" width="4.5" height="7" rx="2"/><rect x="17" y="13" width="4.5" height="7" rx="2"/></svg>
                                        Headphones
                                    </p>
                                    <ul class="space-y-1.5">
                                        <li><a href="/category/over-ear-headphones" class="text-[12.5px] text-ink-muted transition-colors hover:text-electric-600">Over-ear</a></li>
                                        <li><a href="/category/on-ear-headphones" class="text-[12.5px] text-ink-muted transition-colors hover:text-electric-600">On-ear</a></li>
                                        <li><a href="/category/wireless-headphones" class="text-[12.5px] text-ink-muted transition-colors hover:text-electric-600">Wireless</a></li>
                                        <li><a href="/category/gaming-headsets" class="text-[12.5px] text-ink-muted transition-colors hover:text-electric-600">Gaming Headsets</a></li>
                                    </ul>
                                </section>
                                <section>
                                    <p class="mb-2 flex items-center gap-1.5 text-[11px] font-bold uppercase tracking-wider text-ink-muted">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="h-3.5 w-3.5"><circle cx="8" cy="15" r="4"/><path d="M12 15V6.5a2 2 0 0 1 2-2h2"/></svg>
                                        Earbuds &amp; TWS
                                    </p>
                                    <ul class="space-y-1.5">
                                        <li><a href="/category/true-wireless-earbuds" class="text-[12.5px] text-ink-muted transition-colors hover:text-electric-600">True Wireless (TWS)</a></li>
                                        <li><a href="/category/neckband-earphones" class="text-[12.5px] text-ink-muted transition-colors hover:text-electric-600">Neckband</a></li>
                                        <li><a href="/category/noise-cancelling-earbuds" class="text-[12.5px] text-ink-muted transition-colors hover:text-electric-600">Noise-Cancelling</a></li>
                                        <li><a href="/category/wired-earphones" class="text-[12.5px] text-ink-muted transition-colors hover:text-electric-600">Wired Earphones</a></li>
                                    </ul>
                                </section>
                                <section>
                                    <p class="mb-2 flex items-center gap-1.5 text-[11px] font-bold uppercase tracking-wider text-ink-muted">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="h-3.5 w-3.5"><path d="M12 2a3 3 0 0 0-3 3v7a3 3 0 0 0 6 0V5a3 3 0 0 0-3-3Z"/><path d="M19 10v2a7 7 0 0 1-14 0v-2"/><line x1="12" x2="12" y1="19" y2="22"/></svg>
                                        Microphones
                                    </p>
                                    <ul class="space-y-1.5">
                                        <li><a href="/category/wireless-microphones" class="text-[12.5px] text-ink-muted transition-colors hover:text-electric-600">Wireless Mics</a></li>
                                        <li><a href="/category/lavalier-microphones" class="text-[12.5px] text-ink-muted transition-colors hover:text-electric-600">Lavalier Mics</a></li>
                                        <li><a href="/category/studio-usb-microphones" class="text-[12.5px] text-ink-muted transition-colors hover:text-electric-600">Studio / USB</a></li>
                                    </ul>
                                </section>
                                <section>
                                    <p class="mb-2 flex items-center gap-1.5 text-[11px] font-bold uppercase tracking-wider text-ink-muted">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="h-3.5 w-3.5"><path d="M3 18v-6a9 9 0 0 1 18 0v6"/><path d="M21 19a2 2 0 0 1-2 2h-1a2 2 0 0 1-2-2v-3a2 2 0 0 1 2-2h3zM3 19a2 2 0 0 0 2 2h1a2 2 0 0 0 2-2v-3a2 2 0 0 0-2-2H3z"/></svg>
                                        Handsfree
                                    </p>
                                    <ul class="space-y-1.5">
                                        <li><a href="/category/wired-handsfree" class="text-[12.5px] text-ink-muted transition-colors hover:text-electric-600">Wired Handsfree</a></li>
                                        <li><a href="/category/bluetooth-handsfree" class="text-[12.5px] text-ink-muted transition-colors hover:text-electric-600">Bluetooth Handsfree</a></li>
                                        <li><a href="/category/type-c-handsfree" class="text-[12.5px] text-ink-muted transition-colors hover:text-electric-600">Type-C Handsfree</a></li>
                                    </ul>
                                </section>
                                <section>
                                    <p class="mb-2 flex items-center gap-1.5 text-[11px] font-bold uppercase tracking-wider text-ink-muted">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="h-3.5 w-3.5"><path d="M3 3h18v4H3zM3 11h18v4H3zM3 19h18v2H3z"/></svg>
                                        Accessories
                                    </p>
                                    <ul class="space-y-1.5">
                                        <li><a href="/category/ear-tips" class="text-[12.5px] text-ink-muted transition-colors hover:text-electric-600">Ear Tips</a></li>
                                        <li><a href="/category/audio-cases" class="text-[12.5px] text-ink-muted transition-colors hover:text-electric-600">Cases</a></li>
                                        <li><a href="/category/audio-cables" class="text-[12.5px] text-ink-muted transition-colors hover:text-electric-600">Cables &amp; Adapters</a></li>
                                    </ul>
                                </section>
                                <!-- Top Audio Brands -->
                                <div class="col-span-3 border-t border-line/60 pt-4">
                                    <p class="mb-2.5 text-[11px] font-bold uppercase tracking-wider text-ink-muted">Top Audio Brands</p>
                                    <div class="flex flex-wrap gap-2">
                                        <?php foreach (['jbl', 'sony', 'anker', 'jabra', 'sennheiser', 'samsung'] as $bSlug):
                                            $bName = array_values(array_filter($navBrands, fn($b) => $b['slug'] === $bSlug))[0]['name'] ?? ucfirst($bSlug); ?>
                                        <a href="/brand/<?= e($bSlug) ?>" class="inline-flex items-center rounded-full border border-line bg-surface-sunken/70 px-3 py-1 text-[12px] font-semibold text-ink-muted transition-colors hover:border-electric-200 hover:bg-electric-50 hover:text-electric-700"><?= e($bName) ?></a>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            </div>
                            <!-- Right promo card -->
                            <div class="col-span-4 border-l border-line/50">
                                <a href="/category/audio" class="group relative flex h-full min-h-72 flex-col justify-end overflow-hidden bg-gradient-to-br from-slate-900 via-slate-800 to-electric-950 p-6 text-white">
                                    <span class="absolute inset-0 bg-[url('https://images.unsplash.com/photo-1608043152269-423dbba4e7e1?auto=format&fit=crop&w=640&q=80')] bg-cover bg-center opacity-30 transition-opacity duration-300 group-hover:opacity-40"></span>
                                    <span class="absolute inset-0 bg-gradient-to-t from-slate-950/95 via-slate-900/40 to-transparent"></span>
                                    <span class="relative space-y-1">
                                        <span class="inline-flex items-center gap-1.5 rounded-full bg-electric-500/20 px-2.5 py-1 text-[11px] font-bold uppercase tracking-wider text-electric-300">
                                            <svg viewBox="0 0 24 24" fill="currentColor" class="h-3 w-3"><path d="M13 2 4 14h6l-1 8 9-12h-6l1-8Z"/></svg>
                                            New Arrivals
                                        </span>
                                        <span class="block text-xl font-extrabold leading-tight">Premium Audio<br>Collection</span>
                                        <span class="block text-sm text-white/70">JBL · Sony · Anker &amp; more</span>
                                        <span class="mt-3 inline-flex items-center gap-1 text-sm font-bold text-electric-300 transition-colors group-hover:text-electric-200">Shop Now <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" class="h-4 w-4"><path d="M5 12h14M12 5l7 7-7 7"/></svg></span>
                                    </span>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Dynamic category tabs from DB -->
            <?php foreach ($desktopCategories as $cat): $isMega = isset($megaParents[(int) $cat['id']]); ?>
                <?php if ($isMega): ?>
                <div class="nav-mega relative shrink-0" data-mega>
                    <button type="button"
                            class="nav-link <?= $navActive('/category/' . $cat['slug']) ?> flex items-center gap-1 whitespace-nowrap rounded-lg px-2.5 py-2 text-[12.5px] font-semibold text-ink transition-colors hover:bg-electric-50/70 hover:text-electric-600 xl:px-3 xl:text-[13px]"
                            aria-haspopup="true" aria-expanded="false" data-mega-trigger>
                        <?= e($navLabel($cat)) ?>
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" class="h-3 w-3 opacity-40 transition-transform duration-200" aria-hidden="true"><path d="m6 9 6 6 6-6"/></svg>
                    </button>
                    <div class="mega-panel absolute left-0 top-full z-50 hidden w-[min(76rem,calc(100vw-2rem))] pt-2" data-mega-panel>
                        <div class="overflow-hidden rounded-2xl border border-line bg-white shadow-2xl">
                            <!-- Promo strip -->
                            <div class="flex items-center gap-3 border-b border-line/60 bg-surface-sunken px-6 py-2.5">
                                <span class="text-[11.5px] font-bold uppercase tracking-widest text-ink"><?= e($cat['name']) ?></span>
                                <span class="h-3 w-px bg-line"></span>
                                <span class="text-[11.5px] text-ink-muted">Browse all <?= e(strtolower($cat['name'])) ?> categories &amp; brands</span>
                                <a href="/category/<?= e($cat['slug']) ?>" class="ml-auto text-[11.5px] font-bold text-electric-600 hover:text-electric-700">View all →</a>
                            </div>
                            <div class="grid grid-cols-12 gap-0">
                                <div class="col-span-8 grid grid-cols-3 gap-x-6 gap-y-5 p-6">
                                    <?php foreach ($cat['children'] as $child): ?>
                                    <section>
                                        <a href="/category/<?= e($child['slug']) ?>" class="mb-2 flex items-center gap-1.5 text-[12.5px] font-bold text-ink hover:text-electric-600">
                                            <span class="inline-block h-1.5 w-1.5 rounded-full bg-electric-500"></span>
                                            <?= e($child['name']) ?>
                                        </a>
                                        <?php if (!empty($child['children'])): ?>
                                        <ul class="space-y-1.5 pl-3">
                                            <?php foreach ($child['children'] as $leaf): ?>
                                            <li><a href="/category/<?= e($leaf['slug']) ?>" class="text-[12px] text-ink-muted transition-colors hover:text-electric-600"><?= e($leaf['name']) ?></a></li>
                                            <?php endforeach; ?>
                                        </ul>
                                        <?php endif; ?>
                                    </section>
                                    <?php endforeach; ?>

                                    <!-- Brands row -->
                                    <div class="col-span-3 border-t border-line/60 pt-4">
                                        <p class="mb-2.5 text-[11px] font-bold uppercase tracking-wider text-ink-muted">Shop by Brand</p>
                                        <div class="flex flex-wrap gap-2">
                                            <?php foreach (array_slice($navBrands, 0, 8) as $brand): ?>
                                            <a href="/brand/<?= e($brand['slug']) ?>"
                                               class="inline-flex items-center rounded-full border border-line bg-surface-sunken/70 px-3 py-1 text-[12px] font-semibold text-ink-muted transition-colors hover:border-electric-200 hover:bg-electric-50 hover:text-electric-700">
                                                <?= e($brand['name']) ?>
                                            </a>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-span-4 border-l border-line/50">
                                    <a href="/category/<?= e($cat['slug']) ?>" class="group relative flex h-full min-h-72 flex-col justify-end overflow-hidden bg-slate-900 p-6 text-white">
                                        <?php if (!empty($cat['banner_image'] ?? $cat['image'] ?? null)): ?>
                                        <img src="<?= e(image_url($cat['banner_image'] ?? $cat['image'])) ?>" alt="" width="640" height="480" loading="lazy"
                                             class="absolute inset-0 h-full w-full object-cover opacity-50 transition duration-300 group-hover:opacity-60 group-hover:scale-105">
                                        <?php endif; ?>
                                        <span class="absolute inset-0 bg-gradient-to-t from-slate-950/95 via-slate-900/40 to-transparent"></span>
                                        <span class="relative space-y-1.5">
                                            <span class="block text-xl font-extrabold leading-tight">Explore<br><?= e($cat['name']) ?></span>
                                            <span class="block text-sm text-white/70"><?= count($cat['children']) ?> sub-categories available</span>
                                            <span class="mt-3 inline-flex items-center gap-1 text-sm font-bold text-electric-300 transition-colors group-hover:text-electric-200">Shop Now <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" class="h-4 w-4"><path d="M5 12h14M12 5l7 7-7 7"/></svg></span>
                                        </span>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <?php else: ?>
                <a href="/category/<?= e($cat['slug']) ?>"
                   class="nav-link <?= $navActive('/category/' . $cat['slug']) ?> shrink-0 whitespace-nowrap rounded-lg px-2.5 py-2 text-[12.5px] font-semibold text-ink transition-colors hover:bg-electric-50/70 hover:text-electric-600 xl:px-3 xl:text-[13px]">
                    <?= e($navLabel($cat)) ?>
                </a>
                <?php endif; ?>
            <?php endforeach; ?>

            <!-- Static links -->
            <a href="/repair/screen-replacement"
               class="nav-link <?= $navActive('/repair') ?> group flex shrink-0 items-center gap-1.5 whitespace-nowrap rounded-lg px-2.5 py-2 text-[12.5px] font-semibold text-ink transition-colors hover:bg-violet-50 hover:text-violet-600 xl:px-3 xl:text-[13px]">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" class="h-4 w-4 text-violet-500" aria-hidden="true"><rect x="5" y="2" width="14" height="20" rx="2"/><path d="M9 7h6M9 11h6M9 15h4"/><circle cx="17" cy="17" r="3"/><path d="m19.5 19.5 1.5 1.5"/></svg>
                Repairs
            </a>
            <a href="/blog"
               class="nav-link <?= $navActive('/blog') ?> hidden shrink-0 whitespace-nowrap rounded-lg px-2.5 py-2 text-[12.5px] font-semibold text-ink transition-colors hover:bg-electric-50/70 hover:text-electric-600 xl:block xl:px-3 xl:text-[13px]">
                Blog
            </a>
            <a href="/page/contact"
               class="nav-link <?= $navActive('/page/contact') ?> hidden shrink-0 whitespace-nowrap rounded-lg px-2.5 py-2 text-[12.5px] font-semibold text-ink transition-colors hover:bg-electric-50/70 hover:text-electric-600 xl:block xl:px-3 xl:text-[13px]">
                Contact
            </a>

        </div>
    </nav>
</header>

<!-- ============================== Mobile drawer ============================== -->
<div id="drawer-overlay" class="fixed inset-0 z-[60] hidden bg-navy-950/60 backdrop-blur-sm" data-drawer-overlay></div>
<aside id="mobile-drawer"
       class="fixed inset-y-0 left-0 z-[70] flex w-[88%] max-w-sm -translate-x-full flex-col bg-white shadow-2xl transition-transform duration-300"
       aria-label="Mobile menu" aria-hidden="true" role="dialog" aria-modal="true">
    <div class="flex h-16 shrink-0 items-center justify-between border-b border-line px-4">
        <img src="/img/logo.png" alt="<?= e($siteName) ?>" class="h-10 w-auto object-contain">
        <button type="button" id="drawer-close" class="grid h-10 w-10 place-items-center rounded-xl hover:bg-surface-sunken" aria-label="Close menu">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="h-5 w-5"><path d="M18 6 6 18M6 6l12 12"/></svg>
        </button>
    </div>

    <!-- Quick actions -->
    <div class="grid shrink-0 grid-cols-3 gap-2 border-b border-line p-3">
        <a href="/wishlist" class="flex flex-col items-center gap-1 rounded-xl border border-line px-2 py-2.5 text-[11.5px] font-bold text-ink hover:border-flame-200 hover:bg-flame-50 hover:text-flame-600">
            <span class="relative">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="h-5 w-5"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg>
                <span data-wishlist-count class="absolute -right-2 -top-1.5 grid h-4 min-w-4 place-items-center rounded-full bg-flame-500 px-0.5 text-[9px] font-bold leading-none text-white <?= $wishlistCount > 0 ? '' : 'hidden' ?>"><?= $wishlistCount ?></span>
            </span>
            Wishlist
        </a>
        <a href="/cart" class="flex flex-col items-center gap-1 rounded-xl border border-line px-2 py-2.5 text-[11.5px] font-bold text-ink hover:border-electric-200 hover:bg-electric-50 hover:text-electric-600">
            <span class="relative">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="h-5 w-5"><path d="M3 4h2l2.4 12.3a1 1 0 0 0 1 .8h9.7a1 1 0 0 0 1-.8L21 8H6"/><circle cx="9" cy="20" r="1.4"/><circle cx="18" cy="20" r="1.4"/></svg>
                <span data-cart-count class="absolute -right-2 -top-1.5 grid h-4 min-w-4 place-items-center rounded-full bg-electric-600 px-0.5 text-[9px] font-bold leading-none text-white <?= $cartCount > 0 ? '' : 'hidden' ?>"><?= $cartCount ?></span>
            </span>
            Cart
        </a>
        <a href="<?= !empty($_SESSION['customer_id']) ? '/account' : '/account/login' ?>" class="flex flex-col items-center gap-1 rounded-xl border border-line px-2 py-2.5 text-[11.5px] font-bold text-ink hover:border-electric-200 hover:bg-electric-50 hover:text-electric-600">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="h-5 w-5"><circle cx="12" cy="8" r="4"/><path d="M4 21a8 8 0 0 1 16 0"/></svg>
            Account
        </a>
    </div>

    <nav class="flex-1 overflow-y-auto py-2" aria-label="Mobile primary">
        <a href="/deals" class="flex items-center gap-3 px-5 py-3 font-bold text-flame-600 hover:bg-flame-50">
            <svg viewBox="0 0 24 24" fill="currentColor" class="h-5 w-5"><path d="M13 2 4 14h6l-1 8 9-12h-6l1-8Z"/></svg>
            Deals &amp; Offers
        </a>
        <a href="/category/headphones" class="flex items-center gap-3 border-y border-line/60 bg-electric-50/60 px-5 py-3 font-bold text-electric-700 hover:bg-electric-100">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" class="h-5 w-5"><path d="M4 14v-2a8 8 0 0 1 16 0v2"/><rect x="2.5" y="13" width="4.5" height="7" rx="2"/><rect x="17" y="13" width="4.5" height="7" rx="2"/></svg>
            Headphones
        </a>
        <a href="/compare" class="block px-5 py-3 font-semibold text-ink hover:bg-surface-sunken">Compare products</a>

        <?php foreach ($navCategories as $cat): ?>
            <?php if (!empty($cat['children'])): ?>
            <details class="border-b border-line/60">
                <summary class="flex cursor-pointer list-none items-center justify-between px-5 py-3.5 font-semibold text-ink">
                    <?= e($cat['name']) ?>
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" class="h-4 w-4 opacity-50"><path d="m6 9 6 6 6-6"/></svg>
                </summary>
                <div class="bg-surface-sunken/50 pb-2">
                    <a href="/category/<?= e($cat['slug']) ?>" class="block px-5 py-2.5 text-sm font-bold text-electric-600">All <?= e($cat['name']) ?></a>
                    <?php foreach ($cat['children'] as $child): ?>
                    <a href="/category/<?= e($child['slug']) ?>" class="block px-5 py-2.5 text-sm text-ink-muted hover:text-electric-600"><?= e($child['name']) ?></a>
                        <?php foreach (($child['children'] ?? []) as $leaf): ?>
                        <a href="/category/<?= e($leaf['slug']) ?>" class="block py-2 pl-9 pr-5 text-[13px] text-ink-muted hover:text-electric-600"><?= e($leaf['name']) ?></a>
                        <?php endforeach; ?>
                    <?php endforeach; ?>
                </div>
            </details>
            <?php else: ?>
            <a href="/category/<?= e($cat['slug']) ?>" class="block border-b border-line/60 px-5 py-3.5 font-semibold text-ink hover:bg-surface-sunken"><?= e($cat['name']) ?></a>
            <?php endif; ?>
        <?php endforeach; ?>

        <a href="/repair/screen-replacement" class="block border-b border-line/60 px-5 py-3.5 font-semibold text-ink hover:bg-surface-sunken">Repair a device</a>
        <a href="/blog" class="block border-b border-line/60 px-5 py-3.5 font-semibold text-ink hover:bg-surface-sunken">Blog</a>
        <a href="/page/contact" class="block border-b border-line/60 px-5 py-3.5 font-semibold text-ink hover:bg-surface-sunken">Contact</a>
    </nav>

    <?php if ($phone !== ''): ?>
    <div class="shrink-0 space-y-2 border-t border-line p-4">
        <a href="https://wa.me/923005970504" target="_blank" rel="noopener noreferrer"
           class="btn btn-md btn-block flex items-center justify-center gap-2 !bg-[#25D366] !text-white hover:!bg-[#20ba59]">
            <svg viewBox="0 0 24 24" fill="currentColor" class="h-4.5 w-4.5"><path d="M12.04 2c-5.46 0-9.91 4.45-9.91 9.91 0 1.75.46 3.45 1.32 4.95L2.05 22l5.25-1.38c1.45.79 3.08 1.21 4.74 1.21 5.46 0 9.91-4.45 9.91-9.91 0-2.65-1.03-5.14-2.9-7.01A9.82 9.82 0 0 0 12.04 2zm.01 1.67c4.54 0 8.24 3.7 8.24 8.24 0 2.2-.86 4.27-2.42 5.82-1.55 1.56-3.62 2.42-5.82 2.42-1.42 0-2.81-.37-4.04-1.08l-.29-.17-3.01.79.8-2.93-.19-.3a8.21 8.21 0 0 1-1.26-4.31c0-4.54 3.7-8.24 8.24-8.24zm4.52 11.66c-.25-.13-1.47-.72-1.7-.81-.23-.08-.39-.13-.56.13-.17.25-.64.81-.79.97-.14.17-.29.19-.54.06-.25-.13-1.06-.39-2.02-1.25-.75-.67-1.26-1.5-1.4-1.76-.15-.25-.02-.39.11-.51.11-.11.25-.29.38-.44.13-.14.17-.25.25-.42.08-.17.04-.31-.02-.44-.06-.13-.56-1.35-.77-1.85-.2-.49-.41-.42-.56-.43h-.48c-.17 0-.44.06-.67.31-.23.25-.88.86-.88 2.1 0 1.24.9 2.44 1.03 2.61.13.17 1.77 2.7 4.29 3.79.6.26 1.07.41 1.43.53.6.19 1.15.16 1.58.1.48-.07 1.47-.6 1.68-1.18.21-.58.21-1.08.15-1.18-.06-.1-.23-.17-.48-.29z"/></svg>
            WhatsApp +92 300 5970504
        </a>
        <a href="tel:<?= e(preg_replace('/[^0-9+]/', '', $phone)) ?>" class="btn btn-dark btn-md btn-block">Call <?= e($phone) ?></a>
    </div>
    <?php endif; ?>
</aside>
