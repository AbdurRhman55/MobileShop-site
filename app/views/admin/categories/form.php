<?php
/**
* Admin — category create/edit form.
*
* @var ?array $category @var array $parents @var ?array $seo
*/
$editing = $category !== null;
$action  = $editing ? '/admin/categories/' . ( int ) $category[ 'id' ] . '/edit' : '/admin/categories/create';
?>
<form method = 'post' action = "<?= e($action) ?>" class = 'grid gap-5 lg:grid-cols-3'>
<?= \App\Core\Csrf::field() ?>

<!-- Main column -->
<div class = 'space-y-5 lg:col-span-2'>
<section class = 'card p-5 sm:p-6' aria-labelledby = 'category-details'>
<h2 id = 'category-details' class = 'text-[16px] font-extrabold tracking-tight text-ink'>Category details</h2>

<div class = 'mt-5 space-y-4'>
<div>
<label for = 'name' class = 'mb-1.5 block text-[13px] font-bold text-ink'>Name <span class = 'text-flame-500'>*</span></label>
<input type = 'text' id = 'name' name = 'name' required maxlength = '160'
value = "<?= e((string) ($category['name'] ?? '')) ?>" class = 'input'>
</div>

<div class = 'grid gap-4 md:grid-cols-3'>
<div>
<label for = 'slug' class = 'mb-1.5 block text-[13px] font-bold text-ink'>Slug</label>
<input type = 'text' id = 'slug' name = 'slug' maxlength = '160'
value = "<?= e((string) ($category['slug'] ?? '')) ?>" class = 'input font-mono !text-[13px]'>
<p class = 'field-hint'>Blank → generated from the name.</p>
</div>
<div>
<label for = 'parent_id' class = 'mb-1.5 block text-[13px] font-bold text-ink'>Parent</label>
<select id = 'parent_id' name = 'parent_id' class = 'input'>
<option value = '0'>— Top level —</option>
<?php foreach ( $parents as $p ): ?>
<option value = "<?= (int) $p['id'] ?>" <?= ( int ) ( $category[ 'parent_id' ] ?? 0 ) === ( int ) $p[ 'id' ] ? 'selected' : '' ?>><?= e( $p[ 'name' ] ) ?></option>
<?php endforeach;
?>
</select>
</div>
<div>
<label for = 'status' class = 'mb-1.5 block text-[13px] font-bold text-ink'>Status</label>
<select id = 'status' name = 'status' class = 'input'>
<option value = 'active' <?= ( $category[ 'status' ] ?? 'active' ) === 'active' ? 'selected' : '' ?>>Active</option>
<option value = 'hidden' <?= ( $category[ 'status' ] ?? '' ) === 'hidden' ? 'selected' : '' ?>>Hidden</option>
</select>
</div>
</div>

<div>
<label for = 'intro' class = 'mb-1.5 block text-[13px] font-bold text-ink'>Intro content <span class = 'font-medium text-ink-muted'>( landing copy, HTML sanitized )</span></label>
<textarea id = 'intro' name = 'intro' rows = '6' class = 'input'><?= e( ( string ) ( $category[ 'intro' ] ?? '' ) ) ?></textarea>
<p class = 'field-hint'>Write genuinely useful category copy — not boilerplate. This also becomes the fallback meta description.</p>
</div>

<div class = 'grid gap-4 md:grid-cols-2'>
<div><label for = 'image' class = 'mb-1.5 block text-[13px] font-bold text-ink'>Tile image URL or public path</label><input id = 'image' name = 'image' maxlength = '512' value = "<?= e((string) ($category['image'] ?? '')) ?>" class = 'input' placeholder = 'https://… or img/categories/audio.jpg'></div>
<div><label for = 'banner_image' class = 'mb-1.5 block text-[13px] font-bold text-ink'>Listing banner image URL or public path</label><input id = 'banner_image' name = 'banner_image' maxlength = '512' value = "<?= e((string) ($category['banner_image'] ?? '')) ?>" class = 'input' placeholder = 'https://… or img/categories/audio-banner.jpg'></div>
<div><label for = 'icon' class = 'mb-1.5 block text-[13px] font-bold text-ink'>Icon key</label><input id = 'icon' name = 'icon' maxlength = '64' value = "<?= e((string) ($category['icon'] ?? '')) ?>" class = 'input' placeholder = 'headphones'></div>
<div><label for = 'sort_order' class = 'mb-1.5 block text-[13px] font-bold text-ink'>Navigation sort order</label><input type = 'number' id = 'sort_order' name = 'sort_order' value = "<?= (int) ($category['sort_order'] ?? $category['position'] ?? 0) ?>" class = 'input'></div>
</div>

<div class = 'grid gap-4 md:grid-cols-2'>
<div><label for = 'meta_title' class = 'mb-1.5 block text-[13px] font-bold text-ink'>Category meta title</label><input id = 'meta_title' name = 'meta_title' maxlength = '180' value = "<?= e((string) ($category['meta_title'] ?? '')) ?>" class = 'input'></div>
<div><label for = 'meta_description' class = 'mb-1.5 block text-[13px] font-bold text-ink'>Category meta description</label><textarea id = 'meta_description' name = 'meta_description' rows = '2' maxlength = '320' class = 'input'><?= e( ( string ) ( $category[ 'meta_description' ] ?? '' ) ) ?></textarea></div>
</div>

<div class = 'sm:max-w-[10rem]'>
<label for = 'position' class = 'mb-1.5 block text-[13px] font-bold text-ink'>Nav position</label>
<input type = 'number' id = 'position' name = 'position' value = "<?= (int) ($category['position'] ?? 0) ?>" class = 'input'>
<p class = 'field-hint'>Lower numbers appear first.</p>
</div>
</div>
</section>

<?= \App\Core\View::partial( 'partials/seo-fields', [ 'seo' => $seo, 'canonicalHint' => $editing ? '/category/' . $category[ 'slug' ] : '/category/your-slug' ] ) ?>
</div>

<!-- Publish rail -->
<aside class = 'lg:col-span-1'>
<div class = 'card sticky top-24 p-5'>
<h2 class = 'text-[15px] font-extrabold text-ink'>Publish</h2>
<p class = 'mt-1.5 text-[13px] leading-relaxed text-ink-muted'>
<?= $editing ? 'Changes go live as soon as you save.' : 'The category appears in the main navigation once created.' ?>
</p>
<button type = 'submit' class = 'btn btn-primary btn-md btn-block mt-4'>
<?= $editing ? 'Save changes' : 'Create category' ?>
</button>
<a href = '/admin/categories' class = 'btn btn-ghost btn-sm btn-block mt-2'>Cancel</a>
</div>
</aside>
</form>
