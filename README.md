# Lala Mobiles — E-commerce with a Dynamic SEO System

A complete mobile-phone storefront for the Pakistani market (PHP 8.3, MySQL 8.x,
Tailwind CSS v4, vanilla JS) with a **fully dynamic, database-driven SEO layer**:
no hard-coded meta tags, everything editable from the admin dashboard.

---

## What's implemented

### Storefront
- Home, product detail, category & brand listings, admin-curated filter landing
  pages, blog, static pages, repair-service pages, internal search
- Session cart, guest checkout, order confirmation
- Responsive, semantic HTML; lazy-loaded images (main product image excluded);
  compiled Tailwind bundle (no CDN)

### SEO engine (`app/Seo/`)
| Class | Responsibility |
|-------|----------------|
| `Url` | Absolute URLs from the **configured canonical domain** — never the `Host` header |
| `SeoSettings` | Cached key/value settings accessor |
| `MetaResolver` | Title/description/canonical/robots/OG/Twitter resolution with admin overrides + auto-defaults |
| `StructuredData` | JSON-LD: Product, Offer, BreadcrumbList, Organization, WebSite, Article, LocalBusiness, Service |
| `SitemapGenerator` | Sitemap **index** + per-section files, 50k URL cap with automatic splitting, XML-escaped absolute URLs |
| `RobotsTxt` | Dynamic robots.txt, staging noindex guard, admin extra rules, sitemap reference |
| `RedirectManager` | Chain-resolving 301/302 lookup, auto-created on slug changes |
| `MerchantFeed` | Google Merchant Center RSS feed (`g:` namespace), real data only |
| `SeoAudit` | 12 database-backed audit reports with CSV export |
| `Facets` | Explicit indexing policy for filtered/sorted/paginated URLs |

### Admin dashboard (`/admin`)
Products, categories, brands, blog, pages (incl. repair fields), reviews
(approve/reject), orders, plus a full **SEO section**: settings, reusable SEO
editor on every entity form, audit reports, redirect manager, sitemap control,
filter landing pages, Merchant feed preview.

### SEO guarantees (all covered by tests)
- Canonical URLs always built from the configured domain — Host-header poisoning
  has no effect
- Product lifecycle: `deleted` → **410** (or **301** to replacement);
  `discontinued` → stays live with an `OutOfStock` offer
- Slug changes auto-create **301** redirects
- Variant URLs canonicalise to the base product
- Ad-hoc filter/sort URLs → `noindex,follow` + canonical to parent; only
  admin-published `/shop/{slug}` pages are indexable; search results noindex
- `noindex` entities are excluded from the sitemap
- Sitemap really splits past 50,000 URLs (verified with a bulk-insert test)
- Staging guard: one switch → site-wide `noindex` + `Disallow: /`
- `AggregateRating` only past the approved-review threshold; `LocalBusiness`
  only when address + geo are set; **no fabricated ratings, GTINs or hours**
- Admin-entered HTML sanitized (`custom_head` strips `<script>`/event handlers);
  all output escaped with `e()`; open-redirect protection on redirect targets
- CSRF on all state-changing requests including login

---

## Requirements

- PHP 8.3+ (`pdo_mysql`, `curl` for tests)
- MySQL 8.x
- Node.js 18+ (for the Tailwind build)

## Install

```bash
cp config/.env.example config/.env      # then edit DB credentials
php bin/migrate.php                     # create schema
php bin/seed.php                        # demo catalogue + admin user
npm install && npm run build:css        # compile Tailwind
```

Admin: **`/admin`** — `admin` / `Lala@Mobiles2026` *(change it)*

## Run (development)

```bash
php -S 127.0.0.1:8088 bin/serve.php
```

`bin/serve.php` is a router script for PHP's built-in server that mirrors the
`.htaccess` rewrite rules. In production use Apache with `public/.htaccess`, or
the nginx config shown in `docs/SEO-SETUP.md`. Point the vhost at `public/`.

## Test

```bash
php bin/smoke-test.php  http://127.0.0.1:8088   # 52 assertions
php bin/admin-test.php  http://127.0.0.1:8088   # 51 assertions
php bin/seo-test.php    http://127.0.0.1:8088   # 41 assertions (includes a 50k-row bulk test)
```

All three exit non-zero on failure and restore the data they touch.

---

## Configuration still required before launch

1. **Set the canonical domain** (SEO → Settings) — every absolute URL, canonical
   tag, sitemap `<loc>`, feed link and robots.txt sitemap reference derives from
   it. Change it *before* submitting anything to search engines.
2. **Google Search Console** — verify the site with the HTML-tag token
   (SEO → Settings), then submit `https://<domain>/sitemap.xml`.
3. **GA4** — add the `G-…` measurement ID; test `view_item`, `add_to_cart`,
   `begin_checkout` and `purchase` (incl. refresh dedup) in DebugView.
4. **Merchant Center** — point a feed at `/feed/products.xml`; supply real
   GTINs/MPNs per product where you have them (the feed never invents them).
5. **Real data** — replace seeded products, prices, stock, images, addresses and
   opening hours with genuine values.

Full external-setup walkthrough: **[`docs/SEO-SETUP.md`](docs/SEO-SETUP.md)**.

---

## Honest limitations

- **No ranking guarantees.** Nothing here can — or claims to — guarantee
  positions, traffic or revenue. Search engines decide what to index and where
  to rank.
- **Indexing is a request, not an order.** Submitting a sitemap does not force
  crawling or indexing.
- **Merchant Center approval depends on your data and Google's policies.** The
  feed format is correct and validated; approval is platform-side.
- **`SeoAudit` is database-backed, not a crawler.** Its orphan-page report is a
  heuristic (products with no category/brand coverage) and says so. Broken-link
  detection scans product description HTML only, not the whole link graph.
- **Seeded content is demo data.** Replace it before going live.

## Project layout

```
bin/            migrate, seed, dev-server router, test suites
config/         config.php, .env.example
database/       schema.sql, migrations/
app/Core/       Router, Database, Request, View, Csrf, Http, Config, HttpRedirect
app/Seo/        the SEO engine (10 classes)
app/Models/     Product, Catalog, Content, Order, Cart, Redirect, SeoMeta, FilterPage
app/Controllers public + Admin controllers
app/views/      storefront + admin templates, incl. partials/seo-fields.php
public/         front controller, .htaccess, compiled assets
resources/css/  Tailwind input
docs/           SEO-SETUP.md
```
