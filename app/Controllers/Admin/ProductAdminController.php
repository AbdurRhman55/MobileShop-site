<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Database;
use App\Core\HttpRedirect;
use App\Models\Product;
use App\Models\SeoMeta;

/** Product CRUD with integrated SEO editor. */
final class ProductAdminController extends AdminBaseController
{
    public function index(): void
    {
        $q     = trim((string) $this->request->query('q', ''));
        $page  = max(1, (int) ($this->request->query('page') ?? 1));
        $size  = 25;
        $where = '1=1';
        $params = [];
        if ($q !== '') {
            $where = '(p.name LIKE ? OR p.slug LIKE ? OR p.sku LIKE ?)';
            $like = '%' . $q . '%';
            $params = [$like, $like, $like];
        }
        $total = (int) Database::fetchColumn("SELECT COUNT(*) FROM products p WHERE $where", $params);
        $rows  = Database::fetchAll(
            "SELECT p.*, b.name AS brand_name, c.name AS category_name,
                    m.seo_title, m.meta_description
             FROM products p
             LEFT JOIN brands b ON b.id = p.brand_id
             LEFT JOIN categories c ON c.id = p.category_id
             LEFT JOIN seo_meta m ON m.entity_type='product' AND m.entity_id = p.id
             WHERE $where ORDER BY p.id DESC LIMIT $size OFFSET " . (int) (($page - 1) * $size),
            $params
        );

        $this->renderAdmin('admin/products/index', [
            'rows'  => $rows,
            'q'     => $q,
            'page'  => $page,
            'pages' => (int) ceil($total / $size),
            'total' => $total,
            'flash' => $this->takeFlash(),
        ]);
    }

    public function create(): void
    {
        $this->renderAdmin('admin/products/form', [
            'product'  => null,
            'brands'   => Database::fetchAll("SELECT id,name FROM brands WHERE status='active' ORDER BY name"),
            'categories' => Database::fetchAll("SELECT id,name FROM categories WHERE status='active' ORDER BY name"),
            'seo'      => null,
            'variants' => [],
            'specs'    => [],
            'flash'    => $this->takeFlash(),
        ]);
    }

    public function store(): void
    {
        $data = $this->collect();
        if ($data === null) {
            $this->flash('Validation failed — check the required fields.', 'error');
            HttpRedirect::to('/admin/products/create', 303);
        }
        $id = Product::save($data);
        $this->saveChildData($id);
        $this->saveSeo($id);
        $this->flash('Product created.');
        HttpRedirect::to("/admin/products/$id/edit", 303);
    }

    public function edit(string $id): void
    {
        $product = Product::find((int) $id);
        if ($product === null) {
            $this->flash('Product not found.', 'error');
            HttpRedirect::to('/admin/products', 303);
        }
        $this->renderAdmin('admin/products/form', [
            'product'  => $product,
            'brands'   => Database::fetchAll("SELECT id,name FROM brands ORDER BY name"),
            'categories' => Database::fetchAll("SELECT id,name FROM categories ORDER BY name"),
            'seo'      => SeoMeta::get('product', (int) $id),
            'variants' => Product::variants((int) $id),
            'specs'    => Product::specs((int) $id),
            'flash'    => $this->takeFlash(),
        ]);
    }

    public function update(string $id): void
    {
        $product = Product::find((int) $id);
        if ($product === null) {
            $this->flash('Product not found.', 'error');
            HttpRedirect::to('/admin/products', 303);
        }
        $data = $this->collect();
        if ($data === null) {
            $this->flash('Validation failed — check the required fields.', 'error');
            HttpRedirect::to("/admin/products/$id/edit", 303);
        }
        // Delete-then-replace children (simple, transactional).
        Database::transaction(function () use ($id, $data) {
            Product::save($data, (int) $id);
            foreach (['product_variants', 'product_specs'] as $t) {
                Database::query("DELETE FROM `$t` WHERE product_id = ?", [(int) $id]);
            }
        });
        $this->saveChildData((int) $id);
        $this->saveSeo((int) $id);
        $this->flash('Product updated. Sitemap cache flushed.');
        HttpRedirect::to("/admin/products/$id/edit", 303);
    }

    public function delete(string $id): void
    {
        // "Delete" marks the product deleted (keeps audit trail + redirect ability).
        Database::query(
            "UPDATE products SET status = 'deleted' WHERE id = ?",
            [(int) $id]
        );
        \App\Seo\SitemapGenerator::flushCache();
        $this->flash('Product marked as deleted. Its URL now returns 410 unless a replacement is configured.', 'warning');
        HttpRedirect::to('/admin/products', 303);
    }

    /* ------------------------------------------------------- helpers -- */

    /** Collect + validate product fields. Returns null on validation failure. */
    private function collect(): ?array
    {
        $name = trim((string) $this->request->input('name', ''));
        $slug = slugify((string) $this->request->input('slug', '') ?: $name);
        if ($name === '' || $slug === '') {
            return null;
        }
        $price = (float) $this->request->input('price', 0);
        if ($price < 0) {
            return null;
        }
        $saleRaw = trim((string) $this->request->input('sale_price', ''));
        $sale = $saleRaw === '' ? null : (float) $saleRaw;

        $status = (string) $this->request->input('status', 'active');
        if (!in_array($status, ['active', 'discontinued', 'deleted'], true)) {
            $status = 'active';
        }
        $condition = (string) $this->request->input('condition', 'new');
        if (!in_array($condition, ['new', 'used', 'refurbished'], true)) {
            $condition = 'new';
        }

        return [
            'slug'         => $slug,
            'name'         => $name,
            'brand_id'     => ((int) $this->request->input('brand_id', 0)) ?: null,
            'category_id'  => ((int) $this->request->input('category_id', 0)) ?: null,
            'short_desc'   => mb_substr(trim((string) $this->request->input('short_desc', '')), 0, 512) ?: null,
            'description'  => sanitizeRichHtml((string) $this->request->input('description', '')),
            'condition'    => $condition,
            'price'        => $price,
            'sale_price'   => $sale,
            'stock'        => max(0, (int) $this->request->input('stock', 0)),
            'sku'          => trim((string) $this->request->input('sku', '')) ?: null,
            'gtin'         => trim((string) $this->request->input('gtin', '')) ?: null,
            'mpn'          => trim((string) $this->request->input('mpn', '')) ?: null,
            'warranty'     => trim((string) $this->request->input('warranty', '')) ?: null,
            'main_image'   => trim((string) $this->request->input('main_image', '')) ?: null,
            'image_alt'    => mb_substr(trim((string) $this->request->input('image_alt', '')), 0, 255) ?: null,
            'featured'     => (int) $this->request->input('featured', 0) ? 1 : 0,
            'status'       => $status,
            'redirect_to_id' => ((int) $this->request->input('redirect_to_id', 0)) ?: null,
            'published_at' => $this->request->input('published_at') ?: date('Y-m-d H:i:s'),
        ];
    }

    private function saveChildData(int $id): void
    {
        // Variants: parallel arrays from the form.
        $labels  = (array) $this->request->input('v_label', []);
        $rams    = (array) $this->request->input('v_ram', []);
        $storages= (array) $this->request->input('v_storage', []);
        $colors  = (array) $this->request->input('v_color', []);
        $deltas  = (array) $this->request->input('v_delta', []);
        $stocks  = (array) $this->request->input('v_stock', []);
        foreach ($labels as $i => $label) {
            $label = trim((string) $label);
            if ($label === '') {
                continue;
            }
            Database::insert('product_variants', [
                'product_id'  => $id,
                'label'       => $label,
                'ram'         => trim((string) ($rams[$i] ?? '')) ?: null,
                'storage'     => trim((string) ($storages[$i] ?? '')) ?: null,
                'color'       => trim((string) ($colors[$i] ?? '')) ?: null,
                'price_delta' => (float) ($deltas[$i] ?? 0),
                'stock'       => (int) ($stocks[$i] ?? 0),
                'position'    => $i,
            ]);
        }
        // Specs: parallel arrays.
        $groups = (array) $this->request->input('s_group', []);
        $labels2= (array) $this->request->input('s_label', []);
        $values = (array) $this->request->input('s_value', []);
        foreach ($labels2 as $i => $label) {
            $label = trim((string) $label);
            $value = trim((string) ($values[$i] ?? ''));
            if ($label === '' || $value === '') {
                continue;
            }
            Database::insert('product_specs', [
                'product_id' => $id,
                'spec_group' => trim((string) ($groups[$i] ?? '')) ?: 'General',
                'spec_label' => $label,
                'spec_value' => $value,
                'position'   => $i,
            ]);
        }
    }

    private function saveSeo(int $id): void
    {
        SeoMeta::save('product', $id, [
            'seo_title'        => (string) $this->request->input('seo_title', ''),
            'meta_description' => (string) $this->request->input('meta_description', ''),
            'canonical_path'   => (string) $this->request->input('canonical_path', ''),
            'robots'           => (string) $this->request->input('robots', ''),
            'og_title'         => (string) $this->request->input('og_title', ''),
            'og_description'   => (string) $this->request->input('og_description', ''),
            'og_image'         => (string) $this->request->input('og_image', ''),
            'twitter_card'     => (string) $this->request->input('twitter_card', ''),
            'twitter_title'    => (string) $this->request->input('twitter_title', ''),
            'twitter_description' => (string) $this->request->input('twitter_description', ''),
            'custom_head'      => (string) $this->request->input('custom_head', ''),
        ]);
    }
}
