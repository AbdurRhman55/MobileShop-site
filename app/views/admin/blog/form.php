<?php
/**
 * Admin — blog post create/edit form.
 *
 * @var ?array $post @var ?array $seo
 */
$editing = $post !== null;
$action  = $editing ? '/admin/blog/' . (int) $post['id'] . '/edit' : '/admin/blog/create';
?>
<form method="post" action="<?= e($action) ?>" class="grid gap-5 lg:grid-cols-3">
    <?= \App\Core\Csrf::field() ?>

    <!-- Main column -->
    <div class="space-y-5 lg:col-span-2">
        <section class="card p-5 sm:p-6" aria-labelledby="post-content">
            <h2 id="post-content" class="text-[16px] font-extrabold tracking-tight text-ink">Post content</h2>

            <div class="mt-5 space-y-4">
                <div>
                    <label for="title" class="mb-1.5 block text-[13px] font-bold text-ink">Title <span class="text-flame-500">*</span></label>
                    <input type="text" id="title" name="title" required maxlength="255"
                           value="<?= e((string) ($post['title'] ?? '')) ?>" class="input">
                </div>

                <div class="grid gap-4 md:grid-cols-3">
                    <div>
                        <label for="slug" class="mb-1.5 block text-[13px] font-bold text-ink">Slug</label>
                        <input type="text" id="slug" name="slug" maxlength="190"
                               value="<?= e((string) ($post['slug'] ?? '')) ?>" class="input font-mono !text-[13px]">
                        <p class="field-hint">Blank → generated from the title.</p>
                    </div>
                    <div>
                        <label for="author_name" class="mb-1.5 block text-[13px] font-bold text-ink">Author</label>
                        <input type="text" id="author_name" name="author_name" maxlength="120"
                               value="<?= e((string) ($post['author_name'] ?? '')) ?>" class="input">
                    </div>
                    <div>
                        <label for="status" class="mb-1.5 block text-[13px] font-bold text-ink">Status</label>
                        <select id="status" name="status" class="input">
                            <option value="draft" <?= ($post['status'] ?? 'draft') === 'draft' ? 'selected' : '' ?>>Draft</option>
                            <option value="published" <?= ($post['status'] ?? '') === 'published' ? 'selected' : '' ?>>Published</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label for="excerpt" class="mb-1.5 block text-[13px] font-bold text-ink">Excerpt <span class="font-medium text-ink-muted">(meta description fallback)</span></label>
                    <textarea id="excerpt" name="excerpt" rows="2" maxlength="512" class="input"><?= e((string) ($post['excerpt'] ?? '')) ?></textarea>
                </div>

                <div>
                    <label for="body" class="mb-1.5 block text-[13px] font-bold text-ink">Body <span class="font-medium text-ink-muted">(HTML, sanitized on save)</span></label>
                    <textarea id="body" name="body" rows="16" class="input font-mono !text-[13.5px]"><?= e((string) ($post['body'] ?? '')) ?></textarea>
                </div>
            </div>
        </section>

        <?= \App\Core\View::partial('partials/seo-fields', ['seo' => $seo, 'canonicalHint' => $editing ? '/blog/' . $post['slug'] : '/blog/your-slug']) ?>
    </div>

    <!-- Publish rail -->
    <aside class="lg:col-span-1">
        <div class="card sticky top-24 p-5">
            <h2 class="text-[15px] font-extrabold text-ink">Publish</h2>
            <p class="mt-1.5 text-[13px] leading-relaxed text-ink-muted">
                <?= ($post['status'] ?? 'draft') === 'published'
                    ? 'This post is live on the blog.'
                    : 'Drafts are saved but not shown on the blog until published.' ?>
            </p>
            <button type="submit" class="btn btn-primary btn-md btn-block mt-4">
                <?= $editing ? 'Save changes' : 'Create post' ?>
            </button>
            <a href="/admin/blog" class="btn btn-ghost btn-sm btn-block mt-2">Cancel</a>
        </div>
    </aside>
</form>
