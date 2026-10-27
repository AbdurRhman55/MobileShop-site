<?php
/**
 * Search results page.
 *
 * @var string $term @var array $results
 *
 * This page is noindex (set by the controller) — search results are not a
 * valuable index target and must not dilute the catalogue.
 */
use App\Models\Product;

$count = count($results);
?>
<div class="shell pb-16 pt-5">
    <nav aria-label="Breadcrumb" class="text-sm text-ink-muted">
        <ol class="flex flex-wrap items-center gap-1.5">
            <li><a href="/" class="hover:text-electric-600">Home</a></li>
            <li aria-hidden="true" class="text-line">/</li>
            <li aria-current="page" class="text-ink font-medium">Search</li>
        </ol>
    </nav>

    <h1 class="mt-5 text-[28px] md:text-4xl font-extrabold tracking-tight text-ink">
        <?php if ($term !== ''): ?>
        Results for <span class="text-electric-600">“<?= e($term) ?>”</span>
        <?php else: ?>
        Search products
        <?php endif; ?>
    </h1>

    <!-- Search box (same field the header uses, pre-filled) -->
    <form action="/search" method="get" role="search" class="mt-6 max-w-xl">
        <label for="search-q" class="sr-only">Search products</label>
        <div class="flex gap-2.5">
            <div class="relative flex-1">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"
                     class="pointer-events-none absolute left-3.5 top-1/2 h-4.5 w-4.5 -translate-y-1/2 text-ink-muted" style="width:1.15rem;height:1.15rem">
                    <circle cx="11" cy="11" r="7"/><path d="m20 20-3.5-3.5"/>
                </svg>
                <input type="search" id="search-q" name="q" value="<?= e($term) ?>"
                       placeholder="Search phones, brands, accessories…"
                       class="input !rounded-full !py-3 !pl-11">
            </div>
            <button type="submit" class="btn btn-primary btn-md">Search</button>
        </div>
    </form>

    <?php if ($term !== '' && $results === []): ?>
    <!-- Empty state -->
    <div class="mt-10 rounded-2xl border border-dashed border-line bg-white p-12 text-center">
        <span class="mx-auto grid h-14 w-14 place-items-center rounded-2xl bg-surface-muted text-ink-muted">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="h-7 w-7"><circle cx="11" cy="11" r="7"/><path d="m20 20-3.5-3.5"/></svg>
        </span>
        <h2 class="mt-4 text-lg font-extrabold text-ink">No products matched “<?= e($term) ?>”</h2>
        <p class="mx-auto mt-2 max-w-md text-sm text-ink-muted">Check the spelling, try a broader term, or browse a category below.</p>
        <div class="mt-6 flex flex-wrap justify-center gap-3">
            <a href="/category/smartphones" class="btn btn-tech btn-md">Browse smartphones</a>
            <a href="/category/accessories" class="btn btn-ghost btn-md">Browse accessories</a>
            <a href="/category/used-phones" class="btn btn-ghost btn-md">Used phones</a>
        </div>
    </div>

    <?php elseif ($results !== []): ?>
    <p class="mt-7 text-sm font-semibold text-ink-muted">
        <?= number_format($count) ?> result<?= $count === 1 ? '' : 's' ?>
        <span class="font-normal">— this page is excluded from search engines as it is a private result set.</span>
    </p>

    <ul class="product-grid mt-5">
        <?php foreach ($results as $p): ?>
        <li class="h-full flex flex-col">
            <?= \App\Core\View::partial('partials/product-card', ['p' => $p, 'ctx' => 'search', 'lazy' => true]) ?>
        </li>
        <?php endforeach; ?>
    </ul>

    <?php else: ?>
    <!-- No term yet: offer popular entry points rather than an empty screen. -->
    <div class="mt-10 rounded-2xl border border-line bg-white p-8">
        <h2 class="text-[16px] font-extrabold text-ink">Popular right now</h2>
        <div class="mt-4 flex flex-wrap gap-2.5">
            <?php foreach (['Samsung', 'iPhone', 'Xiaomi', 'Power bank', 'Refurbished', 'Budget phone'] as $sug): ?>
            <a href="/search?q=<?= e(urlencode($sug)) ?>" class="btn btn-ghost btn-sm !rounded-full"><?= e($sug) ?></a>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>
</div>
