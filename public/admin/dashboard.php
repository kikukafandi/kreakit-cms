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
<?php admin_layout_start('Dashboard Admin', 'dashboard', 'Ringkasan cepat untuk memastikan website UMKM siap dipublikasikan.'); ?>
<?php admin_flash_block($success ?: null, $error ?: null); ?>
<div class="grid gap-5 md:grid-cols-3">
    <section class="<?= admin_card() ?>">
        <p class="text-sm font-semibold text-slate-500">Login sebagai</p>
        <h2 class="mt-2 text-2xl font-black text-slate-950"><?= e($admin['name'] ?? 'Admin') ?></h2>
        <p class="mt-1 break-all text-sm text-slate-500"><?= e($admin['email'] ?? '') ?></p>
    </section>
    <section class="<?= admin_card() ?>">
        <p class="text-sm font-semibold text-slate-500">Status MVP</p>
        <h2 class="mt-2 text-2xl font-black text-emerald-700">Admin aktif</h2>
        <p class="mt-1 text-sm text-slate-500">Konten, katalog, dan template bisa dikelola.</p>
    </section>
    <section class="<?= admin_card() ?>">
        <p class="text-sm font-semibold text-slate-500">Website publik</p>
        <a href="<?= e(url('/')) ?>" target="_blank" rel="noopener" class="mt-3 <?= admin_primary_button('w-full') ?>">Buka Preview</a>
    </section>
</div>
<section class="mt-6 <?= admin_card() ?>">
    <div class="flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <h2 class="text-2xl font-black text-slate-950">Langkah edit website</h2>
            <p class="mt-2 text-sm text-slate-500">Mulai dari profil bisnis, lalu lengkapi kategori dan produk/layanan.</p>
        </div>
    </div>
    <div class="mt-6 grid gap-4 md:grid-cols-2 xl:grid-cols-4">
        <?php foreach ([
            ['title' => '1. Profil Bisnis', 'body' => 'Nama, deskripsi, kontak, logo, dan sosial media.', 'href' => url('/admin/business.php')],
            ['title' => '2. Template', 'body' => 'Pilih tampilan yang cocok untuk niche UMKM.', 'href' => url('/admin/templates.php')],
            ['title' => '3. Kategori', 'body' => 'Kelompokkan produk atau layanan agar mudah dibaca.', 'href' => url('/admin/categories.php')],
            ['title' => '4. Produk/Layanan', 'body' => 'Isi katalog, harga, foto, dan pesan WhatsApp.', 'href' => url('/admin/items.php')],
        ] as $step): ?>
            <a href="<?= e($step['href']) ?>" class="rounded-3xl border border-slate-200 bg-slate-50 p-5 transition hover:-translate-y-1 hover:bg-white hover:shadow-lg">
                <h3 class="font-black text-slate-950"><?= e($step['title']) ?></h3>
                <p class="mt-2 text-sm leading-6 text-slate-500"><?= e($step['body']) ?></p>
            </a>
        <?php endforeach; ?>
    </div>
</section>
<?php admin_layout_end(); ?>
