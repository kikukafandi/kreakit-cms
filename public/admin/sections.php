<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/app/bootstrap.php';

use KreaKit\Core\Csrf;
use KreaKit\Core\Session;

Session::start(app_config('session', []));
require_admin();

const SECTION_ROWS = ['highlights' => 4, 'stats' => 4, 'testimonials' => 4, 'faq' => 6];
const GALLERY_MAX = 6;

$csrfKey = (string) app_config('security.csrf_key', '_csrf_token');
$database = db();
$errors = [];

/**
 * Keeps only filled rows of a repeating field group, trimmed to each field's max length.
 */
function section_rows(string $name, array $limits): array
{
    $rows = [];
    foreach ((array) ($_POST[$name] ?? []) as $row) {
        $clean = [];
        foreach ($limits as $field => $max) {
            $clean[$field] = sanitize_multiline_or_null(is_array($row) ? (string) ($row[$field] ?? '') : '', $max) ?? '';
        }
        if (implode('', $clean) !== '') {
            $rows[] = $clean;
        }
    }
    return array_slice($rows, 0, SECTION_ROWS[$name]);
}

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    Csrf::requireValid($_POST[$csrfKey] ?? null, $csrfKey);
    $current = page_sections(settings_array($database));

    $aboutImage = isset($_POST['remove_about_image']) ? null : $current['about']['image'];
    $gallery = array_values(array_diff($current['gallery'], (array) ($_POST['remove_gallery'] ?? [])));

    try {
        $upload = secure_image_upload($_FILES['about_image_file'] ?? null, $database);
        if ($upload !== null) {
            $aboutImage = $upload['path'];
        }
        // $_FILES groups multiple uploads per attribute; regroup them into one array per file.
        $files = $_FILES['gallery_files'] ?? null;
        foreach (is_array($files['name'] ?? null) ? array_keys($files['name']) : [] as $index) {
            if (count($gallery) >= GALLERY_MAX) {
                $errors[] = 'Galeri maksimal ' . GALLERY_MAX . ' foto. Sebagian foto tidak disimpan.';
                break;
            }
            $file = array_combine(array_keys($files), array_column($files, $index));
            $upload = secure_image_upload($file, $database);
            if ($upload !== null) {
                $gallery[] = $upload['path'];
            }
        }
    } catch (RuntimeException $uploadError) {
        $errors[] = $uploadError->getMessage();
    }

    $values = [
        'section_about' => json_encode([
            'title' => sanitize_text((string) ($_POST['about_title'] ?? ''), 120),
            'body' => sanitize_multiline_or_null((string) ($_POST['about_body'] ?? ''), 3000) ?? '',
            'image' => $aboutImage,
        ], JSON_UNESCAPED_UNICODE),
        'section_highlights' => json_encode(section_rows('highlights', ['title' => 80, 'body' => 200]), JSON_UNESCAPED_UNICODE),
        'section_stats' => json_encode(section_rows('stats', ['value' => 20, 'label' => 60]), JSON_UNESCAPED_UNICODE),
        'section_gallery' => json_encode($gallery),
        'section_testimonials' => json_encode(section_rows('testimonials', ['name' => 80, 'role' => 80, 'quote' => 400]), JSON_UNESCAPED_UNICODE),
        'section_faq' => json_encode(section_rows('faq', ['question' => 160, 'answer' => 800]), JSON_UNESCAPED_UNICODE),
        'section_hours' => sanitize_multiline_or_null((string) ($_POST['hours'] ?? ''), 300),
    ];
    foreach ($values as $key => $value) {
        upsert_setting($database, $key, $value);
    }

    if ($errors !== []) {
        Session::flash('error', implode(' ', $errors));
    } else {
        Session::flash('success', 'Konten halaman berhasil disimpan.');
    }
    redirect(isset($_POST['preview_after_save']) && $errors === [] ? url('/') : url('/admin/sections.php'));
}

$sections = page_sections(settings_array($database));
$success = Session::flash('success');
$error = Session::flash('error');
$pad = static fn (array $rows, string $name): array => array_pad($rows, SECTION_ROWS[$name], []);
$input = admin_input_class();
$label = admin_label_class();

http_response_code(200);
?>
<?php admin_layout_start('Konten Halaman', 'sections', 'Section tambahan seperti website company profile. Bagian yang dikosongkan tidak akan tampil di website.'); ?>
<?php admin_flash_block($success ?: null, $error ?: null); ?>

<form method="post" enctype="multipart/form-data" class="grid gap-5">
    <?= Csrf::field($csrfKey) ?>

    <section class="<?= admin_card() ?>">
        <h2 class="text-lg font-extrabold tracking-tight text-slate-950">Tentang kami</h2>
        <p class="<?= admin_help_class() ?>">Cerita singkat bisnis, tampil bersama foto dan angka pencapaian.</p>
        <div class="mt-5 grid gap-5 lg:grid-cols-[1fr_260px]">
            <div class="grid gap-4">
                <div><label class="<?= $label ?>" for="about_title">Judul</label><input id="about_title" name="about_title" maxlength="120" value="<?= e($sections['about']['title']) ?>" placeholder="Contoh: Masakan rumahan sejak 2014" class="<?= $input ?>"></div>
                <div><label class="<?= $label ?>" for="about_body">Cerita</label><textarea id="about_body" name="about_body" rows="6" maxlength="3000" class="<?= $input ?>"><?= e($sections['about']['body']) ?></textarea><p class="<?= admin_help_class() ?>">Pisahkan paragraf dengan satu baris kosong.</p></div>
            </div>
            <div>
                <span class="<?= $label ?>">Foto</span>
                <?php if ($sections['about']['image'] !== null): ?>
                    <img src="<?= e(public_upload_url($sections['about']['image'])) ?>" alt="" class="mt-1.5 aspect-[4/5] w-full rounded-xl object-cover">
                    <label class="mt-2 flex items-center gap-2 text-sm text-slate-600"><input type="checkbox" name="remove_about_image" value="1" class="rounded"> Hapus foto</label>
                <?php endif; ?>
                <input type="file" name="about_image_file" accept="image/jpeg,image/png,image/webp" class="<?= $input ?>">
            </div>
        </div>
        <h3 class="mt-6 text-sm font-bold text-slate-800">Angka pencapaian</h3>
        <div class="mt-2 grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
            <?php foreach ($pad($sections['stats'], 'stats') as $i => $stat): ?>
                <div class="grid grid-cols-[90px_1fr] gap-2">
                    <input name="stats[<?= $i ?>][value]" maxlength="20" value="<?= e($stat['value'] ?? '') ?>" placeholder="10+" aria-label="Angka <?= $i + 1 ?>" class="<?= admin_input_class('!mt-0') ?>">
                    <input name="stats[<?= $i ?>][label]" maxlength="60" value="<?= e($stat['label'] ?? '') ?>" placeholder="Tahun pengalaman" aria-label="Keterangan <?= $i + 1 ?>" class="<?= admin_input_class('!mt-0') ?>">
                </div>
            <?php endforeach; ?>
        </div>
    </section>

    <section class="<?= admin_card() ?>">
        <h2 class="text-lg font-extrabold tracking-tight text-slate-950">Keunggulan</h2>
        <p class="<?= admin_help_class() ?>">Alasan pelanggan memilih bisnis ini. Isi 2 sampai 4 poin.</p>
        <div class="mt-4 grid gap-4 md:grid-cols-2">
            <?php foreach ($pad($sections['highlights'], 'highlights') as $i => $highlight): ?>
                <div class="grid gap-2 rounded-xl bg-slate-50 p-3">
                    <input name="highlights[<?= $i ?>][title]" maxlength="80" value="<?= e($highlight['title'] ?? '') ?>" placeholder="Judul poin <?= $i + 1 ?>" aria-label="Judul keunggulan <?= $i + 1 ?>" class="<?= admin_input_class('!mt-0') ?>">
                    <input name="highlights[<?= $i ?>][body]" maxlength="200" value="<?= e($highlight['body'] ?? '') ?>" placeholder="Penjelasan singkat" aria-label="Penjelasan keunggulan <?= $i + 1 ?>" class="<?= admin_input_class('!mt-0') ?>">
                </div>
            <?php endforeach; ?>
        </div>
    </section>

    <section class="<?= admin_card() ?>">
        <h2 class="text-lg font-extrabold tracking-tight text-slate-950">Galeri</h2>
        <p class="<?= admin_help_class() ?>">Maksimal <?= GALLERY_MAX ?> foto suasana tempat, proses, atau hasil kerja. Bisa pilih beberapa foto sekaligus.</p>
        <?php if ($sections['gallery'] !== []): ?>
            <div class="mt-4 grid grid-cols-3 gap-3 sm:grid-cols-6">
                <?php foreach ($sections['gallery'] as $path): ?>
                    <label class="block">
                        <img src="<?= e(public_upload_url($path)) ?>" alt="" class="aspect-square w-full rounded-xl object-cover">
                        <span class="mt-1.5 flex items-center gap-1.5 text-xs text-slate-600"><input type="checkbox" name="remove_gallery[]" value="<?= e($path) ?>" class="rounded"> Hapus</span>
                    </label>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
        <input type="file" name="gallery_files[]" multiple accept="image/jpeg,image/png,image/webp" class="<?= $input ?> mt-4">
    </section>

    <section class="<?= admin_card() ?>">
        <h2 class="text-lg font-extrabold tracking-tight text-slate-950">Testimoni</h2>
        <p class="<?= admin_help_class() ?>">Kutipan asli dari pelanggan. Cukup 2 sampai 3 kalimat.</p>
        <div class="mt-4 grid gap-4 md:grid-cols-2">
            <?php foreach ($pad($sections['testimonials'], 'testimonials') as $i => $testimonial): ?>
                <div class="grid gap-2 rounded-xl bg-slate-50 p-3">
                    <div class="grid grid-cols-2 gap-2">
                        <input name="testimonials[<?= $i ?>][name]" maxlength="80" value="<?= e($testimonial['name'] ?? '') ?>" placeholder="Nama pelanggan" aria-label="Nama pelanggan <?= $i + 1 ?>" class="<?= admin_input_class('!mt-0') ?>">
                        <input name="testimonials[<?= $i ?>][role]" maxlength="80" value="<?= e($testimonial['role'] ?? '') ?>" placeholder="Keterangan (opsional)" aria-label="Keterangan pelanggan <?= $i + 1 ?>" class="<?= admin_input_class('!mt-0') ?>">
                    </div>
                    <textarea name="testimonials[<?= $i ?>][quote]" rows="2" maxlength="400" placeholder="Isi testimoni" aria-label="Isi testimoni <?= $i + 1 ?>" class="<?= admin_input_class('!mt-0') ?>"><?= e($testimonial['quote'] ?? '') ?></textarea>
                </div>
            <?php endforeach; ?>
        </div>
    </section>

    <section class="<?= admin_card() ?>">
        <h2 class="text-lg font-extrabold tracking-tight text-slate-950">Pertanyaan umum (FAQ)</h2>
        <p class="<?= admin_help_class() ?>">Jawab pertanyaan yang sering masuk lewat WhatsApp, misalnya ongkir, minimal order, atau cara bayar.</p>
        <div class="mt-4 grid gap-3">
            <?php foreach ($pad($sections['faq'], 'faq') as $i => $entry): ?>
                <div class="grid gap-2 md:grid-cols-[1fr_1.4fr]">
                    <input name="faq[<?= $i ?>][question]" maxlength="160" value="<?= e($entry['question'] ?? '') ?>" placeholder="Pertanyaan <?= $i + 1 ?>" aria-label="Pertanyaan <?= $i + 1 ?>" class="<?= admin_input_class('!mt-0') ?>">
                    <input name="faq[<?= $i ?>][answer]" maxlength="800" value="<?= e($entry['answer'] ?? '') ?>" placeholder="Jawaban" aria-label="Jawaban <?= $i + 1 ?>" class="<?= admin_input_class('!mt-0') ?>">
                </div>
            <?php endforeach; ?>
        </div>
    </section>

    <section class="<?= admin_card() ?>">
        <h2 class="text-lg font-extrabold tracking-tight text-slate-950">Jam buka</h2>
        <label class="sr-only" for="hours">Jam buka</label>
        <textarea id="hours" name="hours" rows="3" maxlength="300" placeholder="Senin sampai Jumat: 08.00 - 20.00" class="<?= $input ?>"><?= e($sections['hours']) ?></textarea>
        <p class="<?= admin_help_class() ?>">Tampil di bagian kontak. Satu baris untuk setiap jadwal.</p>
    </section>

    <div class="sticky bottom-4 z-10 flex flex-wrap gap-2 rounded-2xl border border-slate-200 bg-white/90 p-3 backdrop-blur">
        <button type="submit" class="<?= admin_primary_button() ?>">Simpan konten</button>
        <button type="submit" name="preview_after_save" value="1" class="<?= admin_secondary_button() ?>">Simpan & lihat website</button>
    </div>
</form>
<?php admin_layout_end(); ?>
