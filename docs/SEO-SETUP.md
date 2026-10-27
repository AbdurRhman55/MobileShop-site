# SEO Setup Guide — Lala Mobiles

This document covers everything that **cannot be done from inside the codebase**:
the external accounts, verification tokens and platform configuration that make
the on-site SEO work measurable and submittable.

> **Honest scope note:** everything here is configuration, not a guarantee.
> No configuration, plugin or technique can guarantee rankings, positions or
> indexing speed. Search engines decide what to index and where to rank.
> This guide makes the site *correctly crawlable, indexable and interpretable*;
> the ranking outcome is out of our control.

---

## 1. Prerequisites

| Requirement | Notes |
|-------------|-------|
| PHP 8.3+ with `pdo_mysql` | `openssl` recommended for HTTPS |
| MySQL 8.x | Charset `utf8mb4` is required (set in `database/schema.sql`) |
| A web server with rewrite | Apache (`public/.htaccess` shipped) or nginx (see below) |
| HTTPS enabled | Required for Merchant Center, and canonical URLs are `https://` |

### Environment

Copy `config/.env.example` to `config/.env` and set:

```
APP_ENV=production          # 'production' disables display_errors
APP_DEBUG=0
DB_HOST=127.0.0.1
DB_NAME=lala_mobiles
DB_USER=your_user
DB_PASS=your_password
ADMIN_USER=admin            # first-run seed only
ADMIN_PASS=change-this      # first-run seed only
```

### nginx equivalent of `.htaccess`

```nginx
server {
    server_name www.lalamobiles.pk;
    root /var/www/lala-mobiles/public;

    index index.php;

    # Serve real static files directly (correct MIME types / caching).
    location /assets/ { try_files $uri =404; expires 30d; }

    # Everything else → front controller.
    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/run/php/php8.3-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }

    # Never expose dotfiles.
    location ~ /\. { deny all; }
}
```

---

## 2. First-time installation

```bash
# 1. Create the schema
php bin/migrate.php

# 2. Seed demo catalogue + admin account (add --fresh to reset)
php bin/seed.php

# 3. Build the CSS bundle (Tailwind v4, compiled — no CDN)
npm install
npm run build:css
```

Default admin (change immediately): **admin / Lala@Mobiles2026**

Log in at `/admin`, then:

1. **SEO → Settings** — set the canonical domain, organization details and
   verification codes (sections 3–6 below).
2. **SEO → Audit** — run through the open issues; each report has a CSV export.
3. **SEO → Redirects** — add any legacy URLs from the previous site.

---

## 3. Canonical domain (do this FIRST)

**SEO → Settings → Canonical domain**

Every absolute URL the site emits — canonical tags, Open Graph, JSON-LD,
sitemap `<loc>`, `robots.txt` sitemap reference, Merchant feed — is built from
this value, **never** from the incoming `Host` header.

This is deliberate and tested: a request with a poisoned
`Host: evil.example.com` header still produces
`<link rel="canonical" href="https://www.lalamobiles.pk/...">`.

- Set it to exactly `https://www.lalamobiles.pk` (or your final domain).
- Pick **one** form (`www.` *or* bare) and stick to it; configure your server to
  301 the other form to it.
- After changing it, press **Flush cache & regenerate** on **SEO → Sitemap**.

---

## 4. Google Search Console

1. Open <https://searchconsole.google.com> → **Add property**.
2. Choose the **HTML tag** method. Copy the verification token, e.g.
   `abc123def456...`.
3. In the app: **SEO → Settings → Google Search Console verification** → paste
   the token. It is emitted as
   `<meta name="google-site-content" content="…">` on every storefront page.
4. **Immediately flip `robots_index_site` to "Indexing enabled"** (SEO →
   Settings) if you had it off for staging. With it off, the site serves
   `noindex,nofollow,noarchive` on every page *and* `Disallow: /` in robots.txt.
5. Submit the sitemap: **Sitemaps → Add a new sitemap** →
   `https://www.lalamobiles.pk/sitemap.xml`
6. Use **URL Inspection** on a couple of product pages to confirm Google reads
   the canonical you expect.
7. Check **Coverage / Page Indexing** weekly for a while. Expect a lag;
   submitted sitemaps are not crawled instantly.

### Bing / Microsoft IndexNow

Bing Webmaster Tools accepts the same sitemap. There is also an **IndexNow**
endpoint at `/indexnow` that accepts an API key configured in
**SEO → Settings → IndexNow key** — submit the key to Bing once, then ping that
endpoint whenever content changes.

---

## 5. Google Analytics 4 (GA4)

1. Create a GA4 property → copy the measurement ID (`G-XXXXXXXXXX`).
2. **SEO → Settings → GA4 measurement ID** → paste it.
   - Input is validated server-side; a non-`G-` value is rejected.
   - Leave blank to disable GA entirely (no tag is emitted).
3. Optionally add a **GTM container ID** in the same screen; both can coexist.

### Consent mode

**SEO → Settings → "Require consent before analytics"** (on by default):

- No `gtag`/GTM script is loaded until the visitor accepts.
- The cookie banner stores the choice in `localStorage` (`lm_consent`).
- **Nothing is sent before consent.** Visitors who decline are never tracked.

### E-commerce events already wired

| Event | Fired when |
|-------|-----------|
| `view_item` | Product page viewed (with `items[]`, price, currency `PKR`) |
| `add_to_cart` | Add-to-cart succeeds |
| `begin_checkout` | Checkout page reached |
| `purchase` | Order confirmed on the success page |

**Purchase dedup:** the `transaction_id` is stored in `localStorage` and the
event is skipped if it was already sent for that reference, so a page refresh on
the success screen does not double-count a conversion.

> Verify in GA4 **DebugView** (enable the GA4 debugger or the `?ga_debug=1`
> parameter) after going live. Analytics data is not retroactive.

---

## 6. Google Merchant Center (product feed)

1. Merchant Center → **Products → Feeds → +** → **Content API** or
   **Scheduled fetch**.
2. Feed URL: `https://www.lalamobiles.pk/feed/products.xml`
   (RSS 2.0 with the `g:` namespace).
3. Enable it in **SEO → Settings → Merchant feed enabled** (it 404s when off).
4. Currency is `PKR`; availability is a live `https://schema.org/…` URL per item.

### What the feed does **not** do

- **GTIN/MPN are never invented.** Items only emit `g:gtin` / `g:mpn` when the
  product record genuinely carries them. Everything else emits
  `g:identifier_exists = no` and uses the SKU as `g:id`.
- If you have real barcodes, add them per product in the admin product form;
  the feed picks them up on the next regeneration.

### Expectations

Merchant Center will initially flag items for missing GTINs, missing
`google_product_category` mapping, or price/shipping mismatches. Those are
platform-side data-quality warnings, fixable by supplying the real identifiers
— they are not a bug in the feed generator. Budget time for the first review.

---

## 7. Structured data

JSON-LD is emitted per page and is validated in the test suite
(`bin/smoke-test.php` asserts every block parses).

| Type | Where | Notes |
|------|-------|-------|
| `Organization` | Every page | Real name/logo/contact from settings |
| `WebSite` + `SearchAction` | Every page | Points at `/search?q=…` |
| `LocalBusiness` | Homepage | **Only** if address + latitude + longitude are all set |
| `Product` + `Offer` | Product pages | Live price/stock; `PKR` |
| `AggregateRating` | Product pages | **Only** when approved reviews ≥ threshold (default 3) |
| `BreadcrumbList` | All content pages | Matches the visible breadcrumb |
| `Article` | Blog posts | Author + dates from the post record |
| `Service` | Repair pages | Only when a repair page is published |

**Never fabricated:** ratings, review counts, GTINs, opening hours.
`LocalBusiness.openingHours` is emitted only if you type real hours into
**SEO → Settings → Opening hours**.

Validate before launch:
- <https://search.google.com/test/rich-results>
- <https://validator.schema.org/>

---

## 8. Robots.txt & sitemap

- `https://www.lalamobiles.pk/robots.txt` — generated dynamically; editable
  extra rules via **SEO → Settings → robots.txt extra rules**.
- `https://www.lalamobiles.pk/sitemap.xml` — a **sitemap index** pointing at
  section files: `products`, `categories`, `brands`, `pages`, `filter-pages`,
  `blog`, each split at **50,000 URLs** (`products-1.xml`, `products-2.xml`, …).

Guarantees already tested (`bin/seo-test.php`):

- Sections only include canonical, indexable URLs (no `?variant=`, no
  ad-hoc filter/sort URLs, no search pages, no `noindex` entities).
- Deleted products with a 410 and those 301-ing to a replacement are dropped.
- Every `<loc>` is absolute on the canonical domain and XML-escaped.
- CSS/JS are **not** blocked (rendering / Core Web Vitals).
- Staging guard (`robots_index_site = 0`) → `Disallow: /` + `noindex` on every
  page, while still serving the sitemap so crawlers discover the `noindex`.

After any content change, hit **SEO → Sitemap → Flush cache & regenerate**.

---

## 9. Faceted navigation policy

| URL type | Robots | Canonical |
|----------|--------|-----------|
| Base category / brand | `index,follow` | self |
| Paginated (`?page=N`, N≤3) | `index,follow` | self |
| Deep pagination (N>3) | `noindex,follow` | self |
| **Ad-hoc filter/sort** (`?storage=…&sort=…`) | **`noindex,follow`** | **parent** |
| Admin-published `/shop/{slug}` filter page | `index,follow` | self |
| Internal search results | `noindex,follow` | — |

Unlimited filter combinations are the classic way e-commerce sites dilute their
crawl budget. Here they stay usable for customers but are marked `noindex` and
canonicalised to the parent. To get a valuable combination *indexed*, publish it
as a real landing page under **SEO → Filter pages** with genuinely useful copy.

---

## 10. Redirects

- Changing a product, category, brand, blog post, page or filter-page **slug**
  automatically records a **301** from the old URL.
- Deleting a product **301s to its replacement** if one is set, otherwise
  returns a genuine **410 Gone**.
- Discontinued products **stay live** with an `OutOfStock` offer (no 404s, no
  fake scarcity).
- Manual entries: **SEO → Redirects**. Only same-site paths are accepted —
  external destinations are rejected (open-redirect protection).

---

## 11. Pre-launch checklist

- [ ] Canonical domain set to the final `https://` domain
- [ ] `APP_ENV=production`, `APP_DEBUG=0`
- [ ] Server 301s the non-canonical host form to the canonical one
- [ ] HTTPS forced (HSTS recommended)
- [ ] `robots_index_site` = **enabled**
- [ ] GSC verification token added, property verified
- [ ] `https://www.lalamobiles.pk/sitemap.xml` submitted in GSC + Bing
- [ ] GA4 measurement ID added; consent mode tested (accept **and** decline)
- [ ] GA4 `view_item` / `add_to_cart` / `begin_checkout` / `purchase` verified
      in DebugView, including the refresh-dedup on `purchase`
- [ ] Merchant feed enabled and reviewed in Merchant Center
- [ ] Rich Results Test passes on a product page, a category and a blog post
- [ ] robots.txt reviewed (no accidental `Disallow: /`)
- [ ] Admin password changed from the seeded default
- [ ] Real product data entered — no placeholder prices, stock or images

---

## 12. What this system cannot promise

- **Rankings.** No one can guarantee a position. Anyone who does is selling
  something.
- **Indexing speed or completeness.** Submission is a request, not an order.
  Google may crawl, partially index, or ignore pages at its own discretion.
- **Traffic or revenue.** These follow from the site's authority, content,
  competition and many factors outside this codebase.
- **Merchant Center approval.** Feed *format* is correct and validated;
  *approval* depends on your real product data and Google's policies.

What is guaranteed is that the technical layer is correct: crawlable,
correctly canonicalised, honestly marked up, and free of the common
self-inflicted SEO wounds (infinite facet URLs, Host-header canonical leaks,
fabricated ratings, orphaned/dead URLs, blocked assets).

---

## 13. Running the test suites

With the dev server running (`php -S 127.0.0.1:8088 bin/serve.php`):

```bash
php bin/smoke-test.php  http://127.0.0.1:8088   # pages, meta, sitemap, feed, robots
php bin/admin-test.php  http://127.0.0.1:8088   # auth, CSRF, XSS, redirects, admin screens
php bin/seo-test.php    http://127.0.0.1:8088   # 410/301, noindex, facets, staging guard, 50k split
```

Each exits non-zero on failure and restores the rows it touches.
(`seo-test` includes a ~50,000-row bulk insert to prove the sitemap really
splits — expect it to take about 40 seconds.)
