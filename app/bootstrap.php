<?php
/**
 * Application bootstrap: environment, autoloading, configuration, session.
 */

declare(strict_types=1);

define('APP_START', microtime(true));
define('BASE_PATH', dirname(__DIR__));

/* ---------------------------------------------------------------- .env -- */
$envFile = BASE_PATH . '/config/.env';
if (is_readable($envFile)) {
    foreach (file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        $line = trim($line);
        if ($line === '' || str_starts_with($line, '#') || !str_contains($line, '=')) {
            continue;
        }
        [$key, $value] = explode('=', $line, 2);
        $key   = trim($key);
        $value = trim($value);
        // strip optional surrounding quotes
        if (strlen($value) >= 2 && ($value[0] === '"' || $value[0] === "'") && $value[-1] === $value[0]) {
            $value = substr($value, 1, -1);
        }
        if (getenv($key) === false) {
            putenv("$key=$value");
            $_ENV[$key] = $value;
        }
    }
}

/* ------------------------------------------------------------ autoload -- */
spl_autoload_register(static function (string $class): void {
    if (!str_starts_with($class, 'App\\')) {
        return;
    }
    $relative = substr($class, strlen('App\\'));
    $file     = BASE_PATH . '/app/' . str_replace('\\', '/', $relative) . '.php';
    if (is_readable($file)) {
        require $file;
    }
});

require BASE_PATH . '/app/Support/helpers.php';

/* ----------------------------------------------------------- config ----- */
$config = require BASE_PATH . '/config/config.php';
App\Core\Config::boot($config);

date_default_timezone_set($config['app']['timezone']);
mb_internal_encoding('UTF-8');

if (!empty($config['app']['debug'])) {
    ini_set('display_errors', '1');
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', '0');
    ini_set('log_errors', '1');
    ini_set('error_log', $config['paths']['storage'] . '/logs/app.log');
}

/* ---------------------------------------------------------- database ---- */
App\Core\Database::boot($config['db']);

/* ----------------------------------------------------------- session ---- */
if (PHP_SAPI !== 'cli' && session_status() === PHP_SESSION_NONE) {
    session_name($config['session']['name']);
    session_set_cookie_params([
        'lifetime' => $config['session']['lifetime'],
        'path'     => '/',
        'httponly' => true,
        'samesite' => 'Lax',
        'secure'   => (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off'),
    ]);
    session_start();
}
