<?php
/**
* Application routes. Loaded by public/index.php.
*
* URL map:
*   /                              home
*   /product/ {
    slug}
    product detail
    *   /category/ {
        slug}
        category listing ( filters via query string )
        *   /brand/ {
            slug}
            brand listing
            *   /shop/ {
                slug}
                admin-curated filter landing page
                *   /blog, /blog/ {
                    slug}
                    blog index + post
                    *   /page/ {
                        slug}
                        static pages ( about, contact, policies … )
                        *   /repair/ {
                            slug}
                            repair service pages
                            *   /search?q =                     internal search ( noindex )
                            *   /cart, /checkout, /checkout/success/ {
                                ref}
                                *   /sitemap.xml, /sitemap/ {
                                    file}
                                    sitemaps
                                    *   /robots.txt                    robots
                                    *   /feed/products.xml             Merchant Center feed
                                    *   /admin/...                     admin dashboard
                                    */

                                    declare( strict_types = 1 );

                                    use App\Controllers;
                                    use App\Controllers\Admin;

                                    /** ------------------------------------------------------------- storefront */
                                    $router->get( '/', [ Controllers\HomeController::class, 'index' ] );

                                    $router->get( '/product/{slug}', [ Controllers\ProductController::class, 'show' ] );
                                    $router->get( '/category/{slug}', [ Controllers\CategoryController::class, 'show' ] );
                                    $router->get( '/brand/{slug}', [ Controllers\BrandController::class, 'show' ] );
                                    $router->get( '/shop/{slug}', [ Controllers\FilterPageController::class, 'show' ] );

                                    $router->get( '/blog', [ Controllers\BlogController::class, 'index' ] );
                                    $router->get( '/blog/{slug}', [ Controllers\BlogController::class, 'show' ] );

                                    $router->get( '/page/{slug}', [ Controllers\PageController::class, 'show' ] );
                                    $router->get( '/repair/{slug}', [ Controllers\PageController::class, 'repair' ] );

                                    $router->get( '/search', [ Controllers\SearchController::class, 'index' ] );
                                    $router->get( '/search/suggest', [ Controllers\SearchController::class, 'suggest' ] );

                                    $router->get( '/cart', [ Controllers\CartController::class, 'index' ] );
                                    $router->post( '/cart/add', [ Controllers\CartController::class, 'add' ] );
                                    $router->post( '/cart/update', [ Controllers\CartController::class, 'update' ] );
                                    $router->post( '/cart/remove', [ Controllers\CartController::class, 'remove' ] );

                                    $router->get( '/wishlist', [ Controllers\WishlistController::class, 'index' ] );
                                    $router->post( '/wishlist/toggle', [ Controllers\WishlistController::class, 'toggle' ] );
                                    $router->get( '/compare', [ Controllers\CompareController::class, 'index' ] );
                                    $router->post( '/compare/toggle', [ Controllers\CompareController::class, 'toggle' ] );
                                    $router->get( '/deals', [ Controllers\DealsController::class, 'index' ] );
                                    $router->get( '/account', [ Controllers\AccountController::class, 'profile' ] );
                                    $router->get( '/account/login', [ Controllers\AccountController::class, 'loginForm' ] );
                                    $router->post( '/account/login', [ Controllers\AccountController::class, 'login' ] );
                                    $router->get( '/account/register', [ Controllers\AccountController::class, 'registerForm' ] );
                                    $router->post( '/account/register', [ Controllers\AccountController::class, 'register' ] );
                                    $router->get( '/account/orders/{id}', [ Controllers\AccountController::class, 'showOrder' ] );
                                    $router->get( '/account/orders/{id}/invoice', [ Controllers\AccountController::class, 'invoice' ] );
                                    $router->post( '/account/orders/{id}/payment', [ Controllers\AccountController::class, 'reportPayment' ] );
                                    $router->post( '/account/address', [ Controllers\AccountController::class, 'saveAddress' ] );
                                    $router->post( '/account/logout', [ Controllers\AccountController::class, 'logout' ] );

                                    $router->post( '/newsletter', [ Controllers\NewsletterController::class, 'subscribe' ] );
                                    $router->get( '/checkout', [ Controllers\CheckoutController::class, 'index' ] );
                                    $router->post( '/checkout', [ Controllers\CheckoutController::class, 'place' ] );
                                    $router->get( '/checkout/success/{ref}', [ Controllers\CheckoutController::class, 'success' ] );

                                    /** --------------------------------------------------------------- SEO endpoints */
                                    $router->get( '/sitemap.xml', [ Controllers\SeoController::class, 'sitemapIndex' ] );
                                    $router->get( '/sitemap/{file}', [ Controllers\SeoController::class, 'sitemapSection' ] );
                                    $router->get( '/robots.txt', [ Controllers\SeoController::class, 'robots' ] );
                                    $router->get( '/feed/products.xml', [ Controllers\SeoController::class, 'feed' ] );

                                    /* --------------------------------------------------------------- admin auth */
                                    $router->get( '/admin/login', [ Admin\AuthController::class, 'loginForm' ] );
                                    $router->post( '/admin/login', [ Admin\AuthController::class, 'login' ] );
                                    $router->post( '/admin/logout', [ Admin\AuthController::class, 'logout' ] );

                                    /* -------------------------------------------------------------- admin pages */
                                    $router->get( '/admin', [ Admin\DashboardController::class, 'index' ] );
                                    $router->get( '/admin/dashboard', [ Admin\DashboardController::class, 'index' ] );

                                    $router->get( '/admin/products', [ Admin\ProductAdminController::class, 'index' ] );
                                    $router->get( '/admin/products/create', [ Admin\ProductAdminController::class, 'create' ] );
                                    $router->post( '/admin/products/create', [ Admin\ProductAdminController::class, 'store' ] );
                                    $router->get( '/admin/products/{id}/edit', [ Admin\ProductAdminController::class, 'edit' ] );
                                    $router->post( '/admin/products/{id}/edit', [ Admin\ProductAdminController::class, 'update' ] );
                                    $router->post( '/admin/products/{id}/delete', [ Admin\ProductAdminController::class, 'delete' ] );

                                    $router->get( '/admin/categories', [ Admin\CategoryAdminController::class, 'index' ] );
                                    $router->get( '/admin/categories/create', [ Admin\CategoryAdminController::class, 'create' ] );
                                    $router->post( '/admin/categories/create', [ Admin\CategoryAdminController::class, 'store' ] );
                                    $router->get( '/admin/categories/{id}/edit', [ Admin\CategoryAdminController::class, 'edit' ] );
                                    $router->post( '/admin/categories/{id}/edit', [ Admin\CategoryAdminController::class, 'update' ] );

                                    $router->get( '/admin/brands', [ Admin\BrandAdminController::class, 'index' ] );
                                    $router->get( '/admin/brands/create', [ Admin\BrandAdminController::class, 'create' ] );
                                    $router->post( '/admin/brands/create', [ Admin\BrandAdminController::class, 'store' ] );
                                    $router->get( '/admin/brands/{id}/edit', [ Admin\BrandAdminController::class, 'edit' ] );
                                    $router->post( '/admin/brands/{id}/edit', [ Admin\BrandAdminController::class, 'update' ] );

                                    $router->get( '/admin/blog', [ Admin\BlogAdminController::class, 'index' ] );
                                    $router->get( '/admin/blog/create', [ Admin\BlogAdminController::class, 'create' ] );
                                    $router->post( '/admin/blog/create', [ Admin\BlogAdminController::class, 'store' ] );
                                    $router->get( '/admin/blog/{id}/edit', [ Admin\BlogAdminController::class, 'edit' ] );
                                    $router->post( '/admin/blog/{id}/edit', [ Admin\BlogAdminController::class, 'update' ] );

                                    $router->get( '/admin/pages', [ Admin\PageAdminController::class, 'index' ] );
                                    $router->get( '/admin/pages/create', [ Admin\PageAdminController::class, 'create' ] );
                                    $router->post( '/admin/pages/create', [ Admin\PageAdminController::class, 'store' ] );
                                    $router->get( '/admin/pages/{id}/edit', [ Admin\PageAdminController::class, 'edit' ] );
                                    $router->post( '/admin/pages/{id}/edit', [ Admin\PageAdminController::class, 'update' ] );

                                    $router->get( '/admin/reviews', [ Admin\ReviewAdminController::class, 'index' ] );
                                    $router->post( '/admin/reviews/{id}/approve', [ Admin\ReviewAdminController::class, 'approve' ] );
                                    $router->post( '/admin/reviews/{id}/reject', [ Admin\ReviewAdminController::class, 'reject' ] );

                                    $router->get( '/admin/orders', [ Admin\OrderAdminController::class, 'index' ] );
                                    $router->get( '/admin/orders/payment-settings', [ Admin\OrderAdminController::class, 'paymentSettings' ] );
                                    $router->post( '/admin/orders/payment-settings', [ Admin\OrderAdminController::class, 'savePaymentSettings' ] );
                                    $router->get( '/admin/orders/{id}', [ Admin\OrderAdminController::class, 'show' ] );
                                    $router->get( '/admin/orders/{id}/invoice', [ Admin\OrderAdminController::class, 'invoice' ] );
                                    $router->post( '/admin/orders/{id}/status', [ Admin\OrderAdminController::class, 'updateStatus' ] );
                                    $router->post( '/admin/orders/{id}/payment', [ Admin\OrderAdminController::class, 'recordPayment' ] );
                                    $router->post( '/admin/orders/{id}/refund', [ Admin\OrderAdminController::class, 'refund' ] );
                                    $router->post( '/admin/orders/{id}/note', [ Admin\OrderAdminController::class, 'saveNote' ] );

                                    $router->get( '/admin/customers', [ Admin\CustomerAdminController::class, 'index' ] );
                                    $router->get( '/admin/customers/{id}', [ Admin\CustomerAdminController::class, 'show' ] );

                                    /* ------------------------------------------------------ homepage sections */
                                    $router->get( '/admin/homepage', [ Admin\HomepageAdminController::class, 'index' ] );
                                    $router->post( '/admin/homepage', [ Admin\HomepageAdminController::class, 'update' ] );

                                    /* ----------------------------------------------------------- SEO dashboard */
                                    $router->get( '/admin/seo', [ Admin\SeoAdminController::class, 'index' ] );
                                    $router->get( '/admin/seo/settings', [ Admin\SeoAdminController::class, 'settings' ] );
                                    $router->post( '/admin/seo/settings', [ Admin\SeoAdminController::class, 'saveSettings' ] );
                                    $router->get( '/admin/seo/audit', [ Admin\SeoAdminController::class, 'audit' ] );
                                    $router->get( '/admin/seo/audit/{report}/export', [ Admin\SeoAdminController::class, 'exportAudit' ] );
                                    $router->get( '/admin/seo/redirects', [ Admin\SeoAdminController::class, 'redirects' ] );
                                    $router->post( '/admin/seo/redirects', [ Admin\SeoAdminController::class, 'addRedirect' ] );
                                    $router->post( '/admin/seo/redirects/{id}/delete', [ Admin\SeoAdminController::class, 'deleteRedirect' ] );
                                    $router->get( '/admin/seo/sitemap', [ Admin\SeoAdminController::class, 'sitemap' ] );
                                    $router->post( '/admin/seo/sitemap/flush', [ Admin\SeoAdminController::class, 'flushSitemap' ] );
                                    $router->get( '/admin/seo/filter-pages', [ Admin\SeoAdminController::class, 'filterPages' ] );
                                    $router->get( '/admin/seo/filter-pages/create', [ Admin\SeoAdminController::class, 'createFilterPage' ] );
                                    $router->post( '/admin/seo/filter-pages/create', [ Admin\SeoAdminController::class, 'storeFilterPage' ] );
                                    $router->get( '/admin/seo/filter-pages/{id}/edit', [ Admin\SeoAdminController::class, 'editFilterPage' ] );
                                    $router->post( '/admin/seo/filter-pages/{id}/edit', [ Admin\SeoAdminController::class, 'updateFilterPage' ] );
                                    $router->post( '/admin/seo/filter-pages/{id}/delete', [ Admin\SeoAdminController::class, 'deleteFilterPage' ] );
                                    $router->get( '/admin/seo/feed', [ Admin\SeoAdminController::class, 'feed' ] );
                                    $router->post( '/admin/seo/feed/validate', [ Admin\SeoAdminController::class, 'validateFeed' ] );
