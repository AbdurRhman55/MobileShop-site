<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use App\Seo\SeoSettings;

/**
 * Payment ledger for orders.
 *
 * Rules enforced here — not in the views:
 *   - Every order starts with a `pending` payment row.
 *   - Nothing is ever auto-marked as received: only an administrator can
 *     approve a payment, after cash has been collected or a transfer verified.
 *   - Placing an order never changes the payment status, even when the
 *     fulfilment status later becomes `delivered` (COD cash is collected by the
 *     courier first and recorded afterwards).
 *   - Customers may report a bank / wallet transfer; that only flags the order
 *     for review and leaves it pending.
 */
final class Payment
{
    public static function methods(): array
    {
        return [
            'bank_transfer' => [
                'label'       => 'Soneri Bank (Online Transfer)',
                'provider'    => 'bank_transfer',
                'tagline'     => 'Transfer to Soneri Bank & upload payment screenshot.',
                'reportable'  => true,
                'icon'        => 'bank',
            ],
        ];
    }

    public static function isMethod(string $method): bool
    {
        return $method === 'bank_transfer' || array_key_exists($method, self::methods());
    }

    /** Map legacy provider values to current methods. */
    public static function normaliseMethod(string $method): string
    {
        return 'bank_transfer';
    }

    public static function label(string $method): string
    {
        return 'Soneri Bank (Bank Transfer)';
    }

    public static function providerFor(string $method): string
    {
        return 'bank_transfer';
    }

    public static function statusMeta(string $status): array
    {
        return match ($status) {
            'paid'     => ['label' => 'Paid',     'bg' => 'bg-jade-500',     'fg' => 'text-white'],
            'refunded' => ['label' => 'Refunded', 'bg' => 'bg-surface-sunken', 'fg' => 'text-ink-muted'],
            'failed'   => ['label' => 'Failed',   'bg' => 'bg-flame-500',   'fg' => 'text-white'],
            default    => ['label' => 'Pending',  'bg' => 'bg-gold-400',    'fg' => 'text-navy-900'],
        };
    }

    /** Latest ledger row for an order. */
    public static function forOrder(int $orderId): ?array
    {
        return Database::fetch(
            'SELECT * FROM payments WHERE order_id = ? ORDER BY id DESC LIMIT 1',
            [$orderId]
        );
    }

    /** @return array<int, array> */
    public static function allForOrder(int $orderId): array
    {
        return Database::fetchAll('SELECT * FROM payments WHERE order_id = ? ORDER BY id ASC', [$orderId]);
    }

    public static function paidTotal(int $orderId): float
    {
        return (float) Database::fetchColumn(
            "SELECT COALESCE(SUM(amount), 0) FROM payments WHERE order_id = ? AND status = 'paid'",
            [$orderId]
        );
    }

    public static function refundedTotal(int $orderId): float
    {
        return (float) Database::fetchColumn(
            "SELECT COALESCE(SUM(amount), 0) FROM payments WHERE order_id = ? AND status = 'refunded'",
            [$orderId]
        );
    }

    /** Money summary used by the storefront, admin and invoice. */
    public static function summary(array $order): array
    {
        $orderId  = (int) $order['id'];
        $total    = (float) $order['total'];
        $paid     = self::paidTotal($orderId);
        $refunded = self::refundedTotal($orderId);
        $balance  = max(0.0, round($total - $paid, 2));
        $status   = (string) ($order['payment_status'] ?? 'pending');
        $method   = self::normaliseMethod((string) $order['payment_method']);
        $meta     = self::statusMeta($status);

        return [
            'total'          => $total,
            'paid'           => $paid,
            'refunded'       => $refunded,
            'balance'        => $balance,
            'status'         => $status,
            'label'          => $meta['label'],
            'meta'           => $meta,
            'method'         => $method,
            'method_label'   => self::label($method),
            'reported'       => !empty($order['payment_reported_at']),
            'reported_at'    => $order['payment_reported_at'] ?? null,
            'reference'      => (string) ($order['payment_reference'] ?? ''),
            'paid_at'        => $order['paid_at'] ?? null,
        ];
    }

    /** Create the pending ledger row when an order is placed. */
    public static function ensurePending(int $orderId, string $method, float $amount): int
    {
        $existing = Database::fetch(
            "SELECT id FROM payments WHERE order_id = ? AND status = 'pending' ORDER BY id LIMIT 1",
            [$orderId]
        );
        if ($existing !== null) {
            return (int) $existing['id'];
        }

        return Database::insert('payments', [
            'order_id'       => $orderId,
            'provider'       => self::providerFor($method),
            'transaction_id' => null,
            'amount'         => $amount,
            'currency'       => 'PKR',
            'status'         => 'pending',
            'paid_at'        => null,
        ]);
    }

    /**
     * Administrator approval: payment physically received / transfer verified.
     *
     * @return string|null error message, or null on success
     */
    public static function recordReceived(
        int $orderId,
        float $amount,
        string $method,
        ?string $reference = null,
        ?string $paidAt = null,
        ?string $note = null
    ): ?string {
        $order = Order::byId($orderId);
        if ($order === null) {
            return 'Order not found.';
        }
        if ((string) $order['payment_status'] === 'refunded') {
            return 'This payment was already refunded. Reopen it before recording another receipt.';
        }
        if (!self::isMethod($method)) {
            return 'Choose a valid payment method.';
        }
        if ($amount <= 0) {
            return 'Enter the amount that was actually received.';
        }

        $summary = self::summary($order);
        if ($amount > $summary['balance'] + 0.01) {
            return 'The received amount is more than the outstanding balance of ' . price($summary['balance']) . '.';
        }

        $reference = $reference !== null ? trim($reference) : '';
        if (mb_strlen($reference) > 120) {
            return 'The transaction reference is too long (120 characters maximum).';
        }

        $paidAtSql = date('Y-m-d H:i:s');
        if ($paidAt !== null && trim($paidAt) !== '') {
            $ts = strtotime($paidAt);
            if ($ts === false) {
                return 'Enter a valid date for when the payment was received.';
            }
            $paidAtSql = date('Y-m-d H:i:s', $ts);
        }

        Database::transaction(static function () use ($orderId, $amount, $method, $reference, $paidAtSql, $note): void {
            $pending = Database::fetch(
                "SELECT * FROM payments WHERE order_id = ? AND status = 'pending' ORDER BY id ASC LIMIT 1",
                [$orderId]
            );

            $data = [
                'provider'       => self::providerFor($method),
                'transaction_id' => $reference !== '' ? $reference : null,
                'amount'         => $amount,
                'currency'       => 'PKR',
                'status'         => 'paid',
                'paid_at'        => $paidAtSql,
            ];

            if ($pending !== null) {
                Database::update('payments', (int) $pending['id'], $data);
            } else {
                Database::insert('payments', $data + ['order_id' => $orderId]);
            }

            $orderData = [];
            if ($reference !== '') {
                $orderData['payment_reference'] = $reference;
            }
            if ($note !== null && trim($note) !== '') {
                $orderData['admin_note'] = mb_substr(trim($note), 0, 500);
            }
            if ($orderData !== []) {
                Database::update('orders', $orderId, $orderData);
            }

            self::syncOrder($orderId);

            Order::logEvent(
                $orderId,
                'payment',
                'pending',
                'paid',
                trim('Payment received: ' . price($amount) . ' via ' . self::label($method)
                    . ($reference !== '' ? ' — reference ' . $reference : '')
                    . ($note !== null && trim($note) !== '' ? '. ' . trim($note) : '')),
                'admin'
            );
        });

        return null;
    }

    /**
     * Customer reports a bank / wallet transfer. This never marks the order as
     * paid — it only attaches the reference for administrator verification.
     *
     * @return string|null error message, or null on success
     */
    public static function reportByCustomer(int $orderId, string $reference, float $amount, ?string $note = null): ?string
    {
        $order = Order::byId($orderId);
        if ($order === null) {
            return 'Order not found.';
        }

        $method = self::normaliseMethod((string) $order['payment_method']);
        if ($method === 'cod') {
            return 'Cash on Delivery orders are paid when the courier hands over your parcel.';
        }
        if ((string) $order['payment_status'] !== 'pending') {
            return 'This payment has already been reviewed.';
        }

        $reference = trim($reference);
        if (mb_strlen($reference) < 4) {
            return 'Enter the bank / wallet transaction reference (at least 4 characters).';
        }
        if (mb_strlen($reference) > 120) {
            return 'The transaction reference is too long (120 characters maximum).';
        }

        $summary = self::summary($order);
        if ($amount <= 0) {
            return 'Enter the amount you sent.';
        }
        if ($amount > $summary['balance'] + 0.01) {
            return 'The amount is more than the outstanding balance of ' . price($summary['balance']) . '.';
        }

        Database::transaction(static function () use ($orderId, $reference, $amount, $note): void {
            $pending = Database::fetch(
                "SELECT id FROM payments WHERE order_id = ? AND status = 'pending' ORDER BY id ASC LIMIT 1",
                [$orderId]
            );
            if ($pending !== null) {
                Database::update('payments', (int) $pending['id'], [
                    'transaction_id' => $reference,
                    'amount'         => $amount,
                ]);
            }

            Database::update('orders', $orderId, [
                'payment_reference'     => $reference,
                'payment_reported_at'   => date('Y-m-d H:i:s'),
            ]);

            Order::logEvent(
                $orderId,
                'customer',
                'pending',
                'reported',
                trim('Customer reported a payment of ' . price($amount) . ' — reference ' . $reference
                    . ($note !== null && trim($note) !== '' ? '. ' . trim($note) : '')),
                'customer'
            );
        });

        return null;
    }

    /** Refund every received amount and flag the order accordingly. */
    public static function markRefunded(int $orderId, ?string $note = null): ?string
    {
        $order = Order::byId($orderId);
        if ($order === null) {
            return 'Order not found.';
        }
        if (self::paidTotal($orderId) <= 0) {
            return 'There is no received payment to refund on this order.';
        }

        Database::transaction(static function () use ($orderId, $note): void {
            Database::query(
                "UPDATE payments SET status = 'refunded', paid_at = NULL WHERE order_id = ? AND status = 'paid'",
                [$orderId]
            );
            self::syncOrder($orderId);

            if ($note !== null && trim($note) !== '') {
                Database::update('orders', $orderId, ['admin_note' => mb_substr(trim($note), 0, 500)]);
            }

            Order::logEvent(
                $orderId,
                'payment',
                'paid',
                'refunded',
                $note !== null && trim($note) !== '' ? trim($note) : 'Payment refunded in full.',
                'admin'
            );
        });

        return null;
    }

    /**
     * Recalculate the cached payment status from the ledger.
     * `pending` also covers a partially received amount; the admin screen shows
     * the outstanding balance.
     */
    public static function syncOrder(int $orderId): string
    {
        $order = Order::byId($orderId);
        if ($order === null) {
            return 'pending';
        }

        $paid     = self::paidTotal($orderId);
        $refunded = self::refundedTotal($orderId);
        $total    = (float) $order['total'];

        if ($refunded > 0.009 && $paid <= 0.009) {
            $status = 'refunded';
        } elseif ($paid + 0.009 >= $total) {
            $status = 'paid';
        } else {
            $status = 'pending';
        }

        $data = ['payment_status' => $status];
        if ($status === 'paid') {
            $data['paid_at'] = $order['paid_at'] ?: date('Y-m-d H:i:s');
        } elseif ($status === 'refunded') {
            $data['paid_at'] = null;
        }

        Database::update('orders', $orderId, $data);

        return $status;
    }

    /** Bank details strictly for Soneri Bank. */
    public static function bankDetails(): array
    {
        return [
            'bank'         => 'Soneri Bank',
            'title'        => 'Lala Mobiles',
            'account'      => 'PK62SONE0000320013557234',
            'wallet'       => '',
            'instructions' => 'Transfer to our Soneri Bank account, upload the payment receipt/screenshot in checkout and share on WhatsApp for rapid confirmation.',
            'phone'        => '+92 300 5970504',
            'whatsapp'     => '+92 300 5970504',
            'whatsapp_raw' => '923005970504',
            'email'        => (string) SeoSettings::get('organization_email', 'info@lalamobiles.pk'),
        ];
    }

    /**
     * Human payment instructions for an order, used on the confirmation,
     * account order page and invoice.
     *
     * @return array{title:string, lines:array<int,string>}
     */
    public static function instructions(string $method = 'bank_transfer'): array
    {
        $bank = self::bankDetails();
        $phone = $bank['phone'];

        return [
            'title' => 'Soneri Bank Transfer Instructions',
            'lines' => [
                'Bank: ' . $bank['bank'],
                'Account Title: ' . $bank['title'],
                'Account Number / IBAN: ' . $bank['account'],
                'Upload your online payment screenshot when placing the order.',
                'Also send the payment screenshot along with your Order Reference on WhatsApp: ' . $phone,
                'Order dispatch begins immediately after payment verification.',
            ],
        ];
    }
}
