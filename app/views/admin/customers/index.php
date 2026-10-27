<?php
/** @var array $rows @var string $q @var int $page @var int $pages @var int $total */
?>
<div class = 'flex flex-wrap items-end justify-between gap-3'>
<div><p class = 'text-xs font-bold uppercase tracking-[0.16em] text-electric-600'>People</p><h1 class = 'mt-1 text-2xl font-extrabold'>Customers</h1><p class = 'mt-1 text-sm text-ink-muted'>Account customers and linked order value. Guest checkouts remain available.</p></div>
<span class = 'badge badge-soft px-3 py-1.5'><?= number_format( $total ) ?> accounts</span>
</div>
<section class = 'card mt-5 overflow-hidden'>
<form method = 'get' action = '/admin/customers' class = 'flex gap-2 border-b border-line p-4'>
<label for = 'customer-q' class = 'sr-only'>Search customers</label><input id = 'customer-q' class = 'input max-w-md' type = 'search' name = 'q' value = "<?= e($q) ?>" placeholder = 'Search name, email or phone'>
<button class = 'btn btn-primary btn-sm' type = 'submit'>Search</button>
<?php if ( $q !== '' ): ?><a href = '/admin/customers' class = 'btn btn-ghost btn-sm'>Clear</a><?php endif;
?>
</form>
<?php if ( $rows === [] ): ?>
<div class = 'p-12 text-center'><p class = 'font-bold'>No customers found</p><p class = 'mt-1 text-sm text-ink-muted'>Try another search or wait for an account to register.</p></div>
<?php else: ?>
<div class = 'overflow-x-auto'><table class = 'data-table'><caption class = 'sr-only'>Customer accounts</caption><thead><tr><th scope = 'col'>Customer</th><th scope = 'col'>Phone</th><th scope = 'col'>Orders</th><th scope = 'col'>Lifetime value</th><th scope = 'col'>Joined</th><th scope = 'col'><span class = 'sr-only'>Actions</span></th></tr></thead><tbody>
<?php foreach ( $rows as $row ): ?><tr>
<td data-label = 'Customer'><a class = 'font-bold text-electric-600 hover:underline' href = "/admin/customers/<?= (int) $row['id'] ?>"><?= e( $row[ 'name' ] ) ?></a><p class = 'mt-0.5 text-xs text-ink-muted'><?= e( $row[ 'email' ] ) ?></p></td>
<td data-label = 'Phone' class = 'text-ink-muted'><?= e( $row[ 'phone' ] ?? '—' ) ?></td>
<td data-label = 'Orders' class = 'font-semibold'><?= ( int ) $row[ 'order_count' ] ?></td>
<td data-label = 'Lifetime value' class = 'font-bold'><?= price( $row[ 'lifetime_value' ] ) ?></td>
<td data-label = 'Joined' class = 'text-ink-muted'><?= e( date( 'd M Y', strtotime( $row[ 'created_at' ] ) ) ) ?></td>
<td data-label = '' class = 'text-right'><a class = 'btn btn-ghost btn-sm' href = "/admin/customers/<?= (int) $row['id'] ?>">View profile</a></td>
</tr><?php endforeach;
?>
</tbody></table></div>
<?php if ( $pages > 1 ): ?><nav class = 'flex justify-center gap-2 border-t border-line p-4' aria-label = 'Pagination'><?php for ( $i = 1; $i <= $pages; $i++ ): ?><a class = "pagination-link <?= $i === $page ? 'pagination-link-active' : '' ?>" href = "/admin/customers?page=<?= $i ?><?= $q !== '' ? '&q=' . e(urlencode($q)) : '' ?>" <?= $i === $page ? 'aria-current="page"' : '' ?>><?= $i ?></a><?php endfor;
?></nav><?php endif;
?>
<?php endif;
?>
</section>
