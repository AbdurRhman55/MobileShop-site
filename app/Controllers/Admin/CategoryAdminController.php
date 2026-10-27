<?php

declare( strict_types = 1 );

namespace App\Controllers\Admin;

use App\Core\Database;
use App\Core\HttpRedirect;
use App\Models\Catalog;
use App\Models\SeoMeta;

final class CategoryAdminController extends AdminBaseController {
    public function index(): void {
        $rows = Database::fetchAll(
            'SELECT c.*, p.name AS parent_name, m.seo_title
             FROM categories c
             LEFT JOIN categories p ON p.id = c.parent_id
             LEFT JOIN seo_meta m ON m.entity_type="category" AND m.entity_id = c.id
             ORDER BY c.position, c.name'
        );
        $this->renderAdmin( 'admin/categories/index', [ 'rows' => $rows, 'flash' => $this->takeFlash() ] );
    }

    public function create(): void {
        $this->renderAdmin( 'admin/categories/form', [
            'category'  => null,
            'parents'   => Database::fetchAll( "SELECT id,name FROM categories WHERE status='active' ORDER BY name" ),
            'seo'       => null,
            'flash'     => $this->takeFlash(),
        ] );
    }

    public function store(): void {
        $data = $this->collect();
        if ( $data === null ) {
            $this->flash( 'Name is required.', 'error' );
            HttpRedirect::to( '/admin/categories/create', 303 );
        }
        $id = Catalog::saveCategory( $data );
        $this->saveSeo( $id );
        $this->flash( 'Category created.' );
        HttpRedirect::to( "/admin/categories/$id/edit", 303 );
    }

    public function edit( string $id ): void {
        $category = Catalog::categoryById( ( int ) $id );
        if ( $category === null ) {
            HttpRedirect::to( '/admin/categories', 303 );
        }
        $this->renderAdmin( 'admin/categories/form', [
            'category' => $category,
            'parents'  => Database::fetchAll( 'SELECT id,name FROM categories WHERE id <> ? ORDER BY name', [ ( int ) $id ] ),
            'seo'      => SeoMeta::get( 'category', ( int ) $id ),
            'flash'    => $this->takeFlash(),
        ] );
    }

    public function update( string $id ): void {
        $data = $this->collect();
        if ( $data === null ) {
            $this->flash( 'Name is required.', 'error' );
            HttpRedirect::to( "/admin/categories/$id/edit", 303 );
        }
        Catalog::saveCategory( $data, ( int ) $id );
        $this->saveSeo( ( int ) $id );
        $this->flash( 'Category updated.' );
        HttpRedirect::to( "/admin/categories/$id/edit", 303 );
    }

    private function collect(): ?array {
        $name = trim( ( string ) $this->request->input( 'name', '' ) );
        if ( $name === '' ) {
            return null;
        }
        $slug = slugify( ( string ) $this->request->input( 'slug', '' ) ?: $name );
        return [
            'name'      => $name,
            'slug'      => $slug,
            'parent_id' => ( ( int ) $this->request->input( 'parent_id', 0 ) ) ?: null,
            'intro'     => sanitizeRichHtml( ( string ) $this->request->input( 'intro', '' ) ),
            'image'     => trim( ( string ) $this->request->input( 'image', '' ) ) ?: null,
            'banner_image' => trim( ( string ) $this->request->input( 'banner_image', '' ) ) ?: null,
            'icon'      => preg_replace( '/[^a-z0-9-]/i', '', ( string ) $this->request->input( 'icon', '' ) ) ?: null,
            'meta_title' => trim( ( string ) $this->request->input( 'meta_title', '' ) ) ?: null,
            'meta_description' => trim( ( string ) $this->request->input( 'meta_description', '' ) ) ?: null,
            'position'  => ( int ) $this->request->input( 'position', 0 ),
            'sort_order' => ( int ) $this->request->input( 'sort_order', $this->request->input( 'position', 0 ) ),
            'status'    => $this->request->input( 'status' ) === 'hidden' ? 'hidden' : 'active',
            'is_active' => $this->request->input( 'status' ) === 'hidden' ? 0 : 1,
        ];
    }

    private function saveSeo( int $id ): void {
        SeoMeta::save( 'category', $id, [
            'seo_title'        => ( string ) $this->request->input( 'seo_title', '' ),
            'meta_description' => ( string ) $this->request->input( 'meta_description', '' ),
            'canonical_path'   => ( string ) $this->request->input( 'canonical_path', '' ),
            'robots'           => ( string ) $this->request->input( 'robots', '' ),
            'og_title'         => ( string ) $this->request->input( 'og_title', '' ),
            'og_description'   => ( string ) $this->request->input( 'og_description', '' ),
            'custom_head'      => '',
        ] );
    }
}
