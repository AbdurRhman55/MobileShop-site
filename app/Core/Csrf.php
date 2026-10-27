<?php

declare(strict_types=1);

namespace App\Core;

/**
 * CSRF protection. All state-changing forms and AJAX POSTs must carry a token.
 */
final class Csrf
{
    public static function token(): string
    {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }

    public static function field(): string
    {
        return '<input type="hidden" name="_token" value="' . e(self::token()) . '">';
    }

    public static function verify(?string $token): void
    {
        $known = $_SESSION['csrf_token'] ?? '';
        if (!is_string($token) || $known === '' || !hash_equals($known, $token)) {
            http_response_code(419);
            echo 'Invalid or expired form token. Please go back and try again.';
            exit;
        }
    }

    /**
     * Verify the token from the POST body or the X-CSRF-Token header (AJAX).
     *
     * `_token` is the field every server-rendered form uses. `csrf` is kept as
     * a backwards-compatible alias because the storefront JS has always posted
     * it under that name — accepting both means a form and a fetch() call can
     * never silently disagree about the field name again.
     */
    public static function verifyRequest(Request $request): void
    {
        $token = $request->post['_token']
            ?? $request->post['csrf']
            ?? $request->server['HTTP_X_CSRF_TOKEN']
            ?? null;
        self::verify(is_string($token) ? $token : null);
    }
}
