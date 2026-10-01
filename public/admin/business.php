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
                        'INSERT INTO business_profiles (business_name, tagline, description, whatsapp_number, address, maps_url, email, phone) VALUES (:business_name, :tagline, :description, :whatsapp_number, :address, :maps_url, :email, :phone)',
                        [
                            'business_name' => $businessName,
                            'tagline' => $tagline,
                            'description' => $description,
                            'whatsapp_number' => $whatsapp,
                            'address' => $address,
                            'maps_url' => $mapsUrl,
                            'email' => $email,
                            'phone' => $phone,
                        ]
                    );
                } else {
                    $database->execute(
                        'UPDATE business_profiles SET business_name = :business_name, tagline = :tagline, description = :description, whatsapp_number = :whatsapp_number, address = :address, maps_url = :maps_url, email = :email, phone = :phone WHERE id = :id',
                        [
                            'business_name' => $businessName,
                            'tagline' => $tagline,
                            'description' => $description,
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
                redirect(url('/admin/business.php'));
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
                redirect(url('/admin/business.php#settings'));
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
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Profil Bisnis — <?= e(app_config('app.name', 'KreaKit CMS')) ?></title>
</head>
<body>
<main>
    <h1>Profil Bisnis</h1>
    <p><a href="<?= e(url('/admin/dashboard.php')) ?>">Dashboard</a> · <a href="<?= e(url('/admin/categories.php')) ?>">Kategori</a> · <a href="<?= e(url('/admin/items.php')) ?>">Produk/Layanan</a></p>
    <?php if ($success): ?><p role="status"><?= e($success) ?></p><?php endif; ?>
    <?php if ($error): ?><p role="alert"><?= e($error) ?></p><?php endif; ?>

    <form method="post" action="<?= e(url('/admin/business.php')) ?>" novalidate>
        <?= Csrf::field($csrfKey) ?>
        <input type="hidden" name="action" value="save_business">
        <p><label>Nama Bisnis<br><input name="business_name" required maxlength="150" value="<?= e($business['business_name'] ?? '') ?>"></label></p>
        <p><label>Tagline<br><input name="tagline" maxlength="190" value="<?= e($business['tagline'] ?? '') ?>"></label></p>
        <p><label>Deskripsi<br><textarea name="description" rows="5"><?= e($business['description'] ?? '') ?></textarea></label></p>
        <p><label>WhatsApp<br><input name="whatsapp_number" maxlength="30" placeholder="6281234567890" value="<?= e($business['whatsapp_number'] ?? '') ?>"></label></p>
        <p><label>Alamat<br><textarea name="address" rows="3"><?= e($business['address'] ?? '') ?></textarea></label></p>
        <p><label>URL Maps<br><input name="maps_url" type="url" maxlength="500" placeholder="https://maps.google.com/..." value="<?= e($business['maps_url'] ?? '') ?>"></label></p>
        <p><label>Email<br><input name="email" type="email" maxlength="190" value="<?= e($business['email'] ?? '') ?>"></label></p>
        <p><label>Phone<br><input name="phone" maxlength="50" value="<?= e($business['phone'] ?? '') ?>"></label></p>
        <button type="submit">Simpan Profil</button>
    </form>

    <section id="settings">
        <h2>Settings Dasar</h2>
        <form method="post" action="<?= e(url('/admin/business.php')) ?>" novalidate>
            <?= Csrf::field($csrfKey) ?>
            <input type="hidden" name="action" value="save_settings">
            <p><label>Primary Color<br><input name="primary_color" maxlength="20" placeholder="#0f766e" value="<?= e($settings['primary_color'] ?? '') ?>"></label></p>
            <p><label>Secondary Color<br><input name="secondary_color" maxlength="20" placeholder="#f97316" value="<?= e($settings['secondary_color'] ?? '') ?>"></label></p>
            <p><label>Button Style<br><select name="button_style">
                <?php foreach (['rounded' => 'Rounded', 'square' => 'Square', 'pill' => 'Pill'] as $value => $label): ?>
                    <option value="<?= e($value) ?>"<?= (($settings['button_style'] ?? 'rounded') === $value) ? ' selected' : '' ?>><?= e($label) ?></option>
                <?php endforeach; ?>
            </select></label></p>
            <p><label>Mode Katalog<br><select name="catalog_mode">
                <?php foreach (['products' => 'Produk', 'services' => 'Layanan'] as $value => $label): ?>
                    <option value="<?= e($value) ?>"<?= (($settings['catalog_mode'] ?? 'products') === $value) ? ' selected' : '' ?>><?= e($label) ?></option>
                <?php endforeach; ?>
            </select></label></p>
            <p><label>Meta Title<br><input name="site_meta_title" maxlength="150" value="<?= e($settings['site_meta_title'] ?? '') ?>"></label></p>
            <p><label>Meta Description<br><textarea name="site_meta_description" rows="3" maxlength="255"><?= e($settings['site_meta_description'] ?? '') ?></textarea></label></p>
            <button type="submit">Simpan Settings</button>
        </form>
    </section>

    <section id="social">
        <h2>Social Links</h2>
        <form method="post" action="<?= e(url('/admin/business.php')) ?>" novalidate>
            <?= Csrf::field($csrfKey) ?>
            <input type="hidden" name="action" value="save_social">
            <input type="hidden" name="id" value="<?= e((string) ($editSocial['id'] ?? '')) ?>">
            <p><label>Platform<br><select name="platform">
                <?php foreach (social_platform_options() as $value => $label): ?>
                    <option value="<?= e($value) ?>"<?= (($editSocial['platform'] ?? '') === $value) ? ' selected' : '' ?>><?= e($label) ?></option>
                <?php endforeach; ?>
            </select></label></p>
            <p><label>Label<br><input name="label" maxlength="100" value="<?= e($editSocial['label'] ?? '') ?>"></label></p>
            <p><label>URL<br><input name="url" type="url" required maxlength="500" value="<?= e($editSocial['url'] ?? '') ?>"></label></p>
            <p><label>Sort Order<br><input name="sort_order" type="number" value="<?= e((string) ($editSocial['sort_order'] ?? 0)) ?>"></label></p>
            <p><label><input type="checkbox" name="is_active" value="1"<?= ((int) ($editSocial['is_active'] ?? 1) === 1) ? ' checked' : '' ?>> Aktif</label></p>
            <button type="submit"><?= $editSocial ? 'Update Social Link' : 'Tambah Social Link' ?></button>
            <?php if ($editSocial): ?><a href="<?= e(url('/admin/business.php#social')) ?>">Batal edit</a><?php endif; ?>
        </form>

        <?php if ($socialLinks === []): ?>
            <p>Belum ada social link.</p>
        <?php else: ?>
            <table border="1" cellpadding="6">
                <thead><tr><th>Platform</th><th>Label</th><th>URL</th><th>Sort</th><th>Status</th><th>Aksi</th></tr></thead>
                <tbody>
                <?php foreach ($socialLinks as $link): ?>
                    <tr>
                        <td><?= e($link['platform']) ?></td>
                        <td><?= e($link['label'] ?? '') ?></td>
                        <td><a href="<?= e($link['url']) ?>" rel="noopener noreferrer" target="_blank"><?= e($link['url']) ?></a></td>
                        <td><?= e((string) $link['sort_order']) ?></td>
                        <td><?= ((int) $link['is_active'] === 1) ? 'Aktif' : 'Nonaktif' ?></td>
                        <td>
                            <a href="<?= e(url('/admin/business.php?edit_social=' . (int) $link['id'] . '#social')) ?>">Edit</a>
                            <form method="post" action="<?= e(url('/admin/business.php')) ?>" style="display:inline">
                                <?= Csrf::field($csrfKey) ?><input type="hidden" name="action" value="deactivate_social"><input type="hidden" name="id" value="<?= e((string) $link['id']) ?>"><button type="submit">Nonaktifkan</button>
                            </form>
                            <form method="post" action="<?= e(url('/admin/business.php')) ?>" style="display:inline" onsubmit="return confirm('Hapus social link ini?')">
                                <?= Csrf::field($csrfKey) ?><input type="hidden" name="action" value="delete_social"><input type="hidden" name="id" value="<?= e((string) $link['id']) ?>"><button type="submit">Hapus</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </section>
</main>
</body>
</html>
