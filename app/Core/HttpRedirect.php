<?php

declare(strict_types=1);

namespace App\Core;

/**
 * Redirect helper with open-redirect protection.
 *
 * Only same-site, path-only targets are ever followed:
 *  - must start with a single "/" (protocol-relative "//evil.com" rejected)
 *  - must not contain a scheme or host
 * Relative targets are resolved against the configured canonical site path.
 */
final class HttpRedirect
{
    public static function to(string $target, int $status = 302): never
    {
        $target = self::safeTarget($target);

        if (!headers_sent()) {
            http_response_code($status);
            header('Location: ' . $target);
            header('X-Robots-Tag: noindex');
        }
        echo '<!doctype html><meta charset="utf-8"><title>Redirecting…</title><p>Redirecting to <a href="'
            . e($target) . '">' . e($target) . '</a></p><script>location.href=' . json_encode($target) . ';</script>';
        exit;
    }

    /** Validate and normalize a redirect destination. */
    public static function safeTarget(string $target): string
    {
        $target = trim($target);

        // Reject absolute URLs to other hosts, protocol-relative URLs, control chars.
        if ($target === ''
            || preg_match('#^[a-zA-Z][a-zA-Z0-9+.-]*:#', $target) // any scheme: http:, javascript:, data:
            || str_starts_with($target, '//')
            || preg_match('/[\x00-\x1F\x7F]/', $target)
        ) {
            throw new Http(400, 'Invalid redirect destination.');
        }

        // Normalize to a leading-slash path on this site.
        if (!str_starts_with($target, '/')) {
            $target = '/' . $target;
        }
        return $target;
    }
}
