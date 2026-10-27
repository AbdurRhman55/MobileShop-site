<?php
/** @var int $status @var string $title @var string $message */
?>
<div class="shell flex min-h-[52vh] flex-col items-center justify-center py-20 text-center">
    <!-- Status code, set in the brand gradient rather than a flat colour. -->
    <p class="bg-gradient-to-br from-electric-600 to-violet-600 bg-clip-text text-[68px] font-extrabold leading-none tracking-tighter text-transparent md:text-[88px]">
        <?= (int) $status ?>
    </p>

    <h1 class="mt-4 text-[26px] md:text-[34px] font-extrabold tracking-tight text-ink"><?= e($title) ?></h1>
    <p class="mt-3 max-w-lg text-[15px] leading-relaxed text-ink-muted"><?= e($message) ?></p>

    <div class="mt-8 flex flex-wrap justify-center gap-3">
        <a href="/" class="btn btn-primary btn-md">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="h-4 w-4"><path d="M3 11l9-8 9 8M5 10v10h5v-6h4v6h5V10"/></svg>
            Go to homepage
        </a>
        <a href="/search" class="btn btn-ghost btn-md">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="h-4 w-4"><circle cx="11" cy="11" r="7"/><path d="m20 20-3.5-3.5"/></svg>
            Search products
        </a>
    </div>

    <!-- Real destinations, not decorative chips. -->
    <nav aria-label="Popular pages" class="mt-12 w-full max-w-3xl">
        <p class="text-[12px] font-extrabold uppercase tracking-[0.18em] text-ink-muted">Popular right now</p>
        <div class="mt-4 flex flex-wrap justify-center gap-2.5">
            <a href="/category/smartphones" class="btn btn-ghost btn-sm !rounded-full">Smartphones</a>
            <a href="/category/used-phones" class="btn btn-ghost btn-sm !rounded-full">Used phones</a>
            <a href="/category/refurbished-phones" class="btn btn-ghost btn-sm !rounded-full">Refurbished</a>
            <a href="/category/accessories" class="btn btn-ghost btn-sm !rounded-full">Accessories</a>
            <a href="/blog" class="btn btn-ghost btn-sm !rounded-full">Guides &amp; Tips</a>
        </div>
    </nav>
</div>
