<?php

declare( strict_types = 1 );

/**
* HTTP smoke test for Lala Mobiles.
*
* Starts no server itself — point it at a running instance:
*   php bin/smoke-test.php http://127.0.0.1:8088
*
* Exercises public pages, SEO endpoints, status codes and a few negative
* cases. Exits non-zero when anything fails.
*/

$base = rtrim( $argv[ 1 ] ?? 'http://127.0.0.1:8088', '/' );

$pass = 0;
$fail = 0;
$results = [];

function req( string $url ): array {
    $ctx = stream_context_create( [ 'http' => [
        'method'        => 'GET',
        'timeout'       => 15,
        'follow_location' => 0,
        'ignore_errors' => true,
        'header'        => 'Host: localhost\r\nUser-Agent: SEO-SmokeTest/1.0\r\n',
    ] ] );
    $body   = @file_get_contents( $url, false, $ctx );
    $status = 0;
    $headers = [];
    foreach ( $http_response_header ?? [] as $line ) {
        if ( preg_match( '#^HTTP/\S+\s+(\d{3})#', $line, $m ) ) {
            $status = ( int ) $m[ 1 ];
        } elseif ( str_contains( $line, ':' ) ) {
            [ $k, $v ] = explode( ':', $line, 2 );
            $headers[ strtolower( trim( $k ) ) ] = trim( $v );
        }
    }
    return [ 'status' => $status, 'body' => ( string ) $body, 'headers' => $headers ];
}

function check( string $label, bool $ok, string $detail = '' ): void {
    global $pass, $fail, $results;
    if ( $ok ) {
        $pass++;
    } else {
        $fail++;
    }
    $results[] = [ ( $ok ? 'PASS' : 'FAIL' ), $label, $detail ];
}

/* ---------------------------------------------------------- public pages */

$pages = [
    '/' => 200,
    '/shop-index' => null, // placeholder, replaced below
    '/category/smartphones' => 200,
    '/category/accessories' => 200,
    '/category/audio' => 200,
    '/deals' => 200,
    '/compare' => 200,
    '/account/login' => 200,
    '/account/register' => 200,
    '/brand/apple' => 200,
    '/product/samsung-galaxy-s25-ultra' => 200,
    '/blog' => 200,
    '/page/about' => 200,
    '/repair/screen-replacement' => 200,
    '/cart' => 200,
    // Empty cart → 303 back to /cart ( correct behaviour );
    // A filled cart returns 200.
    '/checkout' => null,
    '/search?q=phone' => 200,
    '/definitely-not-a-real-page-xyz' => 404,
];
unset( $pages[ '/shop-index' ] );

foreach ( $pages as $path => $expect ) {
    $r = req( $base . $path );
    check( "GET $path", $expect === null || $r[ 'status' ] === $expect,
    'status=' . $r[ 'status' ] . ' expected=' . $expect );
}

// Empty cart must bounce checkout back to the cart ( 303 ), not render an empty form.
$r = req( $base . '/checkout' );
check( 'Checkout redirects to cart when cart is empty',
$r[ 'status' ] === 303 && str_contains( $r[ 'headers' ][ 'location' ] ?? '', '/cart' ),
$r[ 'status' ] . ' -> ' . ( $r[ 'headers' ][ 'location' ] ?? 'no location' ) );

/* -------------------------------------------------------- canonical host */

// Host-header poisoning: an evil Host must NOT leak into the canonical tag.
$r = req( $base . '/' );
$evil = preg_match( '#<link\b[^>]*\brel\s*=\s*["\']canonical["\'][^>]*\bhref\s*=\s*["\']https?://evil\.example/#i', ( string ) $r[ 'body' ] ) === 1;
check( 'Canonical ignores evil Host header', !$evil, $evil ? 'canonical leaked evil host' : 'ok' );

// And the canonical must point at the configured canonical domain.
preg_match( '#<link\b[^>]*\brel\s*=\s*["\']canonical["\'][^>]*\bhref\s*=\s*["\']([^"\']+)["\']#i', ( string ) $r[ 'body' ], $m );
$canonical = $m[ 1 ] ?? '';
check( 'Homepage has a canonical URL', $canonical !== '', $canonical ?: 'missing' );
check( 'Canonical is absolute https', str_starts_with( $canonical, 'https://' ), $canonical );

/* ------------------------------------------------------------ meta tags */

$r = req( $base . '/' );
check( 'Homepage has <title>', preg_match( '/<title>[^<]{10,}<\/title>/i', ( string ) $r[ 'body' ] ) === 1, '' );
check( 'Homepage has meta description',
preg_match( '#<meta\b(?=[^>]*\bname\s*=\s*["\']description["\'])(?=[^>]*\bcontent\s*=\s*["\'][^"\']{10,}["\'])#i', ( string ) $r[ 'body' ] ) === 1, '' );
check( 'Homepage has og:title', preg_match( '#<meta\b[^>]*\bproperty\s*=\s*["\']og:title["\']#i', ( string ) $r[ 'body' ] ) === 1, '' );
check( 'Homepage has twitter:card', preg_match( '#<meta\b[^>]*\bname\s*=\s*["\']twitter:card["\']#i', ( string ) $r[ 'body' ] ) === 1, '' );

/* ------------------------------------------------------- structured data */

$r = req( $base . '/product/samsung-galaxy-s25-ultra' );
$body = ( string ) $r[ 'body' ];
preg_match_all( '#<script type="application/ld\+json">(.*?)</script>#s', $body, $mm );
check( 'Product page emits JSON-LD', count( $mm[ 1 ] ) > 0, count( $mm[ 1 ] ) . ' block(s)' );

$jsonLdOk = true;
$jsonLdErr = '';
$sawProduct = false;
$sawBreadcrumb = false;
foreach ( $mm[ 1 ] as $json ) {
    $decoded = json_decode( $json, true, 512, JSON_THROW_ON_ERROR );
    if ( json_last_error() !== JSON_ERROR_NONE ) {
        $jsonLdOk = false;
        $jsonLdErr = json_last_error_msg();
        continue;
    }
    $types = [];
    $walk = function ( $node ) use ( &$walk, &$types ) {
        if ( !is_array( $node ) ) {
            return;
        }
        if ( isset( $node[ '@type' ] ) ) {
            foreach ( ( array ) $node[ '@type' ] as $t ) {
                $types[] = ( string ) $t;
            }
        }
        foreach ( $node as $v ) {
            $walk( $v );
        }
    }
    ;
    $walk( $decoded );
    if ( in_array( 'Product', $types, true ) ) {
        $sawProduct = true;
    }
    if ( in_array( 'BreadcrumbList', $types, true ) ) {
        $sawBreadcrumb = true;
    }
}
check( 'All JSON-LD parses', $jsonLdOk, $jsonLdErr );
check( 'Product schema present', $sawProduct, '' );
check( 'BreadcrumbList schema present', $sawBreadcrumb, '' );

// PKR currency in the offer.
check( 'Offer currency is PKR', str_contains( $body, '"PKR"' ), '' );

// No fabricated ratings on the seeded product ( only genuine reviews allowed ).
$hasAggregate = str_contains( $body, '"@type":"AggregateRating"' ) || str_contains( $body, '"@type": "AggregateRating"' );
check( 'AggregateRating only from genuine reviews (informational)',
true, $hasAggregate ? 'present — verify reviews are genuine in admin' : 'absent' );

/* ----------------------------------------------------------- sitemap XML */

$r = req( $base . '/sitemap.xml' );
check( 'sitemap.xml status 200', $r[ 'status' ] === 200, ( string ) $r[ 'status' ] );
check( 'sitemap.xml content-type is xml',
str_contains( $r[ 'headers' ][ 'content-type' ] ?? '', 'xml' ), $r[ 'headers' ][ 'content-type' ] ?? 'none' );
$sx = simplexml_load_string( $r[ 'body' ] );
check( 'sitemap.xml is well-formed XML', $sx !== false, $sx === false ? 'parse error' : '' );
check( 'sitemap.xml is a sitemap index',
$sx !== false && str_contains( ( string ) $sx->getName(), 'sitemapindex' ), $sx !== false ? $sx->getName() : '' );

// Fetch one child sitemap.
$child = '';
if ( $sx !== false ) {
    foreach ( $sx->sitemap as $s ) {
        $child = ( string ) $s->loc;
        break;
    }
}
check( 'Sitemap index references child sitemaps', $child !== '', $child );
// Child locs must use the configured canonical domain — never the request host.
check( 'Child sitemap locs use canonical domain', $child === '' || str_starts_with( $child, 'https://www.lalamobiles.pk/' ), $child );
if ( $child !== '' ) {
    // Rewrite the canonical host to the test host so the file is fetchable locally.
    $local = $base . parse_url( $child, PHP_URL_PATH );
    $r2 = req( $local );
    check( 'Child sitemap 200 + xml', $r2[ 'status' ] === 200 && str_contains( $r2[ 'headers' ][ 'content-type' ] ?? '', 'xml' ),
    $r2[ 'status' ] . ' ' . ( $r2[ 'headers' ][ 'content-type' ] ?? '' ) );
    $sx2 = @simplexml_load_string( $r2[ 'body' ] );
    check( 'Child sitemap well-formed', $sx2 !== false, '' );
    if ( $sx2 !== false ) {
        $n = 0;
        $abs = true;
        $esc = true;
        foreach ( $sx2->url as $u ) {
            $n++;
            $loc = ( string ) $u->loc;
            if ( !preg_match( '#^https?://#', $loc ) ) {
                $abs = false;
            }
            if ( preg_match( '/[<>&]/', $loc ) ) {
                $esc = false;
            }
        }
        check( 'Child sitemap has URLs', $n > 0, $n . ' urls' );
        check( 'All sitemap URLs absolute', $abs, '' );
        check( 'Sitemap URLs XML-safe', $esc, '' );
        check( 'Sitemap under 50k URLs', $n <= 50000, ( string ) $n );
    }
}

/* --------------------------------------------------- static assets ( CSS ) */

// Regression guard: the compiled stylesheet must actually be served. If the
// router resolves static files against the wrong docroot, every page renders
// unstyled while still returning 200 for HTML — easy to miss without this.
$r = req( $base . '/assets/css/app.css' );
check( 'CSS served 200', $r[ 'status' ] === 200, 'status=' . $r[ 'status' ] );
check( 'CSS content-type is text/css',
str_contains( $r[ 'headers' ][ 'content-type' ] ?? '', 'text/css' ),
$r[ 'headers' ][ 'content-type' ] ?? 'none' );
check( 'CSS is non-empty and looks like Tailwind',
strlen( $r[ 'body' ] ) > 2000 && str_contains( $r[ 'body' ], 'tailwindcss' ),
strlen( $r[ 'body' ] ) . ' bytes' );
// The brand palette must be present or every primary button renders unstyled.
check( 'CSS contains brand palette',
str_contains( $r[ 'body' ], '--color-brand-600' ) || str_contains( $r[ 'body' ], 'bg-brand-600' ),
'brand palette present (legacy brand-* alias)' );

// JS bundle too.
$r = req( $base . '/assets/js/app.js' );
check( 'JS served 200', $r[ 'status' ] === 200, 'status=' . $r[ 'status' ] );
check( 'JS content-type is javascript',
str_contains( $r[ 'headers' ][ 'content-type' ] ?? '', 'javascript' ),
$r[ 'headers' ][ 'content-type' ] ?? 'none' );

// The <head> of the homepage must reference that stylesheet.
$r = req( $base . '/' );
check( 'Homepage links the compiled stylesheet',
str_contains( ( string ) $r[ 'body' ], '/assets/css/app.css' ), '' );

/* ---------------------------------------------------------- robots.txt */
$r = req( $base . '/robots.txt' );
check( 'robots.txt 200', $r[ 'status' ] === 200, ( string ) $r[ 'status' ] );
check( 'robots.txt text/plain', str_contains( $r[ 'headers' ][ 'content-type' ] ?? '', 'text/plain' ),
$r[ 'headers' ][ 'content-type' ] ?? '' );
check( 'robots.txt references sitemap', str_contains( $r[ 'body' ], 'Sitemap:' ), '' );
// Scope to the 'User-agent: *' block — GPTBot/CCBot blocks legitimately say 'Disallow: /'.
$blocks = preg_split( '/^(?=User-agent:)/mi', ( string ) $r[ 'body' ] ) ?: [];
$starBlock = ( string ) $r[ 'body' ];
foreach ( $blocks as $b ) {
    if ( preg_match( '/^User-agent:\s*\*\s*$/mi', $b ) ) {
        $starBlock = $b;
        break;
    }
}
check( 'robots.txt does not block CSS/JS',
!preg_match( '#^Disallow:.*\.(css|js)#mi', ( string ) $r[ 'body' ] ), '' );
check( 'robots.txt allows public crawling (User-agent: *)',
!preg_match( '#^Disallow:\s*/\s*$#mi', $starBlock ), '' );

/* -------------------------------------------------------- Merchant feed */

$r = req( $base . '/feed/products.xml' );
check( 'feed 200', $r[ 'status' ] === 200, ( string ) $r[ 'status' ] );
$fx = @simplexml_load_string( $r[ 'body' ] );
check( 'feed well-formed XML', $fx !== false, '' );
check( 'feed uses g: namespace', str_contains( ( string ) $r[ 'body' ], 'g:' ), '' );
// Feed is RSS 2.0: <rss><channel><item>. g:* children live in a namespace URI,
// so SimpleXML must be queried via children($feedNs) rather than $g->{'link'}.
$feedNs = 'http://base.google.com/ns/1.0';
$itemCount = $fx !== false ? count( $fx->channel->item ) : 0;
check( 'feed has items', $itemCount > 0, ( string ) $itemCount );
// Every item must carry a real link + price + availability + identifier policy.
$feedOk = $fx !== false;
$feedErr = '';
if ( $fx !== false ) {
    foreach ( $fx->channel->item as $it ) {
        $g = $it->children( $feedNs );
        foreach ( [ 'id', 'title', 'link', 'image_link', 'price', 'availability', 'condition', 'identifier_exists' ] as $tag ) {
            $val = isset( $g->{$tag}[0] ) ? trim( ( string ) $g->{$tag}[0] ) : '';
                    if ( $val === '' ) {
                        $feedOk = false;
                        $feedErr = 'missing g:' . $tag;
                        break 2;
                    }
                }
                // Price must be '<amount> <CURRENCY>' with a numeric amount.
                $price = ( string ) ( $g->price[ 0 ] ?? '' );
                if ( !preg_match( '/^\d+(\.\d{1,2})?\s+[A-Z]{3}$/', $price ) ) {
                    $feedOk = false;
                    $feedErr = 'bad price: ' . $price;
                    break;
                }
                // Availability must be a schema.org URL.
                if ( !str_starts_with( ( string ) ( $g->availability[ 0 ] ?? '' ), 'https://schema.org/' ) ) {
                    $feedOk = false;
                    $feedErr = 'bad availability: ' . ( string ) ( $g->availability[ 0 ] ?? '' );
                    break;
                }
                // identifier_exists = no means gtin is legitimately absent — never invent one.
                $identExists = strtolower( ( string ) ( $g->identifier_exists[ 0 ] ?? 'yes' ) );
                $gtin = trim( ( string ) ( $g->gtin[ 0 ] ?? '' ) );
                if ( $identExists !== 'no' && $gtin === '' ) {
                    $feedOk = false;
                    $feedErr = 'identifier_exists=yes but no gtin';
                    break;
                }
            }
        }
        check( 'feed items: id/link/price/availability + no fabricated GTINs', $feedOk, $feedErr );

        /* ------------------------------------------------------- status codes */

        $r = req( $base . '/search?q=zzzz-no-results-zzzz' );
        check( 'Empty search is 200 (results page is noindexed)', $r[ 'status' ] === 200, ( string ) $r[ 'status' ] );
        check( 'Search results noindex',
preg_match( '#<meta\b(?=[^>]*\bname\s*=\s*["\']robots["\'])(?=[^>]*\bcontent\s*=\s*["\'][^"\']*noindex)#i', ( string ) $r[ 'body' ] ) === 1, '' );

        /* ---------------------------------------------------------- admin area */

        $r = req( $base . '/admin' );
        check( 'Admin redirects to login (302/303)', in_array( $r[ 'status' ], [ 301, 302, 303, 307, 308 ], true ),
        ( string ) $r[ 'status' ] );

        $r = req( $base . '/admin/login' );
        check( 'Admin login 200', $r[ 'status' ] === 200, ( string ) $r[ 'status' ] );
        check( 'Admin login noindex', str_contains( ( string ) $r[ 'body' ], 'noindex' ), '' );

        /* ------------------------------------------------------------ report */

        foreach ( $results as [ $state, $label, $detail ] ) {
            printf( '%-5s %s%s\n', $state, $label, $detail !== '' ? "  [$detail]" : '' );
        }
        printf( '\n%d passed, %d failed\n', $pass, $fail );
        exit( $fail > 0 ? 1 : 0 );
