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
<body class="min-h-screen bg-slate-950 text-slate-900 antialiased">
    <main class="grid min-h-screen lg:grid-cols-[1.05fr_0.95fr]">
        <section class="hidden bg-gradient-to-br from-brand-900 via-slate-900 to-slate-950 p-10 text-white lg:flex lg:flex-col lg:justify-between">
            <div>
                <p class="text-sm font-semibold uppercase tracking-[0.35em] text-brand-100">KreaKit CMS</p>
                <h1 class="mt-6 max-w-xl text-5xl font-black leading-tight tracking-tight">Kelola website UMKM tanpa ribet teknis.</h1>
                <p class="mt-5 max-w-lg text-lg leading-8 text-slate-200">Login untuk edit profil bisnis, produk/layanan, kategori, sosial media, dan pilihan template website.</p>
            </div>
            <div class="grid gap-4 rounded-3xl border border-white/10 bg-white/10 p-6 backdrop-blur">
                <p class="font-bold">Yang bisa diedit:</p>
                <div class="grid gap-3 text-sm text-slate-100">
                    <span>✓ Profil bisnis dan kontak WhatsApp</span>
                    <span>✓ Produk, layanan, kategori, dan gambar</span>
                    <span>✓ Template siap pakai untuk katalog UMKM</span>
                </div>
            </div>
        </section>
        <section class="flex items-center justify-center bg-slate-100 px-4 py-10 sm:px-6">
            <div class="w-full max-w-md rounded-[2rem] bg-white p-6 shadow-2xl shadow-slate-950/20 sm:p-8">
                <div class="mb-8">
                    <p class="text-sm font-semibold uppercase tracking-[0.25em] text-brand-700">Admin Area</p>
                    <h2 class="mt-2 text-3xl font-black text-slate-950">Login Admin</h2>
                    <p class="mt-2 text-sm leading-6 text-slate-500">Masuk memakai email admin yang dibuat saat instalasi.</p>
                </div>
                <?php admin_flash_block($success ?: null, $error ?: null); ?>
                <?php if (Auth::isLoginLocked()): ?>
                    <div role="alert" class="mb-4 rounded-2xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm font-semibold text-amber-800">Terlalu banyak percobaan login. Coba lagi dalam <?= e((string) Auth::remainingLockSeconds()) ?> detik.</div>
                <?php endif; ?>
                <form method="post" action="<?= e(url('/admin/login.php')) ?>" class="space-y-5" novalidate>
                    <?= Csrf::field($csrfKey) ?>
                    <div>
                        <label for="email" class="<?= admin_label_class() ?>">Email</label>
                        <input id="email" name="email" type="email" value="<?= e($email) ?>" autocomplete="username" required maxlength="190" class="<?= admin_input_class() ?>" placeholder="admin@bisnisanda.com">
                    </div>
                    <div>
                        <label for="password" class="<?= admin_label_class() ?>">Password</label>
                        <input id="password" name="password" type="password" autocomplete="current-password" required class="<?= admin_input_class() ?>" placeholder="Password admin">
                    </div>
                    <button type="submit" class="<?= admin_primary_button('w-full') ?>"<?= Auth::isLoginLocked() ? ' disabled' : '' ?>>Login</button>
                </form>
            </div>
        </section>
    </main>
</body>
</html>
