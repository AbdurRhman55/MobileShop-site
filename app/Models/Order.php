<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

/**
 * Orders.
 *
 * Lifecycle:  pending → confirmed → processing → shipped → delivered
 *                          ↘ cancelled (stock restored once)
 *
 * Payment is a separate, administrator-verified concern (see App\Models\Payment).
 * Placing or delivering an order never marks it paid.
 */
final class Order
{
    public const STATUSES = ['pending', 'confirmed', 'processing', 'shipped', 'delivered', 'cancelled', 'refunded'];

    /** Legal fulfilment transitions. */
    private const TRANSITIONS = [
        'pending'   => ['confirmed', 'processing', 'cancelled'],
        'confirmed' => ['processing', 'cancelled'],
        'processing'=> ['shipped', 'cancelled'],
        'shipped'   => ['delivered'],
        'delivered' => [],
        'cancelled' => [],
        'refunded'  => [],
    ];

    /**
     * Create an order from cart contents. Returns the order row.
     *
     * `$customer` must carry a valid `customer_id`; guest checkout is not
     * offered. A pending payment row and the opening audit events are written
     * in the same transaction.
     */
    public static function create(
        array $customer,
        array $cartItems,
        string $paymentMethod = 'bank_transfer',
        ?string $paymentScreenshot = null,
        ?string $paymentReference = null
    ): array {
        $customerId = (int) ($customer['customer_id'] ?? 0);
        if ($customerId < 1) {
            throw new \RuntimeException('An account is required to place an order.');
        }
        $paymentMethod = 'bank_transfer';

        return Database::transaction(static function () use ($customer, $cartItems, $paymentMethod, $customerId, $paymentScreenshot, $paymentReference) {
            $subtotal = 0.0;
            foreach ($cartItems as $item) {
                $subtotal += $item['unit_price'] * $item['quantity'];
            }
            // Flat shipping: free above Rs 50,000 (matches delivery page copy).
            $shipping = $subtotal >= 50000 ? 0.0 : 250.0;
            $total    = $subtotal + $shipping;

            $reference = 'LM' . strtoupper(bin2hex(random_bytes(5)));

            $orderId = Database::insert('orders', [
                'reference'          => $reference,
                'customer_id'        => $customerId,
                'customer_name'      => $customer['name'],
                'email'              => $customer['email'] ?? null,
                'phone'              => $customer['phone'],
                'address'            => $customer['address'],
                'city'               => $customer['city'],
                'subtotal'           => $subtotal,
                'shipping'           => $shipping,
                'total'              => $total,
                'payment_method'     => $paymentMethod,
                'payment_status'     => 'pending',
                'payment_reference'  => $paymentReference,
                'payment_screenshot' => $paymentScreenshot,
                'payment_reported_at'=> $paymentScreenshot !== null || $paymentReference !== null ? date('Y-m-d H:i:s') : null,
                'status'             => 'pending',
            ]);

            foreach ($cartItems as $item) {
                Database::insert('order_items', [
                    'order_id'   => $orderId,
                    'product_id' => $item['product_id'] ?? null,
                    'variant_id' => $item['variant_id'] ?? null,
                    'item_name'  => $item['name'],
                    'sku'        => $item['sku'] ?? null,
                    'unit_price' => $item['unit_price'],
                    'quantity'   => $item['quantity'],
                ]);
                // Decrement stock.
                if (!empty($item['product_id'])) {
                    Database::query(
                        'UPDATE products SET stock = GREATEST(0, stock - ?) WHERE id = ?',
                        [$item['quantity'], (int) $item['product_id']]
                    );
                }
            }

            Payment::ensurePending($orderId, $paymentMethod, $total);

            self::logEvent($orderId, 'status', null, 'pending', 'Order placed through storefront with Soneri Bank.', 'customer');
            self::logEvent(
                $orderId,
                'payment',
                null,
                'pending',
                'Payment pending verification for Soneri Bank (Lala Mobiles / PK62SONE0000320013557234)'
                . ($paymentScreenshot ? ' — Online payment screenshot attached.' : '')
                . ($paymentReference ? ' — Transaction Ref: ' . $paymentReference : ''),
                'customer'
            );

            return Database::fetch('SELECT * FROM orders WHERE id = ?', [$orderId]);
        });
    }

    public static function byId(int $id): ?array
    {
        return Database::fetch('SELECT * FROM orders WHERE id = ?', [$id]);
    }

    public static function byReference(string $reference): ?array
    {
        return Database::fetch('SELECT * FROM orders WHERE reference = ?', [$reference]);
    }

    public static function items(int $orderId): array
    {
        return Database::fetchAll(
            'SELECT oi.*, p.slug AS product_slug FROM order_items oi
             LEFT JOIN products p ON p.id = oi.product_id WHERE oi.order_id = ?',
            [$orderId]
        );
    }

    public static function recent(int $limit = 20): array
    {
        return Database::fetchAll('SELECT * FROM orders ORDER BY created_at DESC LIMIT ' . (int) $limit);
    }

    public static function filtered(string $status = '', string $query = '', int $limit = 100, string $payment = ''): array
    {
        $where = [];
        $params = [];
        if (in_array($status, self::STATUSES, true)) {
            $where[] = 'status = ?';
            $params[] = $status;
        }
        if (in_array($payment, ['pending', 'paid', 'refunded', 'failed'], true)) {
            $where[] = 'payment_status = ?';
            $params[] = $payment;
        }
        if ($query !== '') {
            $where[] = '(reference LIKE ? OR customer_name LIKE ? OR email LIKE ? OR phone LIKE ? OR payment_reference LIKE ?)';
            $like = '%' . $query . '%';
            array_push($params, $like, $like, $like, $like, $like);
        }
        $sql = 'SELECT * FROM orders' . ($where !== [] ? ' WHERE ' . implode(' AND ', $where) : '')
            . ' ORDER BY created_at DESC LIMIT ' . max(1, $limit);
        return Database::fetchAll($sql, $params);
    }

    public static function count(): int
    {
        return (int) Database::fetchColumn('SELECT COUNT(*) FROM orders');
    }

    /* --------------------------------------------------------- workflow -- */

    public static function statusMeta(string $status): array
    {
        return [
            'pending'    => ['bg' => 'bg-gold-400',      'fg' => 'text-navy-900', 'label' => 'Pending'],
            'processing' => ['bg' => 'bg-electric-100',  'fg' => 'text-electric-800', 'label' => 'Processing'],
            'confirmed'  => ['bg' => 'bg-electric-500',  'fg' => 'text-white',    'label' => 'Confirmed'],
            'shipped'    => ['bg' => 'bg-violet-500',    'fg' => 'text-white',    'label' => 'Shipped'],
            'delivered'  => ['bg' => 'bg-jade-500',      'fg' => 'text-white',    'label' => 'Delivered'],
            'cancelled'  => ['bg' => 'bg-flame-500',     'fg' => 'text-white',    'label' => 'Cancelled'],
            'refunded'   => ['bg' => 'bg-surface-sunken','fg' => 'text-ink-muted','label' => 'Refunded'],
        ][$status] ?? ['bg' => 'bg-electric-500', 'fg' => 'text-white', 'label' => ucfirst($status)];
    }

    /** @return array<int, string> */
    public static function nextStatuses(string $status): array
    {
        return self::TRANSITIONS[$status] ?? [];
    }

    /**
     * Move an order to a new fulfilment status.
     *
     * @return string|null error message, or null on success
     */
    public static function updateStatus(int $orderId, string $to, string $note = '', string $actor = 'admin'): ?string
    {
        $order = self::byId($orderId);
        if ($order === null) {
            return 'Order not found.';
        }

        $from = (string) $order['status'];
        if (!in_array($to, self::STATUSES, true)) {
            return 'Unknown order status.';
        }
        if ($to === $from) {
            return 'The order is already ' . strtolower(self::statusMeta($to)['label']) . '.';
        }
        if (!in_array($to, self::nextStatuses($from), true)) {
            return 'An order that is ' . strtolower(self::statusMeta($from)['label'])
                . ' cannot move to ' . strtolower(self::statusMeta($to)['label']) . '.';
        }

        Database::transaction(static function () use ($orderId, $from, $to, $note, $actor): void {
            $data = ['status' => $to];
            $now  = date('Y-m-d H:i:s');

            if ($to === 'confirmed') {
                $data['confirmed_at'] = $now;
            } elseif ($to === 'shipped') {
                $data['shipped_at'] = $now;
            } elseif ($to === 'delivered') {
                $data['delivered_at'] = $now;
            } elseif ($to === 'cancelled') {
                $data['cancelled_at'] = $now;
            }

            if ($to === 'cancelled') {
                self::restoreStock($orderId);
            }

            Database::update('orders', $orderId, $data);

            self::logEvent(
                $orderId,
                'status',
                $from,
                $to,
                trim((string) ($note !== '' ? $note : 'Fulfilment status updated.')),
                $actor
            );
        });

        return null;
    }

    /** Return reserved stock to the catalogue once, when an order is cancelled. */
    public static function restoreStock(int $orderId): void
    {
        $order = self::byId($orderId);
        if ($order === null || (int) $order['stock_restored'] === 1) {
            return;
        }

        foreach (self::items($orderId) as $item) {
            if (!empty($item['product_id'])) {
                Database::query(
                    'UPDATE products SET stock = stock + ? WHERE id = ?',
                    [(int) $item['quantity'], (int) $item['product_id']]
                );
            }
        }

        Database::update('orders', $orderId, ['stock_restored' => 1]);
    }

    /** Append-only timeline shown on the customer and admin order screens. */
    public static function logEvent(
        int $orderId,
        string $type,
        ?string $from,
        ?string $to,
        ?string $note = null,
        string $actor = 'system'
    ): void {
        if (!in_array($type, ['status', 'payment', 'note', 'customer'], true)) {
            $type = 'note';
        }

        Database::insert('order_events', [
            'order_id'   => $orderId,
            'event_type' => $type,
            'from_value' => $from,
            'to_value'   => $to,
            'note'       => $note !== null ? mb_substr($note, 0, 500) : null,
            'actor'      => mb_substr($actor, 0, 40),
        ]);
    }

    public static function events(int $orderId): array
    {
        return Database::fetchAll(
            'SELECT * FROM order_events WHERE order_id = ? ORDER BY created_at ASC, id ASC',
            [$orderId]
        );
    }

    /** Internal note (not shown to the customer). */
    public static function addNote(int $orderId, string $note, string $actor = 'admin'): void
    {
        Database::update('orders', $orderId, ['admin_note' => mb_substr($note, 0, 500)]);
        self::logEvent($orderId, 'note', null, null, $note, $actor);
    }

    public static function invoiceNumber(array $order): string
    {
        $year = date('Y', strtotime((string) $order['created_at']));
        return 'INV-' . $year . '-' . str_pad((string) $order['id'], 5, '0', STR_PAD_LEFT);
    }

    /** KPI values for the admin order dashboard. */
    public static function dashboardStats(): array
    {
        $row = Database::fetch(
            "SELECT
                COUNT(*) AS total_orders,
                SUM(status = 'pending') AS awaiting_confirmation,
                SUM(payment_status = 'pending' AND status NOT IN ('cancelled','refunded')) AS payment_pending,
                COALESCE(SUM(CASE WHEN payment_status = 'pending' AND status NOT IN ('cancelled','refunded') THEN total ELSE 0 END), 0) AS payment_pending_value,
                COALESCE(SUM(CASE WHEN payment_status = 'paid' THEN total ELSE 0 END), 0) AS received_value,
                COALESCE(SUM(CASE WHEN payment_status = 'paid' AND DATE(paid_at) = CURDATE() THEN total ELSE 0 END), 0) AS received_today
             FROM orders"
        );

        return [
            'total_orders'          => (int) ($row['total_orders'] ?? 0),
            'awaiting_confirmation' => (int) ($row['awaiting_confirmation'] ?? 0),
            'payment_pending'       => (int) ($row['payment_pending'] ?? 0),
            'payment_pending_value' => (float) ($row['payment_pending_value'] ?? 0),
            'received_value'        => (float) ($row['received_value'] ?? 0),
            'received_today'        => (float) ($row['received_today'] ?? 0),
        ];
    }
}
