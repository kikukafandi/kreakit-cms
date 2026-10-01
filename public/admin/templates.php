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

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
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
<?php admin_layout_start('Template & Tampilan', 'templates', 'Pilih desain website yang paling cocok untuk bisnis. Template aktif langsung dipakai di halaman publik.'); ?>
<?php admin_flash_block($success ?: null, $error ?: null); ?>
<?php if ($templates === []): ?>
    <?php admin_empty_state('Belum ada template aktif', 'Import seed template atau aktifkan template dari database.'); ?>
<?php else: ?>
    <div class="grid gap-6 md:grid-cols-2 xl:grid-cols-3">
        <?php foreach ($templates as $template): ?>
            <?php $slug = (string) $template['slug']; $previewUrl = template_preview_image_url($slug); $isActive = $slug === $activeSlug; $hasFile = template_file_for_slug($slug) !== null; ?>
            <article class="overflow-hidden rounded-[2rem] border <?= $isActive ? 'border-brand-600 ring-4 ring-brand-100' : 'border-slate-200' ?> bg-white shadow-sm shadow-slate-200/70">
                <div class="relative aspect-[4/3] bg-gradient-to-br from-slate-100 to-brand-50">
                    <?php if ($previewUrl !== null): ?>
                        <img src="<?= e($previewUrl) ?>" alt="Preview template <?= e($template['name']) ?>" class="h-full w-full object-cover">
                    <?php else: ?>
                        <div class="flex h-full items-center justify-center p-6 text-center">
                            <div>
                                <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-3xl bg-white text-3xl shadow">🎨</div>
                                <p class="mt-4 text-sm font-bold text-slate-600">Preview belum tersedia</p>
                                <p class="mt-1 text-xs text-slate-500">Template tetap bisa dipilih jika file view tersedia.</p>
                            </div>
                        </div>
                    <?php endif; ?>
                    <?php if ($isActive): ?><span class="absolute left-4 top-4 rounded-full bg-slate-950 px-3 py-1 text-xs font-black text-white shadow">Aktif</span><?php endif; ?>
                </div>
                <div class="p-5">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <h2 class="text-xl font-black text-slate-950"><?= e($template['name']) ?></h2>
                            <p class="mt-1 text-xs font-semibold uppercase tracking-wider text-brand-700"><?= e($template['niche'] ?? 'UMKM') ?></p>
                        </div>
                        <?= admin_status_badge($hasFile, 'Siap', 'File hilang') ?>
                    </div>
                    <p class="mt-3 text-sm leading-6 text-slate-500"><?= e($template['description'] ?? '') ?></p>
                    <p class="mt-3 rounded-2xl bg-slate-50 px-3 py-2 text-xs font-semibold text-slate-500">Slug: <?= e($slug) ?></p>
                    <?php if (!$hasFile): ?>
                        <div role="alert" class="mt-4 rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm font-semibold text-red-800">File template belum tersedia, tidak bisa dipilih.</div>
                    <?php else: ?>
                        <form method="post" action="<?= e(url('/admin/templates.php')) ?>" class="mt-5 flex flex-wrap gap-3">
                            <?= Csrf::field($csrfKey) ?><input type="hidden" name="template_slug" value="<?= e($slug) ?>">
                            <button type="submit" class="<?= admin_primary_button() ?>"<?= $isActive ? ' disabled' : '' ?>><?= $isActive ? 'Template Aktif' : 'Pilih Template' ?></button>
                            <button type="submit" name="preview_after_save" value="1" class="<?= admin_secondary_button() ?>">Pilih & Preview</button>
                        </form>
                    <?php endif; ?>
                </div>
            </article>
        <?php endforeach; ?>
    </div>
<?php endif; ?>
<?php admin_layout_end(); ?>
