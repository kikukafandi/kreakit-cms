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
            $name = sanitize_text((string) ($_POST['name'] ?? ''), 100);
            $slug = slug_input_or_name(sanitize_text((string) ($_POST['slug'] ?? ''), 120), $name, 120);
            $description = sanitize_multiline_or_null((string) ($_POST['description'] ?? ''), 2000);
            $sortOrder = int_input('sort_order');
            $isActive = checkbox_bool('is_active');

            if ($name === '') {
                $errors[] = 'Nama kategori wajib diisi.';
            }
            if ($slug === '') {
                $errors[] = 'Slug kategori tidak valid.';
            }
            if ($slug !== '' && !ensure_unique_slug($database, 'categories', $slug, $id > 0 ? $id : null)) {
                $errors[] = 'Slug kategori sudah dipakai. Gunakan slug lain.';
            }

            if ($errors === []) {
                if ($id > 0) {
                    $database->execute(
                        'UPDATE categories SET name = :name, slug = :slug, description = :description, sort_order = :sort_order, is_active = :is_active WHERE id = :id',
                        ['name' => $name, 'slug' => $slug, 'description' => $description, 'sort_order' => $sortOrder, 'is_active' => $isActive, 'id' => $id]
                    );
                    Session::flash('success', 'Kategori berhasil diperbarui.');
                } else {
                    $database->execute(
                        'INSERT INTO categories (name, slug, description, sort_order, is_active) VALUES (:name, :slug, :description, :sort_order, :is_active)',
                        ['name' => $name, 'slug' => $slug, 'description' => $description, 'sort_order' => $sortOrder, 'is_active' => $isActive]
                    );
                    Session::flash('success', 'Kategori berhasil ditambahkan.');
                }
                redirect(url('/admin/categories.php'));
            }
        } elseif ($action === 'deactivate') {
            $id = id_input('id');
            if ($id <= 0) {
                $errors[] = 'Kategori tidak valid.';
            } else {
                $database->execute('UPDATE categories SET is_active = 0 WHERE id = :id', ['id' => $id]);
                Session::flash('success', 'Kategori berhasil dinonaktifkan.');
                redirect(url('/admin/categories.php'));
            }
        } elseif ($action === 'delete') {
            $id = id_input('id');
            if ($id <= 0) {
                $errors[] = 'Kategori tidak valid.';
            } else {
                $database->execute('DELETE FROM categories WHERE id = :id', ['id' => $id]);
                Session::flash('success', 'Kategori berhasil dihapus. Produk terkait menjadi tanpa kategori.');
                redirect(url('/admin/categories.php'));
            }
        }
    } catch (Throwable) {
        $errors[] = 'Kategori tidak dapat disimpan. Periksa slug unik dan data terkait.';
    }

    if ($errors !== []) {
        admin_flash_errors($errors);
        redirect(url('/admin/categories.php'));
    }
}

$editId = filter_input(INPUT_GET, 'edit', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
$editCategory = null;
if (is_int($editId)) {
    $editCategory = $database->selectOne('SELECT * FROM categories WHERE id = :id LIMIT 1', ['id' => $editId]);
}
$categories = $database->select('SELECT c.*, (SELECT COUNT(*) FROM items i WHERE i.category_id = c.id) AS item_count FROM categories c ORDER BY c.sort_order ASC, c.name ASC');
$success = Session::flash('success');
$error = Session::flash('error');
?>
<?php admin_layout_start('Kategori', 'categories', 'Buat kelompok produk atau layanan agar katalog lebih rapi dan mudah dipahami pelanggan.'); ?>
<?php admin_flash_block($success ?: null, $error ?: null); ?>
<div class="grid gap-6 xl:grid-cols-[0.85fr_1.15fr]">
<section class="<?= admin_card() ?>">
    <h2 class="text-2xl font-black text-slate-950"><?= $editCategory ? 'Edit Kategori' : 'Tambah Kategori' ?></h2>
    <p class="mt-2 text-sm text-slate-500">Slug boleh dikosongkan, sistem akan membuat dari nama.</p>
    <form method="post" action="<?= e(url('/admin/categories.php')) ?>" class="mt-6 grid gap-5" novalidate>
        <?= Csrf::field($csrfKey) ?><input type="hidden" name="action" value="save"><input type="hidden" name="id" value="<?= e((string) ($editCategory['id'] ?? '')) ?>">
        <div><label class="<?= admin_label_class() ?>">Nama Kategori</label><input name="name" required maxlength="100" value="<?= e($editCategory['name'] ?? '') ?>" class="<?= admin_input_class() ?>" placeholder="Contoh: Menu Paket"></div>
        <div><label class="<?= admin_label_class() ?>">Slug</label><input name="slug" maxlength="120" placeholder="otomatis dari nama jika kosong" value="<?= e($editCategory['slug'] ?? '') ?>" class="<?= admin_input_class() ?>"></div>
        <div><label class="<?= admin_label_class() ?>">Deskripsi</label><textarea name="description" rows="4" class="<?= admin_input_class() ?>"><?= e($editCategory['description'] ?? '') ?></textarea></div>
        <div class="grid gap-4 sm:grid-cols-2"><div><label class="<?= admin_label_class() ?>">Sort Order</label><input name="sort_order" type="number" value="<?= e((string) ($editCategory['sort_order'] ?? 0)) ?>" class="<?= admin_input_class() ?>"></div><div class="flex items-end"><label class="flex w-full items-center gap-3 rounded-2xl border border-slate-200 px-4 py-3 text-sm font-bold text-slate-700"><input type="checkbox" name="is_active" value="1" class="h-4 w-4 rounded"<?= ((int) ($editCategory['is_active'] ?? 1) === 1) ? ' checked' : '' ?>> Aktif</label></div></div>
        <div class="flex flex-wrap gap-3"><button type="submit" class="<?= admin_primary_button() ?>"><?= $editCategory ? 'Update Kategori' : 'Tambah Kategori' ?></button><?php if ($editCategory): ?><a href="<?= e(url('/admin/categories.php')) ?>" class="<?= admin_secondary_button() ?>">Batal edit</a><?php endif; ?></div>
    </form>
</section>
<section class="<?= admin_card() ?>">
    <h2 class="text-2xl font-black text-slate-950">Daftar Kategori</h2>
    <p class="mt-2 text-sm text-slate-500">Kategori nonaktif tidak ditonjolkan di website publik.</p>
    <div class="mt-6 overflow-hidden rounded-3xl border border-slate-200">
    <?php if ($categories === []): ?><?php admin_empty_state('Belum ada kategori', 'Tambah kategori pertama untuk mengelompokkan katalog.'); ?><?php else: ?>
        <div class="overflow-x-auto"><table class="min-w-full divide-y divide-slate-200 text-sm"><thead class="bg-slate-50 text-left text-xs font-black uppercase tracking-wider text-slate-500"><tr><th class="px-4 py-3">Nama</th><th class="px-4 py-3">Slug</th><th class="px-4 py-3">Item</th><th class="px-4 py-3">Sort</th><th class="px-4 py-3">Status</th><th class="px-4 py-3">Aksi</th></tr></thead><tbody class="divide-y divide-slate-100 bg-white"><?php foreach ($categories as $category): ?><tr><td class="px-4 py-3 font-bold text-slate-950"><?= e($category['name']) ?></td><td class="px-4 py-3 text-slate-500"><?= e($category['slug']) ?></td><td class="px-4 py-3"><?= e((string) $category['item_count']) ?></td><td class="px-4 py-3"><?= e((string) $category['sort_order']) ?></td><td class="px-4 py-3"><?= admin_status_badge((int) $category['is_active'] === 1) ?></td><td class="px-4 py-3"><div class="flex flex-wrap gap-2"><a href="<?= e(url('/admin/categories.php?edit=' . (int) $category['id'])) ?>" class="<?= admin_secondary_button('!px-3 !py-2 !text-xs') ?>">Edit</a><form method="post" action="<?= e(url('/admin/categories.php')) ?>"><?= Csrf::field($csrfKey) ?><input type="hidden" name="action" value="deactivate"><input type="hidden" name="id" value="<?= e((string) $category['id']) ?>"><button type="submit" class="<?= admin_secondary_button('!px-3 !py-2 !text-xs') ?>">Nonaktifkan</button></form><form method="post" action="<?= e(url('/admin/categories.php')) ?>" onsubmit="return confirm('Hapus kategori ini? Item terkait menjadi tanpa kategori.')"><?= Csrf::field($csrfKey) ?><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?= e((string) $category['id']) ?>"><button type="submit" class="<?= admin_danger_button() ?>">Hapus</button></form></div></td></tr><?php endforeach; ?></tbody></table></div>
    <?php endif; ?>
    </div>
</section>
</div>
<?php admin_layout_end(); ?>
