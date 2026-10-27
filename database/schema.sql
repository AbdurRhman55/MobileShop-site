-- Lala Mobiles — core schema
-- Engine: InnoDB, charset: utf8mb4 (full Unicode incl. Urdu text in content).

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ---------------------------------------------------------------------------
-- Schema migrations bookkeeping
-- ---------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS schema_migrations (
    version     VARCHAR(64) NOT NULL PRIMARY KEY,
    applied_at  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------------
-- Key/value settings: global SEO defaults, canonical domain, organization
-- info, social defaults, verification codes, robots configuration, feed switch.
-- Never store secrets that must not be readable by the app; verification
-- codes are public-by-design tokens.
-- ---------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS settings (
    setting_key    VARCHAR(128) NOT NULL PRIMARY KEY,
    setting_value  TEXT NULL,
    updated_at     TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------------
-- SEO metadata, one row per entity. Entities without a row fall back to
-- auto-generated defaults from the entity's own fields.
-- ---------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS seo_meta (
    id                  INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    entity_type         ENUM('home','product','category','brand','blog','page','filter') NOT NULL,
    entity_id           INT UNSIGNED NOT NULL DEFAULT 0,
    seo_title           VARCHAR(180) NULL,
    meta_description    VARCHAR(320) NULL,
    canonical_path      VARCHAR(512) NULL,       -- NULL = auto from entity URL; must be a site path
    robots              VARCHAR(64)  NULL,       -- NULL = default (index,follow); e.g. "noindex,follow"
    og_title            VARCHAR(180) NULL,
    og_description      VARCHAR(320) NULL,
    og_image            VARCHAR(512) NULL,       -- path or absolute URL
    og_type             VARCHAR(32)  NULL,       -- website | product | article | ...
    twitter_card        ENUM('summary','summary_large_image') NULL,
    twitter_title       VARCHAR(180) NULL,
    twitter_description VARCHAR(320) NULL,
    twitter_image       VARCHAR(512) NULL,
    custom_head         TEXT NULL,               -- advanced: sanitized <meta>/<link> markup only
    updated_at          TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_seo_entity (entity_type, entity_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------------
-- Brands
-- ---------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS brands (
    id           INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    slug         VARCHAR(160) NOT NULL,
    name         VARCHAR(160) NOT NULL,
    website      VARCHAR(255) NULL,
    logo         VARCHAR(512) NULL,
    intro        TEXT NULL,                       -- unique brand landing content
    status       ENUM('active','hidden') NOT NULL DEFAULT 'active',
    created_at   TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at   TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_brand_slug (slug)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------------
-- Categories (hierarchical)
-- ---------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS categories (
    id           INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    parent_id    INT UNSIGNED NULL,
    slug         VARCHAR(160) NOT NULL,
    name         VARCHAR(160) NOT NULL,
    intro        TEXT NULL,                       -- unique category landing content
    image        VARCHAR(512) NULL,
    position     INT NOT NULL DEFAULT 0,
    status       ENUM('active','hidden') NOT NULL DEFAULT 'active',
    created_at   TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at   TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_category_slug (slug),
    KEY idx_category_parent (parent_id),
    CONSTRAINT fk_category_parent FOREIGN KEY (parent_id) REFERENCES categories (id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------------
-- Products
-- ---------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS products (
    id              INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    slug            VARCHAR(190) NOT NULL,
    name            VARCHAR(255) NOT NULL,
    brand_id        INT UNSIGNED NULL,
    category_id     INT UNSIGNED NULL,
    short_desc      VARCHAR(512) NULL,
    description     MEDIUMTEXT NULL,              -- rich, crawlable HTML (sanitized)
    `condition`       ENUM('new','used','refurbished') NOT NULL DEFAULT 'new',
    price           DECIMAL(12,2) NOT NULL,
    sale_price      DECIMAL(12,2) NULL,
    stock           INT NOT NULL DEFAULT 0,
    sku             VARCHAR(64) NULL,
    gtin            VARCHAR(64) NULL,             -- only when genuinely known
    mpn             VARCHAR(128) NULL,            -- only when genuinely known
    warranty        VARCHAR(128) NULL,
    main_image      VARCHAR(512) NULL,
    image_alt       VARCHAR(255) NULL,            -- product-specific alt text
    rating_avg      DECIMAL(3,2) NOT NULL DEFAULT 0,  -- maintained by triggers/approval
    rating_count    INT NOT NULL DEFAULT 0,
    featured        TINYINT(1) NOT NULL DEFAULT 0,
    -- lifecycle: active = sellable; discontinued = page stays, offer removed;
    -- deleted = page removed (301 to replacement if set, else 410)
    status          ENUM('active','discontinued','deleted') NOT NULL DEFAULT 'active',
    redirect_to_id  INT UNSIGNED NULL,            -- replacement product when deleted
    published_at    TIMESTAMP NULL,
    created_at      TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at      TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_product_slug (slug),
    KEY idx_product_brand (brand_id),
    KEY idx_product_category (category_id),
    KEY idx_product_status (status),
    KEY idx_product_updated (updated_at),
    CONSTRAINT fk_product_brand FOREIGN KEY (brand_id) REFERENCES brands (id) ON DELETE SET NULL,
    CONSTRAINT fk_product_category FOREIGN KEY (category_id) REFERENCES categories (id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------------
-- Product variants (storage / RAM / colour). Variant URLs canonicalise to the
-- parent product URL: /product/slug?variant=8gb-256gb-black
-- ---------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS product_variants (
    id           INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    product_id   INT UNSIGNED NOT NULL,
    sku          VARCHAR(64) NULL,
    label        VARCHAR(120) NOT NULL,           -- "8GB / 256GB — Black"
    storage      VARCHAR(40) NULL,
    ram          VARCHAR(40) NULL,
    color        VARCHAR(40) NULL,
    price_delta  DECIMAL(12,2) NOT NULL DEFAULT 0,
    stock        INT NOT NULL DEFAULT 0,
    gtin         VARCHAR(64) NULL,
    mpn          VARCHAR(128) NULL,
    position     INT NOT NULL DEFAULT 0,
    is_active    TINYINT(1) NOT NULL DEFAULT 1,
    KEY idx_variant_product (product_id),
    CONSTRAINT fk_variant_product FOREIGN KEY (product_id) REFERENCES products (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------------
-- Product images (ordered; first is primary). Alt text per image.
-- ---------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS product_images (
    id           INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    product_id   INT UNSIGNED NOT NULL,
    path         VARCHAR(512) NOT NULL,
    alt          VARCHAR(255) NULL,
    position     INT NOT NULL DEFAULT 0,
    KEY idx_pimage_product (product_id),
    CONSTRAINT fk_pimage_product FOREIGN KEY (product_id) REFERENCES products (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------------
-- Product specifications — rendered as crawlable HTML tables.
-- ---------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS product_specs (
    id           INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    product_id   INT UNSIGNED NOT NULL,
    spec_group   VARCHAR(80) NOT NULL DEFAULT 'General',
    spec_label   VARCHAR(120) NOT NULL,
    spec_value   VARCHAR(255) NOT NULL,
    position     INT NOT NULL DEFAULT 0,
    KEY idx_pspec_product (product_id),
    CONSTRAINT fk_pspec_product FOREIGN KEY (product_id) REFERENCES products (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------------
-- Reviews — structured data (AggregateRating/Review) is generated ONLY from
-- rows with approved = 1. Never fabricate ratings.
-- ---------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS reviews (
    id                INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    product_id        INT UNSIGNED NOT NULL,
    author            VARCHAR(120) NOT NULL,
    rating            TINYINT NOT NULL,
    title             VARCHAR(180) NULL,
    body              TEXT NULL,
    verified_purchase TINYINT(1) NOT NULL DEFAULT 0,
    approved          TINYINT(1) NOT NULL DEFAULT 0,
    created_at        TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    KEY idx_review_product (product_id, approved),
    CONSTRAINT fk_review_product FOREIGN KEY (product_id) REFERENCES products (id) ON DELETE CASCADE,
    CONSTRAINT chk_review_rating CHECK (rating BETWEEN 1 AND 5)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------------
-- Orders — guest checkout; used for GA4 purchase events (dedup via reference).
-- ---------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS orders (
    id            INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    reference     VARCHAR(32) NOT NULL,
    customer_name VARCHAR(160) NOT NULL,
    email         VARCHAR(190) NULL,
    phone         VARCHAR(40) NOT NULL,
    address       VARCHAR(512) NOT NULL,
    city          VARCHAR(120) NOT NULL,
    subtotal      DECIMAL(12,2) NOT NULL,
    shipping      DECIMAL(12,2) NOT NULL DEFAULT 0,
    total         DECIMAL(12,2) NOT NULL,
    payment_method VARCHAR(40) NOT NULL DEFAULT 'cod',
    status        ENUM('pending','confirmed','shipped','delivered','cancelled') NOT NULL DEFAULT 'pending',
    created_at    TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_order_reference (reference)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS order_items (
    id           INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    order_id     INT UNSIGNED NOT NULL,
    product_id   INT UNSIGNED NULL,
    variant_id   INT UNSIGNED NULL,
    item_name    VARCHAR(255) NOT NULL,           -- snapshot at purchase time
    sku          VARCHAR(64) NULL,
    unit_price   DECIMAL(12,2) NOT NULL,
    quantity     INT NOT NULL DEFAULT 1,
    KEY idx_oitem_order (order_id),
    CONSTRAINT fk_oitem_order FOREIGN KEY (order_id) REFERENCES orders (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------------
-- Blog
-- ---------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS blog_posts (
    id             INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    slug           VARCHAR(190) NOT NULL,
    title          VARCHAR(255) NOT NULL,
    excerpt        VARCHAR(512) NULL,
    body           MEDIUMTEXT NULL,               -- HTML, sanitized
    author_name    VARCHAR(120) NULL,
    cover_image    VARCHAR(512) NULL,
    status         ENUM('draft','published') NOT NULL DEFAULT 'draft',
    published_at   TIMESTAMP NULL,
    created_at     TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at     TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_blog_slug (slug),
    KEY idx_blog_status (status, published_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------------
-- Static / service pages (about, contact, repair services, policies …)
-- ---------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS pages (
    id           INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    slug         VARCHAR(190) NOT NULL,           -- e.g. "repair/screen-replacement"
    title        VARCHAR(255) NOT NULL,
    page_type    ENUM('static','repair') NOT NULL DEFAULT 'static',
    intro        VARCHAR(512) NULL,
    body         MEDIUMTEXT NULL,                 -- HTML, sanitized
    -- repair-specific structured data (nullable; only rendered when present)
    service_name VARCHAR(190) NULL,
    price_from   DECIMAL(12,2) NULL,
    price_to     DECIMAL(12,2) NULL,
    area_served  VARCHAR(255) NULL,
    status       ENUM('draft','published') NOT NULL DEFAULT 'draft',
    updated_at   TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    created_at   TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_page_slug (slug)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------------
-- Redirects — auto-created on slug changes + manual entries.
-- 301 for permanent moves; open-redirect protection enforced in application.
-- ---------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS redirects (
    id           INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    from_path    VARCHAR(512) NOT NULL,
    to_path      VARCHAR(512) NOT NULL,
    status_code  SMALLINT NOT NULL DEFAULT 301,
    reason       VARCHAR(255) NULL,
    created_at   TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_redirect_from (from_path)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------------
-- Indexable filter landing pages: admin-curated filter combinations that
-- deserve their own SEO URL, e.g. /shop/samsung-phones-under-100000
-- ---------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS filter_pages (
    id           INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    slug         VARCHAR(190) NOT NULL,
    name         VARCHAR(190) NOT NULL,
    category_id  INT UNSIGNED NULL,
    brand_id     INT UNSIGNED NULL,
    -- JSON: {"storage":["256GB"],"ram":["8GB"],"color":["Black"],"min_price":0,"max_price":150000,"condition":"new"}
    filters      JSON NULL,
    intro        TEXT NULL,
    is_indexable TINYINT(1) NOT NULL DEFAULT 1,
    status       ENUM('draft','published') NOT NULL DEFAULT 'draft',
    created_at   TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at   TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_filter_slug (slug)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------------
-- Admin users
-- ---------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS admins (
    id            INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    username      VARCHAR(80) NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    display_name  VARCHAR(120) NOT NULL,
    created_at    TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_admin_username (username)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;
