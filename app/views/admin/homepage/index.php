<?php
/**
 * Admin — homepage section manager.
 *
 * @var array $rows  section_key, title, subtitle, position, is_active
 *
 * One form holds every row. The ↑/↓ buttons are ordinary submit buttons
 * carrying name="_move" value="{id}:up|down", so reordering needs no JavaScript
 * and no nested forms. Blank titles fall back to each section's own heading on
 * the storefront — nothing here invents copy.
 *
 * Sections whose source data is empty render nothing on the homepage (no empty
 * shells), so disabling a section is only ever about order/emphasis.
 */
$meta = [
    'hero'        => ['Hero',              'Rotating headline slides with the primary call to action.'],
    'trust'       => ['Trust strip',       'Benefit / reassurance bar directly beneath the hero.'],
    'categories'  => ['Categories',        'Category tiles linking to real category pages.'],
    'flash'       => ['Flash sale',        'Live countdown deals — only campaigns currently in-window are shown.'],
    'bestsellers' => ['Best sellers',      'Ranked by real order lines; products with zero sales are excluded.'],
    'new-arrivals'=> ['New arrivals',      'Most recently published products.'],
    'brands'      => ['Brands',            'Brand wall linking to each brand page.'],
    'banners'     => ['Promo banners',     'Promotional banner row.'],
    'recommended' => ['Popular right now', 'Labelled honestly as popular, not a personalised recommendation.'],
    'repairs'     => ['Repair promo',      'Repair services callout linking to the real service pages.'],
    'reviews'     => ['Reviews',           'Only verified purchases are shown here.'],
    'blog'        => ['Blog',              'Three most recent published posts.'],
    'newsletter'  => ['Newsletter',        'Signup that posts to the real /newsletter endpoint.'],
];

/**
 * Sections that render no heading at all (a hero image area, an icon strip and
 * a banner row) — their title/subtitle overrides are genuinely inapplicable, so
 * the form shows an explicit note rather than a field that would do nothing.
 */
$noHeading = ['hero', 'trust', 'banners'];

$count = count($rows);
$activeCount = 0;
foreach ($rows as $r) { if ((int) $r['is_active'] === 1) { $activeCount++; } }
?>
<div class="flex flex-wrap items-center justify-between gap-3">
    <div>
        <p class="text-[12px] font-extrabold uppercase tracking-[0.18em] text-electric-600">Storefront</p>
        <h2 class="mt-1 text-[24px] font-extrabold tracking-tight text-ink">Homepage sections</h2>
        <p class="mt-1.5 text-[13.5px] text-ink-muted">Reorder, retitle or hide the sections that build the homepage.</p>
    </div>
    <div class="flex items-center gap-3">
        <span class="badge badge-soft px-3 py-1.5"><?= $activeCount ?> of <?= $count ?> visible</span>
        <a href="/" target="_blank" rel="noopener" class="btn btn-ghost btn-sm">View homepage ↗</a>
    </div>
</div>

<p class="mt-4 max-w-3xl rounded-xl border border-line bg-surface px-4 py-3 text-[12.5px] leading-relaxed text-ink-muted">
    Order runs top to bottom. A blank title keeps the section's own default heading — nothing here invents copy.
    Sections with no matching content (e.g. no campaign in window, no approved reviews) render nothing on the
    storefront rather than an empty shell.
</p>

<form method="post" action="/admin/homepage" class="card mt-5 overflow-hidden">
    <?= \App\Core\Csrf::field() ?>

    <!-- Column headings (desktop only) -->
    <div class="hidden gap-3 border-b border-line bg-surface-sunken px-4 py-3 lg:grid lg:grid-cols-[minmax(0,1.5fr)_minmax(0,1.2fr)_minmax(0,1.2fr)_5.5rem_6rem_6.5rem]">
        <span class="text-[11px] font-extrabold uppercase tracking-wide text-ink-muted">Section</span>
        <span class="text-[11px] font-extrabold uppercase tracking-wide text-ink-muted">Title override</span>
        <span class="text-[11px] font-extrabold uppercase tracking-wide text-ink-muted">Subtitle override</span>
        <span class="text-[11px] font-extrabold uppercase tracking-wide text-ink-muted">Order</span>
        <span class="text-[11px] font-extrabold uppercase tracking-wide text-ink-muted">Visible</span>
        <span class="text-[11px] font-extrabold uppercase tracking-wide text-ink-muted">Move</span>
    </div>

    <?php foreach ($rows as $i => $r):
        $id   = (int) $r['id'];
        $key  = (string) $r['section_key'];
        $info = $meta[$key] ?? [ucfirst($key), ''];
        $on   = (int) $r['is_active'] === 1;
    ?>
    <div class="grid gap-3 border-b border-line px-4 py-4 transition last:border-b-0 hover:bg-surface/60 lg:grid-cols-[minmax(0,1.5fr)_minmax(0,1.2fr)_minmax(0,1.2fr)_5.5rem_6rem_6.5rem] lg:items-start">

        <!-- Section identity -->
        <div class="min-w-0">
            <div class="flex flex-wrap items-center gap-2">
                <input type="hidden" name="sec_id[<?= $id ?>]" value="<?= $id ?>">
                <span class="badge <?= $on ? 'bg-electric-50 text-electric-700' : 'badge-soft' ?> px-2 py-0.5 !text-[11px]"><?= e($info[0]) ?></span>
                <code class="font-mono text-[11.5px] text-ink-muted"><?= e($key) ?></code>
            </div>
            <?php if ($info[1] !== ''): ?>
            <p class="mt-1.5 text-[12.5px] leading-relaxed text-ink-muted"><?= e($info[1]) ?></p>
            <?php endif; ?>
        </div>

        <!-- Title -->
        <div class="min-w-0">
            <?php if (in_array($key, $noHeading, true)): ?>
            <!-- Preserve any stored value so an omitted visible field never wipes it. -->
            <input type="hidden" name="sec_title[<?= $id ?>]" value="<?= e((string) ($r['title'] ?? '')) ?>">
            <span class="inline-flex items-center gap-1.5 rounded-lg border border-dashed border-line px-2.5 py-1.5 text-[12px] text-ink-muted">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="h-3.5 w-3.5"><circle cx="12" cy="12" r="9"/><path d="M12 16v-5M12 8h.01"/></svg>
                No heading in this section
            </span>
            <?php else: ?>
            <label class="mb-1 block text-[11px] font-extrabold uppercase tracking-wide text-ink-muted lg:sr-only" for="sec-title-<?= $i ?>">Title override</label>
            <input type="text" id="sec-title-<?= $i ?>" name="sec_title[<?= $id ?>]" maxlength="160"
                   value="<?= e((string) ($r['title'] ?? '')) ?>" class="input !py-2 !text-[13.5px]"
                   placeholder="section default">
            <?php endif; ?>
        </div>

        <!-- Subtitle -->
        <div class="min-w-0">
            <?php if (in_array($key, $noHeading, true)): ?>
            <input type="hidden" name="sec_subtitle[<?= $id ?>]" value="<?= e((string) ($r['subtitle'] ?? '')) ?>">
            <span class="sr-only">Not applicable — this section has no heading.</span>
            <?php else: ?>
            <label class="mb-1 block text-[11px] font-extrabold uppercase tracking-wide text-ink-muted lg:sr-only" for="sec-sub-<?= $i ?>">Subtitle override</label>
            <input type="text" id="sec-sub-<?= $i ?>" name="sec_subtitle[<?= $id ?>]" maxlength="320"
                   value="<?= e((string) ($r['subtitle'] ?? '')) ?>" class="input !py-2 !text-[13.5px]"
                   placeholder="—">
            <?php endif; ?>
        </div>

        <!-- Position -->
        <div>
            <label class="mb-1 block text-[11px] font-extrabold uppercase tracking-wide text-ink-muted lg:sr-only" for="sec-pos-<?= $i ?>">Order</label>
            <input type="number" id="sec-pos-<?= $i ?>" name="sec_position[<?= $id ?>]" value="<?= (int) $r['position'] ?>"
                   class="input !py-2 text-center tabular-nums !text-[13.5px]" step="1">
        </div>

        <!-- Visible toggle -->
        <div class="flex items-center lg:pt-2">
            <label class="inline-flex cursor-pointer items-center gap-2.5">
                <input type="hidden" name="sec_active[<?= $id ?>]" value="0">
                <input type="checkbox" name="sec_active[<?= $id ?>]" value="1" <?= $on ? 'checked' : '' ?>
                       class="h-4 w-4 shrink-0 accent-electric-600">
                <span class="text-[13px] font-bold <?= $on ? 'text-ink' : 'text-ink-muted' ?>">
                    <?= $on ? 'Shown' : 'Hidden' ?>
                </span>
            </label>
        </div>

        <!-- Move -->
        <div class="flex gap-1.5">
            <button type="submit" name="_move" value="<?= $id ?>:up" <?= $i === 0 ? 'disabled' : '' ?>
                    aria-label="Move <?= e($info[0]) ?> up"
                    class="btn btn-ghost btn-sm !px-2.5 <?= $i === 0 ? 'opacity-40' : '' ?>">↑</button>
            <button type="submit" name="_move" value="<?= $id ?>:down" <?= $i === $count - 1 ? 'disabled' : '' ?>
                    aria-label="Move <?= e($info[0]) ?> down"
                    class="btn btn-ghost btn-sm !px-2.5 <?= $i === $count - 1 ? 'opacity-40' : '' ?>">↓</button>
        </div>
    </div>
    <?php endforeach; ?>

    <div class="flex flex-wrap items-center justify-between gap-3 border-t border-line bg-surface px-4 py-4">
        <p class="text-[12.5px] text-ink-muted">↑ / ↓ reorder immediately. “Save layout” persists titles, subtitles, order numbers and visibility.</p>
        <button type="submit" class="btn btn-primary btn-md">Save layout</button>
    </div>
</form>
