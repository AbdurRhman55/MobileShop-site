<?php

declare(strict_types=1);

/**
 * Router script for PHP's built-in development server.
 *
 * Usage:  php -S 127.0.0.1:8088 -t public bin/serve.php
 *    or:  php -S 127.0.0.1:8088 bin/serve.php     (router resolves public/ itself)
 *
 * Serves real files from public/ directly (CSS, JS, images) with correct MIME
 * types, and passes every other request to the front controller — exactly like
 * Apache/nginx with mod_rewrite / try_files.
 *
 * NOTE: this is a *development* convenience. In production use Apache
 * (public/.htaccess) or an equivalent nginx try_files rule.
 */

$path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
$file = __DIR__ . '/../public' . $path;

// Real static file under public/ → stream it ourselves with a correct
// Content-Type. We cannot `return false` here: PHP's built-in server resolves
// that against *its own* docroot (the working directory), which is the project
// root — not public/ — and would 404 every asset.
if ($path !== '/' && is_file($file)) {
    // Security: never execute PHP that lives outside the intended entry point.
    if (str_ends_with(strtolower($path), '.php')) {
        http_response_code(404);
        header('Content-Type: text/plain; charset=utf-8');
        echo 'Not found';
        return true;
    }

    $ext  = strtolower(pathinfo($file, PATHINFO_EXTENSION));
    $mime = match ($ext) {
        'css'  => 'text/css; charset=utf-8',
        'js'   => 'application/javascript; charset=utf-8',
        'mjs'  => 'application/javascript; charset=utf-8',
        'json' => 'application/json; charset=utf-8',
        'svg'  => 'image/svg+xml',
        'png'  => 'image/png',
        'jpg', 'jpeg' => 'image/jpeg',
        'gif'  => 'image/gif',
        'webp' => 'image/webp',
        'avif' => 'image/avif',
        'ico'  => 'image/x-icon',
        'woff' => 'font/woff',
        'woff2'=> 'font/woff2',
        'ttf'  => 'font/ttf',
        'txt'  => 'text/plain; charset=utf-8',
        'xml'  => 'application/xml; charset=utf-8',
        'pdf'  => 'application/pdf',
        'map'  => 'application/json; charset=utf-8',
        default => 'application/octet-stream',
    };

    header('Content-Type: ' . $mime);
    header('Content-Length: ' . (string) filesize($file));
    header('Cache-Control: no-cache');
    readfile($file);
    return true;
}

// Missing real asset under public/ → plain 404 (don't fall through to the app).
if ($path !== '/' && str_starts_with($path, '/assets/') && !is_file($file)) {
    http_response_code(404);
    header('Content-Type: text/plain; charset=utf-8');
    echo 'Asset not found: ' . $path;
    return true;
}

// Everything else → front controller.
$_SERVER['SCRIPT_NAME'] = '/index.php';
$_SERVER['SCRIPT_FILENAME'] = __DIR__ . '/../public/index.php';
require __DIR__ . '/../public/index.php';
return true;
