<?php
/**
 * Invoice / packing slip — print-ready A4.
 *
 * Rendered without a site layout. Administrators and account owners can print
 * this to PDF and attach it to the parcel.
 *
 * @var array $order @var array $items @var array $summary @var string $backUrl
 */
use App\Models\Order;
use App\Models\Payment;
use App\Seo\SeoSettings;

$orderId = (int) $order['id'];
$summary = $summary ?? Payment::summary($order);
$invoiceNo = Order::invoiceNumber($order);
$bank = Payment::bankDetails();
$terms = (string) SeoSettings::get('invoice_terms', '');
$note = (string) SeoSettings::get('invoice_note', '');
$org = [
    'name'    => (string) SeoSettings::get('organization_name', 'Lala Mobiles'),
    'address' => (string) SeoSettings::get('organization_address', ''),
    'city'    => (string) SeoSettings::get('organization_city', ''),
    'region'  => (string) SeoSettings::get('organization_region', ''),
    'postal'  => (string) SeoSettings::get('organization_postal', ''),
    'phone'   => (string) SeoSettings::get('organization_phone', ''),
    'email'   => (string) SeoSettings::get('organization_email', ''),
];
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex,nofollow,noarchive">
    <title>Invoice <?= e($invoiceNo) ?> — <?= e($order['reference']) ?></title>
    <style>
        :root { --ink:#172033; --muted:#475569; --line:#e2e8f0; --brand:#0b1728; --accent:#2563eb; }
        * { box-sizing:border-box; }
        html { font:14px/1.6 -apple-system,BlinkMacSystemFont,"Segoe UI",Inter,system-ui,sans-serif; color:var(--ink); }
        body { margin:0; background:#f7f9fc; }
        .sheet { margin:24px auto; max-width:820px; background:#fff; box-shadow:0 40px 80px -60px rgba(15,23,42,.9); border:1px solid var(--line); }
        .topbar { display:flex; flex-wrap:wrap; align-items:flex-start; justify-content:space-between; gap:16px; padding:32px 36px 24px; border-bottom:1px solid var(--line); }
        .brand { display:flex; align-items:center; gap:12px; }
        .brand img { height:54px; width:auto; object-fit:contain; }
        .brand-text { font-weight:800; letter-spacing:-.01em; }
        .brand-sub { font-size:12px; color:var(--muted); }
        .invoice-meta { text-align:right; }
        .invoice-label { font-size:11px; text-transform:uppercase; letter-spacing:.18em; font-weight:800; color:var(--accent); }
        .invoice-no { margin-top:4px; font-size:24px; font-weight:800; letter-spacing:-.01em; }
        .invoice-dates { margin-top:6px; font-size:12px; color:var(--muted); }
        .grid { display:grid; grid-template-columns:repeat(12,1fr); gap:18px; padding:24px 36px; }
        .card { grid-column:1/-1; border:1px solid var(--line); border-radius:14px; overflow:hidden; }
        .card-head { display:flex; align-items:center; justify-content:space-between; padding:14px 20px; background:#f8fafc; border-bottom:1px solid var(--line); }
        .card-title { font-weight:800; }
        .cols { grid-column:1/-1; display:grid; gap:18px; grid-template-columns:repeat(2,1fr); }
        .panel { border:1px solid var(--line); border-radius:14px; padding:18px 20px; }
        .eyebrow { font-size:11px; text-transform:uppercase; letter-spacing:.16em; font-weight:800; color:var(--muted); }
        .addr { margin-top:6px; white-space:pre-line; }
        .muted { color:var(--muted); }
        table { width:100%; border-collapse:collapse; }
        th { text-align:left; font-size:11px; text-transform:uppercase; letter-spacing:.14em; color:var(--muted); padding:10px 20px; background:#f8fafc; border-bottom:1px solid var(--line); }
        td { padding:12px 20px; border-bottom:1px solid var(--line); vertical-align:top; }
        .right { text-align:right; }
        .num { font-variant-numeric:tabular-nums; }
        .strong { font-weight:700; }
        .totals { grid-column:1/-1; display:flex; justify-content:flex-end; }
        .totals-inner { width:min(360px,100%); }
        .totals-row { display:flex; justify-content:space-between; padding:8px 0; }
        .totals-row + .totals-row { border-top:1px solid var(--line); }
        .grand { font-size:18px; font-weight:800; }
        .status { display:flex; flex-wrap:wrap; gap:10px; align-items:center; justify-content:space-between; border-top:1px solid var(--line); padding:16px 36px; }
        .badge { display:inline-flex; align-items:center; gap:6px; border-radius:999px; padding:6px 12px; font-size:12px; font-weight:800; border:1px solid var(--line); }
        .badge.pending { background:#fef3c7; color:#92400e; border-color:#fbbf24; }
        .badge.paid { background:#ecfdf5; color:#065f46; border-color:#86efac; }
        .footer { padding:18px 36px 30px; display:grid; gap:14px; }
        .note { border:1px solid var(--line); border-radius:12px; padding:14px 18px; background:#f8fafc; }
        .actions { display:flex; flex-wrap:wrap; gap:10px; justify-content:center; padding:0 20px 24px; }
        .btn { display:inline-flex; align-items:center; gap:8px; border-radius:999px; border:1px solid var(--line); padding:9px 16px; font-weight:700; text-decoration:none; color:var(--ink); background:#fff; }
        .btn.primary { background:linear-gradient(135deg,#2563eb,#1d4ed8); color:#fff; border-color:transparent; }
        @media print {
            body { background:#fff; }
            .sheet { margin:0; max-width:none; box-shadow:none; border:none; }
            .actions { display:none; }
            .topbar, .grid, .status, .footer { padding-left:24px; padding-right:24px; }
        }
    </style>
</head>
<body>
    <div class="sheet">
        <header class="topbar">
            <div class="brand">
                <img src="/img/logo.png" alt="Lala Mobiles" width="180" height="117">
                <div>
                    <div class="brand-text"><?= e($org['name']) ?></div>
                    <div class="brand-sub">Premium Mobile Solutions · <?= e(trim($org['city'] . ' ' . $org['region'])) ?></div>
                </div>
            </div>
            <div class="invoice-meta">
                <div class="invoice-label">Invoice</div>
                <div class="invoice-no"><?= e($invoiceNo) ?></div>
                <div class="invoice-dates">
                    Invoice date: <?= e(date('d M Y')) ?><br>
                    Order date: <?= e(date('d M Y, H:i', strtotime((string) $order['created_at']))) ?><br>
                    Order reference: <?= e($order['reference']) ?>
                </div>
            </div>
        </header>

        <div class="grid">
            <div class="cols">
                <section class="panel">
                    <div class="eyebrow">Bill to</div>
                    <div class="addr"><strong><?= e($order['customer_name']) ?></strong>
<?= e($order['address']) ?>
<?= e($order['city']) ?></div>
                    <div class="muted" style="margin-top:8px;">Phone: <?= e($order['phone']) ?><br>Email: <?= e($order['email'] ?? '') ?></div>
                </section>
                <section class="panel">
                    <div class="eyebrow">Ship / deliver to</div>
                    <div class="addr"><strong><?= e($order['customer_name']) ?></strong>
<?= e($order['address']) ?>
<?= e($order['city']) ?></div>
                    <div class="muted" style="margin-top:8px;">Contact: <?= e($order['phone']) ?></div>
                </section>
            </div>

            <div class="card">
                <div class="card-head">
                    <div class="card-title">Items ordered</div>
                    <div class="muted"><?= count($items) ?> line<?= count($items) === 1 ? '' : 's' ?> · Payment: <?= e($summary['method_label']) ?></div>
                </div>
                <div style="overflow-x:auto;">
                    <table>
                        <thead>
                            <tr>
                                <th>Item</th>
                                <th>SKU</th>
                                <th class="right">Qty</th>
                                <th class="right">Unit price</th>
                                <th class="right">Line total</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($items as $it): ?>
                            <tr>
                                <td class="strong"><?= e($it['item_name']) ?></td>
                                <td class="muted"><?= e($it['sku'] ?? '—') ?></td>
                                <td class="right num"><?= (int) $it['quantity'] ?></td>
                                <td class="right num"><?= price((float) $it['unit_price']) ?></td>
                                <td class="right num strong"><?= price((float) $it['unit_price'] * (int) $it['quantity']) ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="totals">
                <div class="totals-inner">
                    <div class="totals-row">
                        <div class="muted">Subtotal</div>
                        <div class="num strong"><?= price((float) $order['subtotal']) ?></div>
                    </div>
                    <div class="totals-row">
                        <div class="muted">Delivery</div>
                        <div class="num strong"><?= (float) $order['shipping'] > 0 ? price((float) $order['shipping']) : 'Free' ?></div>
                    </div>
                    <div class="totals-row grand">
                        <div>Total</div>
                        <div class="num"><?= price((float) $order['total']) ?></div>
                    </div>
                    <div class="totals-row">
                        <div class="muted">Paid so far</div>
                        <div class="num"><?= price($summary['paid']) ?></div>
                    </div>
                    <div class="totals-row">
                        <div class="muted">Balance</div>
                        <div class="num strong"><?= price($summary['balance']) ?></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="status">
            <div style="display:flex; flex-wrap:wrap; gap:12px; align-items:center;">
                <span class="badge <?= $summary['status'] === 'paid' ? 'paid' : 'pending' ?>">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6"><path d="M20 6L9 17l-5-5"/></svg>
                    Payment <?= e($summary['label']) ?> · <?= e($summary['method_label']) ?>
                </span>
                <?php if ($summary['reference'] !== ''): ?>
                <span class="muted">Reference: <strong><?= e($summary['reference']) ?></strong></span>
                <?php endif; ?>
            </div>
            <div class="muted">Order status: <?= e(\App\Models\Order::statusMeta((string)$order['status'])['label']) ?></div>
        </div>

        <footer class="footer">
            <?php if ($summary['status'] === 'pending' && in_array($summary['method'], ['bank_transfer', 'wallet'], true)): ?>
            <div class="note">
                <strong>Payment instructions</strong>
                <ul style="margin:8px 0 0; padding-left:18px;">
                    <?php foreach (Payment::instructions((string)$order['payment_method'])['lines'] as $line): ?><li><?= e($line) ?></li><?php endforeach; ?>
                </ul>
                <?php if ($summary['method'] === 'bank_transfer' && $bank['bank'] !== '' && $bank['account'] !== ''): ?>
                <div style="margin-top:10px; display:grid; grid-template-columns:repeat(auto-fit,minmax(220px,1fr)); gap:10px;">
                    <div><span class="muted">Bank</span><br><strong><?= e($bank['bank']) ?></strong></div>
                    <div><span class="muted">Account title</span><br><strong><?= e($bank['title']) ?></strong></div>
                    <div><span class="muted">Account / IBAN</span><br><strong style="font-family:ui-monospace,SFMono-Regular,Menlo,monospace;"><?= e($bank['account']) ?></strong></div>
                </div>
                <?php elseif ($summary['method'] === 'wallet' && $bank['wallet'] !== ''): ?>
                <div style="margin-top:10px;"><span class="muted">Wallet number</span><br><strong style="font-family:ui-monospace,SFMono-Regular,Menlo,monospace;"><?= e($bank['wallet']) ?></strong></div>
                <?php endif; ?>
            </div>
            <?php endif; ?>

            <?php if ($note !== ''): ?>
            <div class="note"><?= nl2br(e($note)) ?></div>
            <?php endif; ?>
            <?php if ($terms !== ''): ?>
            <div class="muted" style="font-size:12px;"><?= nl2br(e($terms)) ?></div>
            <?php endif; ?>

            <div class="muted" style="text-align:center; font-size:12px;">
                <?= e($org['name']) ?> · <?= e(trim($org['address'] . ', ' . $org['city'])) ?> · <?= e($org['phone']) ?> · <?= e($org['email']) ?>
            </div>
        </footer>

        <div class="actions">
            <a class="btn primary" href="#" onclick="window.print();return false;">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 6 2 18 2 18 9"/><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><rect x="6" y="14" width="12" height="8"/></svg>
                Print / Save as PDF
            </a>
            <a class="btn" href="<?= e($backUrl) ?>">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 12H5M11 18l-6-6 6-6"/></svg>
                Back
            </a>
        </div>
    </div>
</body>
</html>
