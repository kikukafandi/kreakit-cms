<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/app/bootstrap.php';

use KreaKit\Core\Session;

Session::start(app_config('session', []));

http_response_code(200);
?>
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e(app_config('app.name', 'KreaKit CMS')) ?></title>
</head>
<body>
    <main>
        <h1><?= e(app_config('app.name', 'KreaKit CMS')) ?></h1>
        <p>Bootstrap publik berhasil dimuat. Renderer katalog akan ditambahkan pada task berikutnya.</p>
    </main>
</body>
</html>
