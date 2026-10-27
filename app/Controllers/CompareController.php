<?php

declare( strict_types = 1 );

namespace App\Controllers;

use App\Core\Csrf;
use App\Models\Product;

/** Session-scoped product comparison list. */
final class CompareController extends BaseController
 {
    public function index(): void
 {
        $ids = array_slice( array_values( array_unique( array_filter( array_map( 'intval', ( array ) ( $_SESSION[ 'compare_ids' ] ?? [] ) ) ) ) ), 0, 4 );
        $products = [];
        foreach ( $ids as $id ) {
            $product = Product::find( $id );
            if ( $product !== null && $product[ 'status' ] === 'active' ) {
                $products[] = $product;
            }
        }
        $seo = $this->resolveMeta( 'home', 0, 'Compare products — Lala Mobiles', 'Compare product features and prices side by side.', '/compare', [ 'robots' => 'noindex,follow' ] );
        $this->renderPage( $seo, [], 'compare', [ 'products' => $products ] );
    }

    public function toggle(): void
 {
        Csrf::verifyRequest( $this->request );
        $id = ( int ) $this->request->input( 'product_id', 0 );
        $product = Product::find( $id );
        if ( $product === null || $product[ 'status' ] !== 'active' ) {
            $this->json( [ 'error' => 'This product cannot be compared.' ], 404 );
        }
        $ids = array_values( array_unique( array_filter( array_map( 'intval', ( array ) ( $_SESSION[ 'compare_ids' ] ?? [] ) ) ) ) );
        $active = false;
        if ( in_array( $id, $ids, true ) ) {
            $ids = array_values( array_diff( $ids, [ $id ] ) );
        } else {
            if ( count( $ids ) >= 4 ) {
                $this->json( [ 'error' => 'Compare up to four products at a time.' ], 409 );
            }
            $ids[] = $id;
            $active = true;
        }
        $_SESSION[ 'compare_ids' ] = $ids;
        $this->json( [ 'ok' => true, 'active' => $active, 'compare_count' => count( $ids ) ] );
    }
}
