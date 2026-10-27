<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Database;
use App\Core\HttpRedirect;

/** Review moderation. Only approved reviews feed structured data. */
final class ReviewAdminController extends AdminBaseController
{
    public function index(): void
    {
        $rows = Database::fetchAll(
            'SELECT r.*, p.name AS product_name, p.slug AS product_slug
             FROM reviews r JOIN products p ON p.id = r.product_id
             ORDER BY r.approved ASC, r.created_at DESC'
        );
        $this->renderAdmin('admin/reviews/index', ['rows' => $rows, 'flash' => $this->takeFlash()]);
    }

    public function approve(string $id): void
    {
        Database::query('UPDATE reviews SET approved = 1 WHERE id = ?', [(int) $id]);
        $this->syncProductRating((int) $id);
        $this->flash('Review approved. It will now appear in Review/AggregateRating structured data.');
        HttpRedirect::to('/admin/reviews', 303);
    }

    public function reject(string $id): void
    {
        Database::query('UPDATE reviews SET approved = 0 WHERE id = ?', [(int) $id]);
        $this->syncProductRating((int) $id);
        $this->flash('Review hidden — removed from structured data.', 'warning');
        HttpRedirect::to('/admin/reviews', 303);
    }

    private function syncProductRating(int $reviewId): void
    {
        $productId = (int) Database::fetchColumn('SELECT product_id FROM reviews WHERE id = ?', [$reviewId]);
        if ($productId <= 0) {
            return;
        }
        Database::query(
            'UPDATE products p SET
                p.rating_count = (SELECT COUNT(*) FROM reviews r WHERE r.product_id = p.id AND r.approved = 1),
                p.rating_avg   = COALESCE((SELECT ROUND(AVG(rating),2) FROM reviews r WHERE r.product_id = p.id AND r.approved = 1), 0)
             WHERE p.id = ?',
            [$productId]
        );
    }
}
