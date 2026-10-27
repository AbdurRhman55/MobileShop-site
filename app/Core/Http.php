<?php

declare(strict_types=1);

namespace App\Core;

/**
 * Application exception carrying an HTTP status code.
 */
final class Http extends \RuntimeException
{
    public function __construct(
        private readonly int $status,
        string $message = '',
    ) {
        parent::__construct($message);
    }

    public function getStatusCode(): int
    {
        return $this->status;
    }

    /** Emit a full error response (404 / 410 / 500 …) and stop execution. */
    public static function errorResponse(int $status, string $message = ''): never
    {
        http_response_code($status);

        $titles = [
            404 => 'Page Not Found',
            410 => 'Page Removed',
            500 => 'Server Error',
        ];
        $title   = $titles[$status] ?? 'Error';
        $content = $message !== '' ? $message : 'The page you are looking for is unavailable.';

        // Try the branded error view; fall back to plain text.
        try {
            echo view('errors/error', [
                'status'  => $status,
                'title'   => $title,
                'message' => $content,
            ]);
        } catch (\Throwable) {
            header('Content-Type: text/plain; charset=utf-8');
            echo "$status — $content";
        }
        exit;
    }
}
