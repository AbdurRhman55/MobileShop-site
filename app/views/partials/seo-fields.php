<?php
/**
 * Reusable SEO editor fieldset.
 * Included by product/category/brand/blog/page/filter-page forms.
 *
 * @var ?array $seo   existing seo_meta row (or null)
 * @var string $canonicalHint  example auto-canonical path shown as a hint
 *
 * Behaviour is unchanged: blank fields fall back to the auto-generated
 * defaults, and custom_head is sanitized server-side (scripts stripped).
 */
$seo = $seo ?? null;
$val = fn(string $key) => e((string) ($seo[$key] ?? ''));
?>
<section class="card p-5 sm:p-6" aria-labelledby="seo-fields-heading">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <h2 id="seo-fields-heading" class="text-[16px] font-extrabold tracking-tight text-ink">SEO</h2>
        <span class="badge badge-soft px-2.5 py-1">Optional</span>
    </div>

    <p class="mt-2 text-[13px] leading-relaxed text-ink-muted">
        Leave these blank to use the auto-generated defaults. Default canonical:
        <code class="rounded bg-surface-muted px-1.5 py-0.5 text-[12.5px] text-ink"><?= e($canonicalHint ?? '') ?></code>
    </p>

    <div class="mt-5 grid gap-4 md:grid-cols-2">
        <div class="md:col-span-2">
            <label for="seo_title" class="mb-1.5 block text-[13px] font-bold text-ink">SEO title</label>
            <input type="text" id="seo_title" name="seo_title" maxlength="180" value="<?= $val('seo_title') ?>"
                   class="input" placeholder="Unique, ~60 characters">
            <p class="field-hint">Shown as the blue link in search results.</p>
        </div>

        <div class="md:col-span-2">
            <label for="meta_description" class="mb-1.5 block text-[13px] font-bold text-ink">Meta description</label>
            <textarea id="meta_description" name="meta_description" rows="2" maxlength="320"
                      class="input" placeholder="Unique, ~155 characters"><?= $val('meta_description') ?></textarea>
        </div>

        <div>
            <label for="canonical_path" class="mb-1.5 block text-[13px] font-bold text-ink">Canonical path override</label>
            <input type="text" id="canonical_path" name="canonical_path" maxlength="512" value="<?= $val('canonical_path') ?>"
                   class="input font-mono !text-[13px]" placeholder="/product/example">
        </div>

        <div>
            <label for="robots" class="mb-1.5 block text-[13px] font-bold text-ink">Robots directives</label>
            <select id="robots" name="robots" class="input">
                <option value="">Default (index,follow)</option>
                <?php foreach (['index,follow', 'noindex,follow', 'nofollow', 'noindex,nofollow'] as $r): ?>
                <option value="<?= $r ?>" <?= ($seo['robots'] ?? '') === $r ? 'selected' : '' ?>><?= $r ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div>
            <label for="og_title" class="mb-1.5 block text-[13px] font-bold text-ink">Open Graph title</label>
            <input type="text" id="og_title" name="og_title" maxlength="180" value="<?= $val('og_title') ?>" class="input">
        </div>

        <div>
            <label for="og_image" class="mb-1.5 block text-[13px] font-bold text-ink">Open Graph image path</label>
            <input type="text" id="og_image" name="og_image" maxlength="512" value="<?= $val('og_image') ?>"
                   class="input font-mono !text-[13px]" placeholder="img/products/example.svg">
        </div>

        <div class="md:col-span-2">
            <label for="og_description" class="mb-1.5 block text-[13px] font-bold text-ink">Open Graph description</label>
            <textarea id="og_description" name="og_description" rows="2" maxlength="320" class="input"><?= $val('og_description') ?></textarea>
        </div>

        <div>
            <label for="twitter_card" class="mb-1.5 block text-[13px] font-bold text-ink">Twitter card type</label>
            <select id="twitter_card" name="twitter_card" class="input">
                <option value="">Default (summary_large_image)</option>
                <option value="summary" <?= ($seo['twitter_card'] ?? '') === 'summary' ? 'selected' : '' ?>>summary</option>
                <option value="summary_large_image" <?= ($seo['twitter_card'] ?? '') === 'summary_large_image' ? 'selected' : '' ?>>summary_large_image</option>
            </select>
        </div>

        <div>
            <label for="twitter_title" class="mb-1.5 block text-[13px] font-bold text-ink">Twitter title override</label>
            <input type="text" id="twitter_title" name="twitter_title" maxlength="180" value="<?= $val('twitter_title') ?>" class="input">
        </div>

        <div class="md:col-span-2">
            <label for="twitter_description" class="mb-1.5 block text-[13px] font-bold text-ink">Twitter description override</label>
            <textarea id="twitter_description" name="twitter_description" rows="2" maxlength="320" class="input"><?= $val('twitter_description') ?></textarea>
        </div>

        <div class="md:col-span-2">
            <label for="custom_head" class="mb-1.5 block text-[13px] font-bold text-ink">Advanced: custom head markup</label>
            <textarea id="custom_head" name="custom_head" rows="2" class="input font-mono !text-[13px]"
                      placeholder="&lt;meta name=&quot;robots&quot; content=&quot;...&quot;&gt;"><?= $val('custom_head') ?></textarea>
            <p class="field-hint">Server-side sanitized: script, style, iframe and event handlers are removed to prevent XSS. Only meta/link/title tags are kept.</p>
        </div>
    </div>
</section>
