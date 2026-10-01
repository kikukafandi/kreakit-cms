<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/app/bootstrap.php';

use KreaKit\Core\Csrf;
use KreaKit\Core\Session;

Session::start(app_config('session', []));
require_admin();

$csrfKey = (string) app_config('security.csrf_key', '_csrf_token');
$database = db();
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    Csrf::requireValid($_POST[$csrfKey] ?? null, $csrfKey);
    $slug = sanitize_text((string) ($_POST['template_slug'] ?? ''), 100);

    if (!preg_match('/^[a-z0-9-]{1,100}$/', $slug)) {
        $errors[] = 'Template slug tidak valid.';
    }

    $template = $errors === []
        ? $database->selectOne('SELECT * FROM templates WHERE slug = :slug AND is_active = 1 LIMIT 1', ['slug' => $slug])
        : null;

    if ($template === null) {
        $errors[] = 'Template tidak tersedia.';
    } elseif (template_file_for_slug($slug) === null) {
        $errors[] = 'File template tidak ditemukan.';
    }

    if ($errors === []) {
        upsert_setting($database, 'active_template_slug', $slug);
        Session::flash('success', 'Template aktif berhasil disimpan.');
        redirect(isset($_POST['preview_after_save']) ? url('/') : url('/admin/templates.php'));
    }

    admin_flash_errors($errors);
    redirect(url('/admin/templates.php'));
}

$settings = settings_array($database);
$activeSlug = (string) ($settings['active_template_slug'] ?? '');
$templates = $database->select('SELECT * FROM templates WHERE is_active = 1 ORDER BY id ASC, name ASC');
$success = Session::flash('success');
$error = Session::flash('error');
?>
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Template — <?= e(app_config('app.name', 'KreaKit CMS')) ?></title>
</head>
<body>
<main>
    <h1>Template & Tampilan</h1>
    <p><a href="<?= e(url('/admin/dashboard.php')) ?>">Dashboard</a> · <a href="<?= e(url('/admin/business.php')) ?>">Profil Bisnis</a> · <a href="<?= e(url('/admin/items.php')) ?>">Produk/Layanan</a> · <a href="<?= e(url('/')) ?>" target="_blank" rel="noopener">Preview Website</a></p>
    <?php if ($success): ?><p role="status"><?= e($success) ?></p><?php endif; ?>
    <?php if ($error): ?><p role="alert"><?= e($error) ?></p><?php endif; ?>

    <?php if ($templates === []): ?>
        <p>Belum ada template aktif.</p>
    <?php else: ?>
        <div>
            <?php foreach ($templates as $template): ?>
                <?php $slug = (string) $template['slug']; ?>
                <section style="border:1px solid #ddd;margin:1rem 0;padding:1rem">
                    <h2><?= e($template['name']) ?><?= $slug === $activeSlug ? ' (Aktif)' : '' ?></h2>
                    <p><strong>Slug:</strong> <?= e($slug) ?> · <strong>Niche:</strong> <?= e($template['niche'] ?? '') ?></p>
                    <p><?= e($template['description'] ?? '') ?></p>
                    <?php if (template_file_for_slug($slug) === null): ?>
                        <p role="alert">File template belum tersedia, tidak bisa dipilih.</p>
                    <?php else: ?>
                        <form method="post" action="<?= e(url('/admin/templates.php')) ?>">
                            <?= Csrf::field($csrfKey) ?>
                            <input type="hidden" name="template_slug" value="<?= e($slug) ?>">
                            <button type="submit"<?= $slug === $activeSlug ? ' disabled' : '' ?>>Pilih Template</button>
                            <button type="submit" name="preview_after_save" value="1">Pilih & Preview</button>
                        </form>
                    <?php endif; ?>
                </section>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</main>
</body>
</html>
