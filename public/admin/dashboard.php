<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/app/bootstrap.php';

use KreaKit\Core\Session;

Session::start(app_config('session', []));
require_admin();

$admin = \KreaKit\Core\Auth::admin();
$success = Session::flash('success');
$error = Session::flash('error');

// Read-only summary of what the public site currently shows.
$database = db();
$settings = settings_array($database);
$business = $database->selectOne('SELECT * FROM business_profiles ORDER BY id ASC LIMIT 1') ?? [];
$itemStats = $database->selectOne('SELECT COUNT(*) AS total, COALESCE(SUM(is_active = 1), 0) AS active, COALESCE(SUM(is_active = 1 AND image_path IS NOT NULL AND image_path <> \'\'), 0) AS with_image FROM items') ?? [];
$categoryCount = (int) ($database->selectOne('SELECT COUNT(*) AS total FROM categories WHERE is_active = 1')['total'] ?? 0);
$socialCount = (int) ($database->selectOne('SELECT COUNT(*) AS total FROM social_links WHERE is_active = 1')['total'] ?? 0);
$template = active_template_row($database, $settings);
$previewUrl = $template ? template_preview_image_url((string) $template['slug']) : null;

$activeItems = (int) ($itemStats['active'] ?? 0);
$checklist = [
    ['done' => !empty($business['business_name']) && !empty($business['description']), 'label' => 'Nama dan deskripsi bisnis', 'href' => url('/admin/business.php')],
    ['done' => !empty($business['whatsapp_number']), 'label' => 'Nomor WhatsApp untuk tombol pesan', 'href' => url('/admin/business.php')],
    ['done' => !empty($business['logo_path']), 'label' => 'Logo bisnis', 'href' => url('/admin/business.php')],
    ['done' => !empty($business['hero_image_path']), 'label' => 'Foto utama di bagian atas website', 'href' => url('/admin/business.php')],
    ['done' => !empty($business['address']), 'label' => 'Alamat dan link Google Maps', 'href' => url('/admin/business.php')],
    ['done' => $activeItems >= 3, 'label' => 'Minimal 3 produk atau layanan aktif', 'href' => url('/admin/items.php')],
    ['done' => $activeItems > 0 && (int) ($itemStats['with_image'] ?? 0) === $activeItems, 'label' => 'Foto untuk setiap produk aktif', 'href' => url('/admin/items.php')],
    ['done' => $socialCount > 0, 'label' => 'Minimal 1 link sosial media', 'href' => url('/admin/business.php')],
];
$doneCount = count(array_filter($checklist, static fn (array $step): bool => $step['done']));
$progress = (int) round($doneCount / count($checklist) * 100);

http_response_code(200);
?>
<?php admin_layout_start('Halo, ' . ($admin['name'] ?? 'Admin'), 'dashboard', 'Ringkasan website ' . ($business['business_name'] ?? 'bisnis') . ' dan langkah yang masih perlu dilengkapi.'); ?>
<?php admin_flash_block($success ?: null, $error ?: null); ?>

<div class="grid gap-5 lg:grid-cols-[1.4fr_1fr]">
    <section class="<?= admin_card('overflow-hidden !p-0') ?>">
        <div class="grid sm:grid-cols-[1.1fr_1fr]">
            <div class="aspect-[16/10] bg-slate-100 sm:aspect-auto">
                <?php if ($previewUrl !== null): ?>
                    <img src="<?= e($previewUrl) ?>" alt="Preview template <?= e($template['name'] ?? '') ?>" class="h-full w-full object-cover object-left-top">
                <?php endif; ?>
            </div>
            <div class="flex flex-col p-6">
                <p class="text-sm font-medium text-slate-500">Template aktif</p>
                <h2 class="mt-1 text-xl font-extrabold tracking-tight text-slate-950"><?= e($template['name'] ?? 'Belum dipilih') ?></h2>
                <?php if (!empty($template['description'])): ?><p class="mt-2 text-sm leading-6 text-slate-500"><?= e($template['description']) ?></p><?php endif; ?>
                <div class="mt-auto flex flex-wrap gap-2 pt-5">
                    <a href="<?= e(url('/')) ?>" target="_blank" rel="noopener" class="<?= admin_primary_button() ?>"><?= admin_icon('eye') ?> Lihat website</a>
                    <a href="<?= e(url('/admin/templates.php')) ?>" class="<?= admin_secondary_button() ?>">Ganti template</a>
                </div>
            </div>
        </div>
    </section>

    <section class="grid grid-cols-2 gap-px overflow-hidden rounded-2xl border border-slate-200 bg-slate-200">
        <?php foreach ([
            ['value' => $activeItems, 'label' => 'Produk/layanan aktif', 'href' => url('/admin/items.php')],
            ['value' => $categoryCount, 'label' => 'Kategori', 'href' => url('/admin/categories.php')],
            ['value' => (int) ($itemStats['with_image'] ?? 0), 'label' => 'Item dengan foto', 'href' => url('/admin/items.php')],
            ['value' => $socialCount, 'label' => 'Link sosial media', 'href' => url('/admin/business.php')],
        ] as $stat): ?>
            <a href="<?= e($stat['href']) ?>" class="bg-white p-5 transition hover:bg-slate-50">
                <span class="block text-3xl font-extrabold tracking-tight text-slate-950"><?= e($stat['value']) ?></span>
                <span class="mt-1 block text-sm text-slate-500"><?= e($stat['label']) ?></span>
            </a>
        <?php endforeach; ?>
    </section>
</div>

<section class="mt-5 <?= admin_card() ?>">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h2 class="text-lg font-extrabold tracking-tight text-slate-950">Kelengkapan website</h2>
            <p class="mt-1 text-sm text-slate-500"><?= $doneCount === count($checklist) ? 'Semua sudah lengkap. Website siap dibagikan ke pelanggan.' : 'Lengkapi poin di bawah supaya website terlihat meyakinkan.' ?></p>
        </div>
        <div class="flex items-center gap-3 sm:w-64">
            <div class="h-2 flex-1 overflow-hidden rounded-full bg-slate-100"><div class="h-full rounded-full bg-brand-600" style="width: <?= $progress ?>%"></div></div>
            <span class="text-sm font-bold text-slate-700"><?= $doneCount ?>/<?= count($checklist) ?></span>
        </div>
    </div>
    <ul class="mt-5 grid gap-x-8 sm:grid-cols-2">
        <?php foreach ($checklist as $step): ?>
            <li>
                <a href="<?= e($step['href']) ?>" class="group flex items-center gap-3 border-t border-slate-100 py-3 text-sm">
                    <span class="text-xl <?= $step['done'] ? 'text-brand-600' : 'text-slate-300' ?>"><?= admin_icon($step['done'] ? 'check-circle' : 'circle') ?></span>
                    <span class="flex-1 <?= $step['done'] ? 'text-slate-500' : 'font-semibold text-slate-800' ?>"><?= e($step['label']) ?></span>
                    <?php if (!$step['done']): ?><span class="text-xs font-semibold text-brand-700 opacity-0 transition group-hover:opacity-100">Lengkapi</span><?php endif; ?>
                </a>
            </li>
        <?php endforeach; ?>
    </ul>
</section>
<?php admin_layout_end(); ?>
