<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/app/bootstrap.php';
require_once base_path('app/Helpers/installer.php');

use KreaKit\Core\Csrf;
use KreaKit\Core\Session;

Session::start(app_config('session', []));

$csrfKey = (string) app_config('security.csrf_key', '_csrf_token');
$requirements = installer_requirements();
$requirementsOk = installer_requirements_ok();
$locked = installer_is_locked();
$action = (string) ($_POST['action'] ?? '');
$success = null;
$error = null;
$connectionOk = false;

$form = [
    'base_url' => rtrim((string) app_config('app.base_url', ''), '/'),
    'timezone' => (string) app_config('app.timezone', 'Asia/Jakarta'),
    'db_host' => (string) app_config('database.host', 'localhost'),
    'db_port' => (string) app_config('database.port', '3306'),
    'db_name' => (string) app_config('database.name', ''),
    'db_user' => (string) app_config('database.user', ''),
    'db_password' => '',
    'db_charset' => (string) app_config('database.charset', 'utf8mb4'),
    'admin_name' => 'Admin KreaKit',
    'admin_email' => '',
    'create_database' => '',
];

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST' && !$locked) {
    Csrf::requireValid($_POST[$csrfKey] ?? null, $csrfKey);

    foreach (array_keys($form) as $key) {
        if ($key === 'create_database') {
            $form[$key] = isset($_POST[$key]) ? '1' : '';
            continue;
        }
        $form[$key] = trim((string) ($_POST[$key] ?? ''));
    }
    $form['db_password'] = (string) ($_POST['db_password'] ?? '');

    $attemptCreateDatabase = $form['create_database'] === '1';

    if ($action === 'test_connection') {
        $result = installer_test_connection($form, $attemptCreateDatabase);
        $connectionOk = $result['success'];
        $success = $result['success'] ? $result['message'] : null;
        $error = $result['success'] ? null : $result['message'];
    } elseif ($action === 'install') {
        $adminPassword = (string) ($_POST['admin_password'] ?? '');
        $adminPasswordConfirmation = (string) ($_POST['admin_password_confirmation'] ?? '');
        $validationErrors = [];

        if ($form['db_host'] === '' || $form['db_name'] === '' || $form['db_user'] === '') {
            $validationErrors[] = 'Host, nama database, dan user database wajib diisi.';
        }
        if (!filter_var($form['admin_email'], FILTER_VALIDATE_EMAIL)) {
            $validationErrors[] = 'Email admin tidak valid.';
        }
        if (trim($form['admin_name']) === '') {
            $validationErrors[] = 'Nama admin wajib diisi.';
        }
        if (strlen($adminPassword) < 10) {
            $validationErrors[] = 'Password admin minimal 10 karakter.';
        }
        if ($adminPassword !== $adminPasswordConfirmation) {
            $validationErrors[] = 'Konfirmasi password admin tidak sama.';
        }

        if ($validationErrors !== []) {
            $error = implode(' ', $validationErrors);
        } else {
            $installInput = $form;
            $installInput['admin_password'] = $adminPassword;
            $result = installer_install($installInput, $attemptCreateDatabase);
            if ($result['success']) {
                Session::regenerate();
                $success = $result['message'];
                $locked = true;
            } else {
                $error = $result['message'];
            }
        }
    }
}

function install_e(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function install_input_class(): string
{
    return 'mt-2 w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-teal-600 focus:ring-4 focus:ring-teal-100';
}

function install_label_class(): string
{
    return 'block text-sm font-semibold text-slate-700';
}

http_response_code(200);
?>
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Install KreaKit CMS</title>
    <?= admin_tailwind_cdn() ?>
</head>
<body class="min-h-screen bg-slate-100 text-slate-900 antialiased">
    <main class="mx-auto max-w-5xl px-4 py-8 sm:px-6 lg:px-8">
        <section class="mb-6 rounded-3xl bg-slate-950 p-6 text-white shadow-xl sm:p-8">
            <p class="text-sm font-bold uppercase tracking-[0.28em] text-teal-200">KreaKit CMS</p>
            <div class="mt-4 flex flex-col gap-4 md:flex-row md:items-end md:justify-between">
                <div>
                    <h1 class="text-3xl font-black tracking-tight sm:text-4xl">Installer Wizard</h1>
                    <p class="mt-3 max-w-2xl text-sm leading-6 text-slate-300">Cek requirement, simpan konfigurasi database, import schema/seed, buat admin pertama, lalu kunci installer untuk shared hosting/cPanel.</p>
                </div>
                <a href="<?= install_e(url('/')) ?>" class="inline-flex rounded-full border border-white/20 px-4 py-2 text-sm font-bold text-white hover:bg-white/10">Buka Website</a>
            </div>
        </section>

        <?php if ($locked): ?>
            <section class="rounded-3xl border border-emerald-200 bg-white p-6 shadow-sm sm:p-8">
                <div class="rounded-2xl bg-emerald-50 p-5 text-emerald-900">
                    <h2 class="text-2xl font-black">Installer terkunci</h2>
                    <p class="mt-2 text-sm leading-6">File <code class="rounded bg-emerald-100 px-1">storage/installed.lock</code> sudah ada. Untuk keamanan, installer menolak berjalan lagi.</p>
                    <p class="mt-2 text-sm leading-6">Jika Anda sengaja ingin install ulang, hapus lock file tersebut lewat file manager/FTP setelah backup database dan file.</p>
                </div>
                <div class="mt-6 flex flex-wrap gap-3">
                    <a href="<?= install_e(url('/admin/login.php')) ?>" class="rounded-xl bg-teal-700 px-5 py-3 text-sm font-bold text-white hover:bg-teal-800">Login Admin</a>
                    <a href="<?= install_e(url('/')) ?>" class="rounded-xl border border-slate-300 px-5 py-3 text-sm font-bold text-slate-700 hover:bg-slate-50">Lihat Website</a>
                </div>
            </section>
        <?php else: ?>
            <?php if ($success): ?>
                <div role="alert" class="mb-5 rounded-2xl border border-emerald-200 bg-emerald-50 px-5 py-4 text-sm font-semibold text-emerald-900"><?= install_e($success) ?></div>
            <?php endif; ?>
            <?php if ($error): ?>
                <div role="alert" class="mb-5 rounded-2xl border border-red-200 bg-red-50 px-5 py-4 text-sm font-semibold text-red-900"><?= install_e($error) ?></div>
            <?php endif; ?>

            <section class="mb-6 rounded-3xl bg-white p-6 shadow-sm sm:p-8">
                <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h2 class="text-2xl font-black">1. Requirement Check</h2>
                        <p class="mt-1 text-sm text-slate-500">Perbaiki item merah sebelum menjalankan instalasi.</p>
                    </div>
                    <span class="rounded-full px-4 py-2 text-sm font-bold <?= $requirementsOk ? 'bg-emerald-100 text-emerald-800' : 'bg-red-100 text-red-800' ?>"><?= $requirementsOk ? 'Siap install' : 'Belum siap' ?></span>
                </div>
                <div class="mt-5 grid gap-3 md:grid-cols-2">
                    <?php foreach ($requirements as $requirement): ?>
                        <div class="rounded-2xl border <?= $requirement['ok'] ? 'border-emerald-200 bg-emerald-50' : 'border-red-200 bg-red-50' ?> p-4">
                            <div class="flex items-start gap-3">
                                <span class="mt-0.5 inline-flex h-6 w-6 items-center justify-center rounded-full text-xs font-black <?= $requirement['ok'] ? 'bg-emerald-600 text-white' : 'bg-red-600 text-white' ?>"><?= $requirement['ok'] ? '✓' : '!' ?></span>
                                <div>
                                    <p class="font-bold"><?= install_e($requirement['label']) ?></p>
                                    <p class="mt-1 text-sm text-slate-600"><?= install_e($requirement['detail']) ?></p>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </section>

            <form method="post" action="<?= install_e(url('/install/index.php')) ?>" class="space-y-6" novalidate>
                <?= Csrf::field($csrfKey) ?>

                <section class="rounded-3xl bg-white p-6 shadow-sm sm:p-8">
                    <h2 class="text-2xl font-black">2. Database & Config</h2>
                    <p class="mt-1 text-sm text-slate-500">Mode shared hosting: buat database dan user dari cPanel lebih dulu, lalu masukkan credential di sini.</p>
                    <div class="mt-5 grid gap-5 md:grid-cols-2">
                        <div>
                            <label for="base_url" class="<?= install_label_class() ?>">Base URL (opsional)</label>
                            <input id="base_url" name="base_url" value="<?= install_e($form['base_url']) ?>" class="<?= install_input_class() ?>" placeholder="https://domainanda.com">
                        </div>
                        <div>
                            <label for="timezone" class="<?= install_label_class() ?>">Timezone</label>
                            <input id="timezone" name="timezone" value="<?= install_e($form['timezone']) ?>" class="<?= install_input_class() ?>" placeholder="Asia/Jakarta">
                        </div>
                        <div>
                            <label for="db_host" class="<?= install_label_class() ?>">DB Host</label>
                            <input id="db_host" name="db_host" value="<?= install_e($form['db_host']) ?>" required class="<?= install_input_class() ?>" placeholder="localhost">
                        </div>
                        <div>
                            <label for="db_port" class="<?= install_label_class() ?>">DB Port</label>
                            <input id="db_port" name="db_port" value="<?= install_e($form['db_port']) ?>" required inputmode="numeric" class="<?= install_input_class() ?>" placeholder="3306">
                        </div>
                        <div>
                            <label for="db_name" class="<?= install_label_class() ?>">DB Name</label>
                            <input id="db_name" name="db_name" value="<?= install_e($form['db_name']) ?>" required class="<?= install_input_class() ?>" placeholder="cpaneluser_kreakit">
                        </div>
                        <div>
                            <label for="db_user" class="<?= install_label_class() ?>">DB User</label>
                            <input id="db_user" name="db_user" value="<?= install_e($form['db_user']) ?>" required class="<?= install_input_class() ?>" autocomplete="username" placeholder="cpaneluser_dbuser">
                        </div>
                        <div class="md:col-span-2">
                            <label for="db_password" class="<?= install_label_class() ?>">DB Password</label>
                            <input id="db_password" name="db_password" value="" type="password" class="<?= install_input_class() ?>" autocomplete="new-password" placeholder="Password database">
                            <p class="mt-2 text-xs text-slate-500">Password tidak ditampilkan ulang dan tidak ditulis ke log installer.</p>
                        </div>
                        <div>
                            <label for="db_charset" class="<?= install_label_class() ?>">DB Charset</label>
                            <input id="db_charset" name="db_charset" value="<?= install_e($form['db_charset']) ?>" class="<?= install_input_class() ?>" placeholder="utf8mb4">
                        </div>
                        <div class="rounded-2xl border border-amber-200 bg-amber-50 p-4">
                            <label class="flex items-start gap-3 text-sm font-semibold text-amber-900">
                                <input type="checkbox" name="create_database" value="1" class="mt-1" <?= $form['create_database'] === '1' ? 'checked' : '' ?>>
                                <span>Advanced: coba buat database otomatis jika user DB punya privilege CREATE DATABASE.</span>
                            </label>
                            <p class="mt-2 text-xs leading-5 text-amber-800">Jangan centang di shared hosting biasa kecuali provider memang memberi privilege tersebut.</p>
                        </div>
                    </div>
                    <div class="mt-5">
                        <button type="submit" name="action" value="test_connection" class="rounded-xl border border-teal-700 px-5 py-3 text-sm font-bold text-teal-800 hover:bg-teal-50">Test Koneksi</button>
                        <?php if ($connectionOk): ?><span class="ml-3 text-sm font-bold text-emerald-700">Koneksi OK</span><?php endif; ?>
                    </div>
                </section>

                <section class="rounded-3xl bg-white p-6 shadow-sm sm:p-8">
                    <h2 class="text-2xl font-black">3. Admin Pertama & Install</h2>
                    <p class="mt-1 text-sm text-slate-500">Schema dan seed akan dijalankan, lalu admin pertama dibuat/ditimpa memakai password_hash().</p>
                    <div class="mt-5 grid gap-5 md:grid-cols-2">
                        <div>
                            <label for="admin_name" class="<?= install_label_class() ?>">Nama Admin</label>
                            <input id="admin_name" name="admin_name" value="<?= install_e($form['admin_name']) ?>" required maxlength="100" class="<?= install_input_class() ?>" placeholder="Admin Bisnis">
                        </div>
                        <div>
                            <label for="admin_email" class="<?= install_label_class() ?>">Email Admin</label>
                            <input id="admin_email" name="admin_email" value="<?= install_e($form['admin_email']) ?>" type="email" required maxlength="190" class="<?= install_input_class() ?>" autocomplete="username" placeholder="admin@domainanda.com">
                        </div>
                        <div>
                            <label for="admin_password" class="<?= install_label_class() ?>">Password Admin</label>
                            <input id="admin_password" name="admin_password" type="password" required minlength="10" class="<?= install_input_class() ?>" autocomplete="new-password" placeholder="Minimal 10 karakter">
                        </div>
                        <div>
                            <label for="admin_password_confirmation" class="<?= install_label_class() ?>">Konfirmasi Password</label>
                            <input id="admin_password_confirmation" name="admin_password_confirmation" type="password" required minlength="10" class="<?= install_input_class() ?>" autocomplete="new-password" placeholder="Ulangi password">
                        </div>
                    </div>
                    <div class="mt-6 rounded-2xl border border-slate-200 bg-slate-50 p-4 text-sm leading-6 text-slate-600">
                        Setelah berhasil, installer membuat <code class="rounded bg-white px-1">storage/installed.lock</code> dan halaman ini akan menolak install ulang sampai lock dihapus secara sengaja.
                    </div>
                    <div class="mt-6">
                        <button type="submit" name="action" value="install" class="rounded-xl bg-teal-700 px-6 py-3 text-sm font-black text-white shadow-lg shadow-teal-900/20 hover:bg-teal-800" <?= $requirementsOk ? '' : 'disabled' ?>>Jalankan Install</button>
                    </div>
                </section>
            </form>
        <?php endif; ?>
    </main>
</body>
</html>
