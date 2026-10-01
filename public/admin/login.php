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

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
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
    <?php admin_head('Login Admin'); ?>
</head>
<body class="min-h-screen bg-slate-50 text-slate-900">
    <main class="flex min-h-screen flex-col items-center justify-center px-4 py-10">
        <div class="mb-8 flex items-center gap-3">
            <span class="grid size-10 place-items-center rounded-xl bg-brand-700 text-lg text-white"><?= admin_icon('sparkle') ?></span>
            <span class="leading-tight"><span class="block text-base font-extrabold tracking-tight text-slate-950">KreaKit</span><span class="block text-xs font-medium text-slate-500">Panel website</span></span>
        </div>
        <div class="w-full max-w-sm rounded-2xl border border-slate-200 bg-white p-6 shadow-[0_24px_60px_-28px_rgba(15,23,42,0.25)] sm:p-8">
            <h1 class="text-xl font-extrabold tracking-tight text-slate-950">Masuk ke panel</h1>
            <p class="mt-1.5 mb-6 text-sm leading-6 text-slate-500">Pakai email admin yang dibuat saat instalasi.</p>
            <?php admin_flash_block($success ?: null, $error ?: null); ?>
            <?php if (Auth::isLoginLocked()): ?>
                <div role="alert" class="mb-4 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm font-semibold text-amber-800">Terlalu banyak percobaan login. Coba lagi dalam <?= e((string) Auth::remainingLockSeconds()) ?> detik.</div>
            <?php endif; ?>
            <form method="post" action="<?= e(url('/admin/login.php')) ?>" class="space-y-4" novalidate>
                <?= Csrf::field($csrfKey) ?>
                <div>
                    <label for="email" class="<?= admin_label_class() ?>">Email</label>
                    <input id="email" name="email" type="email" value="<?= e($email) ?>" autocomplete="username" required maxlength="190" class="<?= admin_input_class() ?>" placeholder="admin@bisnisanda.com">
                </div>
                <div>
                    <label for="password" class="<?= admin_label_class() ?>">Password</label>
                    <input id="password" name="password" type="password" autocomplete="current-password" required class="<?= admin_input_class() ?>">
                </div>
                <button type="submit" class="<?= admin_primary_button('w-full !py-3') ?>"<?= Auth::isLoginLocked() ? ' disabled' : '' ?>>Masuk</button>
            </form>
        </div>
        <a href="<?= e(url('/')) ?>" class="mt-6 inline-flex items-center gap-1.5 text-sm font-semibold text-slate-500 hover:text-slate-900"><?= admin_icon('external') ?> Lihat website</a>
    </main>
</body>
</html>
