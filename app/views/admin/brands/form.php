<?php
/**
 * Admin — brand create/edit form.
 *
 * @var ?array $brand @var ?array $seo
 */
$editing  = $brand !== null;
$action   = $editing ? '/admin/brands/' . (int) $brand['id'] . '/edit' : '/admin/brands/create';
?>
<form method="post" action="<?= e($action) ?>" class="grid gap-5 lg:grid-cols-3">
    <?= \App\Core\Csrf::field() ?>

    <!-- Main column -->
    <div class="space-y-5 lg:col-span-2">
        <section class="card p-5 sm:p-6" aria-labelledby="brand-details">
            <h2 id="brand-details" class="text-[16px] font-extrabold tracking-tight text-ink">Brand details</h2>

            <div class="mt-5 space-y-4">
                <div>
                    <label for="name" class="mb-1.5 block text-[13px] font-bold text-ink">Name <span class="text-flame-500">*</span></label>
                    <input type="text" id="name" name="name" required maxlength="160"
                           value="<?= e((string) ($brand['name'] ?? '')) ?>" class="input">
                </div>

                <div class="grid gap-4 md:grid-cols-3">
                    <div>
                        <label for="slug" class="mb-1.5 block text-[13px] font-bold text-ink">Slug</label>
                        <input type="text" id="slug" name="slug" maxlength="160"
                               value="<?= e((string) ($brand['slug'] ?? '')) ?>" class="input font-mono !text-[13px]">
                        <p class="field-hint">Blank → generated from the name.</p>
                    </div>
                    <div>
                        <label for="website" class="mb-1.5 block text-[13px] font-bold text-ink">Website</label>
                        <input type="url" id="website" name="website" maxlength="255" placeholder="https://…"
                               value="<?= e((string) ($brand['website'] ?? '')) ?>" class="input">
                    </div>
                    <div>
                        <label for="status" class="mb-1.5 block text-[13px] font-bold text-ink">Status</label>
                        <select id="status" name="status" class="input">
                            <option value="active" <?= ($brand['status'] ?? 'active') === 'active' ? 'selected' : '' ?>>Active</option>
                            <option value="hidden" <?= ($brand['status'] ?? '') === 'hidden' ? 'selected' : '' ?>>Hidden</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label for="intro" class="mb-1.5 block text-[13px] font-bold text-ink">Brand intro <span class="font-medium text-ink-muted">(unique copy, HTML sanitized)</span></label>
                    <textarea id="intro" name="intro" rows="5" class="input"><?= e((string) ($brand['intro'] ?? '')) ?></textarea>
                </div>
            </div>
        </section>

        <?= \App\Core\View::partial('partials/seo-fields', ['seo' => $seo, 'canonicalHint' => $editing ? '/brand/' . $brand['slug'] : '/brand/your-slug']) ?>
    </div>

    <!-- Publish rail -->
    <aside class="lg:col-span-1">
        <div class="card sticky top-24 p-5">
            <h2 class="text-[15px] font-extrabold text-ink">Publish</h2>
            <p class="mt-1.5 text-[13px] leading-relaxed text-ink-muted">
                <?= $editing ? 'Changes go live as soon as you save.' : 'The brand appears in navigation and on its own page once created.' ?>
            </p>
            <button type="submit" class="btn btn-primary btn-md btn-block mt-4">
                <?= $editing ? 'Save changes' : 'Create brand' ?>
            </button>
            <a href="/admin/brands" class="btn btn-ghost btn-sm btn-block mt-2">Cancel</a>
        </div>
    </aside>
</form>
