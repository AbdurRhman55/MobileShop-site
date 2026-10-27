<?php
/**
 * Migration runner: php bin/migrate.php
 *
 * Applies database/migrations/*.sql in filename order, tracking applied
 * versions in schema_migrations. The baseline schema is database/schema.sql.
 */

declare(strict_types=1);

require __DIR__ . '/../app/bootstrap.php';

use App\Core\Database;

$dbName = App\Core\Config::get('db.name');

// Create the database if it does not exist yet.
Database::serverPdo()->exec(
    "CREATE DATABASE IF NOT EXISTS `$dbName` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci"
);

echo "Migrating database `$dbName`...\n";

// 1) Baseline schema (multi-statement file → exec, not prepared query)
Database::serverPdo()->exec(
    "USE `$dbName`"
);
Database::pdo()->exec(file_get_contents(BASE_PATH . '/database/schema.sql'));
echo "  [baseline] schema.sql applied\n";
Database::query(
    "INSERT IGNORE INTO schema_migrations (version) VALUES ('baseline_schema')"
);

// 2) Incremental migrations
$files = glob(BASE_PATH . '/database/migrations/*.sql') ?: [];
sort($files);

foreach ($files as $file) {
    $version = basename($file, '.sql');
    $exists  = Database::fetch(
        'SELECT 1 FROM schema_migrations WHERE version = ?',
        [$version]
    );
    if ($exists !== null) {
        echo "  [skip] $version\n";
        continue;
    }
    Database::pdo()->exec(file_get_contents($file));
    Database::query('INSERT INTO schema_migrations (version) VALUES (?)', [$version]);
    echo "  [apply] $version\n";
}

echo "Done.\n";
