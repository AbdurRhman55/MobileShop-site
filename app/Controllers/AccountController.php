<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Csrf;
use App\Core\Database;
use App\Core\Http;
use App\Core\HttpRedirect;
use App\Core\View;
use App\Models\Order;
use App\Models\Payment;
use App\Support\EmailRule;

/**
 * Customer accounts, order history, payment reporting and invoices.
 *
 * Checkout requires an account, so every storefront order has a verified email
 * owner and a customer_id. Registration does not sign the customer in: the
 * account is created, then the customer signs in explicitly.
 */
final class AccountController extends BaseController
{
    public function loginForm(): void
    {
        $next = $this->safeNext($this->request->query('next'));
        if (!empty($_SESSION['customer_id'])) {
            HttpRedirect::to($next !== '' ? $next : '/account', 303);
        }

        $this->render('login', null, $next, (string) $this->request->query('registered', '') === '1');
    }

    public function registerForm(): void
    {
        $next = $this->safeNext($this->request->query('next'));
        if (!empty($_SESSION['customer_id'])) {
            HttpRedirect::to($next !== '' ? $next : '/account', 303);
        }

        $this->render('register', null, $next, false);
    }

    public function login(): void
    {
        Csrf::verifyRequest($this->request);

        $next     = $this->safeNext($this->request->input('next'));
        $email    = mb_strtolower(trim((string) $this->request->input('email', '')));
        $password = (string) $this->request->input('password', '');

        $customer = filter_var($email, FILTER_VALIDATE_EMAIL)
            ? Database::fetch('SELECT * FROM customers WHERE email = ?', [$email])
            : null;

        if ($customer === null || !password_verify($password, (string) $customer['password_hash'])) {
            $this->render('login', 'Email or password was not recognized.', $next, false);
            return;
        }

        session_regenerate_id(true);
        $_SESSION['customer_id']   = (int) $customer['id'];
        $_SESSION['customer_name'] = (string) $customer['name'];

        HttpRedirect::to($next !== '' ? $next : '/account', 303);
    }

    public function register(): void
    {
        Csrf::verifyRequest($this->request);

        $next     = $this->safeNext($this->request->input('next'));
        $name     = trim((string) $this->request->input('name', ''));
        $email    = mb_strtolower(trim((string) $this->request->input('email', '')));
        $phone    = trim((string) $this->request->input('phone', ''));
        $password = (string) $this->request->input('password', '');
        $confirm  = (string) $this->request->input('password_confirmation', '');

        $error = null;

        if (mb_strlen($name) < 3 || mb_strlen($name) > 160) {
            $error = 'Enter your full name (3–160 characters).';
        } elseif (($emailError = EmailRule::error($email)) !== null) {
            $error = $emailError;
        } elseif ($phone !== '' && !preg_match('/^[0-9+\-\s()]{7,20}$/', $phone)) {
            $error = 'Enter a valid phone number, for example 03XX-XXXXXXX.';
        } elseif (strlen($password) < 10) {
            $error = 'Choose a password of at least 10 characters.';
        } elseif (!hash_equals($password, $confirm)) {
            $error = 'The two passwords do not match.';
        } elseif (Database::fetch('SELECT id FROM customers WHERE email = ?', [$email]) !== null) {
            $error = 'An account already uses that email. Sign in instead.';
        }

        if ($error !== null) {
            $this->render('register', $error, $next, false, [
                'name'  => $name,
                'email' => $email,
                'phone' => $phone,
            ]);
            return;
        }

        Database::insert('customers', [
            'name'          => $name,
            'email'         => $email,
            'password_hash' => password_hash($password, PASSWORD_DEFAULT),
            'phone'         => $phone !== '' ? $phone : null,
        ]);

        // Account created — the customer now signs in explicitly.
        $target = '/account/login?registered=1'
            . ($next !== '' ? '&next=' . rawurlencode($next) : '');

        HttpRedirect::to($target, 303);
    }

    public function profile(): void
    {
        $customer = $this->requireCustomer('/account');

        $orders = Database::fetchAll(
            "SELECT o.*,
                    COALESCE((SELECT SUM(p.amount) FROM payments p
                              WHERE p.order_id = o.id AND p.status = 'paid'), 0) AS paid_total
             FROM orders o
             WHERE o.customer_id = ?
             ORDER BY o.created_at DESC
             LIMIT 50",
            [(int) $customer['id']]
        );

        $addresses = Database::fetchAll(
            'SELECT * FROM customer_addresses WHERE customer_id = ? ORDER BY is_default DESC, created_at DESC',
            [(int) $customer['id']]
        );

        $this->renderPage(
            $this->resolveMeta(
                'home',
                0,
                'Your account — Lala Mobiles',
                'Manage your customer account and order history.',
                '/account',
                ['robots' => 'noindex,nofollow']
            ),
            [],
            'account',
            [
                'customer'     => $customer,
                'orders'       => $orders,
                'addresses'    => $addresses,
                'accountFlash' => $this->takeFlash(),
            ]
        );
    }

    /** Customer order detail: payment instructions, timeline and invoice. */
    public function showOrder(string $id): void
    {
        $orderId  = (int) $id;
        $customer = $this->requireCustomer('/account/orders/' . $orderId);
        $order    = $this->ownedOrder($orderId, (int) $customer['id']);

        $this->renderPage(
            $this->resolveMeta(
                'home',
                0,
                'Order ' . $order['reference'] . ' — Lala Mobiles',
                'Order status, payment instructions and invoice.',
                '/account/orders/' . $orderId,
                ['robots' => 'noindex,nofollow,noarchive']
            ),
            [],
            'account-order',
            [
                'customer' => $customer,
                'order'    => $order,
                'items'    => Order::items($orderId),
                'events'   => Order::events($orderId),
                'payment'  => Payment::summary($order),
                'flash'    => $this->takeFlash(),
            ]
        );
    }

    /** Customer submits the reference of a bank / wallet transfer for review. */
    public function reportPayment(string $id): void
    {
        Csrf::verifyRequest($this->request);

        $orderId  = (int) $id;
        $customer = $this->requireCustomer('/account/orders/' . $orderId);
        $order    = $this->ownedOrder($orderId, (int) $customer['id']);

        $reference = trim((string) $this->request->input('reference', ''));
        $amountRaw = str_replace([',', ' '], '', (string) $this->request->input('amount', ''));
        $amount    = is_numeric($amountRaw) ? (float) $amountRaw : 0.0;
        $note      = trim((string) $this->request->input('note', ''));

        $error = Payment::reportByCustomer($orderId, $reference, $amount, $note !== '' ? $note : null);

        if ($error !== null) {
            $this->flash($error, 'error');
            HttpRedirect::to('/account/orders/' . $orderId . '#payment', 303);
        }

        $this->flash(
            'Thanks — your reference was submitted. The order stays “Payment pending” until our team verifies the transfer.',
            'success'
        );
        HttpRedirect::to('/account/orders/' . $orderId . '#payment', 303);
    }

    /** Printable A4 invoice for the signed-in customer. */
    public function invoice(string $id): void
    {
        $orderId  = (int) $id;
        $customer = $this->requireCustomer('/account/orders/' . $orderId . '/invoice');
        $order    = $this->ownedOrder($orderId, (int) $customer['id']);

        $this->outputInvoice($order, '/account/orders/' . $orderId);
    }

    public function saveAddress(): void
    {
        Csrf::verifyRequest($this->request);
        $customer  = $this->requireCustomer('/account');
        $recipient = trim((string) $this->request->input('recipient', ''));
        $phone     = trim((string) $this->request->input('phone', ''));
        $address   = trim((string) $this->request->input('address', ''));
        $city      = trim((string) $this->request->input('city', ''));

        if ($recipient === '' || $phone === '' || $address === '' || $city === '') {
            HttpRedirect::to('/account?address=invalid', 303);
        }

        Database::insert('customer_addresses', [
            'customer_id' => (int) $customer['id'],
            'label'       => trim((string) $this->request->input('label', 'Home')) ?: 'Home',
            'recipient'   => $recipient,
            'phone'       => $phone,
            'address'     => $address,
            'city'        => $city,
            'region'      => trim((string) $this->request->input('region', '')) ?: null,
            'postal_code' => trim((string) $this->request->input('postal_code', '')) ?: null,
            'is_default'  => 0,
        ]);

        HttpRedirect::to('/account?address=saved', 303);
    }

    public function logout(): void
    {
        Csrf::verifyRequest($this->request);
        unset($_SESSION['customer_id'], $_SESSION['customer_name']);
        session_regenerate_id(true);
        HttpRedirect::to('/account/login', 303);
    }

    /* ---------------------------------------------------------- helpers -- */

    private function render(
        string $mode,
        ?string $error,
        string $next = '',
        bool $registered = false,
        array $old = []
    ): void {
        $title = $mode === 'register' ? 'Create your account' : 'Sign in to your account';

        $this->renderPage(
            $this->resolveMeta(
                'home',
                0,
                $title . ' — Lala Mobiles',
                'Customer account access.',
                '/account/' . $mode,
                ['robots' => 'noindex,nofollow']
            ),
            [],
            'account-auth',
            [
                'mode'       => $mode,
                'error'      => $error,
                'next'       => $next,
                'registered' => $registered,
                'old'        => $old,
            ]
        );
    }

    private function requireCustomer(?string $next = null): array
    {
        $id = (int) ($_SESSION['customer_id'] ?? 0);

        if ($id < 1) {
            $target = $next ?? $this->currentPath();
            HttpRedirect::to('/account/login?next=' . rawurlencode($target), 303);
        }

        $customer = Database::fetch(
            'SELECT id, name, email, phone, created_at FROM customers WHERE id = ?',
            [$id]
        );

        if ($customer === null) {
            unset($_SESSION['customer_id'], $_SESSION['customer_name']);
            $target = $next ?? $this->currentPath();
            HttpRedirect::to('/account/login?next=' . rawurlencode($target), 303);
        }

        return $customer;
    }

    /** Orders are private: unknown ids and other customers' orders are both 404. */
    private function ownedOrder(int $orderId, int $customerId): array
    {
        $order = Database::fetch(
            'SELECT * FROM orders WHERE id = ? AND customer_id = ?',
            [$orderId, $customerId]
        );

        if ($order === null) {
            throw new Http(404, 'That order could not be found in your account.');
        }

        return $order;
    }

    private function outputInvoice(array $order, string $backUrl): void
    {
        if (!headers_sent()) {
            header('Content-Type: text/html; charset=utf-8');
            header('X-Robots-Tag: noindex, nofollow');
        }

        echo View::render('invoice', [
            'order'   => $order,
            'items'   => Order::items((int) $order['id']),
            'summary' => Payment::summary($order),
            'backUrl' => $backUrl,
        ], null);
    }

    /** Only same-site paths are kept; external URLs are discarded. */
    private function safeNext(mixed $next): string
    {
        $next = trim((string) $next);
        if ($next === '') {
            return '';
        }

        try {
            $safe = HttpRedirect::safeTarget($next);
        } catch (\Throwable) {
            return '';
        }

        // Customers have no business being sent into the admin area.
        if (str_starts_with($safe, '/admin')) {
            return '';
        }

        return $safe;
    }

    private function currentPath(): string
    {
        $path = (string) ($this->request->server['REQUEST_URI'] ?? '/account');
        $path = parse_url($path, PHP_URL_PATH) ?: '/account';
        return str_starts_with($path, '/') ? $path : '/account';
    }

    private function flash(string $message, string $type = 'success'): void
    {
        $_SESSION['account_flash'] = ['message' => $message, 'type' => $type];
    }

    private function takeFlash(): ?array
    {
        $flash = $_SESSION['account_flash'] ?? null;
        unset($_SESSION['account_flash']);
        return $flash;
    }
}
