<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Csrf;
use App\Core\HttpRedirect;
use App\Models\Newsletter;

/**
 * Newsletter subscription endpoint (footer + homepage).
 *
 * Records a consent timestamp server-side. AJAX consumers get JSON; plain
 * form posts get a friendly redirect. The email is validated before insert.
 */
final class NewsletterController extends BaseController
{
    public function subscribe(): void
    {
        Csrf::verifyRequest($this->request);

        $email = (string) $this->request->input('email', '');
        $ok    = Newsletter::subscribe($email);

        if ($this->request->wantsJson()) {
            if ($ok) {
                $this->json(['ok' => true]);
            }
            $this->json(['error' => 'Please enter a valid email address.'], 422);
        }

        // Non-JS fallback: redirect back to the page they came from (same-site
        // path only — HttpRedirect::to rejects anything else).
        $back = (string) ($this->request->server['HTTP_REFERER'] ?? '');
        $path = $back !== '' ? (parse_url($back, PHP_URL_PATH) ?: '/') : '/';
        if (!str_starts_with($path, '/') || str_starts_with($path, '//')) {
            $path = '/';
        }
        HttpRedirect::to($path, 303);
    }
}
