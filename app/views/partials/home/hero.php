<?php
/**
 * Hero slides carousel — cinematic, full-bleed.
 *
 * Design intent
 *  - Full-viewport width (no max-width box) so the banner reads as a stage.
 *  - One strong focal column + a quiet "shop by need" glass panel on desktop.
 *  - Depth from a real product photo, layered navy gradients, a faint mesh and
 *    two slow-drifting colour ornaments — all skipped under reduced motion.
 *  - Controls: side arrows, a slide counter, and segmented progress indicators
 *    that fill over the real interval (so the timing is legible, not decorative).
 *
 * Accessibility
 *  - role=group per slide with "n of m", aria-hidden on inactive slides,
 *    keyboard ← → support, pause on hover/focus, and no autoplay when the user
 *    prefers reduced motion.
 *
 * @var array $slides      active, in-window hero_slides rows
 * @var array $sectionCfg  section config (title/subtitle overrides)
 */
if ($slides === []) { return; }

$slidesCount = count($slides);
$intervalMs  = 6000;
?>
<section class="relative overflow-hidden bg-navy-950 reveal" data-reveal aria-roledescription="carousel" aria-label="Featured highlights"
         data-hero data-hero-slider data-interval="<?= $intervalMs ?>" tabindex="0">

    <ul class="relative flex transition-transform duration-700 ease-[cubic-bezier(0.25,0.46,0.45,0.94)]"
        data-hero-track style="transform: translateX(0%)">

        <?php foreach ($slides as $i => $s):
            $img       = (string) ($s['image'] ?? '');
            $imgMob    = (string) ($s['image_mobile'] ?? $img);
            $imgUrl    = $img !== '' ? image_url($img) : '';
            $imgMobUrl = $imgMob !== '' ? image_url($imgMob) : '';
            $ctaUrl    = \App\Models\SiteContent::isSafeUrl((string) ($s['cta_url'] ?? '')) ? (string) $s['cta_url'] : '/';
            $theme     = (string) ($s['theme'] ?? 'tech');

            // Overlay strength per theme: keeps text legible over any photo.
            $scrim = match ($theme) {
                'flame' => 'from-[#2a0b12]/95 via-[#2a0b12]/75 to-navy-950/10',
                'jade'  => 'from-[#04241a]/95 via-[#04241a]/75 to-navy-950/10',
                'navy'  => 'from-navy-950 via-navy-950/80 to-navy-950/5',
                default => 'from-navy-950 via-navy-950/80 to-navy-950/0',
            };
            $accent = match ($theme) {
                'flame' => 'bg-flame-400',
                'jade'  => 'bg-jade-400',
                'navy'  => 'bg-electric-400',
                default => 'bg-gold-400',
            };
        ?>
        <li class="relative flex w-full shrink-0 items-center overflow-hidden hero-stage <?= $i === 0 ? 'is-active' : '' ?>"
            data-hero-slide role="group" aria-roledescription="slide"
            aria-label="<?= $i + 1 ?> of <?= $slidesCount ?>"
            <?= $i > 0 ? 'aria-hidden="true"' : '' ?>>

            <?php if ($imgUrl !== ''): ?>
            <picture class="absolute inset-0 hero-parallax">
                <source media="(max-width: 767px)" srcset="<?= e($imgMobUrl ?: $imgUrl) ?>">
                <img src="<?= e($imgUrl) ?>" alt="<?= e($s['title'] ?? '') ?>"
                     width="1800" height="900" loading="<?= $i === 0 ? 'eager' : 'lazy' ?>"
                     decoding="async" fetchpriority="<?= $i === 0 ? 'high' : 'low' ?>"
                     class="h-full w-full object-cover"
                     data-hero-img>
            </picture>
            <?php endif; ?>

            <!-- Layered scrim: horizontal for copy legibility, vertical for depth. -->
            <div class="absolute inset-0 bg-gradient-to-r <?= $scrim ?>" aria-hidden="true"></div>
            <div class="absolute inset-0 bg-gradient-to-t from-navy-950/92 via-navy-950/10 to-transparent" aria-hidden="true"></div>
            <div class="absolute inset-0 hero-mesh" aria-hidden="true"></div>

            <!-- Slow colour ornaments -->
            <div class="hero-ornament hero-ornament--drift -right-24 -top-24 h-[26rem] w-[26rem] bg-electric-500/25" aria-hidden="true"></div>
            <div class="hero-ornament -left-20 bottom-0 h-72 w-72 bg-violet-600/20" aria-hidden="true"></div>

            <div class="nav-shell relative z-10 w-full pb-24 pt-14 md:pb-28 md:pt-18 lg:pb-32 lg:pt-24">
                <div class="grid items-center gap-10 lg:grid-cols-12">

                    <!-- Focal column -->
                    <div class="max-w-2xl lg:col-span-7 xl:col-span-6">
                        <?php if (!empty($s['badge'])): ?>
                        <span class="inline-flex items-center gap-2 rounded-full border border-white/20 bg-white/10 px-3.5 py-1.5 text-[11.5px] font-bold uppercase tracking-[0.16em] text-white backdrop-blur-md">
                            <span class="relative flex h-1.5 w-1.5">
                                <span class="absolute inline-flex h-full w-full animate-ping rounded-full <?= $accent ?> opacity-70"></span>
                                <span class="relative inline-flex h-1.5 w-1.5 rounded-full <?= $accent ?>"></span>
                            </span>
                            <?= e($s['badge']) ?>
                        </span>
                        <?php endif; ?>

                        <h1 class="mt-5 text-[clamp(2rem,4.6vw,3.65rem)] font-extrabold leading-[1.03] tracking-[-0.03em] text-white">
                            <?= e($s['title'] ?? '') ?>
                        </h1>

                        <?php if (!empty($s['subtitle'])): ?>
                        <p class="mt-4 max-w-xl text-[15px] leading-relaxed text-white/75 md:text-[17px]">
                            <?= e($s['subtitle']) ?>
                        </p>
                        <?php endif; ?>

                        <?php if (!empty($s['cta_label'])): ?>
                        <div class="mt-8 flex flex-wrap items-center gap-3">
                            <a href="<?= e($ctaUrl) ?>"
                               class="btn btn-primary btn-lg group shadow-[0_18px_40px_-18px_rgba(37,99,235,0.9)]">
                                <?= e($s['cta_label']) ?>
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4"
                                     class="h-4 w-4 transition-transform duration-200 group-hover:translate-x-1"><path d="M5 12h14m-6-6 6 6-6 6"/></svg>
                            </a>
                            <a href="/deals"
                               class="btn btn-lg border border-white/25 bg-navy-950/45 text-white backdrop-blur-md hover:bg-white/20">
                                <svg viewBox="0 0 24 24" fill="currentColor" class="h-4 w-4 text-gold-300"><path d="M13 2 4 14h6l-1 8 9-12h-6l1-8Z"/></svg>
                                Today's deals
                            </a>
                        </div>
                        <?php endif; ?>

                        <!-- Honest reassurance chips (kept from the original copy). -->
                        <ul class="mt-9 flex flex-wrap items-center gap-x-5 gap-y-2.5 text-[12.5px] font-medium text-white/65">
                            <li class="flex items-center gap-1.5">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="h-4 w-4 text-gold-300" aria-hidden="true"><path d="M12 2l7 4v6c0 4.4-3 8.3-7 9-4-.7-7-4.6-7-9V6l7-4z"/><path d="M9 12l2 2 4-4"/></svg>
                                100% Genuine
                            </li>
                            <li class="flex items-center gap-1.5">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="h-4 w-4 text-gold-300" aria-hidden="true"><path d="M3 7h11v9H3z"/><path d="M14 10h4l3 3v3h-7z"/><circle cx="7" cy="18" r="2"/><circle cx="17.5" cy="18" r="2"/></svg>
                                Free delivery
                            </li>
                            <li class="flex items-center gap-1.5">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="h-4 w-4 text-gold-300" aria-hidden="true"><path d="M3 12a9 9 0 1 0 3-6.7"/><path d="M3 4v5h5"/></svg>
                                Easy returns
                            </li>
                        </ul>
                    </div>

                    <!-- Quiet navigation panel (desktop only, real routes only) -->
                    <div class="hidden lg:col-span-5 lg:block xl:col-span-6">
                        <div class="ml-auto max-w-sm rounded-2xl border border-white/15 bg-navy-950/55 p-5 shadow-[0_30px_60px_-30px_rgba(0,0,0,0.7)] backdrop-blur-xl">
                            <p class="text-[11px] font-bold uppercase tracking-[0.16em] text-white/55">Shop by need</p>
                            <ul class="mt-3 divide-y divide-white/10">
                                <li>
                                    <a href="/category/smartphones" class="group flex items-center gap-3 py-3 text-white">
                                        <span class="grid h-9 w-9 shrink-0 place-items-center rounded-xl bg-white/15 text-gold-300">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="h-4.5 w-4.5" style="width:1.1rem;height:1.1rem"><rect x="7" y="2" width="10" height="20" rx="2.5"/><path d="M11 18h2"/></svg>
                                        </span>
                                        <span class="min-w-0 flex-1">
                                            <span class="block text-[14px] font-bold">New smartphones</span>
                                            <span class="block text-[12px] text-white/55">Latest models in stock</span>
                                        </span>
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" class="h-4 w-4 shrink-0 text-white/40 transition-transform group-hover:translate-x-0.5 group-hover:text-white"><path d="M5 12h14m-6-6 6 6-6 6"/></svg>
                                    </a>
                                </li>
                                <li>
                                    <a href="/category/used-phones" class="group flex items-center gap-3 py-3 text-white">
                                        <span class="grid h-9 w-9 shrink-0 place-items-center rounded-xl bg-white/15 text-jade-300">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="h-4.5 w-4.5" style="width:1.1rem;height:1.1rem"><path d="M20 6L9 17l-5-5"/></svg>
                                        </span>
                                        <span class="min-w-0 flex-1">
                                            <span class="block text-[14px] font-bold">Certified used</span>
                                            <span class="block text-[12px] text-white/55">Checked &amp; warranted</span>
                                        </span>
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" class="h-4 w-4 shrink-0 text-white/40 transition-transform group-hover:translate-x-0.5 group-hover:text-white"><path d="M5 12h14m-6-6 6 6-6 6"/></svg>
                                    </a>
                                </li>
                                <li>
                                    <a href="/repair/screen-replacement" class="group flex items-center gap-3 pt-3 text-white">
                                        <span class="grid h-9 w-9 shrink-0 place-items-center rounded-xl bg-white/15 text-electric-300">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="h-4.5 w-4.5" style="width:1.1rem;height:1.1rem"><path d="M14.7 6.3a4 4 0 0 0 5 5L21 21H3L14.7 6.3z"/><path d="M3 7l4-4"/></svg>
                                        </span>
                                        <span class="min-w-0 flex-1">
                                            <span class="block text-[14px] font-bold">Repair a device</span>
                                            <span class="block text-[12px] text-white/55">Screen &amp; battery</span>
                                        </span>
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" class="h-4 w-4 shrink-0 text-white/40 transition-transform group-hover:translate-x-0.5 group-hover:text-white"><path d="M5 12h14m-6-6 6 6-6 6"/></svg>
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </li>
        <?php endforeach; ?>
    </ul>

    <?php if ($slidesCount > 1): ?>
    <!-- Bottom control bar -->
    <div class="absolute inset-x-0 bottom-0 z-20">
        <div class="nav-shell flex items-center justify-between gap-4 pb-4 pt-10">
            <div class="flex items-center gap-4">
                <span class="flex items-baseline gap-1 font-mono text-[12px] font-bold text-white/80">
                    <span data-hero-current>1</span>
                    <span class="text-white/35">/</span>
                    <span class="text-white/50"><?= $slidesCount ?></span>
                </span>
                <div class="flex items-center gap-1.5" role="tablist" aria-label="Choose slide">
                    <?php foreach ($slides as $i => $s): ?>
                    <button type="button" data-hero-dot="<?= $i ?>" role="tab"
                            aria-selected="<?= $i === 0 ? 'true' : 'false' ?>"
                            aria-label="Slide <?= $i + 1 ?>"
                            class="group h-1.5 overflow-hidden rounded-full bg-white/25 transition-all duration-300 <?= $i === 0 ? 'w-10' : 'w-5 hover:bg-white/40' ?>">
                        <span data-hero-seg-fill class="block h-full w-0 rounded-full bg-gold-400"></span>
                    </button>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Arrows live down here so they can never sit on top of the copy. -->
            <div class="flex items-center gap-2">
                <span class="mr-1 hidden items-center gap-1.5 rounded-full bg-navy-950/45 px-2.5 py-1 text-[11.5px] font-medium text-white/70 backdrop-blur-md lg:flex">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="h-3.5 w-3.5" aria-hidden="true"><path d="M8 3H5a2 2 0 0 0-2 2v3m18 0V5a2 2 0 0 0-2-2h-3m0 18h3a2 2 0 0 0 2-2v-3M3 16v3a2 2 0 0 0 2 2h3"/></svg>
                    Swipe or use ← →
                </span>
                <button type="button" data-hero-prev aria-label="Previous slide"
                        class="grid h-9 w-9 place-items-center rounded-full border border-white/25 bg-navy-950/45 text-white backdrop-blur-md transition hover:scale-105 hover:bg-white/25">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" class="h-4 w-4"><path d="M15 18l-6-6 6-6"/></svg>
                </button>
                <button type="button" data-hero-next aria-label="Next slide"
                        class="grid h-9 w-9 place-items-center rounded-full border border-white/25 bg-navy-950/45 text-white backdrop-blur-md transition hover:scale-105 hover:bg-white/25">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" class="h-4 w-4"><path d="M9 6l6 6-6 6"/></svg>
                </button>
            </div>
        </div>
    </div>
    <?php endif; ?>
</section>

<?php if ($slidesCount > 1): ?>
<script>
(function () {
    var section = document.querySelector('[data-hero]');
    if (!section) return;

    var track    = section.querySelector('[data-hero-track]');
    var slides   = section.querySelectorAll('[data-hero-slide]');
    var dots     = section.querySelectorAll('[data-hero-dot]');
    var prevBtn  = section.querySelector('[data-hero-prev]');
    var nextBtn  = section.querySelector('[data-hero-next]');
    var counter  = section.querySelector('[data-hero-current]');
    var total    = slides.length;
    var interval = parseInt(section.getAttribute('data-interval'), 10) || 6000;
    var reduced  = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    var cur      = 0;
    var timer    = null;
    var paused   = false;

    if (!track || total < 2) return;

    function paintSegments() {
        dots.forEach(function (dot, i) {
            var fill = dot.querySelector('[data-hero-seg-fill]');
            dot.classList.toggle('w-10', i === cur);
            dot.classList.toggle('w-5', i !== cur);
            dot.setAttribute('aria-selected', i === cur ? 'true' : 'false');
            if (!fill) return;

            fill.style.transition = 'none';
            fill.style.width = i < cur ? '100%' : '0%';

            if (i === cur && !reduced) {
                void fill.offsetWidth; // reflow so the transition restarts
                fill.style.transition = 'width ' + interval + 'ms linear';
                fill.style.width = '100%';
            }
        });
    }

    function go(idx) {
        cur = ((idx % total) + total) % total;

        slides.forEach(function (slide, i) {
            var active = i === cur;
            slide.classList.toggle('is-active', active);
            if (active) { slide.removeAttribute('aria-hidden'); }
            else { slide.setAttribute('aria-hidden', 'true'); }
        });

        track.style.transform = 'translateX(-' + (cur * 100) + '%)';
        if (counter) counter.textContent = cur + 1;
        paintSegments();
        schedule();
    }

    function schedule() {
        clearTimeout(timer);
        if (paused || reduced) return;
        timer = setTimeout(function () { go(cur + 1); }, interval);
    }

    function pause()  { paused = true;  clearTimeout(timer); }
    function resume() { paused = false; schedule(); }

    if (prevBtn) prevBtn.addEventListener('click', function () { go(cur - 1); });
    if (nextBtn) nextBtn.addEventListener('click', function () { go(cur + 1); });

    dots.forEach(function (dot) {
        dot.addEventListener('click', function () {
            go(parseInt(dot.getAttribute('data-hero-dot'), 10) || 0);
        });
    });

    section.addEventListener('mouseenter', pause);
    section.addEventListener('mouseleave', resume);
    section.addEventListener('focusin', pause);
    section.addEventListener('focusout', resume);

    section.addEventListener('keydown', function (e) {
        if (e.key === 'ArrowLeft')  { e.preventDefault(); go(cur - 1); }
        if (e.key === 'ArrowRight') { e.preventDefault(); go(cur + 1); }
    });

    // Touch swipe
    var touchX = null;
    section.addEventListener('touchstart', function (e) {
        touchX = e.touches[0].clientX;
        pause();
    }, { passive: true });
    section.addEventListener('touchend', function (e) {
        if (touchX === null) return;
        var diff = touchX - e.changedTouches[0].clientX;
        if (Math.abs(diff) > 45) { go(diff > 0 ? cur + 1 : cur - 1); }
        else { resume(); }
        touchX = null;
    }, { passive: true });

    // Don't burn cycles while the tab is hidden.
    document.addEventListener('visibilitychange', function () {
        if (document.hidden) { pause(); } else { resume(); }
    });

    paintSegments();
    schedule();
})();
</script>
<?php endif; ?>
