<?php
/** @var array $products */
?>
<div class = 'shell py-8 sm:py-12'>
<nav aria-label = 'Breadcrumb' class = 'text-sm text-ink-muted'><a href = '/' class = 'hover:text-electric-600'>Home</a><span class = 'mx-2'>/</span><span aria-current = 'page' class = 'font-semibold text-ink'>Compare products</span></nav>
<header class = 'mt-5 flex flex-wrap items-end justify-between gap-3'><div><p class = 'text-xs font-bold uppercase tracking-[0.16em] text-electric-600'>Make a confident choice</p><h1 class = 'mt-1 text-3xl font-extrabold'>Compare products</h1><p class = 'mt-2 text-sm text-ink-muted'>Select up to four products from any product card.</p></div><a href = '/category/audio' class = 'btn btn-ghost btn-sm'>Continue shopping</a></header>
<?php if ( $products === [] ): ?>
<section class = 'mt-7 rounded-2xl border border-dashed border-line bg-white px-6 py-14 text-center'><h2 class = 'text-lg font-bold'>Your comparison is empty</h2><p class = 'mt-2 text-sm text-ink-muted'>Use the Compare button on product cards to add items here.</p><a href = '/category/accessories' class = 'btn btn-primary btn-md mt-5'>Browse products</a></section>
<?php else: ?>
<div class = 'mt-7 overflow-x-auto rounded-2xl border border-line bg-white'><table class = 'min-w-[700px] w-full text-left'><caption class = 'sr-only'>Side-by-side product comparison</caption><thead><tr><th class = 'w-40 bg-surface-muted p-4 text-xs font-bold uppercase tracking-wide text-ink-muted'>Product</th><?php foreach ( $products as $product ): ?><th class = 'min-w-52 border-l border-line p-4 align-top'><a href = "/product/<?= e($product['slug']) ?>"><img src = "<?= e(image_url($product['main_image'])) ?>" alt = "<?= e($product['image_alt'] ?: $product['name']) ?>" width = '280' height = '280' loading = 'lazy' class = 'aspect-square w-full rounded-xl bg-surface-muted object-cover'><span class = 'mt-3 block text-sm font-bold text-ink hover:text-electric-600'><?= e( $product[ 'name' ] ) ?></span></a><button type = 'button' data-compare-toggle data-product-id = "<?= (int) $product['id'] ?>" class = 'mt-3 text-xs font-semibold text-ink-muted hover:text-danger-600'>Remove from comparison</button></th><?php endforeach;
?></tr></thead><tbody>
<?php foreach ( [ [ 'Brand', 'brand_name' ], [ 'Category', 'category_name' ], [ 'Price', 'price' ], [ 'Availability', 'stock' ], [ 'Warranty', 'warranty' ], [ 'Condition', 'condition' ] ] as [ $label, $key ] ): ?><tr class = 'border-t border-line'><th scope = 'row' class = 'bg-surface-muted p-4 text-xs font-bold uppercase tracking-wide text-ink-muted'><?= e( $label ) ?></th><?php foreach ( $products as $product ): ?><td class = 'border-l border-line p-4 text-sm'><?php if ( $key === 'price' ): ?><?= price( \App\Models\Product::effectivePrice( $product ) ) ?><?php elseif ( $key === 'stock' ): ?><?= ( int ) $product[ 'stock' ] > 0 ? 'In stock' : 'Out of stock' ?><?php else: ?><?= e( ( string ) ( $product[ $key ] ?? '—' ) ) ?><?php endif;
?></td><?php endforeach;
?></tr><?php endforeach;
?>
</tbody></table></div>
<?php endif;
?>
</div>
