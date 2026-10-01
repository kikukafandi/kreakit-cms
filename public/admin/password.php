<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/app/bootstrap.php';

use KreaKit\Core\Auth;
use KreaKit\Core\Csrf;
use KreaKit\Core\Session;

Session::start(app_config('session', []));
require_admin();

$csrfKey = (string) app_config('security.csrf_key', '_csrf_token');
$errors = [];
$success = Session::flash('success');

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    Csrf::requireValid($_POST[$csrfKey] ?? null, $csrfKey);

    $currentPassword = (string) ($_POST['current_password'] ?? '');
    $newPassword = (string) ($_POST['new_password'] ?? '');
    $confirmPassword = (string) ($_POST['confirm_password'] ?? '');

    if ($currentPassword === '' || $newPassword === '' || $confirmPassword === '') {
        $errors[] = 'Password tidak dapat diperbarui. Periksa input Anda.';
    }
    if (strlen($newPassword) < 10) {
        $errors[] = 'Password baru minimal 10 karakter.';
    }
    if ($newPassword !== $confirmPassword) {
        $errors[] = 'Konfirmasi password baru tidak sesuai.';
    }
    if ($currentPassword !== '' && hash_equals($currentPassword, $newPassword)) {
        $errors[] = 'Password baru harus berbeda dari password saat ini.';
    }

    if ($errors === []) {
        $result = Auth::updatePassword(db(), $currentPassword, $newPassword);
        if ($result['success']) {
            Session::flash('success', $result['message']);
            redirect(url('/admin/password.php'));
        }
        $errors[] = $result['message'];
    }
}

$admin = Auth::admin();
http_response_code(200);
?>
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Ubah Password — <?= e(app_config('app.name', 'KreaKit CMS')) ?></title>
</head>
<body>
    <main>
        <h1>Ubah Password</h1>
        <p>Admin: <?= e($admin['email'] ?? '') ?></p>
        <?php if ($success): ?><p role="status"><?= e($success) ?></p><?php endif; ?>
        <?php if ($errors !== []): ?>
            <div role="alert">
                <?php foreach ($errors as $error): ?>
                    <p><?= e($error) ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
        <form method="post" action="<?= e(url('/admin/password.php')) ?>" novalidate>
            <?= Csrf::field($csrfKey) ?>
            <div>
                <label for="current_password">Password Saat Ini</label>
                <input id="current_password" name="current_password" type="password" autocomplete="current-password" required>
            </div>
            <div>
                <label for="new_password">Password Baru</label>
                <input id="new_password" name="new_password" type="password" autocomplete="new-password" required minlength="10">
            </div>
            <div>
                <label for="confirm_password">Konfirmasi Password Baru</label>
                <input id="confirm_password" name="confirm_password" type="password" autocomplete="new-password" required minlength="10">
            </div>
            <button type="submit">Simpan Password</button>
        </form>
        <p><a href="<?= e(url('/admin/dashboard.php')) ?>">Kembali ke dashboard</a></p>
    </main>
</body>
</html>
