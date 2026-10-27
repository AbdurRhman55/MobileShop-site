<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Http;
use App\Models\Catalog;
use App\Models\FilterPage;
use App\Models\Product;
use App\Seo\StructuredData;
use App\Seo\Url;

/**
 * Admin-curated filter landing page (/shop/{slug}).
 *
 * These are the ONLY filter combinations that are indexable: a human decides
 * which faceted URLs deserve unique content and their own SEO metadata.
 */
final class FilterPageController extends BaseController
{
    public function show(string $slug): void
    {
        $filterPage = FilterPage::bySlug($slug);
        if ($filterPage === null) {
            throw new Http(404, 'This page does not exist.');
        }

        $page = max(1, (int) ($this->request->query('page') ?? 1));

        // Curated filters define the page; the visitor may narrow further.
        $filters   = json_decode((string) $filterPage['filters'], true) ?: [];
        $userQuery = $this->request->query;
        $query     = [];
        foreach (['storage', 'ram', 'color'] as $dim) {
            if (!empty($filters[$dim])) {
                $query[$dim] = (array) $filters[$dim];
            }
        }
        foreach (['min_price', 'max_price', 'condition'] as $scalar) {
            if (isset($filters[$scalar]) && $filters[$scalar] !== '') {
                $query[$scalar] = $filters[$scalar];
            }
        }
        foreach (\App\Seo\Facets::FILTER_PARAMS as $param) {
            if (isset($userQuery[$param]) && $userQuery[$param] !== '' && $userQuery[$param] !== []) {
                $query[$param] = $userQuery[$param];
            }
        }
        if (!empty($userQuery['sort'])) {
            $query['sort'] = (string) $userQuery['sort'];
        }

        $categoryId = $filterPage['category_id'] !== null ? (int) $filterPage['category_id'] : null;
        $listing    = Product::listing(
            $query,
            $categoryId,
            $filterPage['brand_id'] !== null ? (int) $filterPage['brand_id'] : null,
            $page,
            12
        );

        $userFilters = array_intersect_key($userQuery, array_flip(\App\Seo\Facets::FILTER_PARAMS));
        $isFiltered  = $userFilters !== [];

        $brands = $categoryId !== null
            ? Catalog::brandsForCategoryIds(Catalog::descendantCategoryIds($categoryId))
            : [];

        $autoTitle = $filterPage['name'] . ' in Pakistan — Lala Mobiles';
        $autoDesc  = $filterPage['intro']
            ? excerpt(strip_tags($filterPage['intro']), 155)
            : 'Shop ' . $filterPage['name'] . ' at Lala Mobiles with warranty and nationwide delivery.';

        $seo = $this->resolveMeta(
            'filter',
            (int) $filterPage['id'],
            $autoTitle,
            $autoDesc,
            '/shop/' . $filterPage['slug'],
            ['og_type' => 'website', 'robots' => $isFiltered ? 'noindex,follow' : 'index,follow'],
        );

        $crumbs = [['Home', '/'], [$filterPage['name'], '/shop/' . $filterPage['slug']]];

        $schema = [
            $this->breadcrumbs($crumbs),
            StructuredData::itemList(
                $filterPage['name'],
                Url::absolute('/shop/' . $filterPage['slug']),
                $listing['items'],
                $listing['total']
            ),
        ];

        $this->renderPage($seo, $schema, 'listing', [
            'listing'        => $listing,
            'heading'        => $filterPage['name'],
            'intro'          => $filterPage['intro'],
            'breadcrumbs'    => $crumbs,
            'basePath'       => '/shop/' . $filterPage['slug'],
            'children'       => [],
            'seoRules'       => ['robots' => $isFiltered ? 'noindex,follow' : 'index,follow', 'is_filtered' => $isFiltered, 'page' => $page, 'canonical_path' => '/shop/' . $filterPage['slug']],
            'filterPageMatch'=> $filterPage,
            'activeFilters'  => $userFilters,
            'sort'           => (string) ($userQuery['sort'] ?? ''),
            'brands'         => $brands,
        ]);
    }
}
