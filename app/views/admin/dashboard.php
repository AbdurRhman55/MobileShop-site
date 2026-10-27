<?php
/**
* Executive Admin Dashboard — High-Performance Commerce & Analytics Suite
*
* @var array $stats
* @var array $recentOrders
* @var array $auditSummary
* @var array $chart
* @var array $statusMix
* @var array $seoSettings
*/

$labels  = $chart[ 'labels' ]  ?? [];
$orders  = $chart[ 'orders' ]  ?? [];
$revenue = $chart[ 'revenue' ] ?? [];
$n       = count( $labels );

$totalRevenue14 = ( float )( $stats[ 'revenue_14d' ] ?? 0 );
$totalOrders14  = ( int )( $stats[ 'orders_14d' ] ?? 0 );
$avgOrderValue  = $totalOrders14 > 0 ? ( $totalRevenue14 / $totalOrders14 ) : 0;

$maxOrders  = $n > 0 ? max( 1, max( $orders ) ) : 1;
$maxRevenue = $n > 0 ? max( 1.0, max( $revenue ) ) : 1.0;

// Status styling map with gradients and glow rings
$statusConfig = [
    'pending'   => [ 'bg' => 'bg-amber-500',   'badge' => 'bg-amber-50 text-amber-700 border-amber-200', 'label' => 'Pending Review' ],
    'processing'=> [ 'bg' => 'bg-indigo-500',  'badge' => 'bg-indigo-50 text-indigo-700 border-indigo-200', 'label' => 'Processing' ],
    'confirmed' => [ 'bg' => 'bg-blue-500',    'badge' => 'bg-blue-50 text-blue-700 border-blue-200', 'label' => 'Confirmed' ],
    'shipped'   => [ 'bg' => 'bg-purple-500', 'badge' => 'bg-purple-50 text-purple-700 border-purple-200', 'label' => 'Shipped / In Transit' ],
    'delivered' => [ 'bg' => 'bg-emerald-500', 'badge' => 'bg-emerald-50 text-emerald-700 border-emerald-200', 'label' => 'Delivered' ],
    'cancelled' => [ 'bg' => 'bg-rose-500',    'badge' => 'bg-rose-50 text-rose-700 border-rose-200', 'label' => 'Cancelled' ],
    'refunded'  => [ 'bg' => 'bg-slate-400',   'badge' => 'bg-slate-100 text-slate-700 border-slate-200', 'label' => 'Refunded' ],
];

$statusTotal = array_sum( $statusMix );

// Calculate SEO score
$totalIssues = 0;
foreach ( $auditSummary as $r ) {
    $totalIssues += ( int ) $r[ 'count' ];
}
$seoScore = max( 60, 100 - ( $totalIssues * 5 ) );
?>

<?php if ( !empty( $stats[ 'demo_orders' ] ) ): ?>
<div class = 'mb-5 flex flex-wrap items-center justify-between gap-2 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-950' role = 'note'>
<span><strong>Demo data is enabled.</strong> <?= ( int ) $stats[ 'demo_orders' ] ?> demo orders are included in these charts;
references are prefixed DEMO.</span>
<a href = '/admin/orders?q=DEMO-2026' class = 'font-bold underline underline-offset-2'>Filter demo orders</a>
</div>
<?php endif;
?>

<!-- ===  ===  ===  ===  ===  ===  ===  ===  ===  ===  === TOP BANNER / WELCOME STRIP ===  ===  ===  ===  ===  ===  ===  ===  ===  ===  === -->
<div class = 'mb-8 overflow-hidden rounded-3xl bg-gradient-to-r from-navy-950 via-navy-900 to-indigo-950 p-6 md:p-8 text-white shadow-xl relative border border-white/10'>
<div class = 'absolute -right-16 -top-16 h-64 w-64 rounded-full bg-electric-500/10 blur-3xl pointer-events-none'></div>
<div class = 'absolute right-40 -bottom-16 h-48 w-48 rounded-full bg-violet-500/10 blur-2xl pointer-events-none'></div>

<div class = 'relative z-10 flex flex-col md:flex-row items-start md:items-center justify-between gap-6'>
<div>
<div class = 'inline-flex items-center gap-2 rounded-full bg-white/10 backdrop-blur-md px-3.5 py-1 text-xs font-semibold text-gold-300 ring-1 ring-white/15 mb-3'>
<span class = 'h-2 w-2 rounded-full bg-emerald-400 animate-pulse'></span>
Store Operational &bull;
Live Telemetry
</div>
<h1 class = 'text-2xl md:text-3xl font-black tracking-tight text-white'>Store Analytics &amp;
Control Hub</h1>
<p class = 'mt-1 text-sm md:text-base text-navy-200 font-normal'>Real-time inventory intelligence, order lifecycle tracking &amp;
SEO diagnostics.</p>
</div>

<div class = 'flex flex-wrap items-center gap-3'>
<a href = '/admin/products/create' class = 'inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-gradient-to-r from-electric-500 to-violet-600 text-white font-bold text-xs uppercase tracking-wider hover:shadow-lg hover:shadow-electric-500/25 transition-all duration-200 hover:-translate-y-0.5'>
<svg viewBox = '0 0 24 24' fill = 'none' stroke = 'currentColor' stroke-width = '2.5' class = 'h-4 w-4'><path d = 'M12 5v14M5 12h14'/></svg>
Add Product
</a>
<a href = '/admin/orders' class = 'inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-white/10 backdrop-blur-md text-white hover:bg-white/20 border border-white/15 font-bold text-xs uppercase tracking-wider transition-all duration-200'>
<svg viewBox = '0 0 24 24' fill = 'none' stroke = 'currentColor' stroke-width = '2' class = 'h-4 w-4'><path d = 'M6 2h12l2 5-8 3.5L6 7zM6 7v13a1 1 0 0 0 1 1h10a1 1 0 0 0 1-1V7'/></svg>
View Orders ( <?= ( int )$stats[ 'orders' ] ?> )
</a>
<a href = '/admin/customers' class = 'inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-white/10 text-white hover:bg-white/20 border border-white/15 font-bold text-xs uppercase tracking-wider transition-all duration-200'>Customers</a>
<a href = '/' target = '_blank' class = 'inline-flex items-center gap-2 px-3 py-2.5 rounded-xl bg-white/5 hover:bg-white/10 text-navy-200 hover:text-white border border-white/10 text-xs font-semibold transition-all'>
<svg viewBox = '0 0 24 24' fill = 'none' stroke = 'currentColor' stroke-width = '2' class = 'h-4 w-4'><path d = 'M14 4h6v6M20 4l-9 9M18 14v5a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1V7a1 1 0 0 1 1-1h5'/></svg>
</a>
</div>
</div>
</div>

<!-- ===  ===  ===  ===  ===  ===  ===  ===  ===  ===  === PRIMARY METRIC KPI TILES ===  ===  ===  ===  ===  ===  ===  ===  ===  ===  === -->
<section aria-label = 'Store KPIs' class = 'mb-8'>
<div class = 'grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 md:gap-5'>

<!-- Revenue Card -->
<div class = 'relative overflow-hidden rounded-2xl bg-white border border-line p-5 shadow-sm hover:shadow-md transition-all duration-200 group'>
<div class = 'flex items-center justify-between'>
<span class = 'text-xs font-extrabold uppercase tracking-wider text-ink-muted'>14-Day Revenue</span>
<span class = 'flex h-10 w-10 items-center justify-center rounded-xl bg-emerald-50 text-emerald-600 group-hover:scale-110 transition-transform'>
<svg viewBox = '0 0 24 24' fill = 'none' stroke = 'currentColor' stroke-width = '2.2' class = 'h-5 w-5'><path d = 'M12 2v20M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6'/></svg>
</span>
</div>
<div class = 'mt-3'>
<div class = 'text-2xl font-black tracking-tight text-ink'><?= price( $totalRevenue14 ) ?></div>
<div class = 'mt-1 flex items-center gap-2 text-xs font-medium text-emerald-600'>
<span class = 'flex items-center text-emerald-700 font-bold bg-emerald-100/70 px-1.5 py-0.5 rounded'>
<svg viewBox = '0 0 24 24' fill = 'none' stroke = 'currentColor' stroke-width = '3' class = 'h-3 w-3 mr-0.5'><path d = 'm18 15-6-6-6 6'/></svg>
Avg <?= price( $avgOrderValue ) ?>
</span>
<span class = 'text-ink-muted'>per order</span>
</div>
</div>
<div class = 'absolute bottom-0 inset-x-0 h-1 bg-gradient-to-r from-emerald-400 to-teal-500'></div>
</div>

<!-- Orders Card -->
<div class = 'relative overflow-hidden rounded-2xl bg-white border border-line p-5 shadow-sm hover:shadow-md transition-all duration-200 group'>
<div class = 'flex items-center justify-between'>
<span class = 'text-xs font-extrabold uppercase tracking-wider text-ink-muted'>Total Orders</span>
<span class = 'flex h-10 w-10 items-center justify-center rounded-xl bg-blue-50 text-blue-600 group-hover:scale-110 transition-transform'>
<svg viewBox = '0 0 24 24' fill = 'none' stroke = 'currentColor' stroke-width = '2.2' class = 'h-5 w-5'><path d = 'M6 2h12l2 5-8 3.5L6 7zM6 7v13a1 1 0 0 0 1 1h10a1 1 0 0 0 1-1V7'/></svg>
</span>
</div>
<div class = 'mt-3'>
<div class = 'text-2xl font-black tracking-tight text-ink'><?= number_format( ( int )$stats[ 'orders' ] ) ?></div>
<div class = 'mt-1 flex items-center gap-2 text-xs font-medium text-blue-600'>
<span class = 'font-bold text-blue-700 bg-blue-100/70 px-1.5 py-0.5 rounded'><?= $totalOrders14 ?> recent</span>
<span class = 'text-ink-muted'>in last 14 days</span>
</div>
</div>
<div class = 'absolute bottom-0 inset-x-0 h-1 bg-gradient-to-r from-blue-500 to-indigo-600'></div>
</div>

<!-- Catalog Products Card -->
<div class = 'relative overflow-hidden rounded-2xl bg-white border border-line p-5 shadow-sm hover:shadow-md transition-all duration-200 group'>
<div class = 'flex items-center justify-between'>
<span class = 'text-xs font-extrabold uppercase tracking-wider text-ink-muted'>Active Products</span>
<span class = 'flex h-10 w-10 items-center justify-center rounded-xl bg-purple-50 text-purple-600 group-hover:scale-110 transition-transform'>
<svg viewBox = '0 0 24 24' fill = 'none' stroke = 'currentColor' stroke-width = '2.2' class = 'h-5 w-5'><path d = 'M6 2h9l5 5v13a1 1 0 0 1-1 1H6a1 1 0 0 1-1-1V3a1 1 0 0 1 1-1zM14 2v6h6'/></svg>
</span>
</div>
<div class = 'mt-3'>
<div class = 'text-2xl font-black tracking-tight text-ink'><?= number_format( ( int )$stats[ 'products' ] ) ?></div>
<div class = 'mt-1 flex items-center gap-2 text-xs text-ink-muted'>
<span class = 'font-semibold text-ink'><?= ( int )$stats[ 'categories' ] ?> Categories</span>
<span>&bull;
</span>
<span class = 'font-semibold text-ink'><?= ( int )$stats[ 'brands' ] ?> Brands</span>
</div>
</div>
<div class = 'absolute bottom-0 inset-x-0 h-1 bg-gradient-to-r from-purple-500 to-pink-500'></div>
</div>

<!-- Pending Moderation / Health -->
<div class = 'relative overflow-hidden rounded-2xl bg-white border border-line p-5 shadow-sm hover:shadow-md transition-all duration-200 group'>
<div class = 'flex items-center justify-between'>
<span class = 'text-xs font-extrabold uppercase tracking-wider text-ink-muted'>Review Queue</span>
<span class = "flex h-10 w-10 items-center justify-center rounded-xl <?= (int)$stats['reviews_pending'] > 0 ? 'bg-amber-50 text-amber-600' : 'bg-emerald-50 text-emerald-600' ?> group-hover:scale-110 transition-transform">
<svg viewBox = '0 0 24 24' fill = 'none' stroke = 'currentColor' stroke-width = '2.2' class = 'h-5 w-5'><path d = 'M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z'/></svg>
</span>
</div>
<div class = 'mt-3'>
<div class = 'text-2xl font-black tracking-tight text-ink'><?= ( int )$stats[ 'reviews_pending' ] ?></div>
<div class = 'mt-1 flex items-center gap-2 text-xs'>
<?php if ( ( int )$stats[ 'reviews_pending' ] > 0 ): ?>
<a href = '/admin/reviews' class = 'font-bold text-amber-700 bg-amber-100/80 px-2 py-0.5 rounded-md hover:underline'>Moderate Now &rarr;
</a>
<?php else: ?>
<span class = 'font-bold text-emerald-600 flex items-center gap-1'>
<svg viewBox = '0 0 24 24' fill = 'none' stroke = 'currentColor' stroke-width = '3' class = 'h-3.5 w-3.5'><path d = 'M20 6L9 17l-5-5'/></svg>
All Caught Up
</span>
<?php endif;
?>
</div>
</div>
<div class = 'absolute bottom-0 inset-x-0 h-1 bg-gradient-to-r from-amber-400 to-orange-500'></div>
</div>

</div>
</section>

<!-- ===  ===  ===  ===  ===  ===  ===  ===  ===  ===  === CHARTS & BREAKDOWN ROW ===  ===  ===  ===  ===  ===  ===  ===  ===  ===  === -->
<div class = 'grid grid-cols-1 lg:grid-cols-12 gap-6 mb-8'>

<!-- 14-Day Performance Analytics Chart ( 8 cols ) -->
<section class = 'lg:col-span-8 rounded-3xl bg-white border border-line p-6 shadow-sm flex flex-col justify-between'>
<div>
<div class = 'flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-5 border-b border-line'>
<div>
<h2 class = 'text-lg font-black tracking-tight text-ink flex items-center gap-2'>
<span class = 'h-3 w-3 rounded-full bg-electric-500 animate-ping'></span>
14-Day Sales &amp;
Order Trajectory
</h2>
<p class = 'text-xs text-ink-muted mt-0.5'>Continuous telemetry computed from verified database transactions.</p>
</div>
<div class = 'flex items-center gap-2'>
<span class = 'inline-flex items-center gap-1.5 text-xs font-bold text-ink-muted bg-surface-sunken px-3 py-1.5 rounded-lg border border-line'>
<span class = 'h-2 w-2 rounded-full bg-electric-500'></span> Orders
<span class = 'h-2 w-2 rounded-full bg-emerald-500 ml-2'></span> Revenue
</span>
<a href = '/admin/orders' class = 'text-xs font-bold text-electric-600 hover:text-electric-700'>Orders &rarr;
</a>
</div>
</div>

<?php if ( $n === 0 || $totalOrders14 === 0 ): ?>
<div class = 'py-16 text-center'>
<div class = 'mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-surface-sunken text-ink-muted mb-3'>
<svg viewBox = '0 0 24 24' fill = 'none' stroke = 'currentColor' stroke-width = '1.8' class = 'h-7 w-7'><path d = 'M3 13h4l3 7 4-16 3 9h4'/></svg>
</div>
<h3 class = 'text-sm font-bold text-ink'>Awaiting Real Transaction Flow</h3>
<p class = 'text-xs text-ink-muted mt-1 max-w-sm mx-auto'>Analytics render automatically as verified orders enter the pipeline.</p>
</div>
<?php else: ?>
<!-- Interactive SVG Visualizer -->
<div class = 'mt-6 relative'>
<?php
$chartWidth = 720;
$chartHeight = 200;
$step = $chartWidth / max( 1, $n );
?>
<div class = 'w-full overflow-x-auto'>
<svg viewBox = "0 0 <?= $chartWidth ?> <?= $chartHeight + 40 ?>" class = 'w-full h-56 select-none' role = 'img'>
<defs>
<linearGradient id = 'orderGrad' x1 = '0' y1 = '0' x2 = '0' y2 = '1'>
<stop offset = '0%' stop-color = '#2563eb' stop-opacity = '0.9'/>
<stop offset = '100%' stop-color = '#3b82f6' stop-opacity = '0.3'/>
</linearGradient>
<linearGradient id = 'revGrad' x1 = '0' y1 = '0' x2 = '0' y2 = '1'>
<stop offset = '0%' stop-color = '#10b981' stop-opacity = '0.9'/>
<stop offset = '100%' stop-color = '#10b981' stop-opacity = '0.2'/>
</linearGradient>
</defs>

<!-- Subtle Grid Guidelines -->
<line x1 = '0' y1 = '40' x2 = "<?= $chartWidth ?>" y2 = '40' stroke = '#f1f5f9' stroke-width = '1' stroke-dasharray = '4 4' />
<line x1 = '0' y1 = '100' x2 = "<?= $chartWidth ?>" y2 = '100' stroke = '#f1f5f9' stroke-width = '1' stroke-dasharray = '4 4' />
<line x1 = '0' y1 = '160' x2 = "<?= $chartWidth ?>" y2 = '160' stroke = '#f1f5f9' stroke-width = '1' stroke-dasharray = '4 4' />
<line x1 = '0' y1 = "<?= $chartHeight ?>" x2 = "<?= $chartWidth ?>" y2 = "<?= $chartHeight ?>" stroke = '#cbd5e1' stroke-width = '1.5' />

<?php
$barW = max( 8, $step * 0.42 );
for ( $i = 0; $i < $n; $i++ ):
$ord = ( int )$orders[ $i ];
$rev = ( float )$revenue[ $i ];
$x   = ( $i * $step ) + ( $step / 2 );

$barH = $ord > 0 ? max( 6, ( $ord / $maxOrders ) * ( $chartHeight - 35 ) ) : 2;
$y    = $chartHeight - $barH;
?>
<!-- Order bar with glow on hover -->
<g class = 'transition-all duration-200 cursor-pointer group'>
<rect x = "<?= number_format($x - $barW/2, 1) ?>"
y = "<?= number_format($y, 1) ?>"
width = "<?= number_format($barW, 1) ?>"
height = "<?= number_format($barH, 1) ?>"
rx = '4'
fill = 'url(#orderGrad)'

class = 'transition-all duration-200 group-hover:opacity-80'>
<title><?= e( $labels[ $i ] ) ?> &bull;
<?= $ord ?> Orders &bull;
<?= price( $rev ) ?></title>
</rect>

<?php if ( $ord > 0 ): ?>
<circle cx = "<?= number_format($x, 1) ?>" cy = "<?= number_format($y - 5, 1) ?>" r = '3' fill = '#2563eb' />
<?php endif;
?>

<!-- Axis label -->
<text x = "<?= number_format($x, 1) ?>" y = "<?= $chartHeight + 20 ?>"
text-anchor = 'middle' font-size = '10' font-weight = '700' fill = '#64748b'><?= e( $labels[ $i ] ) ?></text>
</g>
<?php endfor;
?>
</svg>
</div>
</div>
<?php endif;
?>
</div>

<div class = 'mt-6 pt-4 border-t border-line grid grid-cols-2 sm:grid-cols-4 gap-4 text-center'>
<div class = 'rounded-xl bg-surface-sunken p-2.5'>
<span class = 'block text-[11px] font-bold uppercase text-ink-muted'>Peak Velocity</span>
<span class = 'text-sm font-extrabold text-ink'><?= $maxOrders ?> orders/day</span>
</div>
<div class = 'rounded-xl bg-surface-sunken p-2.5'>
<span class = 'block text-[11px] font-bold uppercase text-ink-muted'>Cycle Total</span>
<span class = 'text-sm font-extrabold text-ink'><?= $totalOrders14 ?> orders</span>
</div>
<div class = 'rounded-xl bg-surface-sunken p-2.5'>
<span class = 'block text-[11px] font-bold uppercase text-ink-muted'>Gross Volume</span>
<span class = 'text-sm font-extrabold text-emerald-600'><?= price( $totalRevenue14 ) ?></span>
</div>
<div class = 'rounded-xl bg-surface-sunken p-2.5'>
<span class = 'block text-[11px] font-bold uppercase text-ink-muted'>Fulfillment</span>
<span class = 'text-sm font-extrabold text-blue-600'>Active</span>
</div>
</div>
</section>

<!-- Order Lifecycle & Fulfillment Mix ( 4 cols ) -->
<section class = 'lg:col-span-4 rounded-3xl bg-white border border-line p-6 shadow-sm flex flex-col justify-between'>
<div>
<div class = 'flex items-center justify-between pb-4 border-b border-line'>
<div>
<h2 class = 'text-lg font-black tracking-tight text-ink'>Order Pipeline</h2>
<p class = 'text-xs text-ink-muted mt-0.5'>Status breakdown &amp;
processing flow.</p>
</div>
<span class = 'flex h-8 w-8 items-center justify-center rounded-xl bg-surface-sunken text-ink-muted'>
<svg viewBox = '0 0 24 24' fill = 'none' stroke = 'currentColor' stroke-width = '2' class = 'h-4 w-4'><circle cx = '12' cy = '12' r = '10'/><path d = 'M12 6v6l4 2'/></svg>
</span>
</div>

<?php if ( $statusMix === [] ): ?>
<div class = 'py-12 text-center text-xs text-ink-muted'>No orders processed yet.</div>
<?php else: ?>
<!-- Multi-Color Segmented Progress Strip -->
<div class = 'mt-5'>
<div class = 'flex h-3 w-full overflow-hidden rounded-full bg-surface-sunken shadow-inner'>
<?php foreach ( $statusMix as $st => $cnt ):
$pct = $statusTotal > 0 ? ( $cnt / $statusTotal ) * 100 : 0;
$conf = $statusConfig[ $st ] ?? [ 'bg' => 'bg-slate-400' ];
?>
<div class = "<?= $conf['bg'] ?> h-full transition-all duration-500" style = "width: <?= number_format($pct, 2) ?>%" title = "<?= e($st) ?>: <?= $cnt ?> (<?= round($pct) ?>%)"></div>
<?php endforeach;
?>
</div>
</div>

<!-- Detailed Status Legend List -->
<ul class = 'mt-6 space-y-3'>
<?php foreach ( $statusConfig as $key => $meta ):
$cnt = $statusMix[ $key ] ?? 0;
$pct = $statusTotal > 0 ? round( ( $cnt / $statusTotal ) * 100 ) : 0;
?>
<li class = 'flex items-center justify-between p-2.5 rounded-xl hover:bg-surface-sunken transition-colors'>
<div class = 'flex items-center gap-2.5 min-w-0'>
<span class = "h-3 w-3 rounded-full <?= $meta['bg'] ?> ring-2 ring-white shadow-sm shrink-0"></span>
<span class = 'text-xs font-bold text-ink truncate'><?= e( $meta[ 'label' ] ) ?></span>
</div>
<div class = 'flex items-center gap-2 shrink-0'>
<span class = 'text-xs font-black text-ink tabular-nums'><?= $cnt ?></span>
<span class = 'text-[11px] font-semibold text-ink-muted tabular-nums'>( <?= $pct ?>% )</span>
</div>
</li>
<?php endforeach;
?>
</ul>
<?php endif;
?>
</div>

<div class = 'mt-6 pt-4 border-t border-line'>
<a href = '/admin/orders' class = 'w-full flex items-center justify-center gap-2 py-2.5 px-4 rounded-xl bg-surface-sunken hover:bg-slate-200 text-ink font-bold text-xs transition-colors'>
Manage Order Queue
<svg viewBox = '0 0 24 24' fill = 'none' stroke = 'currentColor' stroke-width = '2.5' class = 'h-3.5 w-3.5'><path d = 'M5 12h14m-6-6 6 6-6 6'/></svg>
</a>
</div>
</section>
</div>

<!-- ===  ===  ===  ===  ===  ===  ===  ===  ===  ===  === SECOND ROW: RECENT ORDERS & STORE HEALTH ===  ===  ===  ===  ===  ===  ===  ===  ===  ===  === -->
<div class = 'grid grid-cols-1 lg:grid-cols-12 gap-6'>

<!-- Recent Orders Live Log ( 7 cols ) -->
<section class = 'lg:col-span-7 rounded-3xl bg-white border border-line p-6 shadow-sm'>
<div class = 'flex items-center justify-between pb-4 border-b border-line'>
<div>
<h2 class = 'text-lg font-black tracking-tight text-ink flex items-center gap-2'>
<span class = 'h-2 w-2 rounded-full bg-emerald-500 animate-pulse'></span>
Recent Orders Feed
</h2>
<p class = 'text-xs text-ink-muted mt-0.5'>Real-time incoming customer orders.</p>
</div>
<a href = '/admin/orders' class = 'text-xs font-bold text-electric-600 hover:text-electric-700'>View All &rarr;
</a>
</div>

<?php if ( $recentOrders === [] ): ?>
<div class = 'py-12 text-center text-xs text-ink-muted'>No orders placed yet.</div>
<?php else: ?>
<div class = 'mt-4 divide-y divide-line'>
<?php foreach ( array_slice( $recentOrders, 0, 6 ) as $ord ):
$st = ( string )( $ord[ 'status' ] ?? 'pending' );
$conf = $statusConfig[ $st ] ?? [ 'badge' => 'bg-slate-100 text-slate-700 border-slate-200', 'label' => ucfirst( $st ) ];
?>
<div class = 'py-3.5 flex items-center justify-between gap-4 hover:bg-surface-sunken/50 rounded-xl px-2 transition-colors'>
<div class = 'flex items-center gap-3 min-w-0'>
<span class = 'flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-navy-900 text-white font-extrabold text-xs shadow-sm'>
<?= strtoupper( substr( ( string )$ord[ 'customer_name' ], 0, 2 ) ) ?>
</span>
<div class = 'min-w-0'>
<div class = 'flex items-center gap-2'>
<a href = "/admin/orders/<?= (int)$ord['id'] ?>" class = 'text-xs font-black text-ink hover:text-electric-600 transition-colors truncate'>
<?= e( $ord[ 'reference' ] ) ?>
</a>
<span class = "inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold border <?= $conf['badge'] ?>">
<?= e( $conf[ 'label' ] ) ?>
</span>
</div>
<div class = 'text-[11px] text-ink-muted truncate mt-0.5'>
<?= e( $ord[ 'customer_name' ] ) ?> &bull;
<?= e( $ord[ 'city' ] ?: 'Pakistan' ) ?>
</div>
</div>
</div>

<div class = 'text-right shrink-0'>
<div class = 'text-xs font-black text-ink tabular-nums'><?= price( ( float )$ord[ 'total' ] ) ?></div>
<div class = 'text-[10px] font-semibold text-ink-muted mt-0.5'><?= date( 'd M, h:i A', strtotime( ( string )$ord[ 'created_at' ] ) ) ?></div>
</div>
</div>
<?php endforeach;
?>
</div>
<?php endif;
?>
</section>

<!-- SEO & Infrastructure Diagnostics ( 5 cols ) -->
<section class = 'lg:col-span-5 rounded-3xl bg-white border border-line p-6 shadow-sm'>
<div class = 'flex items-center justify-between pb-4 border-b border-line'>
<div>
<h2 class = 'text-lg font-black tracking-tight text-ink flex items-center gap-2'>
<svg viewBox = '0 0 24 24' fill = 'none' stroke = 'currentColor' stroke-width = '2' class = 'h-4 w-4 text-electric-600'><circle cx = '12' cy = '12' r = '10'/><path d = 'M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z'/><path d = 'M2 12h20'/></svg>
SEO &amp;
Search Engine Health
</h2>
<p class = 'text-xs text-ink-muted mt-0.5'>Automated crawling readiness score.</p>
</div>
<a href = '/admin/seo/audit' class = 'text-xs font-bold text-electric-600 hover:text-electric-700'>Audit &rarr;
</a>
</div>

<!-- SEO Score Visual Indicator -->
<div class = 'mt-5 rounded-2xl bg-gradient-to-br from-navy-950 to-indigo-950 p-4 text-white'>
<div class = 'flex items-center justify-between'>
<div>
<span class = 'text-[11px] font-extrabold uppercase tracking-wider text-navy-300'>Readiness Score</span>
<div class = 'text-3xl font-black text-white mt-1'><?= $seoScore ?> <span class = 'text-xs font-semibold text-navy-300'>/ 100</span></div>
</div>
<div class = 'flex h-12 w-12 items-center justify-center rounded-2xl bg-white/10 ring-1 ring-white/20 text-gold-300 font-black text-sm'>
<?= $seoScore >= 90 ? 'A+' : ( $seoScore >= 80 ? 'A' : 'B' ) ?>
</div>
</div>
<div class = 'mt-3 h-2 w-full overflow-hidden rounded-full bg-white/10'>
<div class = 'h-full bg-gradient-to-r from-emerald-400 to-teal-400 rounded-full' style = "width: <?= $seoScore ?>%"></div>
</div>
</div>

<!-- Settings Status Check List -->
<div class = 'mt-5 space-y-2.5'>
<div class = 'flex items-center justify-between p-2.5 rounded-xl bg-surface-sunken text-xs'>
<span class = 'font-bold text-ink'>Canonical Domain</span>
<span class = "font-extrabold <?= !empty($seoSettings['canonical_domain']) ? 'text-emerald-600' : 'text-rose-600' ?>">
<?= e( $seoSettings[ 'canonical_domain' ] ?: 'Unset' ) ?>
</span>
</div>
<div class = 'flex items-center justify-between p-2.5 rounded-xl bg-surface-sunken text-xs'>
<span class = 'font-bold text-ink'>Robots Indexing</span>
<span class = "font-extrabold <?= !empty($seoSettings['site_indexable']) ? 'text-emerald-600' : 'text-amber-600' ?>">
<?= !empty( $seoSettings[ 'site_indexable' ] ) ? 'Active (Index, Follow)' : 'Staging (Noindex)' ?>
</span>
</div>
<div class = 'flex items-center justify-between p-2.5 rounded-xl bg-surface-sunken text-xs'>
<span class = 'font-bold text-ink'>Google Search Console</span>
<span class = "font-extrabold <?= !empty($seoSettings['gsc']) ? 'text-emerald-600' : 'text-ink-muted' ?>">
<?= !empty( $seoSettings[ 'gsc' ] ) ? 'Connected' : 'Not Configured' ?>
</span>
</div>
<div class = 'flex items-center justify-between p-2.5 rounded-xl bg-surface-sunken text-xs'>
<span class = 'font-bold text-ink'>Google Analytics ( GA4 )</span>
<span class = "font-extrabold <?= !empty($seoSettings['ga4']) ? 'text-emerald-600' : 'text-ink-muted' ?>">
<?= !empty( $seoSettings[ 'ga4' ] ) ? e( $seoSettings[ 'ga4' ] ) : 'Unset' ?>
</span>
</div>
</div>

<!-- Audit Summary Items -->
<div class = 'mt-5 pt-4 border-t border-line'>
<div class = 'text-xs font-bold text-ink-muted uppercase tracking-wider mb-2'>Audit Issues</div>
<div class = 'space-y-1.5'>
<?php foreach ( array_slice( $auditSummary, 0, 3 ) as $k => $item ):
$cnt = ( int )$item[ 'count' ];
?>
<div class = 'flex items-center justify-between text-xs py-1'>
<span class = 'text-ink truncate'><?= e( $item[ 'label' ] ) ?></span>
<span class = "px-2 py-0.5 rounded-full text-[10px] font-bold <?= $cnt > 0 ? 'bg-amber-100 text-amber-800' : 'bg-emerald-100 text-emerald-800' ?>">
<?= $cnt ?>
</span>
</div>
<?php endforeach;
?>
</div>
</div>
</section>

</div>
