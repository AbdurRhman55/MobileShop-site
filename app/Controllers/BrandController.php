<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Http;
use App\Models\Catalog;
use App\Models\Product;
use App\Seo\Facets;
use App\Seo\StructuredData;
use App\Seo\Url;

/** Brand landing page — SEO-optimised, paginated, same facet policy as categories. */
final class BrandController extends BaseController
{
    public function show(string $slug): void
    {
        $brand = Catalog::brandBySlug($slug);
        if ($brand === null) {
            if (\App\Models\Redirect::resolve('/brand/' . $slug) !== null) {
                \App\Seo\RedirectManager::redirectIfFound('/brand/' . $slug);
            }
            throw new Http(404, 'This brand page does not exist.');
        }

        $query = $this->request->query;
        $listing = Product::listing(
            $query,
            null,
            (int) $brand['id'],
            max(1, (int) ($query['page'] ?? 1))
        );

        $basePath = '/brand/' . $brand['slug'];
        $seoRules = Facets::listingSeo($basePath, $query, null);
        $page = $seoRules['page'];

        $titleSuffix = $page > 1 ? " — Page $page" : '';
        $autoTitle = sprintf('%s Mobile Phones Price in Pakistan%s', $brand['name'], $titleSuffix);
        $autoDesc  = $brand['intro']
            ? excerpt(strip_tags($brand['intro']), 155)
            : 'Explore ' . $brand['name'] . ' phones at Lala Mobiles. Genuine devices with warranty and nationwide shipping via Soneri Bank transfer.';

        $seo = $this->resolveMeta(
            'brand',
            (int) $brand['id'],
            $autoTitle,
            $autoDesc,
            $seoRules['canonical_path'],
            ['robots' => $seoRules['robots'], 'og_type' => 'website'],
        );

        $crumbs = [
            ['Home', '/'],
            [$brand['name'], $basePath],
        ];

        $schema = [$this->breadcrumbs($crumbs)];
        if ($page === 1 && !$seoRules['is_filtered']) {
            $schema[] = StructuredData::itemList(
                $brand['name'] . ' Phones',
                Url::absolute($basePath),
                $listing['items'],
                $listing['total']
            );
        }

        $this->renderPage($seo, $schema, 'listing', [
            'listing'       => $listing,
            'heading'       => $brand['name'] . ' Phones',
            'intro'         => $brand['intro'],
            'breadcrumbs'   => $crumbs,
            'basePath'      => $basePath,
            'children'      => [],
            'seoRules'      => $seoRules,
            'filterPageMatch' => null,
            'activeFilters' => array_intersect_key($query, array_flip(Facets::FILTER_PARAMS)),
            'sort'          => (string) ($query['sort'] ?? ''),
            // A brand page is already scoped to one brand, so a brand facet
            // would render an empty box. The other facets still apply.
            'brands'        => [],
        ]);
    }
}
