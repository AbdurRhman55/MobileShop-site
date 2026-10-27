<?php

declare(strict_types=1);

namespace App\Core;

use PDO;
use PDOStatement;

/**
 * PDO wrapper. All queries in the application must go through prepared
 * statements — no string interpolation of user input into SQL, ever.
 */
final class Database
{
    private static ?PDO $pdo = null;
    private static array $db = [];

    public static function boot(array $db): void
    {
        self::$db = $db;
        // Connection is established lazily so scripts (migrate) can create the
        // database first without a fatal error at bootstrap time.
    }

    /** Connect to the MySQL server without selecting a database (for CREATE DATABASE). */
    public static function serverPdo(): PDO
    {
        $dsn = sprintf(
            'mysql:host=%s;port=%d;charset=%s',
            self::$db['host'],
            self::$db['port'],
            self::$db['charset']
        );
        return new PDO($dsn, self::$db['user'], self::$db['pass'], [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        ]);
    }

    public static function pdo(): PDO
    {
        if (self::$pdo === null) {
            $dsn = sprintf(
                'mysql:host=%s;port=%d;dbname=%s;charset=%s',
                self::$db['host'],
                self::$db['port'],
                self::$db['name'],
                self::$db['charset']
            );
            self::$pdo = new PDO($dsn, self::$db['user'], self::$db['pass'], [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ]);
        }
        return self::$pdo;
    }

    public static function query(string $sql, array $params = []): PDOStatement
    {
        $stmt = self::pdo()->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }

    public static function fetch(string $sql, array $params = []): ?array
    {
        $row = self::query($sql, $params)->fetch();
        return $row === false ? null : $row;
    }

    public static function fetchAll(string $sql, array $params = []): array
    {
        return self::query($sql, $params)->fetchAll();
    }

    public static function fetchColumn(string $sql, array $params = []): mixed
    {
        return self::query($sql, $params)->fetchColumn();
    }

    public static function insert(string $table, array $data): int
    {
        $cols = array_keys($data);
        $sql  = sprintf(
            'INSERT INTO `%s` (`%s`) VALUES (%s)',
            $table,
            implode('`,`', $cols),
            implode(',', array_fill(0, count($cols), '?'))
        );
        self::query($sql, array_values($data));
        return (int) self::pdo()->lastInsertId();
    }

    public static function update(string $table, int $id, array $data): void
    {
        if ($data === []) {
            return;
        }
        $sets = implode(',', array_map(fn($c) => "`$c` = ?", array_keys($data)));
        self::query("UPDATE `$table` SET $sets WHERE id = ?", [...array_values($data), $id]);
    }

    public static function transaction(callable $fn): mixed
    {
        self::pdo()->beginTransaction();
        try {
            $result = $fn();
            self::pdo()->commit();
            return $result;
        } catch (\Throwable $e) {
            self::pdo()->rollBack();
            throw $e;
        }
    }
}
