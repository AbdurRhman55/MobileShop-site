<?php
/**
 * Guides & articles grid.
 *
 * @var array $blog  published blog_posts rows
 */
$blog = array_values($blog ?? []);
if ($blog === []) { return; }

$readTime = static function (?string $body): int {
    $words = str_word_count(strip_tags((string) $body));
    return max(1, (int) ceil($words / 200));
};
?>
<section class="shell mt-14" aria-labelledby="blog-heading">
    <?php $viewAllUrl = '/blog'; ?>
    <?= \App\Core\View::partial('partials/section-head', [
        'key' => 'blog', 'fallback' => 'Guides & Tips',
        'sectionCfg' => $sectionCfg, 'viewAllUrl' => $viewAllUrl,
    ]) ?>

    <ul class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
        <?php foreach ($blog as $post): $cover = (string)($post['cover_image'] ?? ''); $coverUrl = $cover !== '' ? image_url($cover) : ''; ?>
        <li>
            <a href="/blog/<?= e($post['slug']) ?>"
               class="group flex h-full flex-col overflow-hidden rounded-2xl border border-line bg-white transition-all duration-200 hover:-translate-y-0.5 hover:border-electric-300 hover:shadow-lift">
                <span class="relative block aspect-[16/9] w-full overflow-hidden bg-surface-sunken">
                    <?php if ($coverUrl !== ''): ?>
                    <img src="<?= e($coverUrl) ?>" alt="<?= e($post['title']) ?>" width="600" height="338" loading="lazy" decoding="async"
                         class="h-full w-full object-cover transition-transform duration-300 group-hover:scale-105">
                    <?php else: ?>
                    <span class="grid h-full w-full place-items-center bg-gradient-to-br from-electric-50 to-violet-50 text-electric-400">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" class="h-10 w-10"><path d="M4 5a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V5Z"/><path d="M8 7h8M8 11h8M8 15h5"/></svg>
                    </span>
                    <?php endif; ?>
                </span>
                <span class="flex flex-1 flex-col p-5">
                    <span class="flex items-center gap-2 text-[12px] font-semibold text-ink-muted">
                        <?php if (!empty($post['published_at'])): ?>
                        <time datetime="<?= e(date('c', strtotime((string)$post['published_at']))) ?>">
                            <?= e(date('M j, Y', strtotime((string)$post['published_at']))) ?>
                        </time>
                        <span aria-hidden="true">·</span>
                        <?php endif; ?>
                        <span><?= $readTime($post['body'] ?? '') ?> min read</span>
                    </span>
                    <h3 class="mt-2 text-[16px] font-bold leading-snug text-ink line-clamp-2 group-hover:text-electric-700">
                        <?= e($post['title']) ?>
                    </h3>
                    <?php if (!empty($post['excerpt'])): ?>
                    <p class="mt-2 text-[14px] leading-relaxed text-ink-muted line-clamp-3"><?= e($post['excerpt']) ?></p>
                    <?php endif; ?>
                    <span class="mt-auto inline-flex items-center gap-1 pt-3.5 text-[13px] font-bold text-electric-600">
                        Read article
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6" class="h-3.5 w-3.5 transition-transform group-hover:translate-x-0.5"><path d="M5 12h14m-6-6 6 6-6 6"/></svg>
                    </span>
                </span>
            </a>
        </li>
        <?php endforeach; ?>
    </ul>
</section>
