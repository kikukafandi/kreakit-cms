<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/app/bootstrap.php';

use KreaKit\Core\Csrf;
use KreaKit\Core\Session;

Session::start(app_config('session', []));
require_admin();

$admin = \KreaKit\Core\Auth::admin();
$csrfKey = (string) app_config('security.csrf_key', '_csrf_token');
$success = Session::flash('success');
$error = Session::flash('error');

http_response_code(200);
?>
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Dashboard Admin — <?= e(app_config('app.name', 'KreaKit CMS')) ?></title>
</head>
<body>
    <main>
        <h1>Dashboard Admin</h1>
        <?php if ($success): ?><p role="status"><?= e($success) ?></p><?php endif; ?>
        <?php if ($error): ?><p role="alert"><?= e($error) ?></p><?php endif; ?>
        <p>Login sebagai <strong><?= e($admin['name'] ?? 'Admin') ?></strong> (<?= e($admin['email'] ?? '') ?>).</p>
        <nav aria-label="Navigasi admin">
            <a href="<?= e(url('/admin/business.php')) ?>">Profil Bisnis & Sosial</a>
            <a href="<?= e(url('/admin/categories.php')) ?>">Kategori</a>
            <a href="<?= e(url('/admin/items.php')) ?>">Produk/Layanan</a>
            <a href="<?= e(url('/admin/password.php')) ?>">Ubah Password</a>
            <a href="<?= e(url('/')) ?>">Lihat Website</a>
        </nav>
        <form method="post" action="<?= e(url('/admin/logout.php')) ?>">
            <?= Csrf::field($csrfKey) ?>
            <button type="submit">Logout</button>
        </form>
        <section>
            <h2>Status MVP</h2>
            <p>Auth admin aktif. CRUD profil bisnis, social links, kategori, dan produk/layanan tersedia lewat navigasi di atas.</p>
        </section>
    </main>
</body>
</html>
