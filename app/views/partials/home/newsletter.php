<?php
/**
 * Newsletter signup. Posts to /newsletter (server-verified CSRF + email).
 */
$nlEmail = 'nl-email-' . bin2hex(random_bytes(4));
$sectionCfg = $sectionCfg ?? [];
?>
<section class="shell mt-14" aria-labelledby="newsletter-heading">
    <div class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-navy-900 via-navy-800 to-violet-900 p-6 text-white md:p-10">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="0.75" class="pointer-events-none absolute -right-10 -top-10 h-72 w-72 text-white/10"><path d="M3 7l9 6 9-6M3 7v10a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V7M3 7a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2"/></svg>

        <div class="relative grid gap-6 md:grid-cols-2 md:items-center">
            <div>
                <span class="inline-flex items-center gap-1.5 rounded-full bg-white/10 px-3 py-1 text-[12px] font-bold uppercase tracking-wide text-gold-300 ring-1 ring-white/15">
                    Newsletter
                </span>
                <?php
                // Admin title/subtitle overrides from homepage_sections. Blank
                // fields keep the original copy, so nothing is ever invented.
                $nlTitle = trim((string) ($sectionCfg['newsletter']['title'] ?? ''));
                $nlSub   = trim((string) ($sectionCfg['newsletter']['subtitle'] ?? ''));
                ?>
                <h2 id="newsletter-heading" class="mt-3 text-2xl md:text-3xl font-extrabold tracking-tight">
                    <?= e($nlTitle !== '' ? $nlTitle : 'Get deals before they sell out') ?>
                </h2>
                <p class="mt-2 max-w-md text-[15px] leading-relaxed text-white/80">
                    <?php if ($nlSub !== ''): ?><?= e($nlSub) ?><?php else: ?>New arrivals, price drops and repair tips — a couple of emails a month, no spam.<?php endif; ?>
                </p>
            </div>

            <form method="post" action="/newsletter" class="w-full" data-newsletter novalidate>
                <?= \App\Core\Csrf::field() ?>
                <div class="flex flex-col gap-3 sm:flex-row">
                    <div class="flex-1">
                        <label for="<?= e($nlEmail) ?>" class="sr-only">Email address</label>
                        <input type="email" id="<?= e($nlEmail) ?>" name="email" required autocomplete="email"
                               placeholder="you@example.com"
                               class="w-full rounded-xl border border-white/15 bg-white/10 px-4 py-3 text-[15px] text-white placeholder:text-white/45 focus:border-gold-300 focus:bg-white/15 focus:outline-none focus:ring-2 focus:ring-gold-300/40">
                    </div>
                    <button type="submit" class="btn btn-primary btn-md sm:shrink-0" data-newsletter-submit>
                        Subscribe
                    </button>
                </div>
                <p class="mt-2.5 text-[12px] leading-relaxed text-white/55">
                    By subscribing you agree to receive marketing emails. Unsubscribe anytime via the link in any email.
                </p>
                <p class="mt-2 hidden text-[13px] font-semibold" data-newsletter-status role="status" aria-live="polite"></p>
            </form>
        </div>
    </div>
</section>
