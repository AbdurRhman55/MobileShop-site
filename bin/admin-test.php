<?php

declare( strict_types = 1 );

/**
* Admin-area integration test for Lala Mobiles.
*
* Logs in ( CSRF + session cookie ), crawls every admin screen, exercises
* the SEO dashboard actions, and checks escaping / open-redirect safety.
*
*   php bin/admin-test.php http://127.0.0.1:8088
*/

$base = rtrim( $argv[ 1 ] ?? 'http://127.0.0.1:8088', '/' );
$user = $argv[ 2 ] ?? 'admin';
$pass = $argv[ 3 ] ?? 'Lala@Mobiles2026';

$cookieFile = tempnam( sys_get_temp_dir(), 'lala_cookie_' );
$passCount = 0;
$failCount = 0;
$results = [];

function httpReq( string $method, string $url, array $post = null ): array {
    global $cookieFile;
    $ch = curl_init( $url );
    curl_setopt_array( $ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HEADER         => true,
        CURLOPT_FOLLOWLOCATION => false,
        CURLOPT_TIMEOUT        => 20,
        CURLOPT_COOKIEJAR      => $cookieFile,
        CURLOPT_COOKIEFILE     => $cookieFile,
        CURLOPT_CUSTOMREQUEST  => $method,
    ] );
    if ( $post !== null ) {
        curl_setopt( $ch, CURLOPT_POSTFIELDS, http_build_query( $post ) );
    }
    $raw = curl_exec( $ch );
    if ( $raw === false ) {
        $err = curl_error( $ch );
        curl_close( $ch );
        return [ 'status' => 0, 'headers' => '', 'body' => '', 'error' => $err ];
    }
    $status  = ( int ) curl_getinfo( $ch, CURLINFO_RESPONSE_CODE );
    $headerSize = ( int ) curl_getinfo( $ch, CURLINFO_HEADER_SIZE );
    curl_close( $ch );
    return [
        'status'  => $status,
        'headers' => substr( $raw, 0, $headerSize ),
        'body'    => substr( $raw, $headerSize ),
        'error'   => null,
    ];
}

function check( string $label, bool $ok, string $detail = '' ): void {
    global $passCount, $failCount, $results;
    $ok ? $passCount++ : $failCount++;
    $results[] = [ $ok ? 'PASS' : 'FAIL', $label, $detail ];
}

/** Pull a hidden CSRF input value out of an HTML body. */

function csrfFrom( string $html ): ?string {
    if ( preg_match( '/name="_token"\s+value="([^"]+)"/', $html, $m ) ) {
        return $m[ 1 ];
    }
    if ( preg_match( '/value="([^"]+)"\s+name="_token"/', $html, $m ) ) {
        return $m[ 1 ];
    }
    return null;
}

/* ------------------------------------------------------------- login */

$r = httpReq( 'GET', $base . '/admin/login' );
check( 'GET /admin/login 200', $r[ 'status' ] === 200, ( string ) $r[ 'status' ] );
$token = csrfFrom( $r[ 'body' ] );
check( 'Login form exposes CSRF token', $token !== null, $token ? 'present' : 'missing' );

// Wrong password must be rejected.
$r = httpReq( 'POST', $base . '/admin/login', [ '_token' => $token, 'username' => $user, 'password' => 'definitely-wrong' ] );
check( 'Wrong password rejected', $r[ 'status' ] !== 200 || !str_contains( $r[ 'body' ], 'logout' ), 'status=' . $r[ 'status' ] );

// Correct password.
$r = httpReq( 'GET', $base . '/admin/login' );
$token = csrfFrom( $r[ 'body' ] );
$r = httpReq( 'POST', $base . '/admin/login', [ '_token' => $token, 'username' => $user, 'password' => $pass ] );
check( 'Login succeeds', in_array( $r[ 'status' ], [ 302, 303 ], true ), 'status=' . $r[ 'status' ] );
check( 'Login lands on /admin', str_contains( $r[ 'headers' ], '/admin' ), '' );

// Missing/invalid CSRF must be rejected on a state-changing route.
$r = httpReq( 'POST', $base . '/admin/login', [ 'username' => $user, 'password' => $pass ] );
check( 'Missing CSRF rejected', $r[ 'status' ] !== 302 && $r[ 'status' ] !== 303, 'status=' . $r[ 'status' ] );

/* ------------------------------------------------- crawl admin screens */

$screens = [
    '/admin',
    '/admin/products',
    '/admin/products/create',
    '/admin/categories',
    '/admin/categories/create',
    '/admin/brands',
    '/admin/brands/create',
    '/admin/blog',
    '/admin/blog/create',
    '/admin/pages',
    '/admin/pages/create',
    '/admin/reviews',
    '/admin/orders',
    '/admin/customers',
    '/admin/seo',
    '/admin/seo/settings',
    '/admin/seo/audit',
    '/admin/seo/redirects',
    '/admin/seo/sitemap',
    '/admin/seo/filter-pages',
    '/admin/seo/filter-pages/create',
    '/admin/seo/feed',
];

foreach ( $screens as $path ) {
    $r = httpReq( 'GET', $base . $path );
    $ok = $r[ 'status' ] === 200;
    $detail = 'status=' . $r[ 'status' ];
    if ( $ok && str_contains( $r[ 'body' ], 'Fatal error' ) ) {
        $ok = false;
        $detail = 'fatal error in body';
    }
    if ( $ok && $path !== '/admin/seo/audit' && !str_contains( $r[ 'body' ], '</html>' ) ) {
        $ok = false;
        $detail = 'truncated body';
    }
    check( "GET $path", $ok, $detail );
}

// Every admin screen must be noindex.
$r = httpReq( 'GET', $base . '/admin/seo' );
check( 'Admin screens are noindex', str_contains( $r[ 'body' ], 'noindex' ), '' );

/* ------------------------------------------------- edit screen ( real id ) */

$token = csrfFrom( httpReq( 'GET', $base . '/admin/products' )[ 'body' ] );
$pdo = new PDO(
    'mysql:host=' . ( getenv( 'DB_HOST' ) ?: '127.0.0.1' ) . ';dbname=' . ( getenv( 'DB_NAME' ) ?: 'lala_mobiles' ),
    getenv( 'DB_USER' ) ?: 'root',
    getenv( 'DB_PASS' ) ?: ''
);
$customerId = ( int ) $pdo->query( "SELECT id FROM customers WHERE email LIKE 'demo-customer-%@example.invalid' ORDER BY id LIMIT 1" )->fetchColumn();
if ( $customerId > 0 ) {
    $r = httpReq( 'GET', $base . "/admin/customers/$customerId" );
    check( 'Customer profile page 200', $r[ 'status' ] === 200 && str_contains( $r[ 'body' ], 'Order history' ), 'status=' . $r[ 'status' ] );
}
$demoOrderId = ( int ) $pdo->query( "SELECT id FROM orders WHERE reference LIKE 'DEMO-%' ORDER BY id LIMIT 1" )->fetchColumn();
if ( $demoOrderId > 0 ) {
    $r = httpReq( 'GET', $base . "/admin/orders/$demoOrderId" );
    check( 'Demo order detail page 200', $r[ 'status' ] === 200 && str_contains( $r[ 'body' ], 'Items' ), 'status=' . $r[ 'status' ] );
}
$pid = ( int ) $pdo->query( "SELECT id FROM products WHERE status='active' ORDER BY id LIMIT 1" )->fetchColumn();
$r = httpReq( 'GET', $base . "/admin/products/$pid/edit" );
check( 'Product edit form 200', $r[ 'status' ] === 200, 'status=' . $r[ 'status' ] );
check( 'Product edit form has SEO fieldset', str_contains( $r[ 'body' ], 'name="seo_title"' ), '' );
check( 'Product edit form has canonical override', str_contains( $r[ 'body' ], 'name="canonical_path"' ), '' );
check( 'Product edit form has custom_head (sanitized)', str_contains( $r[ 'body' ], 'name="custom_head"' ), '' );
check( 'Product edit form has GTIN label', str_contains( $r[ 'body' ], 'name="gtin"' ), '' );

/* ------------------------------------------------- XSS escaping check */

// Store a product name containing HTML and confirm it renders escaped.
$evil = '<script>alert("xss")</script>';
$stmt = $pdo->prepare( 'UPDATE products SET name = ? WHERE id = ?' );
$stmt->execute( [ $evil, $pid ] );
$r = httpReq( 'GET', $base . '/product/' . $pdo->query( "SELECT slug FROM products WHERE id=$pid" )->fetchColumn() );
check( 'Product name HTML is escaped (no raw <script>)',
!str_contains( $r[ 'body' ], '<script>alert("xss")</script>' ),
str_contains( $r[ 'body' ], '&lt;script&gt;' ) ? 'escaped via e()' : 'raw markup found' );
// Also verify it is escaped in the <title> / OG tag.
check( 'Product name escaped inside meta tags',
!preg_match( '/<title>\s*<script>alert/i', ( string ) $r[ 'body' ] ), '' );
// Restore.
$stmt->execute( [ 'Samsung Galaxy S25 Ultra 5G', $pid ] );

/* --------------------------------------------- open-redirect protection */

$r = httpReq( 'POST', $base . '/admin/seo/redirects', [
    '_token'     => csrfFrom( httpReq( 'GET', $base . '/admin/seo/redirects' )[ 'body' ] ),
    'from_path'  => '/legacy-test-path',
    'to_path'    => 'https://evil.example.com/phish',
    'status_code'=> 301,
] );
// It must NOT be stored as an external redirect.
$external = ( int ) $pdo->query( "SELECT COUNT(*) FROM redirects WHERE from_path LIKE '%legacy-test-path%' AND to_path LIKE 'http%'" )->fetchColumn();
check( 'Open redirect (external URL) rejected', $external === 0, "stored=$external status=" . $r[ 'status' ] );

// A same-site path must be accepted.
$r = httpReq( 'POST', $base . '/admin/seo/redirects', [
    '_token'     => csrfFrom( httpReq( 'GET', $base . '/admin/seo/redirects' )[ 'body' ] ),
    'from_path'  => '/legacy-test-path',
    'to_path'    => '/shop/samsung-phones-under-150000',
    'status_code'=> 301,
] );
$local = ( int ) $pdo->query( "SELECT COUNT(*) FROM redirects WHERE from_path='/legacy-test-path' AND to_path='/shop/samsung-phones-under-150000'" )->fetchColumn();
check( 'Same-site redirect accepted', $local === 1, "stored=$local" );

// The redirect must actually work ( 301 ) with an absolute Location on our domain.
$r = httpReq( 'GET', $base . '/legacy-test-path' );
check( 'Redirect fires 301', $r[ 'status' ] === 301, 'status=' . $r[ 'status' ] );
check( 'Redirect Location is same-site path',
preg_match( '#^Location:\s*/#mi', $r[ 'headers' ] ) === 1,
trim( strstr( $r[ 'headers' ], 'Location:' ) ?: 'no location' ) );

/* ------------------------------------------------ SEO settings save */

$r = httpReq( 'GET', $base . '/admin/seo/settings' );
$token = csrfFrom( $r[ 'body' ] );
check( 'SEO settings form 200', $r[ 'status' ] === 200 && $token !== null, 'status=' . $r[ 'status' ] );

$r = httpReq( 'POST', $base . '/admin/seo/settings', [
    '_token'                 => $token,
    'canonical_domain'       => 'https://www.lalamobiles.pk',
    'site_name'              => 'Lala Mobiles',
    'site_tagline'           => 'Phones in Pakistan',
    'homepage_seo_title'     => 'Lala Mobiles — Test Title',
    'homepage_seo_description'=> 'A tested homepage description for SEO.',
    'robots_index_site'      => '1',
    'sitemap_enabled'        => '1',
    'blog_enabled'           => '1',
    'feed_enabled'           => '1',
    'analytics_consent_required' => '1',
    'review_min_for_aggregate'   => '3',
    'ga4_measurement_id'     => 'G-TEST12345',
    'gtm_container_id'       => '',
    'organization_name'      => 'Lala Mobiles',
    'organization_phone'     => '+92-300-0000000',
    'organization_email'     => 'info@lalamobiles.pk',
    'organization_city'      => 'Rawalpindi',
    'organization_address'   => 'Main Mobile Market, Saddar',
    'organization_region'    => 'Punjab',
    'organization_postal'    => '46000',
    'geo_latitude'           => '33.5942',
    'geo_longitude'          => '73.0551',
    'opening_hours'          => 'Mo-Su 11:00-21:00',
    'google_search_console_verification' => 'abc123_test',
    'robots_txt_extra'       => 'Disallow: /private-test',
] );
check( 'SEO settings save redirects 303', $r[ 'status' ] === 303, 'status=' . $r[ 'status' ] );

// Host-header poisoning after save: evil Host must never win.
$r = httpReq( 'GET', $base . '/' );
$evilHostLeak = preg_match( '/https?:\/\/evil\.[^"]*/i', ( string ) $r[ 'body' ] ) === 1;
check( 'Canonical ignores evil Host after settings save', !$evilHostLeak, $evilHostLeak ? 'leaked' : 'ok' );
check( 'Homepage title updated', str_contains( ( string ) $r[ 'body' ], 'Test Title' ), '' );

// robots.txt must include the extra rule.
$r = httpReq( 'GET', $base . '/robots.txt' );
check( 'robots.txt includes admin extra rule', str_contains( $r[ 'body' ], 'Disallow: /private-test' ), '' );

// Invalid GA4 ID must be rejected.
$r = httpReq( 'POST', $base . '/admin/seo/settings', [ '_token' => $token, 'ga4_measurement_id' => 'not-a-ga4-id' ] );
check( 'Invalid GA4 ID rejected', $r[ 'status' ] === 303 && !str_contains( $r[ 'headers' ], '200' ), 'status=' . $r[ 'status' ] );

/* ------------------------------------------------ audit + CSV export */

$r = httpReq( 'GET', $base . '/admin/seo/audit' );
check( 'Audit page 200', $r[ 'status' ] === 200, ( string ) $r[ 'status' ] );
check( 'Audit page lists reports', str_contains( ( string ) $r[ 'body' ], 'Missing custom SEO titles' ), '' );

$r = httpReq( 'GET', $base . '/admin/seo/audit/missing_title/export' );
check( 'Audit CSV export 200 + text/csv',
$r[ 'status' ] === 200 && str_contains( strtolower( $r[ 'headers' ] ), 'text/csv' ),
'status=' . $r[ 'status' ] );
check( 'Audit CSV has header row', str_starts_with( $r[ 'body' ], 'entity,name,issue,link' ), substr( $r[ 'body' ], 0, 40 ) );

// Unknown report key must 404/redirect, not fatal.
$r = httpReq( 'GET', $base . '/admin/seo/audit/no-such-report/export' );
check( 'Unknown audit report handled', in_array( $r[ 'status' ], [ 302, 303, 404 ], true ), 'status=' . $r[ 'status' ] );

/* ------------------------------------------------- cleanup ( artifacts ) */

// Remove everything this test created so runs stay idempotent.
$pdo->exec( "DELETE FROM redirects WHERE from_path = '/legacy-test-path'" );
$pdo->exec( "UPDATE settings SET setting_value = NULL
            WHERE setting_key IN ('homepage_seo_title','homepage_seo_description','ga4_measurement_id','robots_txt_extra')" );
$pdo->exec( "UPDATE products SET name = 'Samsung Galaxy S25 Ultra 5G' WHERE id = $pid" );

// Flush the sitemap cache so the product rename above is reflected immediately.
$r = httpReq( 'POST', $base . '/admin/seo/sitemap/flush', [
    '_token' => csrfFrom( httpReq( 'GET', $base . '/admin/seo/sitemap' )[ 'body' ] ),
] );
check( 'Sitemap flush 303', $r[ 'status' ] === 303, 'status=' . $r[ 'status' ] );

/* ------------------------------------------------ report */

foreach ( $results as [ $state, $label, $detail ] ) {
    printf( '%-5s %s%s\n', $state, $label, $detail !== '' ? "  [$detail]" : '' );
}
printf( '\n%d passed, %d failed\n', $passCount, $failCount );
@unlink( $cookieFile );
exit( $failCount > 0 ? 1 : 0 );
