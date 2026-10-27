<?php

declare(strict_types=1);

namespace App\Core;

/**
 * Static configuration accessor over config/config.php.
 */
final class Config
{
    private static array $config = [];

    public static function boot(array $config): void
    {
        self::$config = $config;
    }

    /** Dot-notation getter: get('db.host') */
    public static function get(string $key, mixed $default = null): mixed
    {
        $node = self::$config;
        foreach (explode('.', $key) as $part) {
            if (!is_array($node) || !array_key_exists($part, $node)) {
                return $default;
            }
            $node = $node[$part];
        }
        return $node;
    }
}
