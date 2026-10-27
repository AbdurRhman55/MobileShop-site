<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Database;
use App\Core\HttpRedirect;
use App\Models\Catalog;
use App\Models\SeoMeta;

final class BrandAdminController extends AdminBaseController
{
    public function index(): void
    {
        $rows = Database::fetchAll(
            'SELECT b.*, m.seo_title
             FROM brands b
             LEFT JOIN seo_meta m ON m.entity_type="brand" AND m.entity_id = b.id
             ORDER BY b.name'
        );
        $this->renderAdmin('admin/brands/index', ['rows' => $rows, 'flash' => $this->takeFlash()]);
    }

    public function create(): void
    {
        $this->renderAdmin('admin/brands/form', ['brand' => null, 'seo' => null, 'flash' => $this->takeFlash()]);
    }

    public function store(): void
    {
        $data = $this->collect();
        if ($data === null) {
            $this->flash('Name is required.', 'error');
            HttpRedirect::to('/admin/brands/create', 303);
        }
        $id = Catalog::saveBrand($data);
        $this->saveSeo($id);
        $this->flash('Brand created.');
        HttpRedirect::to("/admin/brands/$id/edit", 303);
    }

    public function edit(string $id): void
    {
        $brand = Catalog::brandById((int) $id);
        if ($brand === null) {
            HttpRedirect::to('/admin/brands', 303);
        }
        $this->renderAdmin('admin/brands/form', [
            'brand' => $brand,
            'seo'   => SeoMeta::get('brand', (int) $id),
            'flash' => $this->takeFlash(),
        ]);
    }

    public function update(string $id): void
    {
        $data = $this->collect();
        if ($data === null) {
            $this->flash('Name is required.', 'error');
            HttpRedirect::to("/admin/brands/$id/edit", 303);
        }
        Catalog::saveBrand($data, (int) $id);
        $this->saveSeo((int) $id);
        $this->flash('Brand updated.');
        HttpRedirect::to("/admin/brands/$id/edit", 303);
    }

    private function collect(): ?array
    {
        $name = trim((string) $this->request->input('name', ''));
        if ($name === '') {
            return null;
        }
        return [
            'name'    => $name,
            'slug'    => slugify((string) $this->request->input('slug', '') ?: $name),
            'website' => filter_var((string) $this->request->input('website', ''), FILTER_VALIDATE_URL) ?: null,
            'intro'   => sanitizeRichHtml((string) $this->request->input('intro', '')),
            'status'  => $this->request->input('status') === 'hidden' ? 'hidden' : 'active',
        ];
    }

    private function saveSeo(int $id): void
    {
        SeoMeta::save('brand', $id, [
            'seo_title'        => (string) $this->request->input('seo_title', ''),
            'meta_description' => (string) $this->request->input('meta_description', ''),
            'canonical_path'   => (string) $this->request->input('canonical_path', ''),
            'robots'           => (string) $this->request->input('robots', ''),
            'og_title'         => (string) $this->request->input('og_title', ''),
            'og_description'   => (string) $this->request->input('og_description', ''),
        ]);
    }
}
