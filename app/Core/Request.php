<?php

declare(strict_types=1);

namespace App\Core;

/**
 * HTTP request abstraction.
 */
final class Request
{
    public function __construct(
        public readonly string $method,
        public readonly string $path,
        public readonly array $query,
        public readonly array $post,
        public readonly array $server,
    ) {
    }

    public static function capture(): self
    {
        $method = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
        $uri    = $_SERVER['REQUEST_URI'] ?? '/';
        $path   = rawurldecode(parse_url($uri, PHP_URL_PATH) ?: '/');

        // Normalize trailing slashes (except root) — 301 handled by router when needed.
        if ($path !== '/' && str_ends_with($path, '/')) {
            $path = rtrim($path, '/');
        }

        return new self($method, $path, $_GET, $_POST, $_SERVER);
    }

    public function input(string $key, mixed $default = null): mixed
    {
        return $this->post[$key] ?? $this->query[$key] ?? $default;
    }

    public function query(string $key, mixed $default = null): mixed
    {
        return $this->query[$key] ?? $default;
    }

    public function isPost(): bool
    {
        return $this->method === 'POST';
    }

    public function wantsJson(): bool
    {
        return str_contains($this->server['HTTP_ACCEPT'] ?? '', 'application/json')
            || str_contains($this->server['HTTP_X_REQUESTED_WITH'] ?? '', 'XMLHttpRequest');
    }
}
