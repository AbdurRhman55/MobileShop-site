<?php
/**
 * Admin — products list.
 *
 * @var array $rows @var string $q @var int $page @var int $pages @var int $total
 *
 * Search, pagination and the create link all hit the real admin routes. The
 * table becomes stacked cards under lg via .data-table.
 */
?>
<div class="flex flex-wrap items-center justify-between gap-3">
    <div>
        <p class="text-[12px] font-extrabold uppercase tracking-[0.18em] text-electric-600">Catalogue</p>
        <h2 class="mt-1 text-[24px] font-extrabold tracking-tight text-ink">Products</h2>
    </div>
    <div class="flex items-center gap-3">
        <span class="badge badge-soft px-3 py-1.5"><?= number_format((int) $total) ?> product<?= (int) $total === 1 ? '' : 's' ?></span>
        <a href="/admin/products/create" class="btn btn-primary btn-sm">Add product</a>
    </div>
</div>

<div class="card mt-5 overflow-hidden">
    <!-- Search -->
    <form method="get" action="/admin/products" class="flex flex-wrap items-center gap-2 border-b border-line p-4">
        <label for="q" class="sr-only">Search products</label>
        <div class="relative min-w-0 flex-1 sm:max-w-sm">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"
                 class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-ink-muted">
                <circle cx="11" cy="11" r="7"/><path d="m20 20-3.5-3.5"/>
            </svg>
            <input type="search" id="q" name="q" value="<?= e($q) ?>" placeholder="Search name, slug or SKU…"
                   class="input !py-2 !pl-9">
        </div>
        <button type="submit" class="btn btn-ghost btn-sm">Search</button>
        <?php if ($q !== ''): ?>
        <a href="/admin/products" class="btn btn-ghost btn-sm">Clear</a>
        <?php endif; ?>
    </form>

    <?php if ($rows === []): ?>
    <div class="p-12 text-center">
        <span class="mx-auto grid h-14 w-14 place-items-center rounded-2xl bg-surface-muted text-ink-muted">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" class="h-7 w-7"><path d="M6 2h9l5 5v13a1 1 0 0 1-1 1H6a1 1 0 0 1-1-1V3a1 1 0 0 1 1-1zM14 2v6h6"/></svg>
        </span>
        <p class="mt-4 text-[15px] font-bold text-ink"><?= $q !== '' ? 'No products match “' . e($q) . '”' : 'No products yet' ?></p>
        <div class="mt-5 flex flex-wrap justify-center gap-3">
            <?php if ($q !== ''): ?><a href="/admin/products" class="btn btn-ghost btn-sm">Clear search</a><?php endif; ?>
            <a href="/admin/products/create" class="btn btn-primary btn-sm">Add product</a>
        </div>
    </div>
    <?php else: ?>
    <div class="overflow-x-auto">
        <table class="data-table">
            <caption class="sr-only">All products</caption>
            <thead>
                <tr>
                    <th scope="col">Product</th>
                    <th scope="col">Category / Brand</th>
                    <th scope="col">Price</th>
                    <th scope="col">Stock</th>
                    <th scope="col">SEO</th>
                    <th scope="col">Status</th>
                    <th scope="col"><span class="sr-only">Actions</span></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($rows as $r): ?>
                <tr>
                    <td data-label="Product">
                        <p class="font-bold text-ink"><?= e($r['name']) ?></p>
                        <p class="mt-0.5 font-mono text-[12px] text-ink-muted">/product/<?= e($r['slug']) ?></p>
                    </td>
                    <td data-label="Category / Brand" class="text-ink-muted">
                        <?= e($r['category_name'] ?? '—') ?><br>
                        <span class="text-[12px]"><?= e($r['brand_name'] ?? '—') ?></span>
                    </td>
                    <td data-label="Price" class="whitespace-nowrap font-bold tabular-nums text-ink">
                        <?= price($r['sale_price'] ?? $r['price']) ?>
                    </td>
                    <td data-label="Stock">
                        <?php $stock = (int) $r['stock']; ?>
                        <?php if ($stock <= 0): ?>
                        <span class="badge bg-flame-50 px-2.5 py-1 !text-[11px] text-flame-600">Out of stock</span>
                        <?php elseif ($stock <= 5): ?>
                        <span class="badge bg-gold-100 px-2.5 py-1 !text-[11px] text-navy-900"><?= $stock ?> left</span>
                        <?php else: ?>
                        <span class="tabular-nums text-ink"><?= $stock ?></span>
                        <?php endif; ?>
                    </td>
                    <td data-label="SEO" class="whitespace-nowrap">
                        <?php if (!empty($r['seo_title'])): ?>
                        <span class="badge bg-jade-50 px-2.5 py-1 !text-[11px] text-jade-700">Custom title</span>
                        <?php else: ?>
                        <span class="badge badge-soft px-2.5 py-1 !text-[11px]">Auto title</span>
                        <?php endif; ?>
                        <?php if (empty($r['meta_description'])): ?>
                        <span class="badge bg-gold-100 px-2.5 py-1 !text-[11px] text-navy-900">No desc</span>
                        <?php endif; ?>
                    </td>
                    <td data-label="Status">
                        <?php
                        $badge = match ((string) $r['status']) {
                            'active'       => 'bg-jade-50 px-2.5 py-1 !text-[11px] text-jade-700',
                            'discontinued' => 'bg-gold-100 px-2.5 py-1 !text-[11px] text-navy-900',
                            default        => 'badge-soft px-2.5 py-1 !text-[11px]',
                        };
                        $label = match ((string) $r['status']) {
                            'active'       => 'Active',
                            'discontinued' => 'Discontinued',
                            default        => 'Deleted',
                        };
                        ?>
                        <span class="badge <?= $badge ?>"><?= $label ?></span>
                    </td>
                    <td data-label="" class="whitespace-nowrap text-right">
                        <a href="/admin/products/<?= (int) $r['id'] ?>/edit" class="btn btn-ghost btn-sm">Edit</a>
                        <a href="/product/<?= e($r['slug']) ?>" target="_blank" rel="noopener" class="btn btn-ghost btn-sm">View ↗</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <?php if ($pages > 1): ?>
    <nav aria-label="Pagination" class="flex flex-wrap items-center justify-center gap-2 border-t border-line p-4">
        <?php for ($i = 1; $i <= $pages; $i++): ?>
        <a href="/admin/products?page=<?= $i ?><?= $q !== '' ? '&q=' . e(urlencode($q)) : '' ?>"
           <?= $i === $page ? 'aria-current="page"' : '' ?>
           class="pagination-link <?= $i === $page ? 'pagination-link-active' : '' ?>">
            <?= $i ?>
        </a>
        <?php endfor; ?>
    </nav>
    <?php endif; ?>
    <?php endif; ?>
</div>
