-- ===========================================================================
-- Lala Mobiles — UI/UX redesign migration
-- Adds database-driven promotional, personalisation and engagement tables.
-- Idempotent: every table uses IF NOT EXISTS so it can be re-applied safely.
--
-- Apply with:  php bin/migrate.php
-- ===========================================================================

SET NAMES utf8mb4;

-- ---------------------------------------------------------------------------
-- Hero slides — homepage carousel. Order + active flag control display;
-- start/end dates schedule a slide. Never show an out-of-window slide.
-- ---------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS hero_slides (
    id           INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    title        VARCHAR(180) NOT NULL,
    subtitle     VARCHAR(320) NULL,
    image        VARCHAR(512) NULL,          -- desktop image
    image_mobile VARCHAR(512) NULL,          -- mobile image (falls back to image)
    badge        VARCHAR(80) NULL,           -- e.g. "New Arrival"
    cta_label    VARCHAR(60) NULL,
    cta_url      VARCHAR(512) NULL,
    theme        ENUM('tech','navy','flame','jade','custom') NOT NULL DEFAULT 'tech',
    overlay      TINYINT(1) NOT NULL DEFAULT 1,
    position     INT NOT NULL DEFAULT 0,
    is_active    TINYINT(1) NOT NULL DEFAULT 1,
    starts_at    TIMESTAMP NULL,
    ends_at      TIMESTAMP NULL,
    created_at   TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at   TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    KEY idx_hero_active (is_active, position)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------------
-- Flash sales — scheduled campaigns. ends_at drives the countdown server-side;
-- an expired campaign simply stops being active (never shown as live).
-- ---------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS flash_sales (
    id           INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    title        VARCHAR(180) NOT NULL,
    subtitle     VARCHAR(320) NULL,
    theme        ENUM('flame','violet','electric','custom') NOT NULL DEFAULT 'flame',
    starts_at    TIMESTAMP NULL,
    ends_at      TIMESTAMP NULL,             -- NULL = no fixed end (never shown as countdown)
    is_active    TINYINT(1) NOT NULL DEFAULT 1,
    created_at   TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at   TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    KEY idx_flash_window (is_active, starts_at, ends_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Explicit product picks for a campaign. Campaign price overrides product sale
-- price only while the campaign is live; the reference price stays the product's
-- normal price so any "discount" shown is genuine.
CREATE TABLE IF NOT EXISTS flash_sale_items (
    id            INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    flash_sale_id INT UNSIGNED NOT NULL,
    product_id    INT UNSIGNED NOT NULL,
    campaign_price DECIMAL(12,2) NULL,       -- optional fixed campaign price
    discount_pct  TINYINT UNSIGNED NULL,     -- or a percentage off the normal price
    position      INT NOT NULL DEFAULT 0,
    UNIQUE KEY uq_flash_item (flash_sale_id, product_id),
    CONSTRAINT fk_flash_item_sale FOREIGN KEY (flash_sale_id) REFERENCES flash_sales (id) ON DELETE CASCADE,
    CONSTRAINT fk_flash_item_product FOREIGN KEY (product_id) REFERENCES products (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------------
-- Promo banners — configurable homepage / section campaign tiles.
-- ---------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS promo_banners (
    id          INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    title       VARCHAR(160) NOT NULL,
    description VARCHAR(320) NULL,
    image       VARCHAR(512) NULL,
    url         VARCHAR(512) NULL,
    theme       ENUM('navy','electric','violet','flame','jade','custom') NOT NULL DEFAULT 'electric',
    location    VARCHAR(40) NOT NULL DEFAULT 'home',   -- home, shop, product …
    position    INT NOT NULL DEFAULT 0,
    is_active   TINYINT(1) NOT NULL DEFAULT 1,
    starts_at   TIMESTAMP NULL,
    ends_at     TIMESTAMP NULL,
    created_at  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    KEY idx_banner_loc (location, is_active, position)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------------
-- Trust indicators — the reassurance row under the hero. Editable in admin.
-- ---------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS trust_indicators (
    id         INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    label      VARCHAR(120) NOT NULL,
    detail     VARCHAR(220) NULL,
    icon       VARCHAR(40) NOT NULL DEFAULT 'check',   -- named icon rendered inline
    position   INT NOT NULL DEFAULT 0,
    is_active  TINYINT(1) NOT NULL DEFAULT 1,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    KEY idx_trust_active (is_active, position)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------------
-- Homepage sections — enable/disable + reorder sections without touching
-- templates. A missing row means the section uses its built-in default order.
-- ---------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS homepage_sections (
    id         INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    section_key VARCHAR(48) NOT NULL,        -- hero, categories, flash, bestsellers …
    title      VARCHAR(160) NULL,            -- optional title override
    subtitle   VARCHAR(320) NULL,
    position   INT NOT NULL DEFAULT 0,
    is_active  TINYINT(1) NOT NULL DEFAULT 1,
    UNIQUE KEY uq_section_key (section_key)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------------
-- Customers + sessions — optional account layer on top of guest checkout.
-- ---------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS customers (
    id            INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    name          VARCHAR(160) NOT NULL,
    email         VARCHAR(190) NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    phone         VARCHAR(40) NULL,
    created_at    TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at    TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_customer_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS customer_sessions (
    id          CHAR(64) NOT NULL PRIMARY KEY,
    customer_id INT UNSIGNED NOT NULL,
    expires_at  TIMESTAMP NOT NULL,
    created_at  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    KEY idx_csession_customer (customer_id),
    CONSTRAINT fk_csession_customer FOREIGN KEY (customer_id) REFERENCES customers (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------------
-- Coupons — validated server-side at checkout. Never trust the client.
-- ---------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS coupons (
    id             INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    code           VARCHAR(40) NOT NULL,
    type           ENUM('fixed','percent') NOT NULL,
    value          DECIMAL(12,2) NOT NULL,
    min_subtotal   DECIMAL(12,2) NOT NULL DEFAULT 0,
    max_uses       INT UNSIGNED NULL,
    used_count     INT UNSIGNED NOT NULL DEFAULT 0,
    is_active      TINYINT(1) NOT NULL DEFAULT 1,
    starts_at      TIMESTAMP NULL,
    ends_at        TIMESTAMP NULL,
    created_at     TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_coupon_code (code)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------------
-- Newsletter subscribers — double-list-friendly; consent + status tracked.
-- ---------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS newsletter_subscribers (
    id           INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    email        VARCHAR(190) NOT NULL,
    status       ENUM('pending','subscribed','unsubscribed') NOT NULL DEFAULT 'subscribed',
    consented_at TIMESTAMP NULL,
    created_at   TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_newsletter_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------------
-- Repair bookings — replaces the old "call us only" flow with a real form.
-- ---------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS repair_bookings (
    id            INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    reference     VARCHAR(32) NOT NULL,
    name          VARCHAR(160) NOT NULL,
    phone         VARCHAR(40) NOT NULL,
    email         VARCHAR(190) NULL,
    device        VARCHAR(120) NOT NULL,
    service       VARCHAR(160) NOT NULL,
    notes         TEXT NULL,
    status        ENUM('new','contacted','scheduled','completed','cancelled') NOT NULL DEFAULT 'new',
    created_at    TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_booking_reference (reference)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------------
-- Wishlist — keyed by an anonymous browser token (cookie) so it works with no
-- account, and is not exposed to other users. No PII, no cross-user leakage.
-- ---------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS wishlists (
    id         INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    token      CHAR(64) NOT NULL,
    product_id INT UNSIGNED NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_wishlist (token, product_id),
    KEY idx_wishlist_token (token),
    CONSTRAINT fk_wishlist_product FOREIGN KEY (product_id) REFERENCES products (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------------
-- Recently viewed — per-token, capped in application code.
-- ---------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS recently_viewed (
    id         INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    token      CHAR(64) NOT NULL,
    product_id INT UNSIGNED NOT NULL,
    viewed_at  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_recent (token, product_id),
    KEY idx_recent_token (token, viewed_at),
    CONSTRAINT fk_recent_product FOREIGN KEY (product_id) REFERENCES products (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
