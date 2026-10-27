<?php
/**
 * Lala Mobiles — central configuration.
 *
 * Environment values come from config/.env (see config/.env.example).
 * Business-critical SEO values (canonical domain, organization info, verification
 * codes) live in the `settings` table and are managed from the admin dashboard.
 * The values here are fallbacks / bootstrap defaults only.
 */

declare(strict_types=1);

return [
    'app' => [
        'name'     => 'Lala Mobiles',
        'env'      => getenv('APP_ENV') ?: 'production', // production | staging | local
        'debug'    => (bool) (getenv('APP_DEBUG') ?: false),
        'url'      => rtrim(getenv('APP_URL') ?: 'http://localhost:8088', '/'),
        'timezone' => 'Asia/Karachi',
        'locale'   => 'en_PK',
        'currency' => 'PKR',
        'currency_symbol' => 'Rs',
    ],

    'db' => [
        'host'     => getenv('DB_HOST') ?: '127.0.0.1',
        'port'     => (int) (getenv('DB_PORT') ?: 3306),
        'name'     => getenv('DB_NAME') ?: 'lala_mobiles',
        'user'     => getenv('DB_USER') ?: 'root',
        'pass'     => getenv('DB_PASS') !== false ? getenv('DB_PASS') : '',
        'charset'  => 'utf8mb4',
    ],

    'paths' => [
        'base'    => dirname(__DIR__),
        'storage' => dirname(__DIR__) . '/storage',
        'cache'   => dirname(__DIR__) . '/storage/cache',
    ],

    'session' => [
        'name'     => 'lala_sess',
        'lifetime' => 86400 * 7,
    ],

    'security' => [
        // Used to sign nothing security-critical; CSRF token pepper for form tokens.
        'csrf_key' => getenv('CSRF_KEY') ?: 'change-this-csrf-key-in-production',
    ],

    'sitemap' => [
        // Google's hard limits: 50,000 URLs / 50 MB uncompressed per file.
        'max_urls'  => 50000,
        'cache_ttl' => 3600, // seconds before a cached sitemap is regenerated
    ],

    'seo' => [
        // Title / description length guidance used by the audit reports.
        'title_max'       => 60,
        'description_max' => 160,
    ],
];
