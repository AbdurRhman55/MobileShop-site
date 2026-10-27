-- Additive catalog and commerce support for the premium catalogue redesign.
-- Existing columns and records remain untouched; legacy fields stay available.
SET NAMES utf8mb4;

ALTER TABLE categories
    ADD COLUMN icon VARCHAR(64) NULL,
    ADD COLUMN banner_image VARCHAR(512) NULL,
    ADD COLUMN meta_title VARCHAR(180) NULL,
    ADD COLUMN meta_description VARCHAR(320) NULL,
    ADD COLUMN sort_order INT NOT NULL DEFAULT 0,
    ADD COLUMN is_active TINYINT(1) NOT NULL DEFAULT 1;

UPDATE categories SET is_active = IF(status = 'active', 1, 0), sort_order = position;

ALTER TABLE orders
    MODIFY COLUMN status ENUM('pending','processing','confirmed','shipped','delivered','cancelled','refunded') NOT NULL DEFAULT 'pending';

CREATE TABLE IF NOT EXISTS payments (
    id             INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    order_id       INT UNSIGNED NOT NULL,
    provider       VARCHAR(60) NOT NULL DEFAULT 'cash_on_delivery',
    transaction_id VARCHAR(120) NULL,
    amount         DECIMAL(12,2) NOT NULL,
    currency       CHAR(3) NOT NULL DEFAULT 'PKR',
    status         ENUM('pending','authorized','paid','failed','refunded') NOT NULL DEFAULT 'pending',
    paid_at        TIMESTAMP NULL,
    created_at     TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at     TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    KEY idx_payment_order (order_id),
    KEY idx_payment_status (status, created_at),
    CONSTRAINT fk_payment_order FOREIGN KEY (order_id) REFERENCES orders (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS customer_addresses (
    id          INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    customer_id INT UNSIGNED NOT NULL,
    label       VARCHAR(60) NOT NULL DEFAULT 'Home',
    recipient   VARCHAR(160) NOT NULL,
    phone       VARCHAR(40) NOT NULL,
    address     VARCHAR(512) NOT NULL,
    city        VARCHAR(120) NOT NULL,
    region      VARCHAR(120) NULL,
    postal_code VARCHAR(20) NULL,
    is_default  TINYINT(1) NOT NULL DEFAULT 0,
    created_at  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    KEY idx_customer_address (customer_id, is_default),
    CONSTRAINT fk_address_customer FOREIGN KEY (customer_id) REFERENCES customers (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
