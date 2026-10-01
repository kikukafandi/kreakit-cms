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
    $action = (string) ($_POST['action'] ?? 'save');

    try {
        if ($action === 'save') {
            $id = id_input('id');
            $categoryId = id_input('category_id');
            $categoryId = $categoryId > 0 ? $categoryId : null;
            $name = sanitize_text((string) ($_POST['name'] ?? ''), 150);
            $slug = slug_input_or_name(sanitize_text((string) ($_POST['slug'] ?? ''), 170), $name, 170);
            $shortDescription = sanitize_text_or_null((string) ($_POST['short_description'] ?? ''), 255);
            $description = sanitize_multiline_or_null((string) ($_POST['description'] ?? ''), 5000);
            $priceRaw = trim((string) ($_POST['price'] ?? ''));
            $price = nullable_decimal_input('price');
            $priceLabel = sanitize_text_or_null((string) ($_POST['price_label'] ?? ''), 100);
            $imagePath = validate_local_upload_path((string) ($_POST['existing_image_path'] ?? ''));
            $manualImagePath = validate_local_upload_path((string) ($_POST['image_path'] ?? ''));
            if ($manualImagePath !== null) {
                $imagePath = $manualImagePath;
            }
            try {
                $imageUpload = secure_image_upload($_FILES['image_file'] ?? null, $database);
                if ($imageUpload !== null) {
                    $imagePath = $imageUpload['path'];
                }
            } catch (RuntimeException $uploadError) {
                $errors[] = $uploadError->getMessage();
            }
            $whatsappMessage = sanitize_text_or_null((string) ($_POST['whatsapp_message'] ?? ''), 255);
            $sortOrder = int_input('sort_order');
            $isFeatured = checkbox_bool('is_featured');
            $isActive = checkbox_bool('is_active');

            if ($name === '') {
                $errors[] = 'Nama produk/layanan wajib diisi.';
            }
            if ($slug === '') {
                $errors[] = 'Slug produk/layanan tidak valid.';
            }
            if ($slug !== '' && !ensure_unique_slug($database, 'items', $slug, $id > 0 ? $id : null)) {
                $errors[] = 'Slug produk/layanan sudah dipakai. Gunakan slug lain.';
            }
            if ($priceRaw !== '' && $price === null) {
                $errors[] = 'Harga harus angka positif dengan maksimal 2 desimal.';
            }
            if ($categoryId !== null && $database->selectOne('SELECT id FROM categories WHERE id = :id LIMIT 1', ['id' => $categoryId]) === null) {
                $errors[] = 'Kategori tidak ditemukan.';
            }
            if ($imagePath !== null && validate_local_upload_path($imagePath) === null) {
                $errors[] = 'Image path harus berasal dari folder uploads/YYYY/MM dan format jpg/png/webp.';
            }

            if ($errors === []) {
                if ($id > 0) {
                    $database->execute(
                        'UPDATE items SET category_id = :category_id, name = :name, slug = :slug, short_description = :short_description, description = :description, price = :price, price_label = :price_label, image_path = :image_path, whatsapp_message = :whatsapp_message, sort_order = :sort_order, is_featured = :is_featured, is_active = :is_active WHERE id = :id',
                        [
                            'category_id' => $categoryId,
                            'name' => $name,
                            'slug' => $slug,
                            'short_description' => $shortDescription,
                            'description' => $description,
                            'price' => $price,
                            'price_label' => $priceLabel,
                            'image_path' => $imagePath,
                            'whatsapp_message' => $whatsappMessage,
                            'sort_order' => $sortOrder,
                            'is_featured' => $isFeatured,
                            'is_active' => $isActive,
                            'id' => $id,
                        ]
                    );
                    Session::flash('success', 'Produk/layanan berhasil diperbarui.');
                } else {
                    $database->execute(
                        'INSERT INTO items (category_id, name, slug, short_description, description, price, price_label, image_path, whatsapp_message, sort_order, is_featured, is_active) VALUES (:category_id, :name, :slug, :short_description, :description, :price, :price_label, :image_path, :whatsapp_message, :sort_order, :is_featured, :is_active)',
                        [
                            'category_id' => $categoryId,
                            'name' => $name,
                            'slug' => $slug,
                            'short_description' => $shortDescription,
                            'description' => $description,
                            'price' => $price,
                            'price_label' => $priceLabel,
                            'image_path' => $imagePath,
                            'whatsapp_message' => $whatsappMessage,
                            'sort_order' => $sortOrder,
                            'is_featured' => $isFeatured,
                            'is_active' => $isActive,
                        ]
                    );
                    Session::flash('success', 'Produk/layanan berhasil ditambahkan.');
                }
                redirect(isset($_POST['preview_after_save']) ? url('/') : url('/admin/items.php'));
            }
        } elseif ($action === 'deactivate') {
            $id = id_input('id');
            if ($id <= 0) {
                $errors[] = 'Produk/layanan tidak valid.';
            } else {
                $database->execute('UPDATE items SET is_active = 0 WHERE id = :id', ['id' => $id]);
                Session::flash('success', 'Produk/layanan berhasil dinonaktifkan.');
                redirect(url('/admin/items.php'));
            }
        } elseif ($action === 'delete') {
            $id = id_input('id');
            if ($id <= 0) {
                $errors[] = 'Produk/layanan tidak valid.';
            } else {
                $database->execute('DELETE FROM items WHERE id = :id', ['id' => $id]);
                Session::flash('success', 'Produk/layanan berhasil dihapus.');
                redirect(url('/admin/items.php'));
            }
        }
    } catch (Throwable) {
        $errors[] = 'Produk/layanan tidak dapat disimpan. Periksa slug unik dan input.';
    }

    if ($errors !== []) {
        admin_flash_errors($errors);
        redirect(url('/admin/items.php'));
    }
}

$editId = filter_input(INPUT_GET, 'edit', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
$editItem = null;
if (is_int($editId)) {
    $editItem = $database->selectOne('SELECT * FROM items WHERE id = :id LIMIT 1', ['id' => $editId]);
}
$categories = $database->select('SELECT id, name FROM categories ORDER BY sort_order ASC, name ASC');
$items = $database->select('SELECT i.*, c.name AS category_name FROM items i LEFT JOIN categories c ON c.id = i.category_id ORDER BY i.sort_order ASC, i.name ASC');
$success = Session::flash('success');
$error = Session::flash('error');
?>
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Produk/Layanan — <?= e(app_config('app.name', 'KreaKit CMS')) ?></title>
</head>
<body>
<main>
    <h1>Produk/Layanan</h1>
    <p><a href="<?= e(url('/admin/dashboard.php')) ?>">Dashboard</a> · <a href="<?= e(url('/admin/business.php')) ?>">Profil Bisnis</a> · <a href="<?= e(url('/admin/categories.php')) ?>">Kategori</a> · <a href="<?= e(url('/admin/templates.php')) ?>">Template</a> · <a href="<?= e(url('/')) ?>" target="_blank" rel="noopener">Preview Website</a></p>
    <?php if ($success): ?><p role="status"><?= e($success) ?></p><?php endif; ?>
    <?php if ($error): ?><p role="alert"><?= e($error) ?></p><?php endif; ?>

    <section>
        <h2><?= $editItem ? 'Edit Produk/Layanan' : 'Tambah Produk/Layanan' ?></h2>
        <form method="post" action="<?= e(url('/admin/items.php')) ?>" enctype="multipart/form-data" novalidate>
            <?= Csrf::field($csrfKey) ?>
            <input type="hidden" name="action" value="save">
            <input type="hidden" name="id" value="<?= e((string) ($editItem['id'] ?? '')) ?>">
            <p><label>Kategori<br><select name="category_id">
                <option value="">Tanpa kategori</option>
                <?php foreach ($categories as $category): ?>
                    <option value="<?= e((string) $category['id']) ?>"<?= ((int) ($editItem['category_id'] ?? 0) === (int) $category['id']) ? ' selected' : '' ?>><?= e($category['name']) ?></option>
                <?php endforeach; ?>
            </select></label></p>
            <p><label>Nama<br><input name="name" required maxlength="150" value="<?= e($editItem['name'] ?? '') ?>"></label></p>
            <p><label>Slug<br><input name="slug" maxlength="170" placeholder="otomatis dari nama jika kosong" value="<?= e($editItem['slug'] ?? '') ?>"></label></p>
            <p><label>Deskripsi Singkat<br><input name="short_description" maxlength="255" value="<?= e($editItem['short_description'] ?? '') ?>"></label></p>
            <p><label>Deskripsi<br><textarea name="description" rows="4"><?= e($editItem['description'] ?? '') ?></textarea></label></p>
            <p><label>Harga Angka<br><input name="price" inputmode="decimal" placeholder="28000.00" value="<?= e($editItem['price'] ?? '') ?>"></label></p>
            <p><label>Label Harga<br><input name="price_label" maxlength="100" placeholder="Mulai Rp50.000" value="<?= e($editItem['price_label'] ?? '') ?>"></label></p>
            <input type="hidden" name="existing_image_path" value="<?= e($editItem['image_path'] ?? '') ?>">
            <p><label>Upload Gambar (jpg/png/webp, maks 2 MB)<br><input name="image_file" type="file" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp"></label><br><?php if (!empty($editItem['image_path'])): ?>Saat ini: <?= e($editItem['image_path']) ?><?php endif; ?></p>
            <p><label>Image Path Upload<br><input name="image_path" maxlength="255" placeholder="uploads/2026/10/namaunik.webp" value="<?= e($editItem['image_path'] ?? '') ?>"></label></p>
            <p><label>Pesan WhatsApp Opsional<br><input name="whatsapp_message" maxlength="255" value="<?= e($editItem['whatsapp_message'] ?? '') ?>"></label></p>
            <p><label>Sort Order<br><input name="sort_order" type="number" value="<?= e((string) ($editItem['sort_order'] ?? 0)) ?>"></label></p>
            <p><label><input type="checkbox" name="is_featured" value="1"<?= ((int) ($editItem['is_featured'] ?? 0) === 1) ? ' checked' : '' ?>> Featured</label></p>
            <p><label><input type="checkbox" name="is_active" value="1"<?= ((int) ($editItem['is_active'] ?? 1) === 1) ? ' checked' : '' ?>> Aktif</label></p>
            <button type="submit"><?= $editItem ? 'Update Produk/Layanan' : 'Tambah Produk/Layanan' ?></button>
            <button type="submit" name="preview_after_save" value="1">Simpan & Preview</button>
            <?php if ($editItem): ?><a href="<?= e(url('/admin/items.php')) ?>">Batal edit</a><?php endif; ?>
        </form>
    </section>

    <section>
        <h2>Daftar Produk/Layanan</h2>
        <?php if ($items === []): ?>
            <p>Belum ada produk/layanan.</p>
        <?php else: ?>
            <table border="1" cellpadding="6">
                <thead><tr><th>Nama</th><th>Slug</th><th>Kategori</th><th>Harga</th><th>Featured</th><th>Status</th><th>Sort</th><th>Aksi</th></tr></thead>
                <tbody>
                <?php foreach ($items as $item): ?>
                    <tr>
                        <td><?= e($item['name']) ?></td>
                        <td><?= e($item['slug']) ?></td>
                        <td><?= e($item['category_name'] ?? 'Tanpa kategori') ?></td>
                        <td><?= e($item['price_label'] ?: rupiah($item['price'])) ?></td>
                        <td><?= ((int) $item['is_featured'] === 1) ? 'Ya' : 'Tidak' ?></td>
                        <td><?= ((int) $item['is_active'] === 1) ? 'Aktif' : 'Nonaktif' ?></td>
                        <td><?= e((string) $item['sort_order']) ?></td>
                        <td>
                            <a href="<?= e(url('/admin/items.php?edit=' . (int) $item['id'])) ?>">Edit</a>
                            <form method="post" action="<?= e(url('/admin/items.php')) ?>" style="display:inline">
                                <?= Csrf::field($csrfKey) ?><input type="hidden" name="action" value="deactivate"><input type="hidden" name="id" value="<?= e((string) $item['id']) ?>"><button type="submit">Nonaktifkan</button>
                            </form>
                            <form method="post" action="<?= e(url('/admin/items.php')) ?>" style="display:inline" onsubmit="return confirm('Hapus produk/layanan ini?')">
                                <?= Csrf::field($csrfKey) ?><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?= e((string) $item['id']) ?>"><button type="submit">Hapus</button>
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
