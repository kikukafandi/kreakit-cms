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
    $action = (string) ($_POST['action'] ?? 'save_business');

    try {
        if ($action === 'save_business') {
            $businessName = sanitize_text((string) ($_POST['business_name'] ?? ''), 150);
            $tagline = sanitize_text_or_null((string) ($_POST['tagline'] ?? ''), 190);
            $description = sanitize_multiline_or_null((string) ($_POST['description'] ?? ''), 5000);
            $whatsapp = valid_whatsapp_number((string) ($_POST['whatsapp_number'] ?? ''));
            $address = sanitize_multiline_or_null((string) ($_POST['address'] ?? ''), 2000);
            $mapsUrlRaw = trim((string) ($_POST['maps_url'] ?? ''));
            $mapsUrl = sanitize_http_url($mapsUrlRaw, 500);
            $email = sanitize_text_or_null((string) ($_POST['email'] ?? ''), 190);
            $phone = sanitize_text_or_null((string) ($_POST['phone'] ?? ''), 50);
            $logoPath = validate_local_upload_path((string) ($_POST['existing_logo_path'] ?? ''));
            $heroImagePath = validate_local_upload_path((string) ($_POST['existing_hero_image_path'] ?? ''));

            try {
                $logoUpload = secure_image_upload($_FILES['logo_file'] ?? null, $database);
                if ($logoUpload !== null) {
                    $logoPath = $logoUpload['path'];
                }
                $heroUpload = secure_image_upload($_FILES['hero_image_file'] ?? null, $database);
                if ($heroUpload !== null) {
                    $heroImagePath = $heroUpload['path'];
                }
            } catch (RuntimeException $uploadError) {
                $errors[] = $uploadError->getMessage();
            }

            if ($businessName === '') {
                $errors[] = 'Nama bisnis wajib diisi.';
            }
            if (trim((string) ($_POST['whatsapp_number'] ?? '')) !== '' && $whatsapp === null) {
                $errors[] = 'Nomor WhatsApp harus berisi nomor internasional valid, contoh 6281234567890.';
            }
            if ($mapsUrlRaw !== '' && $mapsUrl === null) {
                $errors[] = 'URL Maps harus valid dan diawali http/https.';
            }
            if ($email !== null && filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
                $errors[] = 'Email kontak tidak valid.';
            }

            if ($errors === []) {
                $existing = $database->selectOne('SELECT id FROM business_profiles ORDER BY id ASC LIMIT 1');
                if ($existing === null) {
                    $database->execute(
                        'INSERT INTO business_profiles (business_name, tagline, description, logo_path, hero_image_path, whatsapp_number, address, maps_url, email, phone) VALUES (:business_name, :tagline, :description, :logo_path, :hero_image_path, :whatsapp_number, :address, :maps_url, :email, :phone)',
                        [
                            'business_name' => $businessName,
                            'tagline' => $tagline,
                            'description' => $description,
                            'logo_path' => $logoPath,
                            'hero_image_path' => $heroImagePath,
                            'whatsapp_number' => $whatsapp,
                            'address' => $address,
                            'maps_url' => $mapsUrl,
                            'email' => $email,
                            'phone' => $phone,
                        ]
                    );
                } else {
                    $database->execute(
                        'UPDATE business_profiles SET business_name = :business_name, tagline = :tagline, description = :description, logo_path = :logo_path, hero_image_path = :hero_image_path, whatsapp_number = :whatsapp_number, address = :address, maps_url = :maps_url, email = :email, phone = :phone WHERE id = :id',
                        [
                            'business_name' => $businessName,
                            'tagline' => $tagline,
                            'description' => $description,
                            'logo_path' => $logoPath,
                            'hero_image_path' => $heroImagePath,
                            'whatsapp_number' => $whatsapp,
                            'address' => $address,
                            'maps_url' => $mapsUrl,
                            'email' => $email,
                            'phone' => $phone,
                            'id' => (int) $existing['id'],
                        ]
                    );
                }
                Session::flash('success', 'Profil bisnis berhasil disimpan.');
                redirect(isset($_POST['preview_after_save']) ? url('/') : url('/admin/business.php'));
            }
        } elseif ($action === 'save_settings') {
            $settings = [
                'primary_color' => sanitize_text((string) ($_POST['primary_color'] ?? ''), 20),
                'secondary_color' => sanitize_text((string) ($_POST['secondary_color'] ?? ''), 20),
                'button_style' => sanitize_text((string) ($_POST['button_style'] ?? 'rounded'), 30),
                'catalog_mode' => sanitize_text((string) ($_POST['catalog_mode'] ?? 'products'), 30),
                'site_meta_title' => sanitize_text((string) ($_POST['site_meta_title'] ?? ''), 150),
                'site_meta_description' => sanitize_text((string) ($_POST['site_meta_description'] ?? ''), 255),
            ];

            foreach (['primary_color', 'secondary_color'] as $colorKey) {
                if ($settings[$colorKey] !== '' && !preg_match('/^#[0-9a-fA-F]{6}$/', $settings[$colorKey])) {
                    $errors[] = 'Warna harus format hex, contoh #0f766e.';
                    break;
                }
            }
            if (!in_array($settings['button_style'], ['rounded', 'square', 'pill'], true)) {
                $errors[] = 'Button style tidak valid.';
            }
            if (!in_array($settings['catalog_mode'], ['products', 'services'], true)) {
                $errors[] = 'Mode katalog tidak valid.';
            }

            if ($errors === []) {
                foreach ($settings as $key => $value) {
                    $database->execute(
                        'INSERT INTO settings (setting_key, setting_value) VALUES (:setting_key, :setting_value) ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)',
                        ['setting_key' => $key, 'setting_value' => $value === '' ? null : $value]
                    );
                }
                Session::flash('success', 'Settings dasar berhasil disimpan.');
                redirect(isset($_POST['preview_after_save']) ? url('/') : url('/admin/business.php#settings'));
            }
        } elseif ($action === 'save_social') {
            $id = id_input('id');
            $platform = sanitize_text((string) ($_POST['platform'] ?? ''), 50);
            $label = sanitize_text_or_null((string) ($_POST['label'] ?? ''), 100);
            $rawUrl = trim((string) ($_POST['url'] ?? ''));
            $socialUrl = sanitize_http_url($rawUrl, 500);
            $sortOrder = int_input('sort_order');
            $isActive = checkbox_bool('is_active');
            $allowedPlatforms = array_keys(social_platform_options());

            if (!in_array($platform, $allowedPlatforms, true)) {
                $errors[] = 'Platform sosial tidak valid.';
            }
            if ($rawUrl === '' || $socialUrl === null) {
                $errors[] = 'URL sosial wajib valid dan diawali http/https.';
            }

            if ($errors === []) {
                if ($id > 0) {
                    $database->execute(
                        'UPDATE social_links SET platform = :platform, label = :label, url = :url, sort_order = :sort_order, is_active = :is_active WHERE id = :id',
                        ['platform' => $platform, 'label' => $label, 'url' => $socialUrl, 'sort_order' => $sortOrder, 'is_active' => $isActive, 'id' => $id]
                    );
                    Session::flash('success', 'Social link berhasil diperbarui.');
                } else {
                    $database->execute(
                        'INSERT INTO social_links (platform, label, url, sort_order, is_active) VALUES (:platform, :label, :url, :sort_order, :is_active)',
                        ['platform' => $platform, 'label' => $label, 'url' => $socialUrl, 'sort_order' => $sortOrder, 'is_active' => $isActive]
                    );
                    Session::flash('success', 'Social link berhasil ditambahkan.');
                }
                redirect(url('/admin/business.php#social'));
            }
        } elseif ($action === 'delete_social' || $action === 'deactivate_social') {
            $id = id_input('id');
            if ($id <= 0) {
                $errors[] = 'Social link tidak valid.';
            } elseif ($action === 'delete_social') {
                $database->execute('DELETE FROM social_links WHERE id = :id', ['id' => $id]);
                Session::flash('success', 'Social link berhasil dihapus.');
                redirect(url('/admin/business.php#social'));
            } else {
                $database->execute('UPDATE social_links SET is_active = 0 WHERE id = :id', ['id' => $id]);
                Session::flash('success', 'Social link berhasil dinonaktifkan.');
                redirect(url('/admin/business.php#social'));
            }
        }
    } catch (Throwable) {
        $errors[] = 'Data tidak dapat disimpan saat ini.';
    }

    if ($errors !== []) {
        admin_flash_errors($errors);
        redirect(url('/admin/business.php'));
    }
}

$business = $database->selectOne('SELECT * FROM business_profiles ORDER BY id ASC LIMIT 1') ?? [];
$settingsRows = $database->select('SELECT setting_key, setting_value FROM settings WHERE setting_key IN (:active_template_slug, :primary_color, :secondary_color, :button_style, :site_meta_title, :site_meta_description, :catalog_mode)', [
    'active_template_slug' => 'active_template_slug',
    'primary_color' => 'primary_color',
    'secondary_color' => 'secondary_color',
    'button_style' => 'button_style',
    'site_meta_title' => 'site_meta_title',
    'site_meta_description' => 'site_meta_description',
    'catalog_mode' => 'catalog_mode',
]);
$settings = [];
foreach ($settingsRows as $row) {
    $settings[(string) $row['setting_key']] = (string) ($row['setting_value'] ?? '');
}
$socialLinks = $database->select('SELECT * FROM social_links ORDER BY sort_order ASC, id ASC');
$editSocialId = filter_input(INPUT_GET, 'edit_social', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
$editSocial = null;
if (is_int($editSocialId)) {
    $editSocial = $database->selectOne('SELECT * FROM social_links WHERE id = :id LIMIT 1', ['id' => $editSocialId]);
}

$success = Session::flash('success');
$error = Session::flash('error');
?>
<?php admin_layout_start('Profil Bisnis', 'business', 'Isi data utama yang tampil di website: nama bisnis, kontak, warna, SEO, dan sosial media.'); ?>
<?php admin_flash_block($success ?: null, $error ?: null); ?>
<div class="grid gap-6 xl:grid-cols-[1.2fr_0.8fr]">
    <section class="<?= admin_card() ?>">
        <div class="mb-6"><h2 class="text-lg font-extrabold tracking-tight text-slate-950">Data Bisnis</h2><p class="mt-2 text-sm text-slate-500">Bagian ini menjadi isi utama hero dan kontak website publik.</p></div>
        <form method="post" action="<?= e(url('/admin/business.php')) ?>" enctype="multipart/form-data" class="grid gap-5" novalidate>
            <?= Csrf::field($csrfKey) ?><input type="hidden" name="action" value="save_business">
            <div><label class="<?= admin_label_class() ?>">Nama Bisnis</label><input name="business_name" required maxlength="150" value="<?= e($business['business_name'] ?? '') ?>" class="<?= admin_input_class() ?>" placeholder="Contoh: Dapur Ibu Sari"></div>
            <div><label class="<?= admin_label_class() ?>">Tagline</label><input name="tagline" maxlength="190" value="<?= e($business['tagline'] ?? '') ?>" class="<?= admin_input_class() ?>" placeholder="Masakan rumahan siap antar"></div>
            <div><label class="<?= admin_label_class() ?>">Deskripsi</label><textarea name="description" rows="5" class="<?= admin_input_class() ?>" placeholder="Ceritakan bisnis, layanan, area, dan keunggulan utama."><?= e($business['description'] ?? '') ?></textarea></div>
            <input type="hidden" name="existing_logo_path" value="<?= e($business['logo_path'] ?? '') ?>"><input type="hidden" name="existing_hero_image_path" value="<?= e($business['hero_image_path'] ?? '') ?>">
            <div class="grid gap-5 md:grid-cols-2">
                <div><label class="<?= admin_label_class() ?>">Logo</label><input name="logo_file" type="file" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp" class="<?= admin_input_class() ?>"><p class="<?= admin_help_class() ?>">jpg/png/webp, maks 2 MB. <?php if (!empty($business['logo_path'])): ?>Saat ini: <?= e($business['logo_path']) ?><?php endif; ?></p></div>
                <div><label class="<?= admin_label_class() ?>">Hero Image</label><input name="hero_image_file" type="file" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp" class="<?= admin_input_class() ?>"><p class="<?= admin_help_class() ?>">Gambar besar di halaman utama. <?php if (!empty($business['hero_image_path'])): ?>Saat ini: <?= e($business['hero_image_path']) ?><?php endif; ?></p></div>
            </div>
            <div class="grid gap-5 md:grid-cols-2">
                <div><label class="<?= admin_label_class() ?>">WhatsApp</label><input name="whatsapp_number" maxlength="30" placeholder="6281234567890" value="<?= e($business['whatsapp_number'] ?? '') ?>" class="<?= admin_input_class() ?>"><p class="<?= admin_help_class() ?>">Gunakan format internasional tanpa + atau spasi.</p></div>
                <div><label class="<?= admin_label_class() ?>">Phone</label><input name="phone" maxlength="50" value="<?= e($business['phone'] ?? '') ?>" class="<?= admin_input_class() ?>"></div>
            </div>
            <div><label class="<?= admin_label_class() ?>">Alamat</label><textarea name="address" rows="3" class="<?= admin_input_class() ?>"><?= e($business['address'] ?? '') ?></textarea></div>
            <div class="grid gap-5 md:grid-cols-2"><div><label class="<?= admin_label_class() ?>">URL Maps</label><input name="maps_url" type="url" maxlength="500" placeholder="https://maps.google.com/..." value="<?= e($business['maps_url'] ?? '') ?>" class="<?= admin_input_class() ?>"></div><div><label class="<?= admin_label_class() ?>">Email</label><input name="email" type="email" maxlength="190" value="<?= e($business['email'] ?? '') ?>" class="<?= admin_input_class() ?>"></div></div>
            <div class="flex flex-wrap gap-3"><button type="submit" class="<?= admin_primary_button() ?>">Simpan Profil</button><button type="submit" name="preview_after_save" value="1" class="<?= admin_secondary_button() ?>">Simpan & Preview</button></div>
        </form>
    </section>
    <aside class="space-y-6">
        <section id="settings" class="<?= admin_card() ?>">
            <h2 class="text-lg font-extrabold tracking-tight text-slate-950">Settings Dasar</h2><p class="mt-2 text-sm text-slate-500">Atur warna dan metadata sederhana.</p>
            <form method="post" action="<?= e(url('/admin/business.php')) ?>" class="mt-5 grid gap-4" novalidate>
                <?= Csrf::field($csrfKey) ?><input type="hidden" name="action" value="save_settings">
                <div class="grid gap-4 sm:grid-cols-2"><div><label class="<?= admin_label_class() ?>">Primary Color</label><div class="mt-1.5 flex gap-2"><input type="color" aria-label="Pilih warna" value="<?= e(preg_match('/^#[0-9a-fA-F]{6}$/', (string) ($settings['primary_color'] ?? '')) ? $settings['primary_color'] : '#0f766e') ?>" oninput="this.nextElementSibling.value=this.value" class="h-[42px] w-12 shrink-0 cursor-pointer rounded-xl border border-slate-300 bg-white p-1"><input name="primary_color" maxlength="20" placeholder="#0f766e" value="<?= e($settings['primary_color'] ?? '') ?>" oninput="if(/^#[0-9a-fA-F]{6}$/.test(this.value))this.previousElementSibling.value=this.value" class="<?= admin_input_class('!mt-0') ?>"></div></div><div><label class="<?= admin_label_class() ?>">Secondary Color</label><div class="mt-1.5 flex gap-2"><input type="color" aria-label="Pilih warna" value="<?= e(preg_match('/^#[0-9a-fA-F]{6}$/', (string) ($settings['secondary_color'] ?? '')) ? $settings['secondary_color'] : '#f97316') ?>" oninput="this.nextElementSibling.value=this.value" class="h-[42px] w-12 shrink-0 cursor-pointer rounded-xl border border-slate-300 bg-white p-1"><input name="secondary_color" maxlength="20" placeholder="#f97316" value="<?= e($settings['secondary_color'] ?? '') ?>" oninput="if(/^#[0-9a-fA-F]{6}$/.test(this.value))this.previousElementSibling.value=this.value" class="<?= admin_input_class('!mt-0') ?>"></div></div></div>
                <div class="grid gap-4 sm:grid-cols-2"><div><label class="<?= admin_label_class() ?>">Button Style</label><select name="button_style" class="<?= admin_input_class() ?>"><?php foreach (['rounded' => 'Rounded', 'square' => 'Square', 'pill' => 'Pill'] as $value => $label): ?><option value="<?= e($value) ?>"<?= (($settings['button_style'] ?? 'rounded') === $value) ? ' selected' : '' ?>><?= e($label) ?></option><?php endforeach; ?></select></div><div><label class="<?= admin_label_class() ?>">Mode Katalog</label><select name="catalog_mode" class="<?= admin_input_class() ?>"><?php foreach (['products' => 'Produk', 'services' => 'Layanan'] as $value => $label): ?><option value="<?= e($value) ?>"<?= (($settings['catalog_mode'] ?? 'products') === $value) ? ' selected' : '' ?>><?= e($label) ?></option><?php endforeach; ?></select></div></div>
                <div><label class="<?= admin_label_class() ?>">Meta Title</label><input name="site_meta_title" maxlength="150" value="<?= e($settings['site_meta_title'] ?? '') ?>" class="<?= admin_input_class() ?>"></div>
                <div><label class="<?= admin_label_class() ?>">Meta Description</label><textarea name="site_meta_description" rows="3" maxlength="255" class="<?= admin_input_class() ?>"><?= e($settings['site_meta_description'] ?? '') ?></textarea></div>
                <div class="flex flex-wrap gap-3"><button type="submit" class="<?= admin_primary_button() ?>">Simpan Settings</button><button type="submit" name="preview_after_save" value="1" class="<?= admin_secondary_button() ?>">Simpan & Preview</button></div>
            </form>
        </section>
    </aside>
</div>
<section id="social" class="mt-6 <?= admin_card() ?>">
    <div class="mb-6"><h2 class="text-lg font-extrabold tracking-tight text-slate-950">Social Links</h2><p class="mt-2 text-sm text-slate-500">Tambahkan Instagram, marketplace, TikTok, Facebook, atau link lain.</p></div>
    <form method="post" action="<?= e(url('/admin/business.php')) ?>" class="grid gap-4 lg:grid-cols-6" novalidate>
        <?= Csrf::field($csrfKey) ?><input type="hidden" name="action" value="save_social"><input type="hidden" name="id" value="<?= e((string) ($editSocial['id'] ?? '')) ?>">
        <div><label class="<?= admin_label_class() ?>">Platform</label><select name="platform" class="<?= admin_input_class() ?>"><?php foreach (social_platform_options() as $value => $label): ?><option value="<?= e($value) ?>"<?= (($editSocial['platform'] ?? '') === $value) ? ' selected' : '' ?>><?= e($label) ?></option><?php endforeach; ?></select></div>
        <div><label class="<?= admin_label_class() ?>">Label</label><input name="label" maxlength="100" value="<?= e($editSocial['label'] ?? '') ?>" class="<?= admin_input_class() ?>"></div>
        <div class="lg:col-span-2"><label class="<?= admin_label_class() ?>">URL</label><input name="url" type="url" required maxlength="500" value="<?= e($editSocial['url'] ?? '') ?>" class="<?= admin_input_class() ?>"></div>
        <div><label class="<?= admin_label_class() ?>">Sort</label><input name="sort_order" type="number" value="<?= e((string) ($editSocial['sort_order'] ?? 0)) ?>" class="<?= admin_input_class() ?>"></div>
        <div class="flex items-end"><label class="flex w-full items-center gap-3 rounded-2xl border border-slate-200 px-4 py-3 text-sm font-bold text-slate-700"><input type="checkbox" name="is_active" value="1" class="h-4 w-4 rounded"<?= ((int) ($editSocial['is_active'] ?? 1) === 1) ? ' checked' : '' ?>> Aktif</label></div>
        <div class="lg:col-span-6 flex flex-wrap gap-3"><button type="submit" class="<?= admin_primary_button() ?>"><?= $editSocial ? 'Update Social Link' : 'Tambah Social Link' ?></button><?php if ($editSocial): ?><a href="<?= e(url('/admin/business.php#social')) ?>" class="<?= admin_secondary_button() ?>">Batal edit</a><?php endif; ?></div>
    </form>
    <div class="mt-6 overflow-hidden rounded-3xl border border-slate-200">
    <?php if ($socialLinks === []): ?><?php admin_empty_state('Belum ada social link', 'Tambahkan link agar pengunjung bisa menemukan kanal bisnis Anda.'); ?><?php else: ?>
        <div class="overflow-x-auto"><table class="min-w-full divide-y divide-slate-200 text-sm"><thead class="bg-slate-50 text-left text-xs font-black uppercase tracking-wider text-slate-500"><tr><th class="px-4 py-3">Platform</th><th class="px-4 py-3">Label</th><th class="px-4 py-3">URL</th><th class="px-4 py-3">Sort</th><th class="px-4 py-3">Status</th><th class="px-4 py-3">Aksi</th></tr></thead><tbody class="divide-y divide-slate-100 bg-white"><?php foreach ($socialLinks as $link): ?><tr><td class="px-4 py-3 font-bold"><?= e($link['platform']) ?></td><td class="px-4 py-3"><?= e($link['label'] ?? '') ?></td><td class="px-4 py-3"><a href="<?= e($link['url']) ?>" rel="noopener noreferrer" target="_blank" class="text-brand-700 hover:underline"><?= e($link['url']) ?></a></td><td class="px-4 py-3"><?= e((string) $link['sort_order']) ?></td><td class="px-4 py-3"><?= admin_status_badge((int) $link['is_active'] === 1) ?></td><td class="px-4 py-3"><div class="flex flex-wrap gap-2"><a href="<?= e(url('/admin/business.php?edit_social=' . (int) $link['id'] . '#social')) ?>" class="<?= admin_secondary_button('!px-3 !py-2 !text-xs') ?>">Edit</a><form method="post" action="<?= e(url('/admin/business.php')) ?>"><?= Csrf::field($csrfKey) ?><input type="hidden" name="action" value="deactivate_social"><input type="hidden" name="id" value="<?= e((string) $link['id']) ?>"><button type="submit" class="<?= admin_secondary_button('!px-3 !py-2 !text-xs') ?>">Nonaktifkan</button></form><form method="post" action="<?= e(url('/admin/business.php')) ?>" onsubmit="return confirm('Hapus social link ini?')"><?= Csrf::field($csrfKey) ?><input type="hidden" name="action" value="delete_social"><input type="hidden" name="id" value="<?= e((string) $link['id']) ?>"><button type="submit" class="<?= admin_danger_button() ?>">Hapus</button></form></div></td></tr><?php endforeach; ?></tbody></table></div>
    <?php endif; ?>
    </div>
</section>
<?php admin_layout_end(); ?>
