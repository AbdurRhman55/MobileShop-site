<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Request;
use App\Core\Router;
use App\Core\View;
use App\Seo\MetaResolver;
use App\Seo\StructuredData;
use App\Seo\SeoSettings;
use App\Seo\Url;
use App\Models\Catalog;
use App\Models\Content;
use App\Models\Cart;

/**
 * Public storefront controller base.
 *
 * Every page render goes through renderPage() which merges:
 *   - the SEO meta array (MetaResolver)
 *   - the JSON-LD schema list (StructuredData)
 *   - shared layout data (navigation, cart count, verification codes)
 */
abstract class BaseController
{
    public function __construct(
        protected readonly Request $request,
        protected readonly Router $router,
    ) {
    }

    /**
     * @param array    $seo       MetaResolver result
     * @param array    $schema    list of JSON-LD schema arrays
     * @param string   $template  view template
     * @param array    $data      view data
     */
    protected function renderPage(array $seo, array $schema, string $template, array $data = []): void
    {
        $schemaScripts = array_map(
            [StructuredData::class, 'toScriptTag'],
            array_filter($schema)
        );

        echo View::render($template, $data + [
            'seo'            => $seo,
            'schemaScripts'  => implode("\n", $schemaScripts),
            'navCategories'  => Catalog::navCategories(),
            'cartCount'      => Cart::count(),
            'wishlistCount'  => \App\Models\Wishlist::count(),
            'siteName'       => (string) SeoSettings::get('site_name', 'Lala Mobiles'),
            'siteTagline'    => (string) SeoSettings::get('site_tagline', ''),
            'googleVerification' => SeoSettings::googleVerificationMeta(),
            'ga4Id'          => SeoSettings::ga4Id(),
            'gtmId'          => SeoSettings::gtmId(),
            'consentRequired'=> SeoSettings::analyticsConsentRequired(),
            'orgPhone'       => (string) SeoSettings::get('organization_phone', ''),
            'orgEmail'       => (string) SeoSettings::get('organization_email', ''),
            'repairPages'    => Content::publishedRepairPages(),
        ], 'store');
    }

    /** Emit a JSON response and stop. Used by AJAX endpoints (cart, wishlist, etc.). */
    protected function json(array $payload, int $status = 200): void
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        header('X-Content-Type-Options: nosniff');
        echo json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        exit;
    }

    /** Resolve meta for a plain (non-entity) page using stored overrides. */
    protected function resolveMeta(
        string $entityType,
        int $entityId,
        string $autoTitle,
        string $autoDescription,
        string $canonicalPath,
        array $overrides = [],
    ): array {
        return MetaResolver::resolve($entityType, $entityId, $autoTitle, $autoDescription, $canonicalPath, $overrides);
    }

    /** Breadcrumb schema from a list of [name, path] pairs. */
    protected function breadcrumbs(array $crumbs): array
    {
        return StructuredData::breadcrumbList($crumbs);
    }
}
