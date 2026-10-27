<?php
/**
 * Lala Mobiles — front controller.
 *
 * All public requests are routed through this file. Clean URLs are provided
 * via public/.htaccess (Apache/Laragon) or bin/serve.php (PHP built-in server).
 */

declare(strict_types=1);

require __DIR__ . '/../app/bootstrap.php';

use App\Core\Request;
use App\Core\Router;
use App\Core\Http;

$request = Request::capture();

$router = new Router($request);

require __DIR__ . '/../routes.php'; // registers all routes (public + admin)

try {
    $router->dispatch();
} catch (Http $e) {
    Http::errorResponse($e->getStatusCode(), $e->getMessage());
} catch (Throwable $e) {
    error_log('[LalaMobiles] ' . $e->getMessage() . ' @ ' . $e->getFile() . ':' . $e->getLine());
    Http::errorResponse(500, 'Something went wrong. Please try again later.');
}
