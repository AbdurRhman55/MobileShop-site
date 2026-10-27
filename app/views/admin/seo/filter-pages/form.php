<?php
/**
 * Admin — filter landing page create/edit form.
 *
 * @var ?array $page @var ?array $seo @var array $categories @var array $brands
 *
 * This form carries its own reduced SEO fieldset (only title/description/robots
 * apply to filter pages) rather than the full shared partial.
 */
$editing = $page !== null;
$action  = $editing ? '/admin/seo/filter-pages/' . (int) $page['id'] . '/edit' : '/admin/seo/filter-pages/create';
$filters = $page !== null ? (json_decode((string) $page['filters'], true) ?: []) : [];
$fv      = fn(string $key, string $default = '') => e((string) ($filters[$key] ?? $default));
$join    = fn(string $key) => e(implode(', ', (array) ($filters[$key] ?? [])));
$p       = fn(string $key) => e((string) ($page[$key] ?? ''));
?>
<div class="flex flex-wrap items-center justify-between gap-3">
    <div>
        <p class="text-[12px] font-extrabold uppercase tracking-[0.18em] text-electric-600">Search</p>
        <h1 class="mt-1 text-[24px] font-extrabold tracking-tight text-ink"><?= $editing ? 'Edit filter page' : 'Add filter page' ?></h1>
    </div>
    <a href="/admin/seo/filter-pages" class="btn btn-ghost btn-sm">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="h-4 w-4"><path d="M19 12H5M11 18l-6-6 6-6"/></svg>
        All filter pages
    </a>
</div>

<form method="post" action="<?= e($action) ?>" class="mt-5 grid gap-5 lg:grid-cols-3">
    <?= \App\Core\Csrf::field() ?>

    <!-- Main column -->
    <div class="space-y-5 lg:col-span-2">
        <section class="card p-5 sm:p-6" aria-labelledby="fp-details">
            <h2 id="fp-details" class="text-[16px] font-extrabold tracking-tight text-ink">Filter page details</h2>

            <div class="mt-5 space-y-4">
                <div class="grid gap-4 md:grid-cols-2">
                    <div>
                        <label for="name" class="mb-1.5 block text-[13px] font-bold text-ink">Name <span class="text-flame-500">*</span></label>
                        <input type="text" id="name" name="name" required maxlength="190" value="<?= $p('name') ?>"
                               class="input" placeholder="Samsung phones under 100,000">
                    </div>
                    <div>
                        <label for="slug" class="mb-1.5 block text-[13px] font-bold text-ink">Slug</label>
                        <input type="text" id="slug" name="slug" maxlength="190" value="<?= $p('slug') ?>"
                               class="input font-mono !text-[13px]" placeholder="auto from name">
                        <p class="field-hint">Served at <code>/shop/…</code></p>
                    </div>
                </div>

                <div class="grid gap-4 md:grid-cols-2">
                    <div>
                        <label for="category_id" class="mb-1.5 block text-[13px] font-bold text-ink">Category scope</label>
                        <select id="category_id" name="category_id" class="input">
                            <option value="0">— all categories —</option>
                            <?php foreach ($categories as $c): ?>
                            <option value="<?= (int) $c['id'] ?>" <?= (int) ($page['category_id'] ?? 0) === (int) $c['id'] ? 'selected' : '' ?>>
                                <?= e($c['name']) ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label for="brand_id" class="mb-1.5 block text-[13px] font-bold text-ink">Brand scope</label>
                        <select id="brand_id" name="brand_id" class="input">
                            <option value="0">— all brands —</option>
                            <?php foreach ($brands as $b): ?>
                            <option value="<?= (int) $b['id'] ?>" <?= (int) ($page['brand_id'] ?? 0) === (int) $b['id'] ? 'selected' : '' ?>>
                                <?= e($b['name']) ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <!-- Filter conditions -->
                <div class="rounded-xl border border-line bg-surface p-4">
                    <p class="text-[11.5px] font-extrabold uppercase tracking-wide text-ink-muted">Filter conditions</p>
                    <div class="mt-3 grid gap-4 md:grid-cols-3">
                        <div>
                            <label for="storage" class="mb-1.5 block text-[13px] font-bold text-ink">Storage <span class="font-medium text-ink-muted">(comma sep.)</span></label>
                            <input type="text" id="storage" name="storage" value="<?= $join('storage') ?>" class="input" placeholder="128GB, 256GB">
                        </div>
                        <div>
                            <label for="ram" class="mb-1.5 block text-[13px] font-bold text-ink">RAM <span class="font-medium text-ink-muted">(comma sep.)</span></label>
                            <input type="text" id="ram" name="ram" value="<?= $join('ram') ?>" class="input" placeholder="8GB">
                        </div>
                        <div>
                            <label for="color" class="mb-1.5 block text-[13px] font-bold text-ink">Colour <span class="font-medium text-ink-muted">(comma sep.)</span></label>
                            <input type="text" id="color" name="color" value="<?= $join('color') ?>" class="input" placeholder="Black">
                        </div>
                        <div>
                            <label for="min_price" class="mb-1.5 block text-[13px] font-bold text-ink">Min price (PKR)</label>
                            <input type="number" id="min_price" name="min_price" min="0" step="0.01" value="<?= $fv('min_price') ?>" class="input">
                        </div>
                        <div>
                            <label for="max_price" class="mb-1.5 block text-[13px] font-bold text-ink">Max price (PKR)</label>
                            <input type="number" id="max_price" name="max_price" min="0" step="0.01" value="<?= $fv('max_price') ?>" class="input">
                        </div>
                        <div>
                            <label for="condition" class="mb-1.5 block text-[13px] font-bold text-ink">Condition</label>
                            <select id="condition" name="condition" class="input">
                                <option value="">— any —</option>
                                <?php foreach (['new' => 'New', 'used' => 'Used', 'refurbished' => 'Refurbished'] as $val => $label): ?>
                                <option value="<?= $val ?>" <?= ($filters['condition'] ?? '') === $val ? 'selected' : '' ?>><?= $label ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>

                <div>
                    <label for="intro" class="mb-1.5 block text-[13px] font-bold text-ink">Intro copy <span class="font-medium text-ink-muted">(unique landing content, HTML sanitized)</span></label>
                    <textarea id="intro" name="intro" rows="5" class="input"><?= e((string) ($page['intro'] ?? '')) ?></textarea>
                    <p class="field-hint">Write genuinely useful copy explaining this selection — thin doorway pages get noindexed by search engines anyway.</p>
                </div>
            </div>
        </section>

        <!-- SEO (reduced set — only title/desc/robots apply to filter pages) -->
        <section class="card p-5 sm:p-6" aria-labelledby="fp-seo">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <h2 id="fp-seo" class="text-[16px] font-extrabold tracking-tight text-ink">SEO</h2>
                <span class="badge badge-soft px-2.5 py-1">Optional</span>
            </div>
            <p class="mt-2 text-[13px] leading-relaxed text-ink-muted">
                Canonical default: <code class="rounded bg-surface-muted px-1.5 py-0.5 text-[12.5px] text-ink"><?= $editing ? '/shop/' . $page['slug'] : '/shop/your-slug' ?></code>
            </p>

            <div class="mt-5 grid gap-4">
                <div>
                    <label for="seo_title" class="mb-1.5 block text-[13px] font-bold text-ink">SEO title</label>
                    <input type="text" id="seo_title" name="seo_title" maxlength="180"
                           value="<?= e((string) ($seo['seo_title'] ?? '')) ?>" class="input" placeholder="Unique, ~60 characters">
                </div>
                <div>
                    <label for="meta_description" class="mb-1.5 block text-[13px] font-bold text-ink">Meta description</label>
                    <textarea id="meta_description" name="meta_description" rows="2" maxlength="320"
                              class="input"><?= e((string) ($seo['meta_description'] ?? '')) ?></textarea>
                </div>
                <div>
                    <label for="robots" class="mb-1.5 block text-[13px] font-bold text-ink">Robots directives</label>
                    <select id="robots" name="robots" class="input">
                        <option value="">Default (index,follow when published + indexable)</option>
                        <?php foreach (['index,follow', 'noindex,follow'] as $r): ?>
                        <option value="<?= $r ?>" <?= ($seo['robots'] ?? '') === $r ? 'selected' : '' ?>><?= $r ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
        </section>
    </div>

    <!-- Publish rail -->
    <aside class="lg:col-span-1">
        <div class="card sticky top-24 p-5">
            <h2 class="text-[15px] font-extrabold text-ink">Publish</h2>

            <label class="mt-4 flex cursor-pointer items-start gap-3 rounded-xl border border-line bg-surface px-3.5 py-3">
                <input type="checkbox" name="is_indexable" value="1" <?= (int) ($page['is_indexable'] ?? 1) === 1 ? 'checked' : '' ?>
                       class="mt-0.5 h-4 w-4 shrink-0 accent-electric-600">
                <span class="min-w-0">
                    <span class="block text-[13.5px] font-bold text-ink">Indexable</span>
                    <span class="mt-0.5 block text-[12.5px] leading-relaxed text-ink-muted">Uncheck to noindex this page — it keeps working, just isn't submitted for indexing.</span>
                </span>
            </label>

            <div class="mt-4">
                <label for="status" class="mb-1.5 block text-[13px] font-bold text-ink">Status</label>
                <select id="status" name="status" class="input">
                    <option value="draft" <?= ($page['status'] ?? 'draft') === 'draft' ? 'selected' : '' ?>>Draft</option>
                    <option value="published" <?= ($page['status'] ?? '') === 'published' ? 'selected' : '' ?>>Published</option>
                </select>
            </div>

            <button type="submit" class="btn btn-primary btn-md btn-block mt-4">
                <?= $editing ? 'Save changes' : 'Create filter page' ?>
            </button>
            <a href="/admin/seo/filter-pages" class="btn btn-ghost btn-sm btn-block mt-2">Cancel</a>
        </div>

        <div class="card mt-5 p-5">
            <h2 class="text-[14px] font-extrabold text-ink">Indexing policy</h2>
            <ul class="mt-3 space-y-2 text-[12.5px] leading-relaxed text-ink-muted">
                <li><strong class="text-ink">Base shop + pagination</strong> → index</li>
                <li><strong class="text-ink">Ad-hoc</strong> <code class="rounded bg-surface-muted px-1">?filter=…&sort=…</code> → noindex, canonical → parent</li>
                <li><strong class="text-ink">This curated</strong> <code class="rounded bg-surface-muted px-1">/shop/{slug}</code> → index (when published + indexable)</li>
                <li><strong class="text-ink">Search results</strong> → noindex</li>
            </ul>
        </div>
    </aside>
</form>
