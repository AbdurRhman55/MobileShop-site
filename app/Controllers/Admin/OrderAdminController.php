<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Database;
use App\Core\HttpRedirect;
use App\Core\View;
use App\Models\Order;
use App\Models\Payment;
use App\Seo\SeoSettings;

/**
 * Professional order desk.
 *
 * - Fulfilment and payment are deliberately separate: an order can be shipped
 *   or delivered while its payment is still pending (COD), and a payment can
 *   only be marked received by an administrator.
 * - Every change appends to the order timeline.
 * - Invoices are printable A4 documents suitable for attaching to the parcel.
 */
final class OrderAdminController extends AdminBaseController
{
    public function index(): void
    {
        $status  = trim((string) $this->request->query('status', ''));
        $payment = trim((string) $this->request->query('payment', ''));
        $q       = trim((string) $this->request->query('q', ''));

        $rows = Order::filtered($status, $q, 200, $payment);

        $counts = [];
        foreach (Database::fetchAll('SELECT status, COUNT(*) AS count FROM orders GROUP BY status') as $row) {
            $counts[(string) $row['status']] = (int) $row['count'];
        }

        $paymentCounts = [];
        foreach (Database::fetchAll('SELECT payment_status, COUNT(*) AS count FROM orders GROUP BY payment_status') as $row) {
            $paymentCounts[(string) $row['payment_status']] = (int) $row['count'];
        }

        $this->renderAdmin('admin/orders/index', [
            'rows'          => $rows,
            'status'        => $status,
            'payment'       => $payment,
            'q'             => $q,
            'counts'        => $counts,
            'paymentCounts' => $paymentCounts,
            'stats'         => Order::dashboardStats(),
            'flash'         => $this->takeFlash(),
        ]);
    }

    public function show(string $id): void
    {
        $order = $this->findOrder($id);
        if ($order === null) {
            HttpRedirect::to('/admin/orders', 303);
        }

        $events = Order::events((int) $order['id']);

        $this->renderAdmin('admin/orders/show', [
            'order'          => $order,
            'items'          => Order::items((int) $order['id']),
            'events'         => $events,
            'payment'        => Payment::summary($order),
            'paymentRows'    => Payment::allForOrder((int) $order['id']),
            'paymentMethods' => Payment::methods(),
            'nextStatuses'   => Order::nextStatuses((string) $order['status']),
            'invoiceNumber'  => Order::invoiceNumber($order),
            'flash'          => $this->takeFlash(),
        ]);
    }

    /** Move the order through the fulfilment workflow. */
    public function updateStatus(string $id): void
    {
        $order = $this->findOrder($id);
        if ($order === null) {
            HttpRedirect::to('/admin/orders', 303);
        }

        $to   = trim((string) $this->request->input('status', ''));
        $note = trim((string) $this->request->input('note', ''));

        $error = Order::updateStatus((int) $order['id'], $to, $note, 'admin');

        if ($error !== null) {
            $this->flash($error, 'error');
        } else {
            $meta = Order::statusMeta($to);
            $this->flash(
                'Order moved to ' . strtolower($meta['label']) . '.'
                . ($to === 'delivered' ? ' Payment is still pending until you record the cash received.' : ''),
                'success'
            );
        }

        HttpRedirect::to('/admin/orders/' . (int) $order['id'], 303);
    }

    /**
     * Manual payment approval. This is the only place an order becomes paid.
     */
    public function recordPayment(string $id): void
    {
        $order = $this->findOrder($id);
        if ($order === null) {
            HttpRedirect::to('/admin/orders', 303);
        }

        $amountRaw = str_replace([',', ' '], '', (string) $this->request->input('amount', ''));
        $amount    = is_numeric($amountRaw) ? (float) $amountRaw : 0.0;
        $method    = trim((string) $this->request->input('method', (string) $order['payment_method']));
        $reference = trim((string) $this->request->input('reference', ''));
        $paidAt    = trim((string) $this->request->input('paid_at', ''));
        $note      = trim((string) $this->request->input('note', ''));

        $error = Payment::recordReceived(
            (int) $order['id'],
            $amount,
            $method,
            $reference !== '' ? $reference : null,
            $paidAt !== '' ? $paidAt : null,
            $note !== '' ? $note : null
        );

        if ($error !== null) {
            $this->flash($error, 'error');
        } else {
            $this->flash('Payment recorded. The order now shows the received amount.', 'success');
        }

        HttpRedirect::to('/admin/orders/' . (int) $order['id'] . '#payment', 303);
    }

    /** Refund a received payment (full refund of the ledger). */
    public function refund(string $id): void
    {
        $order = $this->findOrder($id);
        if ($order === null) {
            HttpRedirect::to('/admin/orders', 303);
        }

        $note  = trim((string) $this->request->input('note', ''));
        $error = Payment::markRefunded((int) $order['id'], $note !== '' ? $note : null);

        $this->flash(
            $error ?? 'Payment marked as refunded.',
            $error !== null ? 'error' : 'success'
        );

        HttpRedirect::to('/admin/orders/' . (int) $order['id'] . '#payment', 303);
    }

    /** Internal note, shown on the order desk and in the timeline. */
    public function saveNote(string $id): void
    {
        $order = $this->findOrder($id);
        if ($order === null) {
            HttpRedirect::to('/admin/orders', 303);
        }

        $note = trim((string) $this->request->input('note', ''));
        if (mb_strlen($note) < 2) {
            $this->flash('Write a note before saving.', 'error');
            HttpRedirect::to('/admin/orders/' . (int) $order['id'], 303);
        }

        Order::addNote((int) $order['id'], $note, 'admin');
        $this->flash('Internal note saved.', 'success');
        HttpRedirect::to('/admin/orders/' . (int) $order['id'], 303);
    }

    public function paymentSettings(): void
    {
        $this->renderAdmin('admin/orders/settings', [
            'settings' => SeoSettings::all(),
            'flash'    => $this->takeFlash(),
        ]);
    }

    public function savePaymentSettings(): void
    {
        $keys = [
            'payments_bank_name',
            'payments_account_title',
            'payments_account_number',
            'payments_wallet_number',
            'payments_instructions',
            'invoice_terms',
            'invoice_note',
        ];

        foreach ($keys as $key) {
            SeoSettings::set($key, trim((string) $this->request->input($key, '')));
        }

        // Strict email DNS verification (registration + newsletter).
        SeoSettings::set('email_dns_check', $this->request->input('email_dns_check') ? '1' : '0');

        $this->flash('Payment and invoice settings saved.', 'success');
        HttpRedirect::to('/admin/orders/payment-settings', 303);
    }

    /** Printable invoice, available to administrators from the order desk. */
    public function invoice(string $id): void
    {
        $order = $this->findOrder($id);
        if ($order === null) {
            HttpRedirect::to('/admin/orders', 303);
        }

        if (!headers_sent()) {
            header('Content-Type: text/html; charset=utf-8');
            header('X-Robots-Tag: noindex, nofollow');
        }

        echo View::render('invoice', [
            'order'   => $order,
            'items'   => Order::items((int) $order['id']),
            'summary' => Payment::summary($order),
            'backUrl' => '/admin/orders/' . (int) $order['id'],
        ], null);
    }

    /* ---------------------------------------------------------- helpers -- */

    private function findOrder(string $id): ?array
    {
        $orderId = (int) $id;
        if ($orderId < 1) {
            return null;
        }

        return Database::fetch(
            'SELECT o.*, c.id AS account_customer_id
             FROM orders o
             LEFT JOIN customers c ON c.id = o.customer_id
             WHERE o.id = ?',
            [$orderId]
        );
    }
}
