<?php
/**
 * Generic CMS page (shipping, warranty, returns, privacy, terms...).
 *
 * @var array $page @var array $breadcrumbs
 */
?>
<nav aria-label="Breadcrumb" class="shell max-w-3xl pt-5 text-sm text-ink-muted">
    <ol class="flex flex-wrap items-center gap-1.5">
        <li><a href="/" class="hover:text-electric-600">Home</a></li>
        <li aria-hidden="true" class="text-line">/</li>
        <li aria-current="page" class="text-ink font-medium"><?= e($page['title']) ?></li>
    </ol>
</nav>

<article class="shell max-w-3xl pb-16">
    <header class="mt-6">
        <h1 class="text-[28px] md:text-[38px] font-extrabold leading-tight tracking-tight text-ink"><?= e($page['title']) ?></h1>
        <?php if (!empty($page['intro'])): ?>
        <p class="mt-3 text-[17px] leading-relaxed text-ink-muted"><?= e($page['intro']) ?></p>
        <?php endif; ?>
    </header>

    <div class="prose-body mt-7 text-[16px]">
        <?= $page['body'] /* sanitized on save */ ?>
    </div>
</article>
