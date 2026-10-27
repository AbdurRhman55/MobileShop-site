<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Database;
use App\Core\HttpRedirect;
use App\Models\Content;
use App\Models\SeoMeta;

/** Static + repair service pages admin. */
final class PageAdminController extends AdminBaseController
{
    public function index(): void
    {
        $rows = Database::fetchAll(
            'SELECT pg.*, m.seo_title
             FROM pages pg
             LEFT JOIN seo_meta m ON m.entity_type="page" AND m.entity_id = pg.id
             ORDER BY pg.page_type, pg.title'
        );
        $this->renderAdmin('admin/pages/index', ['rows' => $rows, 'flash' => $this->takeFlash()]);
    }

    public function create(): void
    {
        $this->renderAdmin('admin/pages/form', ['page' => null, 'seo' => null, 'flash' => $this->takeFlash()]);
    }

    public function store(): void
    {
        $data = $this->collect();
        if ($data === null) {
            $this->flash('Title is required.', 'error');
            HttpRedirect::to('/admin/pages/create', 303);
        }
        $id = Content::savePage($data);
        $this->saveSeo($id);
        $this->flash('Page created.');
        HttpRedirect::to("/admin/pages/$id/edit", 303);
    }

    public function edit(string $id): void
    {
        $page = Content::pageById((int) $id);
        if ($page === null) {
            HttpRedirect::to('/admin/pages', 303);
        }
        $this->renderAdmin('admin/pages/form', [
            'page'  => $page,
            'seo'   => SeoMeta::get('page', (int) $id),
            'flash' => $this->takeFlash(),
        ]);
    }

    public function update(string $id): void
    {
        $data = $this->collect();
        if ($data === null) {
            $this->flash('Title is required.', 'error');
            HttpRedirect::to("/admin/pages/$id/edit", 303);
        }
        Content::savePage($data, (int) $id);
        $this->saveSeo((int) $id);
        $this->flash('Page updated.');
        HttpRedirect::to("/admin/pages/$id/edit", 303);
    }

    private function collect(): ?array
    {
        $title = trim((string) $this->request->input('title', ''));
        if ($title === '') {
            return null;
        }
        $type = $this->request->input('page_type') === 'repair' ? 'repair' : 'static';
        $slugInput = trim((string) $this->request->input('slug', ''));
        // Repair slugs may contain a sub-path (e.g. repair/screen-replacement).
        $slug = $type === 'repair' && $slugInput !== '' ? trim($slugInput, '/') : slugify($slugInput ?: $title);

        $priceFrom = trim((string) $this->request->input('price_from', ''));
        $priceTo   = trim((string) $this->request->input('price_to', ''));

        return [
            'title'       => $title,
            'slug'        => $slug,
            'page_type'   => $type,
            'intro'       => mb_substr(trim((string) $this->request->input('intro', '')), 0, 512) ?: null,
            'body'        => sanitizeRichHtml((string) $this->request->input('body', '')),
            'service_name'=> $type === 'repair' ? (trim((string) $this->request->input('service_name', '')) ?: null) : null,
            'price_from'  => $priceFrom === '' ? null : (float) $priceFrom,
            'price_to'    => $priceTo === '' ? null : (float) $priceTo,
            'area_served' => $type === 'repair' ? (trim((string) $this->request->input('area_served', '')) ?: null) : null,
            'status'      => $this->request->input('status') === 'published' ? 'published' : 'draft',
        ];
    }

    private function saveSeo(int $id): void
    {
        SeoMeta::save('page', $id, [
            'seo_title'        => (string) $this->request->input('seo_title', ''),
            'meta_description' => (string) $this->request->input('meta_description', ''),
            'canonical_path'   => (string) $this->request->input('canonical_path', ''),
            'robots'           => (string) $this->request->input('robots', ''),
            'og_title'         => (string) $this->request->input('og_title', ''),
            'og_description'   => (string) $this->request->input('og_description', ''),
        ]);
    }
}
