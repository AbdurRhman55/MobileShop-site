<?php
/**
 * Admin layout — professional premium UI with gradient sidebar, mini-stats topbar,
 * and beautiful dark-navy-to-indigo sidebar.
 *
 * @var string $content @var string $adminUser @var array $auditSummary
 * @var string $pageTitle @var ?array $flash
 */
$nav = [
    '/admin'            => ['Dashboard',  'M3 13h8V3H3v10zm0 8h8v-6H3v6zm10 0h8V11h-8v10zm0-18v6h8V3h-8z', 'from-electric-500 to-violet-600'],
    '/admin/products'   => ['Products',   'M6 2h9l5 5v13a1 1 0 0 1-1 1H6a1 1 0 0 1-1-1V3a1 1 0 0 1 1-1zM14 2v6h6', 'from-jade-400 to-emerald-600'],
    '/admin/categories' => ['Categories', 'M3 4h7v7H3zM14 4h7v7h-7zM3 13h7v7H3zM14 13h7v7h-7z', 'from-violet-400 to-fuchsia-600'],
    '/admin/brands'     => ['Brands',     'M20.6 13.4 12 22l-9-9V3h10l7.6 7.6a2 2 0 0 1 0 2.8zM7.5 7.5h.01', 'from-gold-400 to-amber-600'],
    '/admin/blog'       => ['Blog',       'M4 5h16v14H4zM8 9h8M8 13h6', 'from-blue-400 to-indigo-600'],
    '/admin/pages'      => ['Pages',      'M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8zM14 2v6h6M8 13h8M8 17h5', 'from-teal-400 to-cyan-600'],
    '/admin/reviews'    => ['Reviews',    'M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z', 'from-rose-400 to-pink-600'],
    '/admin/orders'     => ['Orders',     'M6 2h12l2 5-8 3.5L6 7zM6 7v13a1 1 0 0 0 1 1h10a1 1 0 0 0 1-1V7', 'from-flame-400 to-orange-600'],
    '/admin/customers'  => ['Customers',  'M16 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2M10 11a4 4 0 1 0 0-8 4 4 0 0 0 0 8m10 10v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75', 'from-electric-400 to-blue-600'],
    '/admin/homepage'   => ['Homepage',   'M3 11 12 3l9 8M5 10v10h14V10M9 20v-6h6v6', 'from-electric-400 to-blue-600'],
    '/admin/seo'        => ['SEO',        'M12 2a10 10 0 1 0 0 20 10 10 0 0 0 0-20zM2 12h20M12 2a15 15 0 0 1 0 20 15 15 0 0 1 0-20z', 'from-sky-400 to-blue-500'],
];
$path  = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/admin';

if (!isset($pageTitle)) {
    $best = '';
    foreach ($nav as $href => [$label]) {
        if (($href === '/admin' ? $path === '/admin' : str_starts_with($path, $href))
            && strlen($href) > strlen($best)) {
            $best = $href;
        }
    }
    $pageTitle = $best === '' ? 'Dashboard' : $nav[$best][0];
    if (str_ends_with($path, '/create')) {
        $pageTitle = 'Add ' . strtolower(preg_replace('/s$/', '', $pageTitle));
    } elseif (preg_match('#/(\d+)/edit$#', $path)) {
        $pageTitle = 'Edit ' . strtolower(preg_replace('/s$/', '', $pageTitle));
    }
}

$issues = 0;
foreach ((array) $auditSummary as $r) {
    $issues += (int) $r['count'];
}
$currentSection = '';
foreach ($nav as $href => [$label, $icon, $grad]) {
    $active = $href === '/admin' ? $path === '/admin' : str_starts_with($path, $href);
    if ($active) { $currentSection = $grad; break; }
}
?><!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex,nofollow,noarchive">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Manrope:wght@500;600;700;800&display=swap" rel="stylesheet">
    <title><?= e($pageTitle ?? 'Dashboard') ?> — Lala Admin</title>
    <link rel="icon" href="/img/favicon.svg" type="image/svg+xml">
    <link rel="stylesheet" href="/assets/css/app.css">
</head>
<body class="min-h-screen bg-surface-muted text-ink antialiased">
<div class="flex min-h-screen">

    <!-- ===================== Sidebar (desktop) ===================== -->
    <aside class="admin-sidebar hidden min-h-screen w-64 shrink-0 flex-col bg-slate-900 lg:flex" aria-label="Admin navigation">
        <!-- Logo area -->
        <a href="/admin" class="flex items-center gap-3 border-b border-white/8 px-5 py-5 hover:bg-white/5 transition-colors">
            <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-white/10 shadow-md">
                <img src="/img/logo-mark.png" alt="Lala Mobiles" width="32" height="32" class="h-8 w-8 object-contain">
            </div>
            <span class="leading-tight">
                <span class="block text-[15px] font-extrabold tracking-tight text-white">Lala Mobiles</span>
                <span class="block text-[11px] font-medium text-navy-300">Admin Control Panel</span>
            </span>
        </a>

        <nav class="flex-1 overflow-y-auto py-4" aria-label="Admin navigation">
            <p class="admin-section-label">Main</p>
            <?php
            $groups = [
                'Main' => ['/admin', '/admin/orders', '/admin/customers', '/admin/products'],
                'Content' => ['/admin/categories', '/admin/brands', '/admin/blog', '/admin/pages', '/admin/reviews'],
                'Settings' => ['/admin/homepage', '/admin/seo'],
            ];
            $allGrouped = array_merge(...array_values($groups));
            ?>
            <?php $groupIdx = 0; foreach ($nav as $href => [$label, $icon, $grad]):
                $active = $href === '/admin' ? $path === '/admin' : str_starts_with($path, $href);
                // Inject section labels
                if ($href === '/admin/categories'): ?>
            <p class="admin-section-label mt-2">Content</p>
            <?php elseif ($href === '/admin/homepage'): ?>
            <p class="admin-section-label mt-2">Config</p>
            <?php endif; ?>
            <a href="<?= e($href) ?>"
               <?= $active ? 'aria-current="page"' : '' ?>
               class="admin-nav-item <?= $active ? 'active' : '' ?>">
                <span class="admin-nav-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9"
                         stroke-linecap="round" stroke-linejoin="round" class="h-4 w-4" aria-hidden="true">
                        <path d="<?= e($icon) ?>"/>
                    </svg>
                </span>
                <?= e($label) ?>
                <?php if ($href === '/admin/orders' && isset($stats['orders_14d']) && (int)$stats['orders_14d'] > 0): ?>
                <span class="ml-auto flex h-5 min-w-5 items-center justify-center rounded-full bg-electric-600 px-1.5 text-[10px] font-bold text-white">
                    <?= (int)($stats['orders_14d'] ?? 0) ?>
                </span>
                <?php endif; ?>
                <?php if ($href === '/admin/reviews' && isset($stats['reviews_pending']) && (int)$stats['reviews_pending'] > 0): ?>
                <span class="ml-auto flex h-5 min-w-5 items-center justify-center rounded-full bg-gold-400 px-1.5 text-[10px] font-bold text-navy-900">
                    <?= (int)$stats['reviews_pending'] ?>
                </span>
                <?php endif; ?>
                <?php if ($href === '/admin/seo' && $issues > 0): ?>
                <span class="ml-auto flex h-5 min-w-5 items-center justify-center rounded-full bg-flame-500 px-1.5 text-[10px] font-bold text-white">
                    <?= $issues ?>
                </span>
                <?php endif; ?>
            </a>
            <?php endforeach; ?>
        </nav>

        <!-- Bottom section: storefront + user -->
        <div class="border-t border-white/8">
            <a href="/" target="_blank" rel="noopener"
               class="flex items-center gap-2.5 px-5 py-3 text-[13px] font-semibold text-navy-300 hover:bg-white/5 hover:text-white transition-colors">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" class="h-4 w-4" aria-hidden="true"><path d="M14 4h6v6M20 4l-9 9M18 14v5a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1V7a1 1 0 0 1 1-1h5"/></svg>
                View Storefront
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="h-3 w-3 ml-auto opacity-50"><path d="M7 17l9.2-9.2M17 17V7H7"/></svg>
            </a>
        </div>
        <div class="border-t border-white/8 px-4 py-4">
            <div class="flex items-center gap-3 rounded-xl bg-white/5 px-3 py-2.5">
                <span class="grid h-9 w-9 shrink-0 place-items-center rounded-full bg-gradient-to-br from-gold-400 to-amber-500 text-[14px] font-extrabold text-navy-900 shadow-md">
                    <?= e(strtoupper(substr((string) $adminUser, 0, 1))) ?>
                </span>
                <span class="min-w-0 flex-1">
                    <span class="block truncate text-[13px] font-bold text-white"><?= e($adminUser) ?></span>
                    <span class="block text-[11px] text-navy-400">Administrator</span>
                </span>
            </div>
            <form method="post" action="/admin/logout" class="mt-2">
                <?= \App\Core\Csrf::field() ?>
                <button type="submit" class="flex w-full items-center gap-2 rounded-xl px-3 py-2 text-[13px] font-semibold text-navy-400 hover:bg-white/5 hover:text-white transition-colors">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" class="h-4 w-4" aria-hidden="true"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4M16 17l5-5-5-5M21 12H9"/></svg>
                    Log out
                </button>
            </form>
        </div>
    </aside>

    <!-- ===================== Main column ===================== -->
    <div class="flex min-w-0 flex-1 flex-col">

        <!-- Top bar -->
        <header class="admin-topbar sticky top-0 z-30">
            <div class="flex items-center gap-3 px-4 py-3 sm:px-6">
                <!-- Mobile nav toggle -->
                <button type="button" id="admin-nav-toggle"
                        class="grid h-10 w-10 shrink-0 place-items-center rounded-xl border border-line text-ink transition-colors hover:bg-surface-sunken lg:hidden"
                        aria-label="Open admin menu" aria-expanded="false" aria-controls="admin-mobile-nav">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="h-5 w-5"><path d="M3 6h18M3 12h18M3 18h18"/></svg>
                </button>

                <!-- Breadcrumb / title -->
                <div class="min-w-0 flex-1">
                    <div class="flex items-center gap-2 text-[13px] text-ink-muted">
                        <a href="/admin" class="hover:text-electric-600 font-medium transition-colors">Admin</a>
                        <?php if ($pageTitle !== 'Dashboard'): ?>
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="h-3.5 w-3.5 opacity-40"><path d="m9 18 6-6-6-6"/></svg>
                        <span class="font-semibold text-ink truncate"><?= e($pageTitle) ?></span>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Right actions -->
                <div class="flex items-center gap-2">
                    <?php if ($issues > 0): ?>
                    <a href="/admin/seo/audit"
                       class="hidden sm:inline-flex items-center gap-2 rounded-xl border border-gold-300 bg-gold-50 px-3 py-2 text-[13px] font-bold text-navy-900 hover:bg-gold-100 transition-colors">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="h-4 w-4 text-gold-600" aria-hidden="true"><path d="M12 9v4M12 17h.01M10.3 3.9 1.8 18a2 2 0 0 0 1.7 3h17a2 2 0 0 0 1.7-3L13.7 3.9a2 2 0 0 0-3.4 0z"/></svg>
                        <?= $issues ?> SEO issue<?= $issues === 1 ? '' : 's' ?>
                    </a>
                    <?php else: ?>
                    <a href="/admin/seo/audit"
                       class="hidden sm:inline-flex items-center gap-2 rounded-xl border border-jade-200 bg-jade-50 px-3 py-2 text-[13px] font-bold text-jade-700 hover:bg-jade-100 transition-colors">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" class="h-4 w-4" aria-hidden="true"><path d="M20 6L9 17l-5-5"/></svg>
                        SEO OK
                    </a>
                    <?php endif; ?>

                    <a href="/" target="_blank" rel="noopener"
                       class="grid h-10 w-10 shrink-0 place-items-center rounded-xl border border-line text-ink-muted hover:border-electric-300 hover:text-electric-600 transition-colors"
                       aria-label="Open storefront">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="h-4.5 w-4.5" style="width:1.15rem;height:1.15rem"><path d="M14 4h6v6M20 4l-9 9M18 14v5a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1V7a1 1 0 0 1 1-1h5"/></svg>
                    </a>
                </div>
            </div>
        </header>

        <!-- Mobile nav drawer -->
        <div id="admin-mobile-nav" class="fixed inset-0 z-40 hidden lg:hidden" data-admin-drawer>
            <div class="absolute inset-0 bg-navy-900/60 backdrop-blur-sm" data-admin-drawer-close></div>
            <nav class="admin-sidebar absolute left-0 top-0 h-full w-72 max-w-[85vw] overflow-y-auto shadow-2xl flex flex-col"
                 aria-label="Admin navigation (mobile)">
                <div class="flex items-center justify-between border-b border-white/8 px-5 py-4">
                    <span class="text-[15px] font-extrabold text-white">Lala Admin</span>
                    <button type="button" class="grid h-9 w-9 place-items-center rounded-xl text-navy-200 hover:bg-white/10 transition-colors"
                            data-admin-drawer-close aria-label="Close menu">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="h-5 w-5"><path d="M18 6 6 18M6 6l12 12"/></svg>
                    </button>
                </div>
                <div class="flex-1 py-4">
                <?php foreach ($nav as $href => [$label, $icon, $grad]):
                    $active = $href === '/admin' ? $path === '/admin' : str_starts_with($path, $href);
                    if ($href === '/admin/categories'): ?>
                <p class="admin-section-label mt-2">Content</p>
                <?php elseif ($href === '/admin/homepage'): ?>
                <p class="admin-section-label mt-2">Config</p>
                <?php endif; ?>
                <a href="<?= e($href) ?>"
                   class="admin-nav-item <?= $active ? 'active' : '' ?>">
                    <span class="admin-nav-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9"
                             stroke-linecap="round" stroke-linejoin="round" class="h-4 w-4" aria-hidden="true">
                            <path d="<?= e($icon) ?>"/>
                        </svg>
                    </span>
                    <?= e($label) ?>
                </a>
                <?php endforeach; ?>
                </div>
                <form method="post" action="/admin/logout" class="border-t border-white/8 px-5 py-4">
                    <?= \App\Core\Csrf::field() ?>
                    <button type="submit" class="text-[13px] font-semibold text-navy-300 hover:text-white transition-colors">Log out (<?= e($adminUser) ?>)</button>
                </form>
            </nav>
        </div>

        <main class="flex-1 p-4 sm:p-6">
            <?php if (!empty($flash)): ?>
            <div class="mb-5 flex items-start gap-3 rounded-2xl border px-4 py-3.5 text-[14px] font-semibold <?= match ($flash['type'] ?? 'success') {
                'error'   => 'border-flame-200 bg-flame-50 text-flame-700',
                'warning' => 'border-gold-300 bg-gold-50 text-navy-900',
                default   => 'border-jade-200 bg-jade-50 text-jade-700',
            } ?>" role="status">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" class="mt-0.5 h-4.5 w-4.5 shrink-0" style="width:1.1rem;height:1.1rem" aria-hidden="true">
                    <path d="<?= ($flash['type'] ?? 'success') === 'error' ? 'M15 9l-6 6M9 9l6 6' : 'M20 6L9 17l-5-5' ?>"/>
                </svg>
                <?= e($flash['message']) ?>
            </div>
            <?php endif; ?>

            <?= $content ?>
        </main>
    </div>
</div>

<script>
/* Mobile admin drawer */
(function () {
    var drawer = document.getElementById('admin-mobile-nav');
    var toggle = document.getElementById('admin-nav-toggle');
    if (!drawer || !toggle) return;
    function open() {
        drawer.classList.remove('hidden');
        toggle.setAttribute('aria-expanded', 'true');
        document.body.style.overflow = 'hidden';
    }
    function close() {
        drawer.classList.add('hidden');
        toggle.setAttribute('aria-expanded', 'false');
        document.body.style.overflow = '';
    }
    toggle.addEventListener('click', open);
    drawer.querySelectorAll('[data-admin-drawer-close]').forEach(function (el) {
        el.addEventListener('click', close);
    });
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape' && !drawer.classList.contains('hidden')) close();
    });
})();
</script>
</body>
</html>
