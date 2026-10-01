<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/app/bootstrap.php';

use KreaKit\Core\Auth;
use KreaKit\Core\Csrf;
use KreaKit\Core\Session;

Session::start(app_config('session', []));
require_admin();

$csrfKey = (string) app_config('security.csrf_key', '_csrf_token');
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Metode tidak diizinkan.');
}

Csrf::requireValid($_POST[$csrfKey] ?? null, $csrfKey);
Auth::logout();
redirect(url('/admin/login.php'));
