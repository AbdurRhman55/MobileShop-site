<?php
/**
 * Blog index.
 *
 * @var array $listing @var array $breadcrumbs
 */
$pages  = (int) $listing['pages'];
$page   = (int) $listing['page'];
?>
<nav aria-label="Breadcrumb" class="shell pt-5 text-sm text-ink-muted">
    <ol class="flex flex-wrap items-center gap-1.5">
        <li><a href="/" class="hover:text-electric-600">Home</a></li>
        <li aria-hidden="true" class="text-line">/</li>
        <li aria-current="page" class="text-ink font-medium">Blog</li>
    </ol>
</nav>

<div class="shell pb-16">
    <header class="mt-6 max-w-3xl">
        <p class="text-[12px] font-extrabold uppercase tracking-[0.18em] text-electric-600">Guides &amp; Tips</p>
        <h1 class="mt-2 text-[28px] md:text-4xl font-extrabold tracking-tight text-ink">Mobile Phone Guides &amp; Tips</h1>
        <p class="mt-3 text-[15px] leading-relaxed text-ink-muted">Practical advice on buying, maintaining and repairing your phone — written by the team that services them every day.</p>
    </header>

    <?php if ($listing['items'] === []): ?>
    <div class="mt-10 rounded-2xl border border-dashed border-line bg-white p-12 text-center">
        <span class="mx-auto grid h-14 w-14 place-items-center rounded-2xl bg-surface-muted text-ink-muted">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="h-7 w-7"><path d="M4 5h16v14H4zM8 9h8M8 13h6"/></svg>
        </span>
        <h2 class="mt-4 text-lg font-extrabold text-ink">No articles published yet</h2>
        <p class="mx-auto mt-2 max-w-md text-sm text-ink-muted">We publish buying guides and repair advice here. Check back soon.</p>
        <a href="/category/smartphones" class="btn btn-tech btn-md mt-6">Browse smartphones</a>
    </div>
    <?php else: ?>
    <ul class="mt-9 grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
        <?php foreach ($listing['items'] as $post):
            $cover = (string) ($post['cover_image'] ?? '');
            $coverUrl = $cover !== '' ? image_url($cover) : '';
            $ts = $post['published_at'] ? strtotime((string) $post['published_at']) : null;
        ?>
        <li class="card group flex h-full flex-col overflow-hidden transition-shadow hover:shadow-card-lg">
            <?php if ($coverUrl !== ''): ?>
            <a href="/blog/<?= e($post['slug']) ?>" class="block overflow-hidden bg-surface-sunken">
                <img src="<?= e($coverUrl) ?>" alt="<?= e($post['title']) ?>" width="600" height="338"
                     loading="lazy" decoding="async"
                     class="aspect-[16/9] w-full object-cover transition-transform duration-300 group-hover:scale-105">
            </a>
            <?php else: ?>
            <a href="/blog/<?= e($post['slug']) ?>" class="relative block aspect-[16/9] w-full overflow-hidden bg-gradient-to-br from-electric-500 to-violet-600">
                <span class="absolute inset-0 grid place-items-center text-[13px] font-extrabold uppercase tracking-widest text-white/80">Guide</span>
            </a>
            <?php endif; ?>

            <div class="flex flex-1 flex-col p-5">
                <h2 class="text-[16px] font-extrabold leading-snug text-ink">
                    <a href="/blog/<?= e($post['slug']) ?>" class="hover:text-electric-600"><?= e($post['title']) ?></a>
                </h2>

                <?php if (!empty($post['excerpt'])): ?>
                <p class="mt-2 flex-1 text-[13.5px] leading-relaxed text-ink-muted"><?= e($post['excerpt']) ?></p>
                <?php endif; ?>

                <div class="mt-4 flex items-center gap-2 border-t border-line pt-3.5 text-[12px] text-ink-muted">
                    <span class="font-semibold text-ink"><?= e($post['author_name'] ?? 'Lala Mobiles Team') ?></span>
                    <?php if ($ts !== false && $ts !== null): ?>
                    <span aria-hidden="true">·</span>
                    <time datetime="<?= e(date('c', $ts)) ?>"><?= e(date('d M Y', $ts)) ?></time>
                    <?php endif; ?>
                </div>
            </div>
        </li>
        <?php endforeach; ?>
    </ul>

    <?php if ($pages > 1): ?>
    <nav aria-label="Pagination" class="mt-10 flex flex-wrap items-center justify-center gap-2">
        <?php
        $href = fn(int $i): string => $i === 1 ? '/blog' : '/blog?page=' . $i;
        $start = max(1, $page - 2);
        $end   = min($pages, $page + 2);
        ?>
        <?php if ($page > 1): ?><a href="<?= e($href($page - 1)) ?>" class="btn btn-ghost btn-sm" rel="prev">← Prev</a><?php endif; ?>

        <?php if ($start > 1): ?>
        <a href="<?= e($href(1)) ?>" class="pagination-link">1</a>
        <?php if ($start > 2): ?><span class="px-1 text-ink-muted" aria-hidden="true">…</span><?php endif; ?>
        <?php endif; ?>

        <?php for ($i = $start; $i <= $end; $i++): ?>
        <a href="<?= e($href($i)) ?>"
           <?= $i === $page ? 'aria-current="page"' : '' ?>
           class="pagination-link <?= $i === $page ? 'pagination-link-active' : '' ?>"><?= $i ?></a>
        <?php endfor; ?>

        <?php if ($end < $pages): ?>
        <?php if ($end < $pages - 1): ?><span class="px-1 text-ink-muted" aria-hidden="true">…</span><?php endif; ?>
        <a href="<?= e($href($pages)) ?>" class="pagination-link"><?= $pages ?></a>
        <?php endif; ?>

        <?php if ($page < $pages): ?><a href="<?= e($href($page + 1)) ?>" class="btn btn-ghost btn-sm" rel="next">Next →</a><?php endif; ?>
    </nav>
    <?php endif; ?>
    <?php endif; ?>
</div>
