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
<?php admin_layout_start('Ubah Password', 'password', 'Gunakan password kuat agar admin website tetap aman.'); ?>
<?php if ($success): ?><div role="status" class="mb-4 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-800"><?= e($success) ?></div><?php endif; ?>
<?php if ($errors !== []): ?>
    <div role="alert" class="mb-4 rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm font-semibold text-red-800">
        <?php foreach ($errors as $error): ?><p><?= e($error) ?></p><?php endforeach; ?>
    </div>
<?php endif; ?>
<section class="<?= admin_card('max-w-2xl') ?>">
    <p class="mb-5 text-sm text-slate-500">Admin: <strong class="text-slate-700"><?= e($admin['email'] ?? '') ?></strong></p>
    <form method="post" action="<?= e(url('/admin/password.php')) ?>" class="space-y-5" novalidate>
        <?= Csrf::field($csrfKey) ?>
        <div><label for="current_password" class="<?= admin_label_class() ?>">Password Saat Ini</label><input id="current_password" name="current_password" type="password" autocomplete="current-password" required class="<?= admin_input_class() ?>"></div>
        <div><label for="new_password" class="<?= admin_label_class() ?>">Password Baru</label><input id="new_password" name="new_password" type="password" autocomplete="new-password" required minlength="10" class="<?= admin_input_class() ?>"><p class="<?= admin_help_class() ?>">Minimal 10 karakter, lebih aman jika memakai kombinasi huruf, angka, dan simbol.</p></div>
        <div><label for="confirm_password" class="<?= admin_label_class() ?>">Konfirmasi Password Baru</label><input id="confirm_password" name="confirm_password" type="password" autocomplete="new-password" required minlength="10" class="<?= admin_input_class() ?>"></div>
        <div class="flex flex-wrap gap-3"><button type="submit" class="<?= admin_primary_button() ?>">Simpan Password</button><a href="<?= e(url('/admin/dashboard.php')) ?>" class="<?= admin_secondary_button() ?>">Kembali</a></div>
    </form>
</section>
<?php admin_layout_end(); ?>
