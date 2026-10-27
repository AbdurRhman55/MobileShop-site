<?php
/**
 * Blog post.
 *
 * @var array $post @var array $breadcrumbs
 *
 * The body is HTML sanitised by sanitizeRichHtml() when saved in admin, so it
 * is echoed unescaped (same contract as the product description).
 */
$published = $post['published_at'] ? strtotime((string) $post['published_at']) : null;
$updated   = $post['updated_at'] ? strtotime((string) $post['updated_at']) : null;
?>
<nav aria-label="Breadcrumb" class="shell max-w-3xl pt-5 text-sm text-ink-muted">
    <ol class="flex flex-wrap items-center gap-1.5">
        <li><a href="/" class="hover:text-electric-600">Home</a></li>
        <li aria-hidden="true" class="text-line">/</li>
        <li><a href="/blog" class="hover:text-electric-600">Blog</a></li>
        <li aria-hidden="true" class="text-line">/</li>
        <li class="min-w-0"><span aria-current="page" class="line-clamp-1 text-ink font-medium"><?= e($post['title']) ?></span></li>
    </ol>
</nav>

<article class="shell max-w-3xl pb-16">
    <header class="mt-6">
        <h1 class="text-[28px] md:text-[38px] font-extrabold leading-tight tracking-tight text-ink"><?= e($post['title']) ?></h1>

        <div class="mt-4 flex flex-wrap items-center gap-x-2.5 gap-y-1.5 border-b border-line pb-5 text-[13px] text-ink-muted">
            <span class="font-bold text-ink"><?= e($post['author_name'] ?? 'Lala Mobiles Team') ?></span>
            <?php if ($published !== false && $published !== null): ?>
            <span aria-hidden="true">·</span>
            <time datetime="<?= e(date('c', $published)) ?>">Published <?= e(date('d M Y', $published)) ?></time>
            <?php endif; ?>
            <?php if ($updated !== false && $updated !== null && $published !== false && $updated !== $published): ?>
            <span aria-hidden="true">·</span>
            <time datetime="<?= e(date('c', $updated)) ?>">Updated <?= e(date('d M Y', $updated)) ?></time>
            <?php endif; ?>
        </div>
    </header>

    <div class="prose-body mt-7 text-[16px]">
        <?= $post['body'] /* sanitized by sanitizeRichHtml() on save */ ?>
    </div>

    <footer class="mt-10 border-t border-line pt-6">
        <a href="/blog" class="btn btn-ghost btn-md">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="h-4 w-4"><path d="M19 12H5M11 18l-6-6 6-6"/></svg>
            Back to all articles
        </a>
    </footer>
</article>
