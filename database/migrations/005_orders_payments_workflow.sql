-- Professional order intake, manual payment verification and invoice workflow.
--
-- Payments are never auto-marked as received. Every order starts with
-- payment_status = 'pending'; an administrator records the received payment
-- (cash collected by the courier, verified bank transfer, verified wallet
-- transfer) from the order screen.
--
-- `order_events` is an append-only audit trail shown to the customer and the
-- administrator.

ALTER TABLE orders
    ADD COLUMN payment_status ENUM('pending','paid','refunded','failed') NOT NULL DEFAULT 'pending' AFTER payment_method,
    ADD COLUMN payment_reference VARCHAR(120) NULL AFTER payment_status,
    ADD COLUMN payment_reported_at TIMESTAMP NULL AFTER payment_reference,
    ADD COLUMN paid_at TIMESTAMP NULL AFTER payment_reported_at,
    ADD COLUMN admin_note VARCHAR(500) NULL AFTER paid_at,
    ADD COLUMN stock_restored TINYINT(1) NOT NULL DEFAULT 0 AFTER admin_note,
    ADD COLUMN confirmed_at TIMESTAMP NULL AFTER stock_restored,
    ADD COLUMN shipped_at TIMESTAMP NULL AFTER confirmed_at,
    ADD COLUMN delivered_at TIMESTAMP NULL AFTER shipped_at,
    ADD COLUMN cancelled_at TIMESTAMP NULL AFTER delivered_at,
    ADD KEY idx_orders_payment_status (payment_status);

CREATE TABLE IF NOT EXISTS order_events (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    order_id INT UNSIGNED NOT NULL,
    event_type ENUM('status','payment','note','customer') NOT NULL,
    from_value VARCHAR(60) NULL,
    to_value VARCHAR(60) NULL,
    note VARCHAR(500) NULL,
    actor VARCHAR(40) NOT NULL DEFAULT 'system',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_order_events_order (order_id, created_at),
    CONSTRAINT fk_order_events_order FOREIGN KEY (order_id) REFERENCES orders (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Backfill existing demo orders so the new screens reflect their real ledger.
UPDATE orders o
SET o.payment_status = 'paid',
    o.paid_at = (SELECT MAX(p.paid_at) FROM payments p WHERE p.order_id = o.id AND p.status = 'paid')
WHERE EXISTS (SELECT 1 FROM payments p WHERE p.order_id = o.id AND p.status = 'paid');

UPDATE orders o
SET o.payment_status = 'refunded'
WHERE NOT EXISTS (SELECT 1 FROM payments p WHERE p.order_id = o.id AND p.status = 'paid')
  AND EXISTS (SELECT 1 FROM payments p WHERE p.order_id = o.id AND p.status = 'refunded');

-- Payment, invoice and strict-email configuration. Empty bank fields are shown
-- as "shared on confirmation" until the store owner fills them in.
INSERT INTO settings (setting_key, setting_value) VALUES
    ('payments_bank_name', ''),
    ('payments_account_title', 'Lala Mobiles'),
    ('payments_account_number', ''),
    ('payments_wallet_number', ''),
    ('payments_instructions', ''),
    ('invoice_terms', 'Payment is due as shown above. Devices carry the warranty stated on the product page and in the parcel. Returns are accepted within 7 days in original condition with all accessories.'),
    ('invoice_note', 'Thank you for shopping with Lala Mobiles. Attach this invoice to the shipment for the courier and the customer.'),
    ('email_dns_check', '1')
ON DUPLICATE KEY UPDATE setting_key = setting_key;
