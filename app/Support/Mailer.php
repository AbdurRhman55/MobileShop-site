<?php

declare(strict_types=1);

namespace App\Support;

use App\Models\Payment;
use App\Seo\SeoSettings;

final class Mailer
{
    /**
     * Send order confirmation email to the customer.
     */
    public static function sendOrderConfirmation(array $order, array $items): bool
    {
        $to = trim((string) ($order['email'] ?? ''));
        if ($to === '' || !filter_var($to, FILTER_VALIDATE_EMAIL)) {
            return false;
        }

        $bank = Payment::bankDetails();
        $siteName = (string) SeoSettings::get('organization_name', 'Lala Mobiles');
        $siteUrl = (string) SeoSettings::get('canonical_domain', 'https://www.lalamobiles.pk');
        $fromEmail = (string) SeoSettings::get('organization_email', 'orders@lalamobiles.pk');
        $phone = '+92 300 5970504';

        $subject = 'Order Confirmation — #' . $order['reference'] . ' — ' . $siteName;

        $itemsHtml = '';
        $itemsText = '';
        foreach ($items as $item) {
            $name = (string) ($item['name'] ?? $item['item_name'] ?? 'Product');
            $qty = (int) ($item['quantity'] ?? 1);
            $price = (float) ($item['unit_price'] ?? 0);
            $lineTotal = $price * $qty;
            
            $itemsHtml .= sprintf(
                '<tr>
                    <td style="padding:10px 12px;border-bottom:1px solid #e2e8f0;font-size:14px;color:#1e293b;">%s</td>
                    <td style="padding:10px 12px;border-bottom:1px solid #e2e8f0;font-size:14px;color:#64748b;text-align:center;">%d</td>
                    <td style="padding:10px 12px;border-bottom:1px solid #e2e8f0;font-size:14px;color:#0f172a;text-align:right;font-weight:600;">Rs %s</td>
                </tr>',
                htmlspecialchars($name, ENT_QUOTES, 'UTF-8'),
                $qty,
                number_format($lineTotal, 0)
            );

            $itemsText .= sprintf("- %s x %d = Rs %s\n", $name, $qty, number_format($lineTotal, 0));
        }

        $waMsg = rawurlencode(
            "Salam Lala Mobiles! I have placed Order #{$order['reference']} for Rs " . number_format((float)$order['total'], 0) . ". Customer: {$order['customer_name']}. Please verify my payment screenshot."
        );
        $waLink = "https://wa.me/923005970504?text={$waMsg}";

        $htmlBody = <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<style>
body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; background-color: #f8fafc; color: #1e293b; margin: 0; padding: 24px; }
.card { max-width: 620px; margin: 0 auto; background: #ffffff; border-radius: 16px; border: 1px solid #e2e8f0; overflow: hidden; box-shadow: 0 4px 16px rgba(0,0,0,0.06); }
.header { background: #0b132b; color: #ffffff; padding: 32px 28px; text-align: center; }
.header h1 { margin: 0; font-size: 24px; font-weight: 800; }
.header p { margin: 6px 0 0; color: #94a3b8; font-size: 14px; }
.content { padding: 28px; }
.alert-box { background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 12px; padding: 18px; margin-bottom: 24px; }
.bank-box { background: #eff6ff; border: 2px dashed #3b82f6; border-radius: 12px; padding: 20px; margin: 24px 0; }
.table { width: 100%; border-collapse: collapse; margin-top: 14px; }
.btn-wa { display: inline-block; background: #25d366; color: #ffffff; font-weight: bold; text-decoration: none; padding: 14px 28px; border-radius: 9999px; font-size: 15px; margin-top: 12px; }
.footer { background: #f1f5f9; padding: 20px 28px; text-align: center; font-size: 12px; color: #64748b; }
</style>
</head>
<body>
<div class="card">
    <div class="header">
        <img src="{$siteUrl}/img/logo.png" alt="{$siteName}" style="height:70px; width:auto; margin-bottom:10px; display:inline-block;">
        <h1 style="margin:4px 0 0; font-size:22px; font-weight:800; letter-spacing:-0.5px;">{$siteName}</h1>
        <p style="margin:4px 0 0; color:#94a3b8; font-size:13px; text-transform:uppercase; letter-spacing:1px;">Premium Mobile Solutions</p>
    </div>
    <div class="content">
        <div class="alert-box">
            <h2 style="margin:0 0 6px;font-size:17px;color:#166534;">🎉 Order #{$order['reference']} Received!</h2>
            <p style="margin:0;font-size:14px;color:#15803d;line-height:1.5;">Dear {$order['customer_name']}, thank you for ordering with {$siteName}. Your order is pending payment verification.</p>
        </div>

        <h3 style="margin:20px 0 8px;font-size:16px;">Order Summary</h3>
        <table class="table">
            <thead>
                <tr style="background:#f8fafc;">
                    <th style="padding:10px 12px;text-align:left;font-size:12px;color:#64748b;text-transform:uppercase;">Item</th>
                    <th style="padding:10px 12px;text-align:center;font-size:12px;color:#64748b;text-transform:uppercase;">Qty</th>
                    <th style="padding:10px 12px;text-align:right;font-size:12px;color:#64748b;text-transform:uppercase;">Total</th>
                </tr>
            </thead>
            <tbody>
                {$itemsHtml}
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="2" style="padding:12px;font-weight:bold;font-size:15px;border-top:2px solid #e2e8f0;">Total Amount</td>
                    <td style="padding:12px;font-weight:800;font-size:18px;color:#0b132b;text-align:right;border-top:2px solid #e2e8f0;">Rs 
HTML
        . number_format((float)$order['total'], 0) . <<<HTML
                    </td>
                </tr>
            </tfoot>
        </table>

        <div class="bank-box">
            <h3 style="margin:0 0 10px;font-size:16px;color:#1d4ed8;">💳 Official Bank Payment Details</h3>
            <p style="margin:0 0 8px;font-size:14px;"><strong>Bank:</strong> Soneri Bank</p>
            <p style="margin:0 0 8px;font-size:14px;"><strong>Account Name:</strong> Lala Mobiles</p>
            <p style="margin:0 0 12px;font-size:15px;font-family:monospace;background:#ffffff;padding:8px 12px;border-radius:6px;border:1px solid #cbd5e1;display:inline-block;"><strong>Account / IBAN:</strong> PK62SONE0000320013557234</p>
            <p style="margin:8px 0 0;font-size:13.5px;color:#334155;line-height:1.5;">
                Please transfer <strong>Rs 
HTML
        . number_format((float)$order['total'], 0) . <<<HTML
</strong> to the above Soneri Bank account. If you haven't uploaded your payment screenshot, please send it to us directly on WhatsApp for priority processing.
            </p>
            <div style="text-align:center;margin-top:16px;">
                <a href="{$waLink}" class="btn-wa">📲 Send Screenshot on WhatsApp (+92 300 5970504)</a>
            </div>
        </div>

        <h3 style="margin:24px 0 8px;font-size:15px;">Delivery Details</h3>
        <p style="margin:0;font-size:13.5px;color:#475569;line-height:1.6;">
            <strong>Recipient:</strong> {$order['customer_name']}<br>
            <strong>Phone:</strong> {$order['phone']}<br>
            <strong>Address:</strong> {$order['address']}, {$order['city']}
        </p>
    </div>

    <div class="footer">
        <p style="margin:0 0 6px;">Need help? WhatsApp / Call us at <strong>{$phone}</strong></p>
        <p style="margin:0;">&copy; {$siteName}. All rights reserved.</p>
    </div>
</div>
</body>
</html>
HTML;

        // Plain text version
        $textBody = "Order Confirmation — #{$order['reference']}\n"
            . "Dear {$order['customer_name']},\n\n"
            . "Thank you for placing order #{$order['reference']} with {$siteName}.\n\n"
            . "Total Amount: Rs " . number_format((float)$order['total'], 0) . "\n\n"
            . "Items:\n" . $itemsText . "\n"
            . "PAYMENT DETAILS (SONERI BANK ONLY):\n"
            . "Bank Name: Soneri Bank\n"
            . "Account Title: Lala Mobiles\n"
            . "Account Number / IBAN: PK62SONE0000320013557234\n\n"
            . "Please send your payment screenshot on WhatsApp at +92 300 5970504 for quick verification.\n\n"
            . "Delivery Address: {$order['address']}, {$order['city']}\n"
            . "Phone: {$order['phone']}\n\n"
            . "{$siteName} Support: +92 300 5970504\n";

        // Try sending via mail()
        $boundary = 'bnd_' . bin2hex(random_bytes(8));
        $headers = [
            'MIME-Version: 1.0',
            "From: {$siteName} <{$fromEmail}>",
            "Reply-To: {$fromEmail}",
            "Content-Type: multipart/alternative; boundary=\"{$boundary}\"",
            'X-Mailer: PHP/' . phpversion(),
        ];

        $multipartBody = "--{$boundary}\r\n"
            . "Content-Type: text/plain; charset=UTF-8\r\n"
            . "Content-Transfer-Encoding: 8bit\r\n\r\n"
            . $textBody . "\r\n\r\n"
            . "--{$boundary}\r\n"
            . "Content-Type: text/html; charset=UTF-8\r\n"
            . "Content-Transfer-Encoding: 8bit\r\n\r\n"
            . $htmlBody . "\r\n\r\n"
            . "--{$boundary}--";

        $sent = false;
        try {
            $sent = @mail($to, $subject, $multipartBody, implode("\r\n", $headers));
        } catch (\Throwable) {
            $sent = false;
        }

        // Always log to mail log for auditing/debugging
        $logDir = dirname(__DIR__, 2) . '/storage/logs';
        if (!is_dir($logDir)) {
            @mkdir($logDir, 0755, true);
        }
        $logEntry = sprintf(
            "[%s] Order %s Confirmation to %s | Status: %s\n",
            date('Y-m-d H:i:s'),
            $order['reference'],
            $to,
            $sent ? 'SENT' : 'LOGGED (Mail server unavailable)'
        );
        @file_put_contents($logDir . '/mail.log', $logEntry, FILE_APPEND);

        return $sent;
    }
}
