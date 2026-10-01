<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/app/bootstrap.php';

use KreaKit\Core\Csrf;
use KreaKit\Core\Session;

Session::start(app_config('session', []));
$csrfToken = Csrf::token((string) app_config('security.csrf_key', '_csrf_token'));

http_response_code(200);
?>
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admin — <?= e(app_config('app.name', 'KreaKit CMS')) ?></title>
</head>
<body>
    <main>
        <h1>Admin KreaKit CMS</h1>
        <p>Bootstrap admin berhasil dimuat. Login dan dashboard akan ditambahkan pada task berikutnya.</p>
        <p>CSRF aktif: <code><?= e(substr($csrfToken, 0, 12)) ?>…</code></p>
    </main>
</body>
</html>
