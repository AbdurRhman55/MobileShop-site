<?php

declare( strict_types = 1 );

namespace App\Controllers\Admin;

use App\Core\Database;
use App\Seo\SeoAudit;
use App\Seo\SeoSettings;
use App\Seo\SitemapGenerator;

/** Admin dashboard: store overview + SEO health widgets. */
final class DashboardController extends AdminBaseController {
    public function index(): void {
        $stats = [
            'products'    => ( int ) Database::fetchColumn( "SELECT COUNT(*) FROM products WHERE status='active'" ),
            'categories'  => ( int ) Database::fetchColumn( "SELECT COUNT(*) FROM categories WHERE status='active'" ),
            'brands'      => ( int ) Database::fetchColumn( "SELECT COUNT(*) FROM brands WHERE status='active'" ),
            'orders'      => ( int ) Database::fetchColumn( 'SELECT COUNT(*) FROM orders' ),
            'reviews_pending' => ( int ) Database::fetchColumn( 'SELECT COUNT(*) FROM reviews WHERE approved=0' ),
            'redirects'   => ( int ) Database::fetchColumn( 'SELECT COUNT(*) FROM redirects' ),
            'blog'        => ( int ) Database::fetchColumn( "SELECT COUNT(*) FROM blog_posts WHERE status='published'" ),
            'filter_pages'=> ( int ) Database::fetchColumn( "SELECT COUNT(*) FROM filter_pages WHERE status='published'" ),
            'customers'   => ( int ) Database::fetchColumn( 'SELECT COUNT(*) FROM customers' ),
            'demo_orders' => ( int ) Database::fetchColumn( "SELECT COUNT(*) FROM orders WHERE reference LIKE 'DEMO-%'" ),
            'low_stock'   => ( int ) Database::fetchColumn( "SELECT COUNT(*) FROM products WHERE status='active' AND stock BETWEEN 1 AND 5" ),
        ];

        $recentOrders = Database::fetchAll( 'SELECT * FROM orders ORDER BY created_at DESC LIMIT 8' );
        $auditSummary = SeoAudit::summary();

        /* --- real activity series ( last 14 days, from actual order rows ) --- */
        $seriesDays = 14;
        $tz = ( string ) \App\Core\Config::get( 'app.timezone', 'Asia/Karachi' );
        try {
            $now = new \DateTimeImmutable( 'now', new \DateTimeZone( $tz ) );
        } catch ( \Throwable ) {
            $now = new \DateTimeImmutable( 'now' );
        }

        // Aggregate orders per day server-side so the chart never ships raw rows.
        // created_at is written in the app timezone ( bootstrap sets PHP's default
        // TZ), so we group on it directly rather than CONVERT_TZ — which would
        // double-shift and requires MySQL's tz tables to be loaded.
        $rows = Database::fetchAll(
            "SELECT DATE(created_at) AS d,
                    COUNT(*) AS orders,
                    COALESCE(SUM(total), 0) AS revenue
             FROM orders
             WHERE created_at >= ?
             GROUP BY d
             ORDER BY d ASC",
            [ $now->modify( '-' . ( $seriesDays - 1 ) . ' days' )->format( 'Y-m-d 00:00:00' ) ]
        );

        $byDay = [];
        foreach ( $rows as $row ) {
            $byDay[ ( string ) $row[ 'd' ] ] = [
                'orders'  => ( int ) $row[ 'orders' ],
                'revenue' => ( float ) $row[ 'revenue' ],
            ];
        }

        $chart = [ 'labels' => [], 'orders' => [], 'revenue' => [] ];
        for ( $i = $seriesDays - 1; $i >= 0; $i-- ) {
            $day  = $now->modify( '-' . $i . ' days' );
            $key  = $day->format( 'Y-m-d' );
            $cell = $byDay[ $key ] ?? [ 'orders' => 0, 'revenue' => 0.0 ];
            $chart[ 'labels' ][] = $day->format( 'd M' );
            $chart[ 'orders' ][] = $cell[ 'orders' ];
            $chart[ 'revenue' ][] = $cell[ 'revenue' ];
        }

        // Totals for the headline tiles ( genuine, from the orders table ).
        $totals = Database::fetch(
            'SELECT COUNT(*) AS orders,
                    COALESCE(SUM(total), 0) AS revenue
             FROM orders
             WHERE created_at >= ?',
            [ $now->modify( '-' . ( $seriesDays - 1 ) . ' days' )->format( 'Y-m-d 00:00:00' ) ]
        ) ?: [ 'orders' => 0, 'revenue' => 0 ];

        $stats[ 'orders_14d' ]  = ( int ) $totals[ 'orders' ];
        $stats[ 'revenue_14d' ] = ( float ) $totals[ 'revenue' ];

        // Order-status mix, for the status breakdown.
        $statusMix = [];
        foreach ( Database::fetchAll( 'SELECT status, COUNT(*) AS c FROM orders GROUP BY status ORDER BY c DESC' ) as $s ) {
            $statusMix[ ( string ) $s[ 'status' ] ] = ( int ) $s[ 'c' ];
        }

        $this->renderAdmin( 'admin/dashboard', [
            'stats'        => $stats,
            'recentOrders' => $recentOrders,
            'auditSummary' => $auditSummary,
            'chart'        => $chart,
            'statusMix'    => $statusMix,
            'seoSettings'  => [
                'canonical_domain' => SeoSettings::get( 'canonical_domain', '' ),
                'ga4'              => SeoSettings::ga4Id() !== '' ? SeoSettings::ga4Id() : null,
                'gsc'              => SeoSettings::googleVerificationMeta() !== '' ? 'configured' : null,
                'site_indexable'   => SeoSettings::siteIndexable(),
            ],
            'flash' => $this->takeFlash(),
        ] );
    }
}
