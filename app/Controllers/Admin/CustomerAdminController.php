<?php

declare( strict_types = 1 );

namespace App\Controllers\Admin;

use App\Core\Database;

/** Read-only customer directory and profile views backed by orders/customers. */
final class CustomerAdminController extends AdminBaseController
 {
    public function index(): void
 {
        $q = trim( ( string ) $this->request->query( 'q', '' ) );
        $page = max( 1, ( int ) $this->request->query( 'page', 1 ) );
        $size = 25;
        $params = [];
        $where = '';
        if ( $q !== '' ) {
            $where = 'WHERE c.name LIKE ? OR c.email LIKE ? OR c.phone LIKE ?';
            $like = '%' . $q . '%';
            $params = [ $like, $like, $like ];
        }
        $total = ( int ) Database::fetchColumn( "SELECT COUNT(*) FROM customers c $where", $params );
        $rows = Database::fetchAll(
            "SELECT c.id, c.name, c.email, c.phone, c.created_at,
                    COUNT(DISTINCT o.id) AS order_count,
                    COALESCE(SUM(CASE WHEN o.status <> 'cancelled' THEN o.total ELSE 0 END), 0) AS lifetime_value
             FROM customers c LEFT JOIN orders o ON o.customer_id = c.id $where
             GROUP BY c.id ORDER BY c.created_at DESC LIMIT $size OFFSET " . ( ( $page - 1 ) * $size ),
            $params
        );
        $this->renderAdmin( 'admin/customers/index', [
            'rows' => $rows, 'q' => $q, 'page' => $page,
            'pages' => ( int ) ceil( $total / $size ), 'total' => $total,
        ] );
    }

    public function show( string $id ): void
 {
        $customer = Database::fetch( 'SELECT id, name, email, phone, created_at FROM customers WHERE id = ?', [ ( int ) $id ] );
        if ( $customer === null ) {
            throw new \App\Core\Http( 404, 'Customer not found.' );
        }
        $orders = Database::fetchAll( 'SELECT * FROM orders WHERE customer_id = ? ORDER BY created_at DESC', [ ( int ) $id ] );
        $stats = Database::fetch(
            "SELECT COUNT(*) AS order_count, COALESCE(SUM(CASE WHEN status <> 'cancelled' THEN total ELSE 0 END), 0) AS lifetime_value
             FROM orders WHERE customer_id = ?",
            [ ( int ) $id ]
        ) ?: [ 'order_count' => 0, 'lifetime_value' => 0 ];
        $addresses = Database::fetchAll( 'SELECT * FROM customer_addresses WHERE customer_id = ? ORDER BY is_default DESC, created_at DESC', [ ( int ) $id ] );
        $this->renderAdmin( 'admin/customers/show', [
            'customer' => $customer, 'orders' => $orders, 'stats' => $stats, 'addresses' => $addresses,
        ] );
    }
}
