<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Csrf;
use App\Core\HttpRedirect;
use App\Models\Wishlist;

/**
 * Wishlist — anonymous, cookie-token backed (works with no account).
 *
 * The /wishlist page is noindex,nofollow (private, per-visitor content).
 * The toggle endpoint returns JSON for the header counter + card hearts, and
 * falls back to a redirect for non-JS form posts.
 */
final class WishlistController extends BaseController
{
    public function index(): void
    {
        $items = Wishlist::items();
        $seo = $this->resolveMeta(
            'home',
            0,
            'Your Wishlist — Lala Mobiles',
            'Products you have saved for later.',
            '/wishlist',
            ['robots' => 'noindex,nofollow']
        );
        $this->renderPage($seo, [], 'wishlist', [
            'items' => $items,
        ]);
    }

    public function toggle(): void
    {
        Csrf::verifyRequest($this->request);

        $productId = (int) $this->request->input('product_id', 0);
        if ($productId <= 0) {
            if ($this->request->wantsJson()) {
                $this->json(['error' => 'Invalid product.'], 400);
            }
            HttpRedirect::to('/wishlist', 303);
            return;
        }

        $active = Wishlist::toggle($productId);

        if ($this->request->wantsJson()) {
            $this->json([
                'ok'             => true,
                'active'         => $active,
                'wishlist_count' => Wishlist::count(),
            ]);
        }
        HttpRedirect::to('/wishlist', 303);
    }
}
