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
<?php admin_layout_start('Produk/Layanan', 'items', 'Kelola katalog yang akan dilihat pelanggan, lengkap dengan harga, foto, dan CTA WhatsApp.'); ?>
<?php admin_flash_block($success ?: null, $error ?: null); ?>
<section class="<?= admin_card() ?>">
    <h2 class="text-2xl font-black text-slate-950"><?= $editItem ? 'Edit Produk/Layanan' : 'Tambah Produk/Layanan' ?></h2>
    <p class="mt-2 text-sm text-slate-500">Isi nama dan deskripsi yang mudah dimengerti calon pelanggan.</p>
    <form method="post" action="<?= e(url('/admin/items.php')) ?>" enctype="multipart/form-data" class="mt-6 grid gap-5" novalidate>
        <?= Csrf::field($csrfKey) ?><input type="hidden" name="action" value="save"><input type="hidden" name="id" value="<?= e((string) ($editItem['id'] ?? '')) ?>">
        <div class="grid gap-5 lg:grid-cols-3"><div><label class="<?= admin_label_class() ?>">Kategori</label><select name="category_id" class="<?= admin_input_class() ?>"><option value="">Tanpa kategori</option><?php foreach ($categories as $category): ?><option value="<?= e((string) $category['id']) ?>"<?= ((int) ($editItem['category_id'] ?? 0) === (int) $category['id']) ? ' selected' : '' ?>><?= e($category['name']) ?></option><?php endforeach; ?></select></div><div><label class="<?= admin_label_class() ?>">Nama</label><input name="name" required maxlength="150" value="<?= e($editItem['name'] ?? '') ?>" class="<?= admin_input_class() ?>" placeholder="Contoh: Paket Nasi Box"></div><div><label class="<?= admin_label_class() ?>">Slug</label><input name="slug" maxlength="170" placeholder="otomatis dari nama jika kosong" value="<?= e($editItem['slug'] ?? '') ?>" class="<?= admin_input_class() ?>"></div></div>
        <div><label class="<?= admin_label_class() ?>">Deskripsi Singkat</label><input name="short_description" maxlength="255" value="<?= e($editItem['short_description'] ?? '') ?>" class="<?= admin_input_class() ?>" placeholder="Ringkasan 1 kalimat untuk kartu katalog"></div>
        <div><label class="<?= admin_label_class() ?>">Deskripsi Lengkap</label><textarea name="description" rows="4" class="<?= admin_input_class() ?>"><?= e($editItem['description'] ?? '') ?></textarea></div>
        <div class="grid gap-5 md:grid-cols-2 lg:grid-cols-4"><div><label class="<?= admin_label_class() ?>">Harga Angka</label><input name="price" inputmode="decimal" placeholder="28000.00" value="<?= e($editItem['price'] ?? '') ?>" class="<?= admin_input_class() ?>"></div><div><label class="<?= admin_label_class() ?>">Label Harga</label><input name="price_label" maxlength="100" placeholder="Mulai Rp50.000" value="<?= e($editItem['price_label'] ?? '') ?>" class="<?= admin_input_class() ?>"></div><div><label class="<?= admin_label_class() ?>">Sort Order</label><input name="sort_order" type="number" value="<?= e((string) ($editItem['sort_order'] ?? 0)) ?>" class="<?= admin_input_class() ?>"></div><div><label class="<?= admin_label_class() ?>">Pesan WhatsApp</label><input name="whatsapp_message" maxlength="255" value="<?= e($editItem['whatsapp_message'] ?? '') ?>" class="<?= admin_input_class() ?>"></div></div>
        <input type="hidden" name="existing_image_path" value="<?= e($editItem['image_path'] ?? '') ?>">
        <div class="grid gap-5 md:grid-cols-2"><div><label class="<?= admin_label_class() ?>">Upload Gambar</label><input name="image_file" type="file" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp" class="<?= admin_input_class() ?>"><p class="<?= admin_help_class() ?>">jpg/png/webp, maks 2 MB. <?php if (!empty($editItem['image_path'])): ?>Saat ini: <?= e($editItem['image_path']) ?><?php endif; ?></p></div><div><label class="<?= admin_label_class() ?>">Image Path Upload</label><input name="image_path" maxlength="255" placeholder="uploads/2026/10/namaunik.webp" value="<?= e($editItem['image_path'] ?? '') ?>" class="<?= admin_input_class() ?>"><p class="<?= admin_help_class() ?>">Opsional untuk path upload yang sudah ada.</p></div></div>
        <div class="flex flex-wrap gap-4"><label class="flex items-center gap-3 rounded-2xl border border-slate-200 px-4 py-3 text-sm font-bold text-slate-700"><input type="checkbox" name="is_featured" value="1" class="h-4 w-4 rounded"<?= ((int) ($editItem['is_featured'] ?? 0) === 1) ? ' checked' : '' ?>> Featured</label><label class="flex items-center gap-3 rounded-2xl border border-slate-200 px-4 py-3 text-sm font-bold text-slate-700"><input type="checkbox" name="is_active" value="1" class="h-4 w-4 rounded"<?= ((int) ($editItem['is_active'] ?? 1) === 1) ? ' checked' : '' ?>> Aktif</label></div>
        <div class="flex flex-wrap gap-3"><button type="submit" class="<?= admin_primary_button() ?>"><?= $editItem ? 'Update Produk/Layanan' : 'Tambah Produk/Layanan' ?></button><button type="submit" name="preview_after_save" value="1" class="<?= admin_secondary_button() ?>">Simpan & Preview</button><?php if ($editItem): ?><a href="<?= e(url('/admin/items.php')) ?>" class="<?= admin_secondary_button() ?>">Batal edit</a><?php endif; ?></div>
    </form>
</section>
<section class="mt-6 <?= admin_card() ?>">
    <h2 class="text-2xl font-black text-slate-950">Daftar Produk/Layanan</h2>
    <div class="mt-6 overflow-hidden rounded-3xl border border-slate-200">
    <?php if ($items === []): ?><?php admin_empty_state('Belum ada produk/layanan', 'Tambahkan item pertama untuk mengisi katalog publik.'); ?><?php else: ?>
        <div class="overflow-x-auto"><table class="min-w-full divide-y divide-slate-200 text-sm"><thead class="bg-slate-50 text-left text-xs font-black uppercase tracking-wider text-slate-500"><tr><th class="px-4 py-3">Nama</th><th class="px-4 py-3">Kategori</th><th class="px-4 py-3">Harga</th><th class="px-4 py-3">Featured</th><th class="px-4 py-3">Status</th><th class="px-4 py-3">Sort</th><th class="px-4 py-3">Aksi</th></tr></thead><tbody class="divide-y divide-slate-100 bg-white"><?php foreach ($items as $item): ?><tr><td class="px-4 py-3"><p class="font-bold text-slate-950"><?= e($item['name']) ?></p><p class="text-xs text-slate-500"><?= e($item['slug']) ?></p></td><td class="px-4 py-3"><?= e($item['category_name'] ?? 'Tanpa kategori') ?></td><td class="px-4 py-3 font-semibold"><?= e($item['price_label'] ?: rupiah($item['price'])) ?></td><td class="px-4 py-3"><?= admin_status_badge((int) $item['is_featured'] === 1, 'Ya', 'Tidak') ?></td><td class="px-4 py-3"><?= admin_status_badge((int) $item['is_active'] === 1) ?></td><td class="px-4 py-3"><?= e((string) $item['sort_order']) ?></td><td class="px-4 py-3"><div class="flex flex-wrap gap-2"><a href="<?= e(url('/admin/items.php?edit=' . (int) $item['id'])) ?>" class="<?= admin_secondary_button('!px-3 !py-2 !text-xs') ?>">Edit</a><form method="post" action="<?= e(url('/admin/items.php')) ?>"><?= Csrf::field($csrfKey) ?><input type="hidden" name="action" value="deactivate"><input type="hidden" name="id" value="<?= e((string) $item['id']) ?>"><button type="submit" class="<?= admin_secondary_button('!px-3 !py-2 !text-xs') ?>">Nonaktifkan</button></form><form method="post" action="<?= e(url('/admin/items.php')) ?>" onsubmit="return confirm('Hapus produk/layanan ini?')"><?= Csrf::field($csrfKey) ?><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?= e((string) $item['id']) ?>"><button type="submit" class="<?= admin_danger_button() ?>">Hapus</button></form></div></td></tr><?php endforeach; ?></tbody></table></div>
    <?php endif; ?>
    </div>
</section>
<?php admin_layout_end(); ?>
