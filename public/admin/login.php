<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/app/bootstrap.php';

use KreaKit\Core\Auth;
use KreaKit\Core\Csrf;
use KreaKit\Core\Session;

Session::start(app_config('session', []));

if (Auth::check()) {
    redirect(url('/admin/dashboard.php'));
}

$csrfKey = (string) app_config('security.csrf_key', '_csrf_token');
$email = '';
$error = Session::flash('error');
$success = Session::flash('success');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    Csrf::requireValid($_POST[$csrfKey] ?? null, $csrfKey);

    $email = trim((string) ($_POST['email'] ?? ''));
    $password = (string) ($_POST['password'] ?? '');

    $result = Auth::attempt(db(), $email, $password);
    if ($result['success']) {
        Session::flash('success', 'Selamat datang di admin KreaKit CMS.');
        redirect(url('/admin/dashboard.php'));
    }

    $error = $result['message'];
}

http_response_code(200);
?>
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login Admin — <?= e(app_config('app.name', 'KreaKit CMS')) ?></title>
</head>
<body>
    <main>
        <h1>Login Admin</h1>
        <?php if ($success): ?>
            <p role="status"><?= e($success) ?></p>
        <?php endif; ?>
        <?php if ($error): ?>
            <p role="alert"><?= e($error) ?></p>
        <?php endif; ?>
        <?php if (Auth::isLoginLocked()): ?>
            <p role="alert">Terlalu banyak percobaan login. Coba lagi dalam <?= e((string) Auth::remainingLockSeconds()) ?> detik.</p>
        <?php endif; ?>
        <form method="post" action="<?= e(url('/admin/login.php')) ?>" novalidate>
            <?= Csrf::field($csrfKey) ?>
            <div>
                <label for="email">Email</label>
                <input id="email" name="email" type="email" value="<?= e($email) ?>" autocomplete="username" required maxlength="190">
            </div>
            <div>
                <label for="password">Password</label>
                <input id="password" name="password" type="password" autocomplete="current-password" required>
            </div>
            <button type="submit"<?= Auth::isLoginLocked() ? ' disabled' : '' ?>>Login</button>
        </form>
    </main>
</body>
</html>
