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
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Kategori — <?= e(app_config('app.name', 'KreaKit CMS')) ?></title>
</head>
<body>
<main>
    <h1>Kategori</h1>
    <p><a href="<?= e(url('/admin/dashboard.php')) ?>">Dashboard</a> · <a href="<?= e(url('/admin/business.php')) ?>">Profil Bisnis</a> · <a href="<?= e(url('/admin/items.php')) ?>">Produk/Layanan</a></p>
    <?php if ($success): ?><p role="status"><?= e($success) ?></p><?php endif; ?>
    <?php if ($error): ?><p role="alert"><?= e($error) ?></p><?php endif; ?>

    <section>
        <h2><?= $editCategory ? 'Edit Kategori' : 'Tambah Kategori' ?></h2>
        <form method="post" action="<?= e(url('/admin/categories.php')) ?>" novalidate>
            <?= Csrf::field($csrfKey) ?>
            <input type="hidden" name="action" value="save">
            <input type="hidden" name="id" value="<?= e((string) ($editCategory['id'] ?? '')) ?>">
            <p><label>Nama<br><input name="name" required maxlength="100" value="<?= e($editCategory['name'] ?? '') ?>"></label></p>
            <p><label>Slug<br><input name="slug" maxlength="120" placeholder="otomatis dari nama jika kosong" value="<?= e($editCategory['slug'] ?? '') ?>"></label></p>
            <p><label>Deskripsi<br><textarea name="description" rows="3"><?= e($editCategory['description'] ?? '') ?></textarea></label></p>
            <p><label>Sort Order<br><input name="sort_order" type="number" value="<?= e((string) ($editCategory['sort_order'] ?? 0)) ?>"></label></p>
            <p><label><input type="checkbox" name="is_active" value="1"<?= ((int) ($editCategory['is_active'] ?? 1) === 1) ? ' checked' : '' ?>> Aktif</label></p>
            <button type="submit"><?= $editCategory ? 'Update Kategori' : 'Tambah Kategori' ?></button>
            <?php if ($editCategory): ?><a href="<?= e(url('/admin/categories.php')) ?>">Batal edit</a><?php endif; ?>
        </form>
    </section>

    <section>
        <h2>Daftar Kategori</h2>
        <?php if ($categories === []): ?>
            <p>Belum ada kategori.</p>
        <?php else: ?>
            <table border="1" cellpadding="6">
                <thead><tr><th>Nama</th><th>Slug</th><th>Item</th><th>Sort</th><th>Status</th><th>Aksi</th></tr></thead>
                <tbody>
                <?php foreach ($categories as $category): ?>
                    <tr>
                        <td><?= e($category['name']) ?></td>
                        <td><?= e($category['slug']) ?></td>
                        <td><?= e((string) $category['item_count']) ?></td>
                        <td><?= e((string) $category['sort_order']) ?></td>
                        <td><?= ((int) $category['is_active'] === 1) ? 'Aktif' : 'Nonaktif' ?></td>
                        <td>
                            <a href="<?= e(url('/admin/categories.php?edit=' . (int) $category['id'])) ?>">Edit</a>
                            <form method="post" action="<?= e(url('/admin/categories.php')) ?>" style="display:inline">
                                <?= Csrf::field($csrfKey) ?><input type="hidden" name="action" value="deactivate"><input type="hidden" name="id" value="<?= e((string) $category['id']) ?>"><button type="submit">Nonaktifkan</button>
                            </form>
                            <form method="post" action="<?= e(url('/admin/categories.php')) ?>" style="display:inline" onsubmit="return confirm('Hapus kategori ini? Item terkait menjadi tanpa kategori.')">
                                <?= Csrf::field($csrfKey) ?><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?= e((string) $category['id']) ?>"><button type="submit">Hapus</button>
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
