<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Database;
use App\Core\HttpRedirect;
use App\Models\Content;
use App\Models\SeoMeta;

final class BlogAdminController extends AdminBaseController
{
    public function index(): void
    {
        $rows = Database::fetchAll(
            'SELECT bp.*, m.seo_title
             FROM blog_posts bp
             LEFT JOIN seo_meta m ON m.entity_type="blog" AND m.entity_id = bp.id
             ORDER BY bp.id DESC'
        );
        $this->renderAdmin('admin/blog/index', ['rows' => $rows, 'flash' => $this->takeFlash()]);
    }

    public function create(): void
    {
        $this->renderAdmin('admin/blog/form', ['post' => null, 'seo' => null, 'flash' => $this->takeFlash()]);
    }

    public function store(): void
    {
        $data = $this->collect();
        if ($data === null) {
            $this->flash('Title is required.', 'error');
            HttpRedirect::to('/admin/blog/create', 303);
        }
        $id = Content::savePost($data);
        $this->saveSeo($id);
        $this->flash('Post created.');
        HttpRedirect::to("/admin/blog/$id/edit", 303);
    }

    public function edit(string $id): void
    {
        $post = Database::fetch('SELECT * FROM blog_posts WHERE id = ?', [(int) $id]);
        if ($post === null) {
            HttpRedirect::to('/admin/blog', 303);
        }
        $this->renderAdmin('admin/blog/form', [
            'post'  => $post,
            'seo'   => SeoMeta::get('blog', (int) $id),
            'flash' => $this->takeFlash(),
        ]);
    }

    public function update(string $id): void
    {
        $data = $this->collect();
        if ($data === null) {
            $this->flash('Title is required.', 'error');
            HttpRedirect::to("/admin/blog/$id/edit", 303);
        }
        Content::savePost($data, (int) $id);
        $this->saveSeo((int) $id);
        $this->flash('Post updated.');
        HttpRedirect::to("/admin/blog/$id/edit", 303);
    }

    private function collect(): ?array
    {
        $title = trim((string) $this->request->input('title', ''));
        if ($title === '') {
            return null;
        }
        $status = $this->request->input('status') === 'published' ? 'published' : 'draft';
        return [
            'title'       => $title,
            'slug'        => slugify((string) $this->request->input('slug', '') ?: $title),
            'excerpt'     => mb_substr(trim((string) $this->request->input('excerpt', '')), 0, 512) ?: null,
            'body'        => sanitizeRichHtml((string) $this->request->input('body', '')),
            'author_name' => trim((string) $this->request->input('author_name', '')) ?: 'Lala Mobiles Team',
            'status'      => $status,
            'published_at'=> $status === 'published' ? ($this->request->input('published_at') ?: date('Y-m-d H:i:s')) : null,
        ];
    }

    private function saveSeo(int $id): void
    {
        SeoMeta::save('blog', $id, [
            'seo_title'        => (string) $this->request->input('seo_title', ''),
            'meta_description' => (string) $this->request->input('meta_description', ''),
            'canonical_path'   => (string) $this->request->input('canonical_path', ''),
            'robots'           => (string) $this->request->input('robots', ''),
            'og_title'         => (string) $this->request->input('og_title', ''),
            'og_description'   => (string) $this->request->input('og_description', ''),
            'og_image'         => (string) $this->request->input('og_image', ''),
        ]);
    }
}
