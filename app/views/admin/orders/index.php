<?php
/**
* Admin — orders list.
*
* @var array $rows
*
* The table collapses into stacked cards under lg via .data-table ( labels
* come from data-label ), so it stays readable on a phone.
*/
$statusMeta = [
    'pending'   => [ 'bg' => 'bg-gold-400',    'fg' => 'text-navy-900',  'label' => 'Pending' ],
    'processing'=> [ 'bg' => 'bg-electric-100', 'fg' => 'text-electric-800', 'label' => 'Processing' ],
    'confirmed' => [ 'bg' => 'bg-electric-500', 'fg' => 'text-white',     'label' => 'Confirmed' ],
    'shipped'   => [ 'bg' => 'bg-violet-500',  'fg' => 'text-white',     'label' => 'Shipped' ],
    'delivered' => [ 'bg' => 'bg-jade-500',    'fg' => 'text-white',     'label' => 'Delivered' ],
    'cancelled' => [ 'bg' => 'bg-flame-500',   'fg' => 'text-white',     'label' => 'Cancelled' ],
    'refunded'  => [ 'bg' => 'bg-surface-sunken', 'fg' => 'text-ink-muted', 'label' => 'Refunded' ],
];
?>
<div class = 'flex flex-wrap items-center justify-between gap-3'>
<div>
<p class = 'text-[12px] font-extrabold uppercase tracking-[0.18em] text-electric-600'>Store</p>
<h2 class = 'mt-1 text-[24px] font-extrabold tracking-tight text-ink'>Orders &amp; payments</h2>
<p class = 'mt-1 text-[13px] text-ink-muted'>Payments are only marked received when you record them here — nothing auto-confirms.</p>
</div>
<div class = 'flex flex-wrap items-center gap-2'>
<a href = '/admin/orders/payment-settings' class = 'btn btn-ghost btn-sm'>Payment &amp; invoice settings</a>
<span class = 'badge badge-soft px-3 py-1.5'><?= number_format( count( $rows ) ) ?> order<?= count( $rows ) === 1 ? '' : 's' ?></span>
</div>
</div>

<?php if ( isset( $stats ) ): ?>
<div class = 'mt-5 grid gap-3 sm:grid-cols-2 xl:grid-cols-4'>
<div class = 'card flex items-center justify-between p-4'>
<div><p class = 'text-[11px] font-bold uppercase tracking-wide text-ink-muted'>Awaiting confirmation</p><p class = 'mt-1 text-[22px] font-extrabold tabular-nums text-ink'><?= (int)$stats['awaiting_confirmation'] ?></p></div>
<div class = 'grid h-10 w-10 place-items-center rounded-xl bg-gold-100 text-amber-700'><svg viewBox = '0 0 24 24' fill = 'none' stroke = 'currentColor' stroke-width = '1.8' class = 'h-5 w-5'><circle cx = '12' cy = '12' r = '9'/><path d = 'M12 7v6M12 17h.01'/></svg></div>
</div>
<div class = 'card flex items-center justify-between p-4'>
<div><p class = 'text-[11px] font-bold uppercase tracking-wide text-ink-muted'>Payment pending</p><p class = 'mt-1 text-[22px] font-extrabold tabular-nums text-ink'><?= (int)$stats['payment_pending'] ?></p><p class = 'text-[11.5px] text-ink-muted'><?= price($stats['payment_pending_value']) ?> outstanding</p></div>
<div class = 'grid h-10 w-10 place-items-center rounded-xl bg-orange-100 text-orange-700'><svg viewBox = '0 0 24 24' fill = 'none' stroke = 'currentColor' stroke-width = '1.8' class = 'h-5 w-5'><rect x = '2' y = '5' width = '20' height = '14' rx = '2'/><path d = 'M2 10h20'/></svg></div>
</div>
<div class = 'card flex items-center justify-between p-4'>
<div><p class = 'text-[11px] font-bold uppercase tracking-wide text-ink-muted'>Received (all time)</p><p class = 'mt-1 text-[22px] font-extrabold tabular-nums text-ink'><?= price($stats['received_value']) ?></p></div>
<div class = 'grid h-10 w-10 place-items-center rounded-xl bg-jade-100 text-jade-700'><svg viewBox = '0 0 24 24' fill = 'none' stroke = 'currentColor' stroke-width = '1.8' class = 'h-5 w-5'><path d = 'M20 6L9 17l-5-5'/></svg></div>
</div>
<div class = 'card flex items-center justify-between p-4'>
<div><p class = 'text-[11px] font-bold uppercase tracking-wide text-ink-muted'>Received today</p><p class = 'mt-1 text-[22px] font-extrabold tabular-nums text-ink'><?= price($stats['received_today']) ?></p></div>
<div class = 'grid h-10 w-10 place-items-center rounded-xl bg-electric-100 text-electric-700'><svg viewBox = '0 0 24 24' fill = 'none' stroke = 'currentColor' stroke-width = '1.8' class = 'h-5 w-5'><circle cx = '12' cy = '12' r = '3'/><path d = 'M12 1v4M12 19v4M4.22 4.22l2.83 2.83M16.95 16.95l2.83 2.83M1 12h4M19 12h4M4.22 19.78l2.83-2.83M16.95 7.05l2.83-2.83'/></svg></div>
</div>
</div>
<?php endif; ?>

<div class = 'card mt-5 overflow-hidden'>
<?php $orderStatuses = [ '' => 'All', 'pending' => 'Pending', 'processing' => 'Processing', 'confirmed' => 'Confirmed', 'shipped' => 'Shipped', 'delivered' => 'Delivered', 'cancelled' => 'Cancelled', 'refunded' => 'Refunded' ];
?>
<div class = 'flex flex-wrap items-center gap-2 border-b border-line px-4 py-2.5 text-[11.5px]'>
<span class = 'font-bold uppercase tracking-wide text-ink-muted'>Fulfilment</span>
<?php foreach ( $orderStatuses as $key => $label ): ?>
<a href = "/admin/orders<?= $key !== '' ? '?status=' . e($key) . ($payment !== '' ? '&payment=' . e($payment) : '') : ($payment !== '' ? '?payment=' . e($payment) : '') ?>" class = "rounded-full px-2.5 py-1 font-bold <?= $status === $key ? 'bg-navy-900 text-white' : 'text-ink-muted hover:bg-surface-muted hover:text-ink' ?>"><?= e( $label ) ?> <span class = 'opacity-70'><?= ( int ) ( $counts[ $key ] ?? ( $key === '' ? array_sum( $counts ) : 0 ) ) ?></span></a>
<?php endforeach; ?>
<span class = 'ml-3 hidden font-bold uppercase tracking-wide text-ink-muted sm:inline'>Payment</span>
<?php foreach ([ '' => 'All', 'pending' => 'Pending', 'paid' => 'Paid', 'refunded' => 'Refunded', 'failed' => 'Failed' ] as $pkey => $plabel): ?>
<a href = "/admin/orders<?= $pkey !== '' ? '?payment=' . e($pkey) . ($status !== '' ? '&status=' . e($status) : '') : ($status !== '' ? '?status=' . e($status) : '') ?>" class = "rounded-full px-2.5 py-1 font-bold <?= $payment === $pkey ? 'bg-electric-600 text-white' : 'text-ink-muted hover:bg-surface-muted hover:text-ink' ?>"><?= e( $plabel ) ?> <span class = 'opacity-70'><?= ( int ) ( $paymentCounts[ $pkey ] ?? ( $pkey === '' ? array_sum( $paymentCounts ) : 0 ) ) ?></span></a>
<?php endforeach; ?>
</div>
<form method = 'get' action = '/admin/orders' class = 'flex flex-wrap gap-2 border-b border-line p-4'>
<?php if ( $status !== '' ): ?><input type = 'hidden' name = 'status' value = "<?= e($status) ?>"><?php endif; ?>
<?php if ( $payment !== '' ): ?><input type = 'hidden' name = 'payment' value = "<?= e($payment) ?>"><?php endif; ?>
<label for = 'order-q' class = 'sr-only'>Search orders</label><input id = 'order-q' class = 'input max-w-lg' type = 'search' name = 'q' value = "<?= e($q) ?>" placeholder = 'Reference, customer, email, phone or payment ref'>
<button type = 'submit' class = 'btn btn-primary btn-sm'>Search</button>
<?php if ($q !== '' || $status !== '' || $payment !== ''): ?><a href = '/admin/orders' class = 'btn btn-ghost btn-sm'>Clear</a><?php endif; ?>
</form>
<?php if ( $rows === [] ): ?>
<div class = 'p-12 text-center'>
<span class = 'mx-auto grid h-14 w-14 place-items-center rounded-2xl bg-surface-muted text-ink-muted'>
<svg viewBox = '0 0 24 24' fill = 'none' stroke = 'currentColor' stroke-width = '1.7' class = 'h-7 w-7'><path d = 'M6 2h12l2 5-8 3.5L6 7zM6 7v13a1 1 0 0 0 1 1h10a1 1 0 0 0 1-1V7'/></svg>
</span>
<p class = 'mt-4 text-[15px] font-bold text-ink'>No orders yet</p>
<p class = 'mx-auto mt-1.5 max-w-sm text-[13.5px] text-ink-muted'>New orders appear here the moment a customer checks out.</p>
</div>
<?php else: ?>
<div class = 'overflow-x-auto'>
<table class = 'data-table'>
<caption class = 'sr-only'>All customer orders</caption>
<thead>
<tr>
<th scope = 'col'>Reference</th>
<th scope = 'col'>Customer</th>
<th scope = 'col'>City</th>
<th scope = 'col'>Total</th>
<th scope = 'col'>Fulfilment</th>
<th scope = 'col'>Payment</th>
<th scope = 'col'>Placed</th>
<th scope = 'col'><span class = 'sr-only'>Actions</span></th>
</tr>
</thead>
<tbody>
<?php foreach ( $rows as $r ):
$status = ( string ) $r[ 'status' ];
$meta   = $statusMeta[ $status ] ?? [ 'bg' => 'bg-electric-500', 'fg' => 'text-white', 'label' => ucfirst( $status ) ];
$pstatus = ( string ) ($r['payment_status'] ?? 'pending');
$pmeta   = \App\Models\Payment::statusMeta($pstatus);
?>
<tr>
<td data-label = 'Reference'>
<a href = "/admin/orders/<?= (int) $r['id'] ?>" class = 'font-bold text-electric-600 hover:text-electric-700'><?= e( $r[ 'reference' ] ) ?></a>
<?php if (!empty($r['payment_reported_at'])): ?><span class = 'ml-1.5 inline-flex items-center rounded-full bg-gold-100 px-1.5 py-0.5 text-[10px] font-bold text-amber-800'>Customer reported</span><?php endif; ?>
</td>
<td data-label = 'Customer' class = 'font-semibold text-ink'><?php if ( !empty( $r[ 'customer_id' ] ) ): ?><a href = "/admin/customers/<?= (int) $r['customer_id'] ?>" class = 'text-electric-700 hover:underline'><?= e( $r[ 'customer_name' ] ) ?></a><?php else: ?><?= e( $r[ 'customer_name' ] ) ?><?php endif; ?>
<?php if (!empty($r['email'])): ?><br><span class = 'text-[11.5px] text-ink-muted'><?= e($r['email']) ?></span><?php endif; ?>
</td>
<td data-label = 'City' class = 'text-ink-muted'><?= e( $r[ 'city' ] ) ?></td>
<td data-label = 'Total' class = 'whitespace-nowrap font-bold tabular-nums text-ink'><?= price( $r[ 'total' ] ) ?></td>
<td data-label = 'Fulfilment'>
<span class = "badge <?= e($meta['bg']) ?> <?= e($meta['fg']) ?> px-2.5 py-1 !text-[11px]"><?= e( $meta[ 'label' ] ) ?></span>
</td>
<td data-label = 'Payment'>
<span class = "badge <?= e($pmeta['bg']) ?> <?= e($pmeta['fg']) ?> px-2.5 py-1 !text-[11px]"><?= e($pmeta['label']) ?></span>
<div class = 'mt-1 text-[11px] text-ink-muted'><?= e(\App\Models\Payment::label((string)$r['payment_method'])) ?></div>
</td>
<td data-label = 'Placed' class = 'whitespace-nowrap text-ink-muted'><?= e( date( 'd M Y, H:i', strtotime( $r[ 'created_at' ] ) ) ) ?></td>
<td data-label = '' class = 'text-right'>
<a href = "/admin/orders/<?= (int) $r['id'] ?>" class = 'btn btn-ghost btn-sm'>Manage</a>
</td>
</tr>
<?php endforeach;
?>
</tbody>
</table>
</div>
<?php endif;
?>
</div>
