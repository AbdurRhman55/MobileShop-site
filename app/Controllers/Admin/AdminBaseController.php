<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Http;
use App\Core\Request;
use App\Core\Router;
use App\Controllers\BaseController;

/**
 * Admin base controller. All /admin/* routes (except login) require an
 * authenticated admin session. Every state-changing POST additionally
 * requires a valid CSRF token.
 */
abstract class AdminBaseController extends BaseController
{
    public function __construct(Request $request, Router $router)
    {
        parent::__construct($request, $router);
        $this->requireAuth();
    }

    protected function requireAuth(): void
    {
        if (empty($_SESSION['admin_id'])) {
            header('Location: /admin/login');
            exit;
        }
        // CSRF for all POSTs.
        if ($this->request->isPost()) {
            \App\Core\Csrf::verifyRequest($this->request);
        }
    }

    /** Render an admin view inside the admin layout. */
    protected function renderAdmin(string $template, array $data = []): void
    {
        echo \App\Core\View::render($template, $data + [
            'adminUser' => $_SESSION['admin_name'] ?? 'Admin',
            'auditSummary' => \App\Seo\SeoAudit::summary(),
        ], 'admin');
    }

    protected function flash(string $message, string $type = 'success'): void
    {
        $_SESSION['flash'] = ['message' => $message, 'type' => $type];
    }

    protected function takeFlash(): ?array
    {
        $f = $_SESSION['flash'] ?? null;
        unset($_SESSION['flash']);
        return $f;
    }
}
