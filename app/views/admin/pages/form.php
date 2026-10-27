<?php
/**
 * Admin — CMS page / repair-service create/edit form.
 *
 * @var ?array $page @var ?array $seo
 *
 * The repair fieldset is toggled client-side but is still submitted with the
 * form; the controller only persists repair values when page_type=repair, so
 * the behaviour is identical with JS disabled.
 */
$editing = $page !== null;
$action  = $editing ? '/admin/pages/' . (int) $page['id'] . '/edit' : '/admin/pages/create';
?>
<form method="post" action="<?= e($action) ?>" class="grid gap-5 lg:grid-cols-3">
    <?= \App\Core\Csrf::field() ?>

    <!-- Main column -->
    <div class="space-y-5 lg:col-span-2">
        <section class="card p-5 sm:p-6" aria-labelledby="page-details">
            <h2 id="page-details" class="text-[16px] font-extrabold tracking-tight text-ink">Page details</h2>

            <div class="mt-5 space-y-4">
                <div>
                    <label for="title" class="mb-1.5 block text-[13px] font-bold text-ink">Title <span class="text-flame-500">*</span></label>
                    <input type="text" id="title" name="title" required maxlength="255"
                           value="<?= e((string) ($page['title'] ?? '')) ?>" class="input">
                </div>

                <div class="grid gap-4 md:grid-cols-3">
                    <div>
                        <label for="page_type" class="mb-1.5 block text-[13px] font-bold text-ink">Type</label>
                        <select id="page_type" name="page_type" class="input">
                            <option value="static" <?= ($page['page_type'] ?? 'static') === 'static' ? 'selected' : '' ?>>Static page</option>
                            <option value="repair" <?= ($page['page_type'] ?? '') === 'repair' ? 'selected' : '' ?>>Repair service</option>
                        </select>
                    </div>
                    <div>
                        <label for="slug" class="mb-1.5 block text-[13px] font-bold text-ink">Slug</label>
                        <input type="text" id="slug" name="slug" maxlength="190" placeholder="about or screen-replacement"
                               value="<?= e((string) ($page['slug'] ?? '')) ?>" class="input font-mono !text-[13px]">
                        <p class="field-hint" id="slug-hint">Served at <code>/page/…</code></p>
                    </div>
                    <div>
                        <label for="status" class="mb-1.5 block text-[13px] font-bold text-ink">Status</label>
                        <select id="status" name="status" class="input">
                            <option value="draft" <?= ($page['status'] ?? 'draft') === 'draft' ? 'selected' : '' ?>>Draft</option>
                            <option value="published" <?= ($page['status'] ?? '') === 'published' ? 'selected' : '' ?>>Published</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label for="intro" class="mb-1.5 block text-[13px] font-bold text-ink">Intro <span class="font-medium text-ink-muted">(meta description fallback)</span></label>
                    <textarea id="intro" name="intro" rows="2" maxlength="512" class="input"><?= e((string) ($page['intro'] ?? '')) ?></textarea>
                </div>

                <div>
                    <label for="body" class="mb-1.5 block text-[13px] font-bold text-ink">Body <span class="font-medium text-ink-muted">(HTML, sanitized on save)</span></label>
                    <textarea id="body" name="body" rows="14" class="input font-mono !text-[13.5px]"><?= e((string) ($page['body'] ?? '')) ?></textarea>
                </div>
            </div>
        </section>

        <section class="card p-5 sm:p-6" id="repair-fields" aria-labelledby="repair-heading">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <h2 id="repair-heading" class="text-[16px] font-extrabold tracking-tight text-ink">Repair service data</h2>
                <span class="badge bg-violet-50 px-2.5 py-1 !text-[11px] text-violet-700">Emits Service schema</span>
            </div>

            <div class="mt-5 grid gap-4 md:grid-cols-2">
                <div>
                    <label for="service_name" class="mb-1.5 block text-[13px] font-bold text-ink">Service name</label>
                    <input type="text" id="service_name" name="service_name" maxlength="190"
                           value="<?= e((string) ($page['service_name'] ?? '')) ?>" class="input">
                </div>
                <div>
                    <label for="area_served" class="mb-1.5 block text-[13px] font-bold text-ink">Areas served <span class="font-medium text-ink-muted">(comma separated)</span></label>
                    <input type="text" id="area_served" name="area_served" maxlength="255"
                           value="<?= e((string) ($page['area_served'] ?? '')) ?>" class="input">
                </div>
                <div>
                    <label for="price_from" class="mb-1.5 block text-[13px] font-bold text-ink">Price from (PKR)</label>
                    <input type="number" id="price_from" name="price_from" step="0.01" min="0"
                           value="<?= e((string) ($page['price_from'] ?? '')) ?>" class="input" placeholder="e.g. 4500">
                </div>
                <div>
                    <label for="price_to" class="mb-1.5 block text-[13px] font-bold text-ink">Price to (PKR)</label>
                    <input type="number" id="price_to" name="price_to" step="0.01" min="0"
                           value="<?= e((string) ($page['price_to'] ?? '')) ?>" class="input" placeholder="e.g. 12000">
                </div>
            </div>

            <p class="mt-3 rounded-xl border border-line bg-surface px-3.5 py-3 text-[12.5px] leading-relaxed text-ink-muted">
                Only fill these for repair pages — the Service schema is emitted only when a service name is present, so a plain page never claims to be a service.
            </p>
        </section>

        <?= \App\Core\View::partial('partials/seo-fields', ['seo' => $seo, 'canonicalHint' => $editing ? (($page['page_type'] === 'repair' ? '/repair/' : '/page/') . $page['slug']) : '/page/your-slug']) ?>
    </div>

    <!-- Publish rail -->
    <aside class="lg:col-span-1">
        <div class="card sticky top-24 p-5">
            <h2 class="text-[15px] font-extrabold text-ink">Publish</h2>
            <p class="mt-1.5 text-[13px] leading-relaxed text-ink-muted">
                <?= ($page['status'] ?? 'draft') === 'published'
                    ? 'This page is live at its canonical URL.'
                    : 'Drafts are saved but return 404 on the storefront until published.' ?>
            </p>
            <button type="submit" class="btn btn-primary btn-md btn-block mt-4">
                <?= $editing ? 'Save changes' : 'Create page' ?>
            </button>
            <a href="/admin/pages" class="btn btn-ghost btn-sm btn-block mt-2">Cancel</a>
        </div>
    </aside>
</form>

<script>
/* Show/hide the repair block and swap the URL hint with the type.
 * Progressive enhancement only — with JS off every field still submits. */
(function () {
    var typeSel = document.getElementById('page_type');
    var fields  = document.getElementById('repair-fields');
    var hint    = document.getElementById('slug-hint');
    if (!typeSel || !fields) return;
    function sync() {
        var isRepair = typeSel.value === 'repair';
        fields.style.display = isRepair ? '' : 'none';
        if (hint) hint.innerHTML = isRepair ? 'Served at <code>/repair/\u2026</code>' : 'Served at <code>/page/\u2026</code>';
    }
    typeSel.addEventListener('change', sync);
    sync();
})();
</script>
