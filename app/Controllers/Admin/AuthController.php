<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Database;
use App\Core\HttpRedirect;
use App\Core\Request;
use App\Core\Router;

/** Admin login / logout. */
final class AuthController extends AdminBaseController
{
    public function __construct(Request $request, Router $router)
    {
        parent::__construct($request, $router);
    }

    // Bypass the *auth guard* for login routes, but NOT CSRF verification —
    // a login POST still needs a valid token (login-CSRF is a real attack).
    protected function requireAuth(): void
    {
        if ($this->request->isPost()) {
            \App\Core\Csrf::verifyRequest($this->request);
        }
        if ($this->request->path !== '/admin/login' && empty($_SESSION['admin_id'])) {
            header('Location: /admin/login');
            exit;
        }
    }

    public function loginForm(): void
    {
        if (!empty($_SESSION['admin_id'])) {
            HttpRedirect::to('/admin', 302);
        }
        echo \App\Core\View::render('admin/login', [
            'error' => null,
            'csrf'  => \App\Core\Csrf::token(),
        ], null);
    }

    public function login(): void
    {
        $username = trim((string) $this->request->input('username', ''));
        $password = (string) $this->request->input('password', '');

        $admin = Database::fetch('SELECT * FROM admins WHERE username = ?', [$username]);

        if ($admin === null || !password_verify($password, $admin['password_hash'])) {
            usleep(500000); // slow brute-force attempts
            echo \App\Core\View::render('admin/login', [
                'error' => 'Invalid username or password.',
                'csrf'  => \App\Core\Csrf::token(),
            ], null);
            return;
        }

        session_regenerate_id(true);
        $_SESSION['admin_id']   = (int) $admin['id'];
        $_SESSION['admin_name'] = $admin['display_name'];
        HttpRedirect::to('/admin', 302);
    }

    public function logout(): void
    {
        unset($_SESSION['admin_id'], $_SESSION['admin_name']);
        session_regenerate_id(true);
        HttpRedirect::to('/admin/login', 302);
    }
}
