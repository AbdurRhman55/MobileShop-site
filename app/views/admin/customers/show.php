<?php
/** @var array $customer @var array $orders @var array $stats @var array $addresses */
?>
<a href = '/admin/customers' class = 'btn btn-ghost btn-sm'>← Customers</a>
<header class = 'mt-5 flex flex-wrap items-start justify-between gap-4'><div><p class = 'text-xs font-bold uppercase tracking-[0.16em] text-electric-600'>Customer profile</p><h1 class = 'mt-1 text-2xl font-extrabold'><?= e( $customer[ 'name' ] ) ?></h1><p class = 'mt-1 text-sm text-ink-muted'>Joined <?= e( date( 'd M Y', strtotime( $customer[ 'created_at' ] ) ) ) ?></p></div><a class = 'btn btn-ghost btn-sm' href = "mailto:<?= e($customer['email']) ?>">Email customer</a></header>
<div class = 'mt-5 grid gap-4 sm:grid-cols-3'>
<section class = 'card p-5'><p class = 'text-xs font-bold uppercase tracking-wide text-ink-muted'>Lifetime value</p><p class = 'mt-2 text-2xl font-extrabold'><?= price( $stats[ 'lifetime_value' ] ) ?></p></section>
<section class = 'card p-5'><p class = 'text-xs font-bold uppercase tracking-wide text-ink-muted'>Orders</p><p class = 'mt-2 text-2xl font-extrabold'><?= ( int ) $stats[ 'order_count' ] ?></p></section>
<section class = 'card p-5'><p class = 'text-xs font-bold uppercase tracking-wide text-ink-muted'>Contact</p><p class = 'mt-2 break-all font-semibold'><?= e( $customer[ 'email' ] ) ?></p><p class = 'mt-1 text-sm text-ink-muted'><?= e( $customer[ 'phone' ] ?? 'No phone on file' ) ?></p></section>
</div>
<div class = 'mt-5 grid gap-5 xl:grid-cols-3'>
<section class = 'card overflow-hidden xl:col-span-2' aria-labelledby = 'customer-orders-heading'><h2 id = 'customer-orders-heading' class = 'border-b border-line px-5 py-4 font-bold'>Order history</h2>
<?php if ( $orders === [] ): ?><div class = 'p-8 text-center text-sm text-ink-muted'>No linked orders yet.</div><?php else: ?>
<div class = 'overflow-x-auto'><table class = 'data-table'><caption class = 'sr-only'>Orders for <?= e( $customer[ 'name' ] ) ?></caption><thead><tr><th scope = 'col'>Order</th><th scope = 'col'>Date</th><th scope = 'col'>Status</th><th scope = 'col'>Total</th></tr></thead><tbody><?php foreach ( $orders as $order ): ?><tr><td data-label = 'Order'><a class = 'font-bold text-electric-600 hover:underline' href = "/admin/orders/<?= (int) $order['id'] ?>"><?= e( $order[ 'reference' ] ) ?></a></td><td data-label = 'Date' class = 'text-ink-muted'><?= e( date( 'd M Y', strtotime( $order[ 'created_at' ] ) ) ) ?></td><td data-label = 'Status'><span class = 'badge badge-soft px-2.5 py-1'><?= e( ucfirst( $order[ 'status' ] ) ) ?></span></td><td data-label = 'Total' class = 'font-bold'><?= price( $order[ 'total' ] ) ?></td></tr><?php endforeach;
?></tbody></table></div><?php endif;
?>
</section>
<section class = 'card p-5'><h2 class = 'font-bold'>Saved addresses</h2><?php if ( $addresses === [] ): ?><p class = 'mt-3 text-sm text-ink-muted'>No saved addresses. Checkout addresses are stored with each order.</p><?php else: ?><ul class = 'mt-4 space-y-3'><?php foreach ( $addresses as $address ): ?><li class = 'rounded-lg border border-line p-3'><p class = 'font-semibold'><?= e( $address[ 'label' ] ) ?><?= !empty( $address[ 'is_default' ] ) ? ' · Default' : '' ?></p><p class = 'mt-1 text-sm text-ink-muted'><?= e( $address[ 'recipient' ] ) ?><br><?= e( $address[ 'address' ] ) ?><br><?= e( $address[ 'city' ] ) ?></p></li><?php endforeach;
?></ul><?php endif;
?></section>
</div>
