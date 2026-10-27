<?php
/**
 * Admin — payment & invoice settings.
 *
 * @var array $settings
 */
use App\Core\Csrf;
?>
<div class="flex flex-wrap items-center justify-between gap-3">
    <div>
        <p class="text-[12px] font-extrabold uppercase tracking-[0.18em] text-electric-600">Orders</p>
        <h2 class="mt-1 text-[24px] font-extrabold tracking-tight text-ink">Payment &amp; invoice settings</h2>
        <p class="mt-1 text-[13px] text-ink-muted">Bank/wallet details used on the order page, confirmation email area and printable invoices. Empty fields fall back to safe text.</p>
    </div>
    <div class="flex gap-2">
        <a href="/admin/orders" class="btn btn-ghost btn-sm">Back to orders</a>
    </div>
</div>

<?php if (!empty($flash)): ?>
<div class="mt-5 flex gap-2.5 rounded-xl px-4 py-3 text-[13px] font-semibold <?= ($flash['type'] ?? 'success') === 'error' ? 'border border-danger-200 bg-danger-50 text-danger-800' : 'border border-jade-200 bg-jade-50 text-jade-800' ?>">
    <?= e($flash['message']) ?>
</div>
<?php endif; ?>

<form method="post" action="/admin/orders/payment-settings" class="mt-6 grid gap-5 lg:grid-cols-2">
    <?= Csrf::field() ?>

    <section class="card p-6 lg:col-span-2">
        <h3 class="text-[15px] font-extrabold text-ink">Bank transfer details</h3>
        <p class="mt-1 text-[12.5px] text-ink-muted">Shown when the customer chooses “Bank Transfer”.</p>
        <div class="mt-4 grid gap-4 md:grid-cols-2">
            <div>
                <label class="mb-1.5 block text-[12.5px] font-bold text-ink">Bank name</label>
                <input class="input" name="payments_bank_name" value="<?= e((string)($settings['payments_bank_name'] ?? '')) ?>" placeholder="Meezan Bank / HBL">
            </div>
            <div>
                <label class="mb-1.5 block text-[12.5px] font-bold text-ink">Account title</label>
                <input class="input" name="payments_account_title" value="<?= e((string)($settings['payments_account_title'] ?? 'Lala Mobiles')) ?>">
            </div>
            <div class="md:col-span-2">
                <label class="mb-1.5 block text-[12.5px] font-bold text-ink">Account number / IBAN</label>
                <input class="input" name="payments_account_number" value="<?= e((string)($settings['payments_account_number'] ?? '')) ?>" placeholder="PK00 XXXX 0000 0000 0000 0000">
            </div>
        </div>
    </section>

    <section class="card p-6">
        <h3 class="text-[15px] font-extrabold text-ink">Easypaisa / JazzCash</h3>
        <div class="mt-4">
            <label class="mb-1.5 block text-[12.5px] font-bold text-ink">Wallet number (mobile)</label>
            <input class="input" name="payments_wallet_number" value="<?= e((string)($settings['payments_wallet_number'] ?? '')) ?>" placeholder="+92-300-0000000">
        </div>
        <label class="mt-4 flex items-start gap-2 text-[12.5px] text-ink">
            <input type="checkbox" name="email_dns_check" value="1" class="mt-0.5 rounded border-line" <?= ($settings['email_dns_check'] ?? '1') === '1' ? 'checked' : '' ?>>
            <span><span class="font-bold">Strict email domain check</span><br><span class="text-ink-muted">Block disposable/example emails and require MX/A records on registration and newsletter (skipped automatically if DNS is unavailable).</span></span>
        </label>
    </section>

    <section class="card p-6">
        <h3 class="text-[15px] font-extrabold text-ink">Payment instructions (customer-facing)</h3>
        <div class="mt-4">
            <label class="mb-1.5 block text-[12.5px] font-bold text-ink">Extra instructions</label>
            <textarea class="input" name="payments_instructions" rows="4" placeholder="Office hours for transfer verification, etc."><?= e((string)($settings['payments_instructions'] ?? '')) ?></textarea>
        </div>
    </section>

    <section class="card p-6 lg:col-span-2">
        <h3 class="text-[15px] font-extrabold text-ink">Invoice</h3>
        <div class="mt-4 grid gap-4 md:grid-cols-2">
            <div>
                <label class="mb-1.5 block text-[12.5px] font-bold text-ink">Invoice terms &amp; conditions</label>
                <textarea class="input" name="invoice_terms" rows="6"><?= e((string)($settings['invoice_terms'] ?? '')) ?></textarea>
            </div>
            <div>
                <label class="mb-1.5 block text-[12.5px] font-bold text-ink">Invoice note (appears at the bottom)</label>
                <textarea class="input" name="invoice_note" rows="6"><?= e((string)($settings['invoice_note'] ?? '')) ?></textarea>
            </div>
        </div>
        <p class="mt-3 text-[11.5px] text-ink-muted">Company name, address, phone and email come from <a href="/admin/seo/settings" class="font-semibold text-electric-700 hover:underline">SEO &amp; organisation settings</a>.</p>
    </section>

    <div class="lg:col-span-2 flex justify-end">
        <button class="btn btn-primary">Save settings</button>
    </div>
</form>
