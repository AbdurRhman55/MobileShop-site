<?php
/**
 * Admin — global SEO settings.
 *
 * @var array $settings
 *
 * Every field posts to the real /admin/seo/settings endpoint. Checkboxes emit a
 * hidden "0" alongside the "1" so an unchecked box still submits a value.
 */
$sv      = fn(string $key) => e((string) ($settings[$key] ?? ''));
$checked = fn(string $key, string $default = '1') => (($settings[$key] ?? $default) === '1') ? 'checked' : '';
?>
<div class="flex flex-wrap items-center justify-between gap-3">
    <div>
        <p class="text-[12px] font-extrabold uppercase tracking-[0.18em] text-electric-600">Search</p>
        <h2 class="mt-1 text-[24px] font-extrabold tracking-tight text-ink">Global SEO settings</h2>
    </div>
    <a href="/admin/seo" class="btn btn-ghost btn-sm">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="h-4 w-4"><path d="M19 12H5M11 18l-6-6 6-6"/></svg>
        SEO overview
    </a>
</div>

<form method="post" action="/admin/seo/settings" class="mt-5 space-y-5">
    <?= \App\Core\Csrf::field() ?>

    <!-- ==================== Identity & canonical ==================== -->
    <section class="card p-5 sm:p-6" aria-labelledby="s-identity">
        <h3 id="s-identity" class="text-[16px] font-extrabold tracking-tight text-ink">Site identity &amp; canonical domain</h3>
        <div class="mt-5 grid gap-4 md:grid-cols-2">
            <div>
                <label for="canonical_domain" class="mb-1.5 block text-[13px] font-bold text-ink">Canonical domain <span class="text-flame-500">*</span></label>
                <input type="url" id="canonical_domain" name="canonical_domain" required value="<?= $sv('canonical_domain') ?>"
                       placeholder="https://www.lalamobiles.pk" class="input font-mono !text-[13px]">
                <p class="field-hint">All canonicals, sitemaps and feeds use this domain (always https). Never derived from the request host.</p>
            </div>
            <div>
                <label for="site_name" class="mb-1.5 block text-[13px] font-bold text-ink">Site name</label>
                <input type="text" id="site_name" name="site_name" value="<?= $sv('site_name') ?>" class="input">
            </div>
            <div class="md:col-span-2">
                <label for="site_tagline" class="mb-1.5 block text-[13px] font-bold text-ink">Tagline</label>
                <input type="text" id="site_tagline" name="site_tagline" value="<?= $sv('site_tagline') ?>" class="input">
            </div>
        </div>
    </section>

    <!-- ==================== Homepage metadata ==================== -->
    <section class="card p-5 sm:p-6" aria-labelledby="s-home-meta">
        <h3 id="s-home-meta" class="text-[16px] font-extrabold tracking-tight text-ink">Homepage &amp; default metadata</h3>
        <div class="mt-5 space-y-4">
            <div>
                <label for="homepage_seo_title" class="mb-1.5 block text-[13px] font-bold text-ink">Homepage SEO title</label>
                <input type="text" id="homepage_seo_title" name="homepage_seo_title" maxlength="180" value="<?= $sv('homepage_seo_title') ?>" class="input">
            </div>
            <div>
                <label for="homepage_seo_description" class="mb-1.5 block text-[13px] font-bold text-ink">Homepage meta description</label>
                <textarea id="homepage_seo_description" name="homepage_seo_description" rows="2" maxlength="320" class="input"><?= $sv('homepage_seo_description') ?></textarea>
            </div>
            <div class="grid gap-4 md:grid-cols-2">
                <div>
                    <label for="default_seo_title" class="mb-1.5 block text-[13px] font-bold text-ink">Default title fallback</label>
                    <input type="text" id="default_seo_title" name="default_seo_title" maxlength="180" value="<?= $sv('default_seo_title') ?>" class="input">
                </div>
                <div>
                    <label for="og_default_image" class="mb-1.5 block text-[13px] font-bold text-ink">Default OG image path</label>
                    <input type="text" id="og_default_image" name="og_default_image" maxlength="512" value="<?= $sv('og_default_image') ?>" class="input font-mono !text-[13px]">
                </div>
            </div>
            <div>
                <label for="default_seo_description" class="mb-1.5 block text-[13px] font-bold text-ink">Default meta description fallback</label>
                <textarea id="default_seo_description" name="default_seo_description" rows="2" maxlength="320" class="input"><?= $sv('default_seo_description') ?></textarea>
            </div>
        </div>
    </section>

    <!-- ==================== Organization ==================== -->
    <section class="card p-5 sm:p-6" aria-labelledby="s-org">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <h3 id="s-org" class="text-[16px] font-extrabold tracking-tight text-ink">Organization</h3>
            <span class="badge bg-violet-50 px-2.5 py-1 !text-[11px] text-violet-700">Feeds Organization / LocalBusiness schema</span>
        </div>
        <div class="mt-5 grid gap-4 md:grid-cols-2">
            <div>
                <label for="organization_name" class="mb-1.5 block text-[13px] font-bold text-ink">Business name</label>
                <input type="text" id="organization_name" name="organization_name" value="<?= $sv('organization_name') ?>" class="input">
            </div>
            <div>
                <label for="organization_phone" class="mb-1.5 block text-[13px] font-bold text-ink">Phone</label>
                <input type="text" id="organization_phone" name="organization_phone" value="<?= $sv('organization_phone') ?>" class="input">
            </div>
            <div>
                <label for="organization_email" class="mb-1.5 block text-[13px] font-bold text-ink">Email</label>
                <input type="email" id="organization_email" name="organization_email" value="<?= $sv('organization_email') ?>" class="input">
            </div>
            <div>
                <label for="organization_city" class="mb-1.5 block text-[13px] font-bold text-ink">City</label>
                <input type="text" id="organization_city" name="organization_city" value="<?= $sv('organization_city') ?>" class="input">
            </div>
            <div class="md:col-span-2">
                <label for="organization_address" class="mb-1.5 block text-[13px] font-bold text-ink">Street address</label>
                <input type="text" id="organization_address" name="organization_address" value="<?= $sv('organization_address') ?>" class="input">
            </div>
            <div>
                <label for="organization_region" class="mb-1.5 block text-[13px] font-bold text-ink">Region</label>
                <input type="text" id="organization_region" name="organization_region" value="<?= $sv('organization_region') ?>" class="input">
            </div>
            <div>
                <label for="organization_postal" class="mb-1.5 block text-[13px] font-bold text-ink">Postal code</label>
                <input type="text" id="organization_postal" name="organization_postal" value="<?= $sv('organization_postal') ?>" class="input">
            </div>
            <div>
                <label for="geo_latitude" class="mb-1.5 block text-[13px] font-bold text-ink">Latitude <span class="font-medium text-ink-muted">(for LocalBusiness)</span></label>
                <input type="text" id="geo_latitude" name="geo_latitude" value="<?= $sv('geo_latitude') ?>" class="input font-mono !text-[13px]" placeholder="31.5204">
            </div>
            <div>
                <label for="geo_longitude" class="mb-1.5 block text-[13px] font-bold text-ink">Longitude</label>
                <input type="text" id="geo_longitude" name="geo_longitude" value="<?= $sv('geo_longitude') ?>" class="input font-mono !text-[13px]" placeholder="74.3587">
            </div>
            <div class="md:col-span-2">
                <label for="opening_hours" class="mb-1.5 block text-[13px] font-bold text-ink">Opening hours <span class="font-medium text-ink-muted">(blank = omitted)</span></label>
                <input type="text" id="opening_hours" name="opening_hours" maxlength="255" value="<?= $sv('opening_hours') ?>"
                       class="input" placeholder="Mo-Su 11:00-21:00">
                <p class="field-hint">Only fill this with your real hours — empty means no <code>openingHours</code> claim is made to search engines.</p>
            </div>
            <div>
                <label for="social_facebook" class="mb-1.5 block text-[13px] font-bold text-ink">Facebook URL</label>
                <input type="url" id="social_facebook" name="social_facebook" value="<?= $sv('social_facebook') ?>" class="input" placeholder="https://facebook.com/…">
            </div>
            <div>
                <label for="social_instagram" class="mb-1.5 block text-[13px] font-bold text-ink">Instagram URL</label>
                <input type="url" id="social_instagram" name="social_instagram" value="<?= $sv('social_instagram') ?>" class="input" placeholder="https://instagram.com/…">
            </div>
        </div>
    </section>

    <!-- ==================== Indexing & features ==================== -->
    <section class="card p-5 sm:p-6" aria-labelledby="s-indexing">
        <h3 id="s-indexing" class="text-[16px] font-extrabold tracking-tight text-ink">Indexing &amp; features</h3>

        <div class="mt-5 space-y-3">
            <?php
            $toggles = [
                ['robots_index_site',       'Site is indexable', 'Uncheck for staging — applies noindex + robots Disallow: /'],
                ['sitemap_enabled',         'XML sitemap enabled', ''],
                ['blog_enabled',            'Blog enabled', ''],
                ['feed_enabled',            'Google Merchant Center product feed enabled', ''],
                ['analytics_consent_required', 'Require consent before loading analytics', 'GDPR-style banner'],
            ];
            foreach ($toggles as [$key, $label, $hint]): ?>
            <label class="flex cursor-pointer items-start gap-3 rounded-xl border border-line bg-surface px-4 py-3 transition hover:border-electric-300">
                <input type="hidden" name="<?= e($key) ?>" value="0">
                <input type="checkbox" name="<?= e($key) ?>" value="1" <?= $checked($key) ?> class="mt-0.5 h-4 w-4 shrink-0 accent-electric-600">
                <span class="min-w-0">
                    <span class="block text-[13.5px] font-bold text-ink"><?= e($label) ?></span>
                    <?php if ($hint !== ''): ?>
                    <span class="mt-0.5 block text-[12.5px] text-ink-muted"><?= e($hint) ?></span>
                    <?php endif; ?>
                </span>
            </label>
            <?php endforeach; ?>

            <div>
                <label for="robots_txt_extra" class="mb-1.5 block text-[13px] font-bold text-ink">Extra robots.txt rules <span class="font-medium text-ink-muted">(one per line)</span></label>
                <textarea id="robots_txt_extra" name="robots_txt_extra" rows="3" class="input font-mono !text-[13px]"
                          placeholder="Disallow: /private-path"><?= $sv('robots_txt_extra') ?></textarea>
            </div>

            <div class="max-w-xs">
                <label for="review_min_for_aggregate" class="mb-1.5 block text-[13px] font-bold text-ink">Minimum approved reviews before AggregateRating</label>
                <input type="number" id="review_min_for_aggregate" name="review_min_for_aggregate" min="1" value="<?= $sv('review_min_for_aggregate') ?>" class="input">
                <p class="field-hint">Structured rating data is withheld below this threshold.</p>
            </div>
        </div>
    </section>

    <!-- ==================== Verification & analytics ==================== -->
    <section class="card p-5 sm:p-6" aria-labelledby="s-verify">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <h3 id="s-verify" class="text-[16px] font-extrabold tracking-tight text-ink">Verification &amp; analytics</h3>
            <span class="badge badge-soft px-2.5 py-1">Public by design</span>
        </div>
        <p class="mt-2 text-[13px] leading-relaxed text-ink-muted">
            These are public-by-design tokens, not secrets. See the setup guide in docs/SEO-SETUP.md for the full verification walkthrough.
        </p>
        <div class="mt-5 grid gap-4 md:grid-cols-2">
            <div>
                <label for="google_search_console_verification" class="mb-1.5 block text-[13px] font-bold text-ink">Google Search Console verification</label>
                <input type="text" id="google_search_console_verification" name="google_search_console_verification"
                       value="<?= $sv('google_search_console_verification') ?>" class="input" placeholder="paste the content value only">
            </div>
            <div>
                <label for="bing_webmaster_verification" class="mb-1.5 block text-[13px] font-bold text-ink">Bing Webmaster verification</label>
                <input type="text" id="bing_webmaster_verification" name="bing_webmaster_verification" value="<?= $sv('bing_webmaster_verification') ?>" class="input">
            </div>
            <div>
                <label for="ga4_measurement_id" class="mb-1.5 block text-[13px] font-bold text-ink">GA4 Measurement ID</label>
                <input type="text" id="ga4_measurement_id" name="ga4_measurement_id" value="<?= $sv('ga4_measurement_id') ?>" placeholder="G-XXXXXXXXXX" class="input font-mono !text-[13px]">
            </div>
            <div>
                <label for="gtm_container_id" class="mb-1.5 block text-[13px] font-bold text-ink">Google Tag Manager Container ID</label>
                <input type="text" id="gtm_container_id" name="gtm_container_id" value="<?= $sv('gtm_container_id') ?>" placeholder="GTM-XXXXXXX" class="input font-mono !text-[13px]">
            </div>
            <div>
                <label for="merchant_center_id" class="mb-1.5 block text-[13px] font-bold text-ink">Merchant Center ID <span class="font-medium text-ink-muted">(informational)</span></label>
                <input type="text" id="merchant_center_id" name="merchant_center_id" value="<?= $sv('merchant_center_id') ?>" class="input">
            </div>
        </div>
    </section>

    <!-- ==================== Merchant feed shipping ==================== -->
    <section class="card p-5 sm:p-6" aria-labelledby="s-ship">
        <h3 id="s-ship" class="text-[16px] font-extrabold tracking-tight text-ink">Merchant feed shipping</h3>
        <div class="mt-5 grid gap-4 md:grid-cols-3">
            <div>
                <label for="feed_shipping_country" class="mb-1.5 block text-[13px] font-bold text-ink">Country</label>
                <input type="text" id="feed_shipping_country" name="feed_shipping_country" value="<?= $sv('feed_shipping_country') ?>" class="input" placeholder="PK">
            </div>
            <div>
                <label for="feed_shipping_region" class="mb-1.5 block text-[13px] font-bold text-ink">Region</label>
                <input type="text" id="feed_shipping_region" name="feed_shipping_region" value="<?= $sv('feed_shipping_region') ?>" class="input">
            </div>
            <div>
                <label for="feed_shipping_service" class="mb-1.5 block text-[13px] font-bold text-ink">Service</label>
                <input type="text" id="feed_shipping_service" name="feed_shipping_service" value="<?= $sv('feed_shipping_service') ?>" class="input">
            </div>
            <div>
                <label for="feed_shipping_price" class="mb-1.5 block text-[13px] font-bold text-ink">Shipping price</label>
                <input type="text" id="feed_shipping_price" name="feed_shipping_price" value="<?= $sv('feed_shipping_price') ?>" class="input" placeholder="250.00 PKR">
            </div>
            <div>
                <label for="feed_free_shipping_over" class="mb-1.5 block text-[13px] font-bold text-ink">Free shipping over</label>
                <input type="text" id="feed_free_shipping_over" name="feed_free_shipping_over" value="<?= $sv('feed_free_shipping_over') ?>" class="input" placeholder="50000.00 PKR">
            </div>
        </div>
    </section>

    <div class="flex justify-end">
        <button type="submit" class="btn btn-primary btn-md px-8">Save SEO settings</button>
    </div>
</form>
