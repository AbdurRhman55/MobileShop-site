<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Csrf;
use App\Core\Database;
use App\Core\Http;
use App\Core\HttpRedirect;
use App\Models\Cart;
use App\Models\Order;
use App\Models\Payment;

/**
 * Account-gated checkout.
 *
 * The cart is validated first (an empty cart always returns to /cart), then the
 * customer must be signed in. Orders are always created with a pending payment
 * row; the confirmation page never claims money has been received.
 */
final class CheckoutController extends BaseController
{
    public function index(): void
    {
        $items = Cart::items();
        if ($items === []) {
            HttpRedirect::to('/cart', 303);
        }

        $customer = $this->requireCustomer('/checkout');
        $old      = $this->prefill($customer);

        $this->renderCheckout($items, [], $old, $customer);
    }

    public function place(): void
    {
        Csrf::verifyRequest($this->request);

        $items = Cart::items();
        if ($items === []) {
            HttpRedirect::to('/cart', 303);
        }

        $customer = $this->requireCustomer('/checkout');

        $old = [
            'name'              => trim((string) $this->request->input('name', '')),
            'email'             => (string) $customer['email'],
            'phone'             => trim((string) $this->request->input('phone', '')),
            'address'           => trim((string) $this->request->input('address', '')),
            'city'              => trim((string) $this->request->input('city', '')),
            'payment_method'    => 'bank_transfer',
            'payment_reference' => trim((string) $this->request->input('payment_reference', '')),
        ];

        // Server-side validation — never trust the client for prices or totals.
        $errors = [];

        if (mb_strlen($old['name']) < 3 || mb_strlen($old['name']) > 160) {
            $errors[] = 'Please enter your full name (3–160 characters).';
        }
        if ($old['phone'] === '' || !preg_match('/^[0-9+\-\s()]{7,20}$/', $old['phone'])) {
            $errors[] = 'Please enter a valid phone number, for example 03XX-XXXXXXX.';
        }
        if (mb_strlen($old['address']) < 5 || mb_strlen($old['address']) > 512) {
            $errors[] = 'Please enter your complete delivery address.';
        }
        if (mb_strlen($old['city']) < 2 || mb_strlen($old['city']) > 120) {
            $errors[] = 'Please enter your city.';
        }

        // Validate mandatory online payment screenshot for Soneri Bank
        $screenshotPath = null;
        if (!isset($_FILES['payment_screenshot']) || $_FILES['payment_screenshot']['error'] === UPLOAD_ERR_NO_FILE) {
            $errors[] = 'Please upload your Soneri Bank online payment screenshot / receipt.';
        } elseif ($_FILES['payment_screenshot']['error'] !== UPLOAD_ERR_OK) {
            $errors[] = 'Failed to upload payment screenshot. Please try again with a valid image (under 10MB).';
        } else {
            $file = $_FILES['payment_screenshot'];
            $maxBytes = 10 * 1024 * 1024; // 10 MB
            if ($file['size'] > $maxBytes) {
                $errors[] = 'The payment screenshot is too large. Maximum size is 10MB.';
            } else {
                $ext = strtolower(pathinfo((string)$file['name'], PATHINFO_EXTENSION));
                $allowedExts = ['jpg', 'jpeg', 'png', 'webp', 'pdf'];
                if (!in_array($ext, $allowedExts, true)) {
                    $errors[] = 'Invalid file format. Please upload a JPG, PNG, WEBP or PDF receipt.';
                } else {
                    $finfo = finfo_open(FILEINFO_MIME_TYPE);
                    $mime = finfo_file($finfo, $file['tmp_name']);
                    finfo_close($finfo);
                    $allowedMimes = ['image/jpeg', 'image/png', 'image/webp', 'application/pdf'];
                    if (!in_array($mime, $allowedMimes, true)) {
                        $errors[] = 'Invalid file type. Please upload a genuine payment receipt screenshot or PDF.';
                    } else {
                        $uploadDir = BASE_PATH . '/public/uploads/payments';
                        if (!is_dir($uploadDir)) {
                            @mkdir($uploadDir, 0755, true);
                        }
                        $filename = 'proof_' . date('Ymd_His') . '_' . bin2hex(random_bytes(6)) . '.' . ($ext === 'jpeg' ? 'jpg' : $ext);
                        $targetPath = $uploadDir . '/' . $filename;
                        if (move_uploaded_file($file['tmp_name'], $targetPath)) {
                            $screenshotPath = '/uploads/payments/' . $filename;
                        } else {
                            $errors[] = 'Could not save the payment screenshot. Please try again.';
                        }
                    }
                }
            }
        }

        if ($errors !== []) {
            $this->renderCheckout($items, $errors, $old, $customer);
            return;
        }

        $order = Order::create([
            'customer_id' => (int) $customer['id'],
            'name'        => $old['name'],
            'email'       => (string) $customer['email'],
            'phone'       => $old['phone'],
            'address'     => $old['address'],
            'city'        => $old['city'],
        ], $items, 'bank_transfer', $screenshotPath, $old['payment_reference'] !== '' ? $old['payment_reference'] : null);

        // Send confirmation email with Soneri Bank details
        \App\Support\Mailer::sendOrderConfirmation($order, $items);

        Cart::clear();

        $_SESSION['order_just_placed'] = $order['reference'];

        HttpRedirect::to('/checkout/success/' . $order['reference'], 303);
    }

    public function success(string $ref): void
    {
        $order = Order::byReference($ref);
        if ($order === null) {
            throw new Http(404, 'Order not found.');
        }

        // Confirmation pages are private to the account that placed the order.
        $customerId = (int) ($_SESSION['customer_id'] ?? 0);
        if ($customerId < 1) {
            HttpRedirect::to(
                '/account/login?next=' . rawurlencode('/checkout/success/' . $order['reference']),
                303
            );
        }
        if ((int) $order['customer_id'] !== $customerId) {
            throw new Http(404, 'Order not found.');
        }

        $seo = $this->resolveMeta(
            'home',
            0,
            'Order Received — Lala Mobiles',
            'Your order has been received and is awaiting confirmation.',
            '/checkout',
            ['robots' => 'noindex,nofollow,noarchive']
        );

        $this->renderPage($seo, [], 'checkout-success', [
            'order'   => $order,
            'items'   => Order::items((int) $order['id']),
            'payment' => Payment::summary($order),
        ]);
    }

    /* ---------------------------------------------------------- helpers -- */

    private function renderCheckout(array $items, array $errors, array $old, array $customer): void
    {
        $seo = $this->resolveMeta(
            'home',
            0,
            'Checkout — Lala Mobiles',
            'Complete your order with Soneri Bank online transfer.',
            '/checkout',
            ['robots' => 'noindex,nofollow']
        );

        $this->renderPage($seo, [], 'checkout', [
            'items'    => $items,
            'subtotal' => Cart::subtotal(),
            'shipping' => Cart::shipping(),
            'total'    => Cart::subtotal() + Cart::shipping(),
            'errors'   => $errors,
            'old'      => $old,
            'customer' => $customer,
            'methods'  => Payment::methods(),
        ]);
    }

    /** Delivery details from the account and its most recent saved address. */
    private function prefill(array $customer): array
    {
        $old = [
            'name'           => (string) $customer['name'],
            'email'          => (string) $customer['email'],
            'phone'          => (string) ($customer['phone'] ?? ''),
            'address'        => '',
            'city'           => '',
            'payment_method' => 'bank_transfer',
        ];

        $address = Database::fetch(
            'SELECT * FROM customer_addresses WHERE customer_id = ?
             ORDER BY is_default DESC, created_at DESC LIMIT 1',
            [(int) $customer['id']]
        );

        if ($address !== null) {
            $old['name']    = (string) ($address['recipient'] ?: $old['name']);
            $old['phone']   = (string) ($address['phone'] ?: $old['phone']);
            $old['address'] = (string) $address['address'];
            $old['city']    = (string) $address['city'];
        }

        return $old;
    }

    private function requireCustomer(string $next): array
    {
        $id = (int) ($_SESSION['customer_id'] ?? 0);

        if ($id < 1) {
            HttpRedirect::to('/account/login?next=' . rawurlencode($next), 303);
        }

        $customer = Database::fetch(
            'SELECT id, name, email, phone FROM customers WHERE id = ?',
            [$id]
        );

        if ($customer === null) {
            unset($_SESSION['customer_id'], $_SESSION['customer_name']);
            HttpRedirect::to('/account/login?next=' . rawurlencode($next), 303);
        }

        return $customer;
    }
}
