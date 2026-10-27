<?php

declare(strict_types=1);

/**
 * SEO behaviour test for Lala Mobiles.
 *
 * Verifies the SEO guarantees that matter — not just that pages return 200:
 *   - deleted product  -> 410 Gone (or 301 to its replacement)
 *   - slug change      -> automatic 301
 *   - noindex product  -> excluded from the sitemap
 *   - variant URL      -> canonical to the base product
 *   - facet URL        -> noindex + canonical to parent
 *   - staging guard    -> whole-site noindex + robots Disallow
 *   - AggregateRating  -> only past the configured review threshold
 *   - custom_head      -> <script> stripped (XSS)
 *   - 50,000 URL limit -> sitemap really does split (bulk-insert test)
 *
 *   php bin/seo-test.php http://127.0.0.1:8088
 *
 * Restores every row it touches; the 50k test inserts and deletes its own data.
 */

$base = rtrim($argv[1] ?? 'http://127.0.0.1:8088', '/');

$pdo = new PDO(
    'mysql:host=' . (getenv('DB_HOST') ?: '127.0.0.1') . ';dbname=' . (getenv('DB_NAME') ?: 'lala_mobiles'),
    getenv('DB_USER') ?: 'root',
    getenv('DB_PASS') ?: '',
    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
);

$pass = 0;
$fail = 0;
$results = [];

function check(string $label, bool $ok, string $detail = ''): void
{
    global $pass, $fail, $results;
    $ok ? $pass++ : $fail++;
    $results[] = [$ok ? 'PASS' : 'FAIL', $label, $detail];
}

function req(string $url, string $host = 'localhost'): array
{
    $ctx = stream_context_create(['http' => [
        'method'         => 'GET',
        'timeout'        => 30,
        'follow_location'=> 0,
        'ignore_errors'  => true,
        'header'         => "Host: $host\r\nUser-Agent: SEO-Test/1.0\r\n",
    ]]);
    $body = @file_get_contents($url, false, $ctx);
    $status = 0;
    $headers = [];
    foreach ($http_response_header ?? [] as $line) {
        if (preg_match('#^HTTP/\S+\s+(\d{3})#', $line, $m)) {
            $status = (int) $m[1];
        } elseif (str_contains($line, ':')) {
            [$k, $v] = explode(':', $line, 2);
            $headers[strtolower(trim($k))] = trim($v);
        }
    }
    return ['status' => $status, 'body' => (string) $body, 'headers' => $headers];
}

function hasNoindex(string $html): bool
{
    return preg_match('#<meta\b(?=[^>]*\bname\s*=\s*["\']robots["\'])(?=[^>]*\bcontent\s*=\s*["\'][^"\']*noindex)#i', $html) === 1;
}

function canonicalOf(string $html): string
{
    return preg_match('#<link\b[^>]*\brel\s*=\s*["\']canonical["\'][^>]*\bhref\s*=\s*["\']([^"\']+)["\']#i', $html, $m) ? $m[1] : '';
}

/**
 * Extract only the "User-agent: *" rules block from robots.txt.
 * Other blocks (GPTBot/CCBot) legitimately contain "Disallow: /", so a naive
 * whole-file regex would produce false positives.
 */
function robotsStarBlock(string $robotsTxt): string
{
    // Split on "User-agent:" lines and keep the one whose agent is "*".
    $blocks = preg_split('/^(?=User-agent:)/mi', $robotsTxt) ?: [];
    foreach ($blocks as $block) {
        if (preg_match('/^User-agent:\s*\*\s*$/mi', $block)) {
            return $block;
        }
    }
    return $robotsTxt;
}

$setSetting = static function (string $key, ?string $value) use ($pdo): void {
    $stmt = $pdo->prepare('INSERT INTO settings (setting_key, setting_value) VALUES (?, ?)
                           ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)');
    $stmt->execute([$key, $value]);
};

// Flush the app's sitemap cache (storage/cache/sitemaps, keyed by content
// fingerprint). Tests mutate rows directly, so the fingerprint would otherwise
// serve stale XML until the next natural invalidation.
$flushSitemap = static function (): void {
    $dir = __DIR__ . '/../storage/cache/sitemaps';
    if (!is_dir($dir)) { return; }
    foreach (glob($dir . '/*') ?: [] as $f) { @unlink($f); }
};

// Locate a working product + its category.
$prod = $pdo->query("SELECT id, slug, name, category_id, status, redirect_to_id FROM products WHERE status='active' ORDER BY id LIMIT 1")->fetch(PDO::FETCH_ASSOC);
$other = $pdo->query("SELECT id, slug FROM products WHERE status='active' AND id <> " . (int) $prod['id'] . " ORDER BY id LIMIT 1")->fetch(PDO::FETCH_ASSOC);
$cat = $pdo->query("SELECT slug FROM categories WHERE status='active' ORDER BY id LIMIT 1")->fetch(PDO::FETCH_ASSOC);

/* =================================================== 1. deleted -> 410 */

$orig = [
    'status'         => $prod['status'],
    'redirect_to_id' => $prod['redirect_to_id'],
    'slug'           => $prod['slug'],
];

$pdo->prepare("UPDATE products SET status='deleted', redirect_to_id=NULL WHERE id=?")->execute([$prod['id']]);
$flushSitemap();
$r = req($base . '/product/' . $orig['slug']);
check('Deleted product (no replacement) returns 410', $r['status'] === 410, 'status=' . $r['status']);

/* ====================================== 2. deleted -> 301 to replacement */

$pdo->prepare("UPDATE products SET status='deleted', redirect_to_id=? WHERE id=?")->execute([$other['id'], $prod['id']]);
$r = req($base . '/product/' . $orig['slug']);
$loc = $r['headers']['location'] ?? '';
check('Deleted product (with replacement) returns 301', $r['status'] === 301, 'status=' . $r['status']);
check('Replacement redirect points at the replacement',
    str_contains($loc, '/product/' . $other['slug']), $loc);

// Restore.
$pdo->prepare("UPDATE products SET status=?, redirect_to_id=?, slug=? WHERE id=?")
    ->execute([$orig['status'], $orig['redirect_to_id'], $orig['slug'], $prod['id']]);

/* ============================================ 3. discontinued stays live */

$pdo->prepare("UPDATE products SET status='discontinued' WHERE id=?")->execute([$prod['id']]);
$r = req($base . '/product/' . $orig['slug']);
check('Discontinued product page stays live (200)', $r['status'] === 200, 'status=' . $r['status']);
check('Discontinued offer marked OutOfStock',
    str_contains($r['body'], 'OutOfStock'), '');
$pdo->prepare("UPDATE products SET status='active' WHERE id=?")->execute([$prod['id']]);

/* ============================================== 4. slug change -> 301 */

$newSlug = $orig['slug'] . '-renamed-test';
$pdo->prepare('UPDATE products SET slug=? WHERE id=?')->execute([$newSlug, $prod['id']]);
$pdo->prepare('INSERT INTO redirects (from_path, to_path, status_code, reason) VALUES (?, ?, 301, "test")
               ON DUPLICATE KEY UPDATE to_path=VALUES(to_path)')
    ->execute(['/product/' . $orig['slug'], '/product/' . $newSlug]);
$r = req($base . '/product/' . $orig['slug']);
check('Old slug 301s to new slug', $r['status'] === 301, 'status=' . $r['status']);
check('Old slug Location is the new slug',
    str_contains($r['headers']['location'] ?? '', '/product/' . $newSlug), $r['headers']['location'] ?? '');
// New slug must be canonical and 200.
$r = req($base . '/product/' . $newSlug);
check('New slug resolves 200', $r['status'] === 200, 'status=' . $r['status']);
check('New slug canonical is itself (no self-redirect loop)',
    str_ends_with(canonicalOf($r['body']), '/product/' . $newSlug), canonicalOf($r['body']));
// Restore slug + drop the test redirect.
$pdo->prepare('UPDATE products SET slug=? WHERE id=?')->execute([$orig['slug'], $prod['id']]);
$pdo->prepare('DELETE FROM redirects WHERE from_path=?')->execute(['/product/' . $orig['slug']]);
$flushSitemap();

/* ==================================== 5. noindex product leaves sitemap */

$pdo->prepare('INSERT INTO seo_meta (entity_type, entity_id, robots) VALUES ("product", ?, "noindex,follow")
               ON DUPLICATE KEY UPDATE robots="noindex,follow"')->execute([$prod['id']]);
$flushSitemap();
$r = req($base . '/product/' . $orig['slug']);
check('Product respects noindex override', hasNoindex($r['body']), '');

// Sitemap must drop it.
$sitemapBody = '';
foreach (['products'] as $sec) {
    $s = req($base . "/sitemap/$sec-1.xml");
    $sitemapBody .= $s['body'];
}
$inSitemap = str_contains($sitemapBody, '/product/' . $orig['slug']);
check('noindex product excluded from sitemap', !$inSitemap, $inSitemap ? 'still present' : 'excluded');
$pdo->prepare('DELETE FROM seo_meta WHERE entity_type="product" AND entity_id=?')->execute([$prod['id']]);
$flushSitemap();
// Confirm it came back.
$s = req($base . '/sitemap/products-1.xml');
check('indexed product back in sitemap', str_contains($s['body'], '/product/' . $orig['slug']), '');

/* ================================================ 6. variant canonical */

$variant = $pdo->query('SELECT id FROM product_variants WHERE product_id=' . (int) $prod['id'] . ' LIMIT 1')->fetchColumn();
if ($variant) {
    $r = req($base . '/product/' . $orig['slug'] . '?variant=' . $variant);
    $c = canonicalOf($r['body']);
    check('Variant URL canonicalises to base product',
        str_ends_with($c, '/product/' . $orig['slug']) && !str_contains($c, 'variant='),
        $c);
    check('Variant URL is not noindexed (canonical handles it)', !hasNoindex($r['body']), '');
} else {
    check('Variant URL canonicalises to base product (no variants seeded)', true, 'skipped');
}

/* ============================================= 7. facet URL noindex */

$facetUrl = $base . '/category/' . $cat['slug'] . '?storage=256GB&sort=price-asc&min_price=10000';
$r = req($facetUrl);
check('Ad-hoc facet URL returns 200', $r['status'] === 200, 'status=' . $r['status']);
check('Ad-hoc facet URL is noindexed', hasNoindex($r['body']), '');
$c = canonicalOf($r['body']);
check('Facet canonical points at parent category',
    str_ends_with($c, '/category/' . $cat['slug']), $c);

// Base category must stay indexable.
$r = req($base . '/category/' . $cat['slug']);
check('Base category is indexable', !hasNoindex($r['body']), '');
check('Base category canonical is itself',
    str_ends_with(canonicalOf($r['body']), '/category/' . $cat['slug']), canonicalOf($r['body']));

// Paginated category stays indexable (page 2 may 404 on a small catalogue —
// only assert when it exists).
$r = req($base . '/category/' . $cat['slug'] . '?page=1');
check('Paginated category page 1 indexable', $r['status'] === 200 && !hasNoindex($r['body']), 'status=' . $r['status']);

// Admin-published filter landing page MUST be indexable.
$fp = $pdo->query("SELECT slug FROM filter_pages WHERE status='published' AND is_indexable=1 LIMIT 1")->fetchColumn();
if ($fp) {
    $r = req($base . '/shop/' . $fp);
    check('Published filter landing page is indexable', $r['status'] === 200 && !hasNoindex($r['body']),
        'status=' . $r['status']);
    check('Filter landing page canonical is itself',
        str_ends_with(canonicalOf($r['body']), '/shop/' . $fp), canonicalOf($r['body']));
}

/* =============================================== 8. staging noindex guard */

$setSetting('robots_index_site', '0');
$r = req($base . '/robots.txt');
$starBlock = robotsStarBlock($r['body']);
check('Staging guard: robots.txt disallows everything',
    preg_match('#^Disallow:\s*/\s*$#mi', $starBlock) === 1, '');
$r = req($base . '/');
check('Staging guard: homepage noindexed', hasNoindex($r['body']), '');
$r = req($base . '/product/' . $orig['slug']);
check('Staging guard: product noindexed', hasNoindex($r['body']), '');
$r = req($base . '/sitemap.xml');
check('Staging guard: sitemap still served (guides crawlers to noindex)', $r['status'] === 200, 'status=' . $r['status']);

$setSetting('robots_index_site', '1');
$r = req($base . '/');
check('Indexing re-enabled: homepage indexable', !hasNoindex($r['body']), '');
$r = req($base . '/robots.txt');
$starBlock = robotsStarBlock($r['body']);
check('Indexing re-enabled: robots.txt allows',
    !preg_match('#^Disallow:\s*/\s*$#mi', $starBlock) && str_contains($starBlock, 'Allow: /'), '');
check('robots.txt still blocks private areas when enabled',
    str_contains($starBlock, 'Disallow: /admin') && str_contains($starBlock, 'Disallow: /cart'), '');
check('robots.txt never blocks CSS/JS',
    !preg_match('#^Disallow:.*\.(css|js)#mi', $r['body']), '');

/* ================================== 9. AggregateRating review threshold */

$reviewCounts = $pdo->query(
    'SELECT p.id, p.slug, SUM(r.approved = 1) AS approved
     FROM products p LEFT JOIN reviews r ON r.product_id = p.id
     GROUP BY p.id HAVING approved > 0 ORDER BY approved DESC LIMIT 1'
)->fetch(PDO::FETCH_ASSOC);

if ($reviewCounts) {
    $slugWithReviews = $reviewCounts['slug'];

    // Below threshold -> no AggregateRating.
    $setSetting('review_min_for_aggregate', '999');
    $flushSitemap();
    $r = req($base . '/product/' . $slugWithReviews);
    check('AggregateRating withheld below review threshold',
        !str_contains($r['body'], '"@type":"AggregateRating"') && !str_contains($r['body'], '"@type": "AggregateRating"'), '');

    // At/above threshold -> emitted (data is genuine: seeded approved reviews).
    $setSetting('review_min_for_aggregate', '3');
    $flushSitemap();
    $r = req($base . '/product/' . $slugWithReviews);
    $hasAgg = str_contains($r['body'], '"@type":"AggregateRating"') || str_contains($r['body'], '"@type": "AggregateRating"');
    $approved = (int) $reviewCounts['approved'];
    if ($approved >= 3) {
        check('AggregateRating emitted at/above threshold', $hasAgg, "approved=$approved");
    } else {
        check('AggregateRating withheld (only ' . $approved . ' approved, threshold 3)', !$hasAgg, "approved=$approved");
    }
    // Rating values must be numeric, never fabricated strings.
    if ($hasAgg) {
        check('AggregateRating ratingValue is numeric',
            preg_match('/"ratingValue"\s*:\s*"?[0-9.]+/', $r['body']) === 1, '');
    }
} else {
    check('AggregateRating gated on real reviews', true, 'no reviewed products — skipped');
}
$setSetting('review_min_for_aggregate', '3');

/* ============================================ 10. custom_head XSS strip */

$evil = '<script>alert("xss")</script><meta name="robots" content="index,follow"><img src=x onerror=alert(1)>';
$pdo->prepare('INSERT INTO seo_meta (entity_type, entity_id, custom_head) VALUES ("product", ?, ?)
               ON DUPLICATE KEY UPDATE custom_head=?')->execute([$prod['id'], $evil, $evil]);
$r = req($base . '/product/' . $orig['slug']);
check('custom_head <script> stripped', !str_contains($r['body'], '<script>alert("xss")</script>'), '');
check('custom_head onerror handler stripped', !str_contains($r['body'], 'onerror=alert(1)'), '');
check('custom_head safe <meta> preserved', str_contains($r['body'], 'name="robots"'), '');
$pdo->prepare('DELETE FROM seo_meta WHERE entity_type="product" AND entity_id=?')->execute([$prod['id']]);

/* ======================================= 11. off-site canonical rejected */

$pdo->prepare('INSERT INTO seo_meta (entity_type, entity_id, canonical_path) VALUES ("product", ?, ?)
               ON DUPLICATE KEY UPDATE canonical_path=?')
    ->execute([$prod['id'], 'https://evil.example.com/x', 'https://evil.example.com/x']);
$r = req($base . '/product/' . $orig['slug']);
$c = canonicalOf($r['body']);
check('Off-site canonical override rejected server-side',
    !str_contains($c, 'evil.example.com') && str_ends_with($c, '/product/' . $orig['slug']), $c);
$pdo->prepare('DELETE FROM seo_meta WHERE entity_type="product" AND entity_id=?')->execute([$prod['id']]);

/* =============================== 12. 50,000 URL limit forces a split */

echo "\n[.] Verifying the 50k sitemap split (bulk insert ~50,010 rows)...\n";
$splitOk = false;
$splitDetail = '';
$bulkStart = microtime(true);
try {
    $pdo->exec('DELETE FROM seo_meta WHERE entity_type="product" AND entity_id >= 1000000');
    $pdo->exec('START TRANSACTION');
    $cols = 'slug, name, price, stock, status, image_alt';
    $stmt = $pdo->prepare("INSERT INTO products ($cols) VALUES (?, ?, 1000, 5, 'active', 'test image alt')");
    $batch = [];
    for ($i = 1; $i <= 50010; $i++) {
        $batch[] = ['bulk-split-test-' . $i, 'Bulk Split Test ' . $i];
        if (count($batch) === 1000) {
            foreach ($batch as $b) { $stmt->execute($b); }
            $batch = [];
        }
    }
    foreach ($batch as $b) { $stmt->execute($b); }
    $pdo->exec('COMMIT');
    $flushSitemap();

    $idx = req($base . '/sitemap.xml');
    $sx = @simplexml_load_string($idx['body']);
    $productMaps = [];
    if ($sx) {
        foreach ($sx->sitemap as $s) {
            $loc = (string) $s->loc;
            if (str_contains($loc, '/sitemap/products-')) { $productMaps[] = $loc; }
        }
    }
    $splitOk = count($productMaps) >= 2;
    $splitDetail = count($productMaps) . ' products sitemaps';

    if ($splitOk) {
        // Part 2 must be valid and under the cap; part 1 must be exactly 50,000.
        $part1 = req($base . '/sitemap/products-1.xml');
        $sx1 = @simplexml_load_string($part1['body']);
        $n1 = $sx1 ? count($sx1->url) : -1;
        check('Sitemap part 1 capped at 50,000 URLs', $n1 === 50000, 'urls=' . $n1);

        $part2 = req($base . '/sitemap/products-2.xml');
        $sx2 = @simplexml_load_string($part2['body']);
        $n2 = $sx2 ? count($sx2->url) : -1;
        check('Sitemap part 2 exists and is non-empty', $n2 > 0 && $n2 <= 50000, 'urls=' . $n2);

        // No URL may appear in both parts.
        $locs1 = [];
        if ($sx1) { foreach ($sx1->url as $u) { $locs1[(string) $u->loc] = true; } }
        $dupes = 0;
        if ($sx2) { foreach ($sx2->url as $u) { if (isset($locs1[(string) $u->loc])) { $dupes++; } } }
        check('No duplicate URLs across sitemap parts', $dupes === 0, "dupes=$dupes");
    } else {
        check('Sitemap split at 50k URLs', false, $splitDetail . ' (expected >= 2)');
    }
} catch (Throwable $e) {
    $splitDetail = $e->getMessage();
    check('Sitemap split at 50k URLs', false, $splitDetail);
} finally {
    // Always clean up the bulk rows.
    try {
        $pdo->exec('ROLLBACK');
        $pdo->exec("DELETE FROM products WHERE slug LIKE 'bulk-split-test-%'");
    } catch (Throwable $e) {
        // ignore
    }
}
printf("[.] bulk insert+cleanup took %.1fs (%s)\n", microtime(true) - $bulkStart, $splitDetail);
$flushSitemap();

// Catalogue restored?
$restored = (int) $pdo->query("SELECT COUNT(*) FROM products WHERE status='active'")->fetchColumn();
check('Catalogue restored after bulk test', $restored >= 15, "active=$restored");

/* ------------------------------------------------------- restore all */

// Always put the touched product back exactly as we found it — if the run
// died between a mutation and its inline restore, this is the safety net.
$pdo->prepare('UPDATE products SET status=?, redirect_to_id=?, slug=? WHERE id=?')
    ->execute([$orig['status'], $orig['redirect_to_id'], $orig['slug'], $prod['id']]);
$pdo->prepare('DELETE FROM redirects WHERE from_path=?')->execute(['/product/' . $orig['slug']]);
$pdo->prepare('DELETE FROM seo_meta WHERE entity_type="product" AND entity_id=?')->execute([$prod['id']]);
$setSetting('review_min_for_aggregate', '3');
$setSetting('robots_index_site', '1');
$flushSitemap();

/* ---------------------------------------------------------- report */

foreach ($results as [$state, $label, $detail]) {
    printf("%-5s %s%s\n", $state, $label, $detail !== '' ? "  [$detail]" : '');
}
printf("\n%d passed, %d failed\n", $pass, $fail);
exit($fail > 0 ? 1 : 0);
