<?php
/**
 * Admin — product create/edit form.
 *
 * @var ?array $product @var array $brands @var array $categories
 * @var ?array $seo @var array $variants @var array $specs
 *
 * Form structure note: the main <form id="product-form"> wraps only the field
 * column, and the sidebar (Publish / Lifecycle / Danger zone) sits OUTSIDE it
 * as a grid sibling. The Save button re-associates via the HTML5 form=""
 * attribute. This is deliberate — a nested <form> is invalid HTML and browsers
 * silently drop the inner tag's action attribute, which previously made the
 * "Mark as deleted" button submit the EDIT action instead of delete.
 *
 * The variant/spec row buttons are progressive enhancement; every server-side
 * path works with JS disabled (existing rows still submit).
 */
$editing       = $product !== null;
$action        = $editing ? '/admin/products/' . (int) $product['id'] . '/edit' : '/admin/products/create';
$canonicalHint = $editing ? '/product/' . $product['slug'] : '/product/your-product-slug';
$v = fn(string $key) => e((string) ($product[$key] ?? ''));
?>
<?php if ($editing): ?>
<div class="flex flex-wrap items-center justify-between gap-3">
    <div>
        <p class="text-[12px] font-extrabold uppercase tracking-[0.18em] text-electric-600">Catalogue</p>
        <h1 class="mt-1 max-w-3xl text-[24px] font-extrabold leading-tight tracking-tight text-ink"><?= e($product['name']) ?></h1>
    </div>
    <a href="/admin/products" class="btn btn-ghost btn-sm">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="h-4 w-4"><path d="M19 12H5M11 18l-6-6 6-6"/></svg>
        All products
    </a>
</div>
<?php else: ?>
<div class="flex flex-wrap items-center justify-between gap-3">
    <div>
        <p class="text-[12px] font-extrabold uppercase tracking-[0.18em] text-electric-600">Catalogue</p>
        <h1 class="mt-1 text-[24px] font-extrabold tracking-tight text-ink">Add product</h1>
    </div>
    <a href="/admin/products" class="btn btn-ghost btn-sm">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="h-4 w-4"><path d="M19 12H5M11 18l-6-6 6-6"/></svg>
        All products
    </a>
</div>
<?php endif; ?>

<div class="mt-5 grid gap-5 lg:grid-cols-3">

    <!-- ============================ Field column ============================ -->
    <form id="product-form" method="post" action="<?= e($action) ?>" class="space-y-5 lg:col-span-2">
        <?= \App\Core\Csrf::field() ?>

        <!-- Basics -->
        <section class="card p-5 sm:p-6" aria-labelledby="product-details">
            <h2 id="product-details" class="text-[16px] font-extrabold tracking-tight text-ink">Product details</h2>

            <div class="mt-5 grid gap-4 md:grid-cols-2">
                <div class="md:col-span-2">
                    <label for="name" class="mb-1.5 block text-[13px] font-bold text-ink">Name <span class="text-flame-500">*</span></label>
                    <input type="text" id="name" name="name" required maxlength="255" value="<?= $v('name') ?>" class="input">
                </div>
                <div>
                    <label for="slug" class="mb-1.5 block text-[13px] font-bold text-ink">URL slug</label>
                    <input type="text" id="slug" name="slug" maxlength="190" value="<?= $v('slug') ?>"
                           class="input font-mono !text-[13px]" placeholder="auto-generated from name">
                </div>
                <div>
                    <label for="sku" class="mb-1.5 block text-[13px] font-bold text-ink">SKU</label>
                    <input type="text" id="sku" name="sku" maxlength="64" value="<?= $v('sku') ?>" class="input">
                </div>
                <div>
                    <label for="brand_id" class="mb-1.5 block text-[13px] font-bold text-ink">Brand</label>
                    <select id="brand_id" name="brand_id" class="input">
                        <option value="0">— none —</option>
                        <?php foreach ($brands as $b): ?>
                        <option value="<?= (int) $b['id'] ?>" <?= (int) ($product['brand_id'] ?? 0) === (int) $b['id'] ? 'selected' : '' ?>><?= e($b['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label for="category_id" class="mb-1.5 block text-[13px] font-bold text-ink">Category</label>
                    <select id="category_id" name="category_id" class="input">
                        <option value="0">— none —</option>
                        <?php foreach ($categories as $c): ?>
                        <option value="<?= (int) $c['id'] ?>" <?= (int) ($product['category_id'] ?? 0) === (int) $c['id'] ? 'selected' : '' ?>><?= e($c['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label for="condition" class="mb-1.5 block text-[13px] font-bold text-ink">Condition <span class="text-flame-500">*</span></label>
                    <select id="condition" name="condition" class="input">
                        <?php foreach (['new' => 'New', 'used' => 'Used', 'refurbished' => 'Refurbished'] as $val => $label): ?>
                        <option value="<?= $val ?>" <?= ($product['condition'] ?? 'new') === $val ? 'selected' : '' ?>><?= $label ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label for="status" class="mb-1.5 block text-[13px] font-bold text-ink">Status</label>
                    <select id="status" name="status" class="input">
                        <?php foreach (['active' => 'Active', 'discontinued' => 'Discontinued', 'deleted' => 'Deleted'] as $val => $label): ?>
                        <option value="<?= $val ?>" <?= ($product['status'] ?? 'active') === $val ? 'selected' : '' ?>><?= $label ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="md:col-span-2">
                    <label for="short_desc" class="mb-1.5 block text-[13px] font-bold text-ink">Short description <span class="font-medium text-ink-muted">(meta fallback)</span></label>
                    <textarea id="short_desc" name="short_desc" rows="2" maxlength="512" class="input"><?= $v('short_desc') ?></textarea>
                </div>
                <div class="md:col-span-2">
                    <label for="description" class="mb-1.5 block text-[13px] font-bold text-ink">Full description <span class="font-medium text-ink-muted">(HTML, sanitized on save)</span></label>
                    <textarea id="description" name="description" rows="10" class="input font-mono !text-[13.5px]"><?= e((string) ($product['description'] ?? '')) ?></textarea>
                </div>
            </div>
        </section>

        <!-- Pricing -->
        <section class="card p-5 sm:p-6" aria-labelledby="pricing-heading">
            <h2 id="pricing-heading" class="text-[16px] font-extrabold tracking-tight text-ink">Pricing &amp; inventory</h2>

            <div class="mt-5 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <div>
                    <label for="price" class="mb-1.5 block text-[13px] font-bold text-ink">Price (PKR) <span class="text-flame-500">*</span></label>
                    <input type="number" id="price" name="price" step="0.01" min="0" required value="<?= $v('price') ?>" class="input">
                    <p class="field-hint">The reference price discounts are measured against.</p>
                </div>
                <div>
                    <label for="sale_price" class="mb-1.5 block text-[13px] font-bold text-ink">Sale price</label>
                    <input type="number" id="sale_price" name="sale_price" step="0.01" min="0" value="<?= $v('sale_price') ?>" class="input" placeholder="empty = no sale">
                </div>
                <div>
                    <label for="stock" class="mb-1.5 block text-[13px] font-bold text-ink">Stock</label>
                    <input type="number" id="stock" name="stock" min="0" value="<?= $v('stock') ?>" class="input">
                    <p class="field-hint">Drives the “Only N left” and sold-out states.</p>
                </div>
                <div>
                    <label for="warranty" class="mb-1.5 block text-[13px] font-bold text-ink">Warranty note</label>
                    <input type="text" id="warranty" name="warranty" maxlength="128" value="<?= $v('warranty') ?>" class="input">
                </div>
                <div>
                    <label for="gtin" class="mb-1.5 block text-[13px] font-bold text-ink">GTIN / IMEI</label>
                    <input type="text" id="gtin" name="gtin" maxlength="64" value="<?= $v('gtin') ?>" class="input" placeholder="leave blank if unknown">
                    <p class="field-hint">Only fill with a genuine identifier — never invent one.</p>
                </div>
                <div>
                    <label for="mpn" class="mb-1.5 block text-[13px] font-bold text-ink">MPN</label>
                    <input type="text" id="mpn" name="mpn" maxlength="128" value="<?= $v('mpn') ?>" class="input" placeholder="leave blank if unknown">
                </div>
                <div class="flex items-end pb-1 sm:col-span-2">
                    <label class="inline-flex cursor-pointer items-center gap-2.5 rounded-xl border border-line bg-surface px-3.5 py-2.5 text-[13.5px] font-bold text-ink">
                        <input type="checkbox" name="featured" value="1" <?= !empty($product['featured']) ? 'checked' : '' ?> class="h-4 w-4 accent-electric-600">
                        Feature on homepage
                    </label>
                </div>
            </div>
        </section>

        <!-- Images -->
        <section class="card p-5 sm:p-6" aria-labelledby="image-heading">
            <h2 id="image-heading" class="text-[16px] font-extrabold tracking-tight text-ink">Main image</h2>

            <div class="mt-5 grid gap-4 md:grid-cols-2">
                <div>
                    <label for="main_image" class="mb-1.5 block text-[13px] font-bold text-ink">Image path</label>
                    <input type="text" id="main_image" name="main_image" maxlength="512" value="<?= $v('main_image') ?>"
                           class="input font-mono !text-[13px]" placeholder="img/products/example.svg">
                    <p class="field-hint">Relative to /img — rendered via the image_url() helper, so a missing file falls back to a placeholder rather than a broken image.</p>
                </div>
                <div>
                    <label for="image_alt" class="mb-1.5 block text-[13px] font-bold text-ink">Image alt text</label>
                    <input type="text" id="image_alt" name="image_alt" maxlength="255" value="<?= $v('image_alt') ?>"
                           class="input" placeholder="Describe the actual product shown">
                    <p class="field-hint">Describe the real product — required for image SEO and accessibility.</p>
                </div>
            </div>
        </section>

        <!-- Variants -->
        <section class="card p-5 sm:p-6" aria-labelledby="variants-heading">
            <h2 id="variants-heading" class="text-[16px] font-extrabold tracking-tight text-ink">Variants <span class="font-medium text-ink-muted">(optional)</span></h2>
            <p class="mt-1.5 text-[13px] leading-relaxed text-ink-muted">Variant URLs canonicalise to this product page — they never create duplicate indexed pages.</p>

            <div id="variant-rows" class="mt-4 space-y-2">
                <?php foreach ($variants as $vRow): ?>
                <div class="grid grid-cols-2 gap-2 sm:grid-cols-12 variant-row">
                    <input type="text" name="v_label[]" value="<?= e($vRow['label']) ?>" placeholder="Label" class="input !py-2 !text-[13px] sm:col-span-4">
                    <input type="text" name="v_ram[]" value="<?= e((string) $vRow['ram']) ?>" placeholder="RAM" class="input !py-2 !text-[13px] sm:col-span-2">
                    <input type="text" name="v_storage[]" value="<?= e((string) $vRow['storage']) ?>" placeholder="Storage" class="input !py-2 !text-[13px] sm:col-span-2">
                    <input type="text" name="v_color[]" value="<?= e((string) $vRow['color']) ?>" placeholder="Colour" class="input !py-2 !text-[13px] sm:col-span-2">
                    <input type="number" name="v_delta[]" value="<?= e((string) $vRow['price_delta']) ?>" placeholder="± Price" step="0.01" class="input !py-2 !text-[13px] sm:col-span-1">
                    <input type="number" name="v_stock[]" value="<?= (int) $vRow['stock'] ?>" placeholder="Stock" class="input !py-2 !text-[13px] sm:col-span-1">
                </div>
                <?php endforeach; ?>
            </div>
            <button type="button" onclick="addVariantRow()" class="btn btn-ghost btn-sm mt-3">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" class="h-4 w-4"><path d="M12 5v14M5 12h14"/></svg>
                Add variant
            </button>
        </section>

        <!-- Specs -->
        <section class="card p-5 sm:p-6" aria-labelledby="specs-heading">
            <h2 id="specs-heading" class="text-[16px] font-extrabold tracking-tight text-ink">Specifications</h2>
            <p class="mt-1.5 text-[13px] leading-relaxed text-ink-muted">Rendered as a crawlable HTML table on the product page.</p>

            <div id="spec-rows" class="mt-4 space-y-2">
                <?php foreach ($specs as $sRow): ?>
                <div class="grid grid-cols-2 gap-2 sm:grid-cols-12 spec-row">
                    <input type="text" name="s_group[]" value="<?= e($sRow['spec_group']) ?>" placeholder="Group" class="input !py-2 !text-[13px] sm:col-span-3">
                    <input type="text" name="s_label[]" value="<?= e($sRow['spec_label']) ?>" placeholder="Label" class="input !py-2 !text-[13px] sm:col-span-4">
                    <input type="text" name="s_value[]" value="<?= e($sRow['spec_value']) ?>" placeholder="Value" class="input !py-2 !text-[13px] sm:col-span-5">
                </div>
                <?php endforeach; ?>
            </div>
            <button type="button" onclick="addSpecRow()" class="btn btn-ghost btn-sm mt-3">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" class="h-4 w-4"><path d="M12 5v14M5 12h14"/></svg>
                Add specification
            </button>
        </section>

        <?= \App\Core\View::partial('partials/seo-fields', ['seo' => $seo, 'canonicalHint' => $canonicalHint]) ?>
    </form>

    <!-- ====================== Sidebar (outside the form) ====================== -->
    <aside class="space-y-5 lg:col-span-1">
        <div class="card sticky top-24 p-5">
            <h2 class="text-[15px] font-extrabold text-ink">Publish</h2>
            <p class="mt-1.5 text-[13px] leading-relaxed text-ink-muted">
                <?= $editing
                    ? 'Saving updates the live product page immediately.'
                    : 'The product becomes visible on the storefront once created.' ?>
            </p>

            <!-- Re-associated with #product-form via the HTML5 form attribute. -->
            <button type="submit" form="product-form" class="btn btn-primary btn-md btn-block mt-4">
                <?= $editing ? 'Save changes' : 'Create product' ?>
            </button>

            <?php if ($editing): ?>
            <a href="/product/<?= e($product['slug']) ?>" target="_blank" rel="noopener" class="btn btn-ghost btn-sm btn-block mt-2">
                View live page ↗
            </a>
            <?php endif; ?>
            <a href="/admin/products" class="btn btn-ghost btn-sm btn-block mt-2">Cancel</a>
        </div>

        <?php if ($editing): ?>
        <div class="card p-5">
            <h2 class="text-[15px] font-extrabold text-ink">Lifecycle</h2>
            <p class="mt-1.5 text-[13px] leading-relaxed text-ink-muted">
                “Deleted” products return 410 Gone, or 301 to a replacement below if one is set. Discontinued products stay live but show as out of stock.
            </p>

            <form method="post" action="/admin/products/<?= (int) $product['id'] ?>/edit" class="mt-4">
                <?= \App\Core\Csrf::field() ?>
                <label for="redirect_to_id" class="mb-1.5 block text-[13px] font-bold text-ink">Replacement product <span class="font-medium text-ink-muted">(when deleted)</span></label>
                <select id="redirect_to_id" name="redirect_to_id" class="input">
                    <option value="0">— none (410 Gone) —</option>
                    <?php
                    $replacements = \App\Core\Database::fetchAll("SELECT id, name FROM products WHERE status='active' AND id <> ? ORDER BY name", [(int) $product['id']]);
                    foreach ($replacements as $rp): ?>
                    <option value="<?= (int) $rp['id'] ?>" <?= (int) ($product['redirect_to_id'] ?? 0) === (int) $rp['id'] ? 'selected' : '' ?>><?= e($rp['name']) ?></option>
                    <?php endforeach; ?>
                </select>
                <button type="submit" class="btn btn-ghost btn-sm btn-block mt-3">Save replacement</button>
            </form>
        </div>

        <!-- Own standalone form — never nested, so its action survives parsing. -->
        <div class="card border-flame-200 p-5">
            <h2 class="text-[15px] font-extrabold text-flame-600">Danger zone</h2>
            <p class="mt-1.5 text-[13px] leading-relaxed text-ink-muted">
                Marks this product as deleted. Its URL will return 410 (or 301 to the replacement above). Reversible by setting status back to Active.
            </p>
            <form method="post" action="/admin/products/<?= (int) $product['id'] ?>/delete" class="mt-4"
                  onsubmit="return confirm('Mark this product as deleted? Its URL will return 410 (or 301 to a replacement).');">
                <?= \App\Core\Csrf::field() ?>
                <button type="submit" class="btn btn-block btn-md border border-flame-300 bg-white font-bold text-flame-600 hover:bg-flame-50">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" class="h-4 w-4"><path d="M4 7h16M10 11v6M14 11v6M6 7l1 13a1 1 0 0 0 1 1h8a1 1 0 0 0 1-1l1-13M9 7V4h6v3"/></svg>
                    Mark as deleted
                </button>
            </form>
        </div>
        <?php endif; ?>
    </aside>
</div>

<script>
/* Progressive enhancement: clone a blank row. Existing rows submit without JS. */
function addVariantRow() {
    var wrap = document.getElementById('variant-rows');
    var div = document.createElement('div');
    div.className = 'grid grid-cols-2 gap-2 sm:grid-cols-12 variant-row';
    var c = 'input !py-2 !text-[13px] ';
    div.innerHTML =
        '<input type="text" name="v_label[]" placeholder="Label" class="' + c + 'sm:col-span-4">' +
        '<input type="text" name="v_ram[]" placeholder="RAM" class="' + c + 'sm:col-span-2">' +
        '<input type="text" name="v_storage[]" placeholder="Storage" class="' + c + 'sm:col-span-2">' +
        '<input type="text" name="v_color[]" placeholder="Colour" class="' + c + 'sm:col-span-2">' +
        '<input type="number" name="v_delta[]" placeholder="\u00b1 Price" step="0.01" class="' + c + 'sm:col-span-1">' +
        '<input type="number" name="v_stock[]" placeholder="Stock" class="' + c + 'sm:col-span-1">';
    wrap.appendChild(div);
    div.querySelector('input').focus();
}
function addSpecRow() {
    var wrap = document.getElementById('spec-rows');
    var div = document.createElement('div');
    div.className = 'grid grid-cols-2 gap-2 sm:grid-cols-12 spec-row';
    var c = 'input !py-2 !text-[13px] ';
    div.innerHTML =
        '<input type="text" name="s_group[]" placeholder="Group" class="' + c + 'sm:col-span-3">' +
        '<input type="text" name="s_label[]" placeholder="Label" class="' + c + 'sm:col-span-4">' +
        '<input type="text" name="s_value[]" placeholder="Value" class="' + c + 'sm:col-span-5">';
    wrap.appendChild(div);
    div.querySelector('input').focus();
}
</script>
