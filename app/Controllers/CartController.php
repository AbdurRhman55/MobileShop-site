<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Csrf;
use App\Core\HttpRedirect;
use App\Models\Cart;

/** Cart — server-side session cart, always noindex (private area). */
final class CartController extends BaseController
{
    public function index(): void
    {
        $items = Cart::items();
        $seo = $this->resolveMeta('home', 0, 'Your Cart — Lala Mobiles', 'Review items in your shopping cart.', '/cart', ['robots' => 'noindex,nofollow']);
        $this->renderPage($seo, [], 'cart', [
            'items'    => $items,
            'subtotal' => Cart::subtotal(),
            'shipping' => Cart::shipping(),
        ]);
    }

    public function add(): void
    {
        Csrf::verifyRequest($this->request);

        $productId = (int) $this->request->input('product_id', 0);
        $variantId = (int) ($this->request->input('variant_id') ?: 0) ?: null;
        $quantity  = (int) $this->request->input('quantity', 1);

        $product = \App\Models\Product::find($productId);
        if ($product === null || $product['status'] !== 'active') {
            if ($this->request->wantsJson()) {
                $this->json(['error' => 'This product is no longer available.'], 409);
            }
            HttpRedirect::to('/cart', 303);
            return;
        }

        Cart::add($productId, $variantId, $quantity);

        if ($this->request->wantsJson()) {
            $this->json([
                'ok'         => true,
                'cart_count' => Cart::count(),
                'subtotal'   => Cart::subtotal(),
                'name'       => $product['name'],
            ]);
        }
        HttpRedirect::to('/cart', 303);
    }

    public function update(): void
    {
        Csrf::verifyRequest($this->request);
        $key = (string) $this->request->input('key', '');
        Cart::updateQuantity($key, (int) $this->request->input('quantity', 1));
        HttpRedirect::to('/cart', 303);
    }

    public function remove(): void
    {
        Csrf::verifyRequest($this->request);
        Cart::remove((string) $this->request->input('key', ''));
        HttpRedirect::to('/cart', 303);
    }
}
