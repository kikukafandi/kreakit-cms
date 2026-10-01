# Technical Design

## Architecture Overview

KreaKit CMS MVP adalah aplikasi CMS native PHP + MySQL/MariaDB untuk 1 website per instalasi, 1 admin user, dan deployment murah di shared hosting/cPanel. Arsitektur sengaja dibuat monolith sederhana tanpa framework berat agar mudah dipasang di Apache/cPanel biasa.

Konsep utama:

- Public website/katalog membaca data bisnis, produk/layanan, kontak, sosial media, dan pilihan template dari database.
- Admin panel dipakai pemilik bisnis/KreaByte untuk login, memilih template, mengedit konten, upload gambar, dan preview.
- Template adalah folder view/CSS reusable yang membaca data standar dari CMS.
- Tidak ada payment gateway, checkout kompleks, multi-user permission, POS, inventory real-time, atau SaaS multi-tenant pada MVP.

Rekomendasi stack MVP:

- PHP 8.1+ native.
- MySQL 5.7+/MariaDB 10.3+.
- Apache dengan `.htaccess`.
- PDO untuk database.
- Session PHP untuk autentikasi admin.
- File upload lokal ke folder `storage/uploads` atau `public/uploads` sesuai batasan hosting.
- CSS vanilla atau Bootstrap lokal/CDN ringan; hindari build step wajib.

Mode deployment utama:

- Satu instalasi CMS untuk satu client/website.
- Database terpisah per instalasi/client.
- Admin URL contoh: `/admin`.
- Public site URL contoh: `/`.

## Components

### 1. Public Site Renderer

Tanggung jawab:

- Menampilkan halaman publik berdasarkan template aktif.
- Menampilkan profil bisnis, hero/banner, produk/layanan, kategori, kontak, alamat/maps, sosial media, dan CTA WhatsApp.
- Menghasilkan meta title/description dasar jika SEO settings MVP dimasukkan sebagai field sederhana.

Halaman publik minimal:

- Home/katalog: `/`
- Detail produk/layanan opsional sederhana: `/item/{slug}` atau `/?item=slug` jika routing sederhana lebih aman di shared hosting.
- Halaman kontak/section kontak bisa menjadi bagian home, tidak perlu halaman terpisah untuk MVP.

### 2. Admin Panel

Tanggung jawab:

- Login/logout admin.
- Dashboard ringkas status website.
- Edit profil bisnis.
- Kelola produk/layanan.
- Kelola kategori produk/layanan.
- Pilih template aktif.
- Upload logo/gambar utama/gambar produk.
- Preview website sebelum/ setelah simpan.
- Pengaturan kontak dan sosial media.

Key screens admin:

1. Login.
2. Dashboard.
3. Profil Bisnis.
4. Template & Tampilan.
5. Produk/Layanan.
6. Kategori.
7. Kontak & Sosial Media.
8. Media Upload sederhana.
9. Preview.
10. Pengaturan Admin: ubah password.

### 3. Template System

Template adalah folder berisi file tampilan publik yang mematuhi kontrak data standar.

Minimal 3 template awal:

1. `kuliner-simple` — cocok untuk makanan/minuman, menonjolkan menu dan WhatsApp order.
2. `jasa-lokal` — cocok untuk laundry, service AC, salon, barbershop, klinik kecil.
3. `produk-katalog` — cocok untuk seller online/katalog produk fisik.

Setiap template wajib punya:

- `template.json` metadata.
- `index.php` view utama.
- `style.css` CSS khusus template.
- `preview.png` untuk admin template picker.

Kontrak data yang diterima template:

- `$business`
- `$items`
- `$categories`
- `$socialLinks`
- `$settings`
- `$themeSettings`

Template tidak boleh query database langsung. Semua data diberikan oleh controller/helper public renderer agar template tetap reusable dan mudah diuji.

### 4. Admin Controllers / Handlers

Karena native PHP, gunakan handler per fitur agar tetap sederhana:

- Auth handler: login, logout, password update.
- Business handler: save profil bisnis.
- Item handler: CRUD produk/layanan.
- Category handler: CRUD kategori.
- Template handler: pilih template aktif.
- Media handler: upload dan hapus file yang tidak dipakai.
- Settings handler: kontak, sosial media, warna dasar, SEO sederhana.

### 5. Database Layer

Gunakan PDO wrapper sederhana:

- Koneksi database dari `config/config.php`.
- Prepared statements wajib untuk semua query.
- Helper transaksi untuk operasi multi-step seperti create item + upload image metadata.
- Tidak perlu ORM pada MVP.

### 6. Migration / Installer Sederhana

Untuk shared hosting, sediakan salah satu pendekatan:

- MVP awal: file SQL `database/schema.sql` + `database/seed.sql` diimpor via phpMyAdmin.
- Opsional jika waktu cukup: `/install` wizard sederhana untuk cek requirement, input database, buat tabel, dan buat admin pertama.

Rekomendasi MVP: mulai dengan SQL import manual + dokumentasi cPanel. Installer bisa menjadi fase berikutnya jika diperlukan.

## Data Flow

### Public Render Flow

1. Visitor membuka `/`.
2. `public/index.php` memuat bootstrap/config.
3. Public controller mengambil:
   - profil bisnis aktif,
   - template aktif,
   - daftar kategori aktif,
   - daftar produk/layanan aktif,
   - kontak dan sosial media,
   - theme settings.
4. Sistem menentukan folder template aktif.
5. Template menerima data bersih dan render HTML.
6. Asset template, gambar upload, dan CSS disajikan oleh Apache.

### Admin Login Flow

1. Admin membuka `/admin`.
2. Jika belum login, redirect ke `/admin/login.php`.
3. Admin submit username/email + password.
4. Sistem validasi CSRF token.
5. Sistem cari admin aktif di database.
6. Password diverifikasi dengan `password_verify()`.
7. Jika valid, session ID di-regenerate dan user masuk dashboard.
8. Jika gagal, tampilkan pesan generik tanpa membocorkan apakah username/password salah.

### Content Editing Flow

1. Admin membuka form edit.
2. Sistem menampilkan data saat ini.
3. Admin submit perubahan.
4. Sistem validasi CSRF, session, input length/type, dan file upload jika ada.
5. Data disimpan menggunakan prepared statements.
6. Admin mendapat flash message sukses/gagal.
7. Preview dapat dibuka dari tombol “Preview Website”.

### Template Selection Flow

1. Admin membuka Template & Tampilan.
2. Sistem membaca metadata template dari tabel `templates` atau folder `templates/*/template.json` yang disinkronkan.
3. Admin memilih template.
4. Sistem menyimpan `active_template_id`/`active_template_slug` ke settings.
5. Public renderer memakai template baru saat halaman dibuka.

### Upload Flow

1. Admin memilih file gambar.
2. Sistem validasi:
   - MIME type gambar,
   - ekstensi allowlist,
   - ukuran maksimum,
   - dimensi opsional,
   - nama file dinormalisasi unik.
3. File disimpan ke `public/uploads/YYYY/MM/` atau `storage/uploads/YYYY/MM/` jika didukung rewrite.
4. Path relatif disimpan di database.
5. File lama bisa dibiarkan dulu atau diberi fitur cleanup manual sederhana.

## Database Design

Gunakan charset `utf8mb4` dan collation `utf8mb4_unicode_ci`. Semua tabel memakai `InnoDB`.

### `admins`

Untuk 1 admin user MVP, tetapi struktur tetap memungkinkan lebih dari 1 bila dibutuhkan nanti.

| Field | Type | Notes |
|---|---|---|
| id | INT UNSIGNED PK AUTO_INCREMENT |  |
| name | VARCHAR(100) | Nama admin |
| email | VARCHAR(190) UNIQUE | Login identifier |
| password_hash | VARCHAR(255) | Dari `password_hash()` |
| is_active | TINYINT(1) DEFAULT 1 |  |
| last_login_at | DATETIME NULL |  |
| created_at | DATETIME |  |
| updated_at | DATETIME |  |

### `business_profiles`

Satu baris utama untuk profil bisnis.

| Field | Type | Notes |
|---|---|---|
| id | INT UNSIGNED PK AUTO_INCREMENT |  |
| business_name | VARCHAR(150) | Wajib |
| tagline | VARCHAR(190) NULL |  |
| description | TEXT NULL |  |
| logo_path | VARCHAR(255) NULL |  |
| hero_image_path | VARCHAR(255) NULL |  |
| whatsapp_number | VARCHAR(30) NULL | Format internasional disarankan, contoh `62812...` |
| address | TEXT NULL |  |
| maps_url | VARCHAR(500) NULL |  |
| email | VARCHAR(190) NULL |  |
| phone | VARCHAR(50) NULL |  |
| created_at | DATETIME |  |
| updated_at | DATETIME |  |

### `categories`

| Field | Type | Notes |
|---|---|---|
| id | INT UNSIGNED PK AUTO_INCREMENT |  |
| name | VARCHAR(100) |  |
| slug | VARCHAR(120) UNIQUE |  |
| description | TEXT NULL |  |
| sort_order | INT DEFAULT 0 |  |
| is_active | TINYINT(1) DEFAULT 1 |  |
| created_at | DATETIME |  |
| updated_at | DATETIME |  |

### `items`

Produk atau layanan memakai satu tabel agar MVP sederhana.

| Field | Type | Notes |
|---|---|---|
| id | INT UNSIGNED PK AUTO_INCREMENT |  |
| category_id | INT UNSIGNED NULL FK categories.id | `ON DELETE SET NULL` |
| name | VARCHAR(150) | Wajib |
| slug | VARCHAR(170) UNIQUE | Untuk detail URL opsional |
| short_description | VARCHAR(255) NULL |  |
| description | TEXT NULL |  |
| price | DECIMAL(12,2) NULL | Simpan angka; format rupiah di view |
| price_label | VARCHAR(100) NULL | Contoh: “Mulai Rp50.000”, jika harga tidak fixed |
| image_path | VARCHAR(255) NULL |  |
| whatsapp_message | VARCHAR(255) NULL | Pesan default per item opsional |
| sort_order | INT DEFAULT 0 |  |
| is_featured | TINYINT(1) DEFAULT 0 |  |
| is_active | TINYINT(1) DEFAULT 1 |  |
| created_at | DATETIME |  |
| updated_at | DATETIME |  |

Index rekomendasi:

- `idx_items_category_active (category_id, is_active)`
- `idx_items_sort (sort_order)`
- `idx_items_slug (slug)`

### `templates`

| Field | Type | Notes |
|---|---|---|
| id | INT UNSIGNED PK AUTO_INCREMENT |  |
| slug | VARCHAR(100) UNIQUE | Sama dengan nama folder template |
| name | VARCHAR(150) | Nama tampil di admin |
| description | VARCHAR(255) NULL |  |
| niche | VARCHAR(100) NULL | Contoh: kuliner, jasa, katalog |
| preview_image | VARCHAR(255) NULL |  |
| is_active | TINYINT(1) DEFAULT 1 | Template tersedia dipilih |
| created_at | DATETIME |  |
| updated_at | DATETIME |  |

### `settings`

Key-value untuk setting sederhana agar tidak perlu banyak tabel kecil.

| Field | Type | Notes |
|---|---|---|
| setting_key | VARCHAR(100) PK |  |
| setting_value | TEXT NULL |  |
| updated_at | DATETIME |  |

Key awal:

- `active_template_slug`
- `primary_color`
- `secondary_color`
- `button_style`
- `site_meta_title`
- `site_meta_description`
- `catalog_mode` — `products` atau `services`

### `social_links`

| Field | Type | Notes |
|---|---|---|
| id | INT UNSIGNED PK AUTO_INCREMENT |  |
| platform | VARCHAR(50) | instagram, facebook, tiktok, marketplace, website |
| label | VARCHAR(100) NULL |  |
| url | VARCHAR(500) |  |
| sort_order | INT DEFAULT 0 |  |
| is_active | TINYINT(1) DEFAULT 1 |  |
| created_at | DATETIME |  |
| updated_at | DATETIME |  |

### `media_files` Opsional tapi Direkomendasikan

Membantu audit upload dan cleanup.

| Field | Type | Notes |
|---|---|---|
| id | INT UNSIGNED PK AUTO_INCREMENT |  |
| original_name | VARCHAR(255) |  |
| stored_name | VARCHAR(255) |  |
| path | VARCHAR(255) |  |
| mime_type | VARCHAR(100) |  |
| size_bytes | INT UNSIGNED |  |
| uploaded_by_admin_id | INT UNSIGNED NULL |  |
| created_at | DATETIME |  |

### `audit_logs` Opsional untuk MVP+

Jika ingin basic trace tanpa kompleksitas:

| Field | Type | Notes |
|---|---|---|
| id | INT UNSIGNED PK AUTO_INCREMENT |  |
| admin_id | INT UNSIGNED NULL |  |
| action | VARCHAR(100) | Contoh: `login`, `update_business`, `create_item` |
| detail | TEXT NULL | JSON/text ringkas |
| ip_address | VARCHAR(45) NULL |  |
| created_at | DATETIME |  |

Untuk MVP sangat cepat, `audit_logs` boleh ditunda.

## API / Integration Design

MVP tidak membutuhkan API publik. Gunakan server-rendered PHP form POST agar kompatibel dengan shared hosting dan tidak butuh frontend build.

### Internal Admin Routes

Jika memakai clean URL via `.htaccess`:

- `GET /admin/login`
- `POST /admin/login`
- `POST /admin/logout`
- `GET /admin/dashboard`
- `GET|POST /admin/business`
- `GET|POST /admin/settings`
- `GET /admin/items`
- `GET|POST /admin/items/create`
- `GET|POST /admin/items/edit?id={id}`
- `POST /admin/items/delete`
- `GET|POST /admin/categories`
- `GET|POST /admin/templates`
- `POST /admin/media/upload`

Fallback shared hosting jika rewrite bermasalah:

- `/admin/login.php`
- `/admin/dashboard.php`
- `/admin/business.php`
- `/admin/items.php`
- `/admin/item-form.php?id=...`
- `/admin/templates.php`

Rekomendasi developer: dukung `.php` fallback dulu, lalu tambahkan clean URL jika aman.

### External Integrations

MVP hanya memakai link keluar:

- WhatsApp CTA: `https://wa.me/{number}?text={encoded_message}`
- Google Maps/link maps: URL yang diinput user.
- Social media links: URL yang diinput user.

Tidak ada integrasi payment, courier, analytics eksternal wajib, atau marketplace API pada MVP.

### Import/Export Opsional

Bukan scope utama, tetapi developer boleh menyiapkan struktur agar mudah ditambah:

- Export JSON konten bisnis.
- Import produk via CSV.
- Export static site ditunda karena masuk COULD.

## Authentication / Authorization

### Authentication

- Login admin berbasis email + password.
- Password disimpan dengan `password_hash(PASSWORD_DEFAULT)`.
- Verifikasi dengan `password_verify()`.
- Regenerate session ID setelah login.
- Logout menghancurkan session.
- Admin pertama dibuat dari seed SQL atau installer manual.

### Authorization

- Satu role: `admin`.
- Semua halaman `/admin/*` wajib memanggil guard `require_admin()`.
- Public site tidak membutuhkan login.
- Tidak ada permission granular pada MVP.

### Session Security

- `session.cookie_httponly = true`.
- `session.cookie_secure = true` jika HTTPS aktif; dokumentasikan wajib HTTPS di hosting produksi.
- `session.cookie_samesite = Lax`.
- Session timeout idle rekomendasi 2 jam.
- Regenerate session ID saat login dan setelah interval tertentu.

### Password Reset

Untuk MVP shared hosting sederhana:

- Tidak perlu fitur forgot password via email pada fase awal.
- Sediakan panduan reset password manual via script CLI/web installer terbatas atau phpMyAdmin menggunakan hash baru yang dibuat lokal oleh KreaByte.
- Jika ditambahkan nanti, wajib token satu kali pakai + expiry.

## Security Considerations

### Input & Output

- Semua input divalidasi server-side.
- Semua output HTML dari database di-escape dengan `htmlspecialchars()` kecuali field yang secara eksplisit diizinkan HTML. Rekomendasi MVP: tidak izinkan HTML bebas.
- URL divalidasi dengan allowlist scheme `http`/`https`.
- WhatsApp number disanitasi hanya angka dan `+`, lalu normalisasi ke format `62...` bila memungkinkan.

### SQL Injection

- Semua query wajib memakai PDO prepared statements.
- Jangan menyusun query dari input user tanpa parameter binding.

### CSRF

- Semua form POST admin wajib memiliki CSRF token.
- Token disimpan di session dan diverifikasi sebelum proses update/delete/upload.

### File Upload

- Allowlist ekstensi: `jpg`, `jpeg`, `png`, `webp`.
- Validasi MIME dengan `finfo_file()`.
- Batas ukuran default: 2 MB per file untuk shared hosting.
- Rename file ke nama unik, jangan pakai nama asli sebagai path.
- Jangan izinkan upload PHP/SVG untuk MVP.
- Simpan upload di folder tanpa execute permission jika hosting memungkinkan.
- Tambahkan `.htaccess` di folder upload untuk menonaktifkan eksekusi PHP:
  - `php_flag engine off` jika didukung.
  - `RemoveHandler .php .phtml .php3 .php4 .php5 .php7 .php8`.

### Admin Protection

- Pesan login gagal generik.
- Rate limit sederhana berbasis session/IP untuk login, misalnya lock sementara setelah 5 percobaan gagal.
- Default URL admin boleh `/admin`, tetapi dokumentasikan opsi mengganti nama folder admin jika client butuh.
- Jangan commit kredensial database nyata.

### Config & Secrets

- `config/config.php` berisi kredensial database dan base URL.
- Sediakan `config/config.example.php` untuk template.
- Pastikan config produksi tidak bisa diakses langsung dari web jika memungkinkan. Jika struktur shared hosting memaksa berada di webroot, lindungi dengan `.htaccess` deny.

### HTTPS

- Produksi wajib memakai SSL dari cPanel/AutoSSL/Let’s Encrypt.
- Jika HTTPS belum aktif, admin login tetap bisa berjalan untuk demo lokal, tetapi dokumentasi harus menandai risiko.

### Backup

- Dokumentasikan backup rutin:
  - export database dari phpMyAdmin,
  - backup folder `uploads`,
  - backup `config/config.php` secara aman.

## Deployment Strategy

Target deployment: cPanel shared hosting dengan Apache, PHP 8.1+, MySQL/MariaDB, phpMyAdmin, File Manager/FTP.

### Recommended Folder Structure

```text
kreakit-cms/
├── app/
│   ├── Controllers/
│   │   ├── Admin/
│   │   │   ├── AuthController.php
│   │   │   ├── BusinessController.php
│   │   │   ├── CategoryController.php
│   │   │   ├── ItemController.php
│   │   │   ├── MediaController.php
│   │   │   ├── SettingsController.php
│   │   │   └── TemplateController.php
│   │   └── PublicController.php
│   ├── Core/
│   │   ├── Database.php
│   │   ├── Router.php
│   │   ├── Session.php
│   │   ├── Csrf.php
│   │   ├── Auth.php
│   │   ├── Validator.php
│   │   └── View.php
│   ├── Models/
│   │   ├── Admin.php
│   │   ├── BusinessProfile.php
│   │   ├── Category.php
│   │   ├── Item.php
│   │   ├── Setting.php
│   │   ├── SocialLink.php
│   │   └── Template.php
│   └── Helpers/
│       ├── formatting.php
│       ├── upload.php
│       ├── url.php
│       └── whatsapp.php
├── config/
│   ├── config.example.php
│   └── config.php
├── database/
│   ├── schema.sql
│   ├── seed.sql
│   └── sample-data.sql
├── docs/
│   ├── hosting-cpanel.md
│   ├── installation.md
│   ├── admin-user-guide.md
│   ├── template-development.md
│   ├── backup-restore.md
│   └── troubleshooting.md
├── public/
│   ├── .htaccess
│   ├── index.php
│   ├── admin/
│   │   ├── index.php
│   │   ├── login.php
│   │   ├── logout.php
│   │   ├── dashboard.php
│   │   ├── business.php
│   │   ├── categories.php
│   │   ├── items.php
│   │   ├── item-form.php
│   │   ├── templates.php
│   │   └── settings.php
│   ├── assets/
│   │   ├── admin/
│   │   │   ├── admin.css
│   │   │   └── admin.js
│   │   └── shared/
│   └── uploads/
│       ├── .htaccess
│       └── .gitkeep
├── templates/
│   ├── kuliner-simple/
│   │   ├── template.json
│   │   ├── index.php
│   │   ├── style.css
│   │   └── preview.png
│   ├── jasa-lokal/
│   │   ├── template.json
│   │   ├── index.php
│   │   ├── style.css
│   │   └── preview.png
│   └── produk-katalog/
│       ├── template.json
│       ├── index.php
│       ├── style.css
│       └── preview.png
├── storage/
│   ├── logs/
│   │   └── .gitkeep
│   └── cache/
│       └── .gitkeep
├── vendor/              # hanya jika nanti memakai dependency ringan; MVP sebaiknya tanpa Composer wajib
├── .gitignore
└── README.md
```

Catatan shared hosting:

- Ideal: document root diarahkan ke `public/`.
- Jika cPanel tidak memungkinkan mengubah document root, sediakan paket deploy dengan isi `public/` ditempatkan di `public_html/`, sedangkan `app/`, `config/`, `database/`, `templates/`, `storage/` berada satu level di atas `public_html/` jika hosting mengizinkan.
- Jika semua harus berada di `public_html/`, lindungi folder internal dengan `.htaccess` deny.

### Deployment Steps cPanel MVP

1. Buat database MySQL/MariaDB dan user database di cPanel.
2. Import `database/schema.sql` via phpMyAdmin.
3. Import `database/seed.sql` untuk admin awal dan 3 template awal.
4. Upload file aplikasi via File Manager/FTP.
5. Salin `config/config.example.php` menjadi `config/config.php`.
6. Isi DB host, DB name, DB user, DB password, dan base URL.
7. Pastikan folder upload writable:
   - `public/uploads` permission biasanya `755` atau `775` tergantung hosting.
8. Aktifkan SSL/AutoSSL.
9. Buka `/admin/login.php`.
10. Login dengan kredensial sementara dari dokumentasi internal, lalu wajib ubah password.
11. Isi profil bisnis, produk/layanan, kontak, dan pilih template.
12. Buka public URL untuk verifikasi.

### Local Development Strategy

- Gunakan PHP built-in server untuk development lokal:
  - `php -S localhost:8000 -t public`
- Gunakan MySQL/MariaDB lokal.
- Jangan wajibkan Docker untuk founder/client; boleh disediakan untuk developer jika membantu.
- Seed sample data harus memungkinkan demo langsung dengan 3 template.

## Technical Risks

1. **Variasi shared hosting/cPanel**
   - Risiko: document root, rewrite rules, permission, dan PHP config berbeda-beda.
   - Mitigasi: dukung fallback `.php` route, minimalkan dependency, dokumentasikan dua mode deploy.

2. **Keamanan upload di shared hosting**
   - Risiko: upload file berbahaya jika validasi lemah.
   - Mitigasi: MIME validation, ekstensi allowlist, rename file, disable PHP execution di uploads.

3. **Template menjadi terlalu bebas/rumit**
   - Risiko: setiap template query DB sendiri sehingga susah maintain.
   - Mitigasi: kontrak data standar; template hanya render data.

4. **Scope creep menuju SaaS/ecommerce kompleks**
   - Risiko: MVP lambat selesai.
   - Mitigasi: pertahankan 1 instalasi = 1 website, 1 admin, tanpa checkout/payment.

5. **Backup/restore manual rawan dilupakan**
   - Risiko: data client hilang saat migrasi hosting.
   - Mitigasi: dokumentasi backup database + uploads + config wajib.

6. **Password admin awal bocor**
   - Risiko: seed default dipakai di produksi.
   - Mitigasi: dokumentasi wajib ubah password setelah login pertama; installer/admin settings mengingatkan.

7. **Kualitas desain template awal kurang menjual**
   - Risiko: produk sulit didemokan meski CMS berfungsi.
   - Mitigasi: template awal harus punya sample data dan preview visual yang rapi.

## Technical Decisions

1. **Native PHP monolith, bukan Laravel/WordPress/plugin**
   - Alasan: ringan, mudah dipasang di shared hosting murah, cepat dibuat sebagai aset produk KreaByte.

2. **1 website per instalasi untuk MVP**
   - Alasan: menghindari kompleksitas multi-tenant, billing, isolasi data, dan domain mapping.

3. **1 admin role untuk MVP**
   - Alasan: cukup untuk UMKM dan paket setup; permission granular ditunda.

4. **Server-rendered HTML + form POST**
   - Alasan: paling kompatibel dengan cPanel, tidak wajib Node/build step.

5. **PDO prepared statements tanpa ORM**
   - Alasan: aman, ringan, dan cukup untuk data model kecil.

6. **Template folder dengan kontrak data standar**
   - Alasan: template mudah ditambah tanpa mengubah core CMS besar-besaran.

7. **SQL import manual sebagai deployment awal**
   - Alasan: paling realistis untuk cPanel/phpMyAdmin dan cepat untuk MVP.

8. **Tidak ada payment/checkout di MVP**
   - Alasan: sesuai out-of-scope; CTA WhatsApp cukup untuk konversi awal.

9. **Upload gambar lokal**
   - Alasan: tidak perlu biaya/object storage eksternal; cocok untuk skala UMKM kecil.

10. **Clean URL opsional, `.php` fallback wajib**
   - Alasan: rewrite support di shared hosting tidak selalu konsisten.

## Open Technical Questions

1. Template awal final yang dipilih apakah tetap:
   - kuliner,
   - jasa lokal,
   - katalog produk?

2. Apakah KreaByte ingin paket deploy utama:
   - client hosting sendiri,
   - hosting dikelola KreaByte,
   - atau keduanya?

3. Apakah perlu installer web `/install` untuk membuat database/admin pertama, atau cukup SQL import + dokumentasi untuk MVP pertama?

4. Apakah detail produk/layanan perlu halaman sendiri pada MVP, atau cukup katalog satu halaman?

5. Apakah SEO settings sederhana masuk MUST untuk MVP pertama, atau ditunda ke iterasi berikutnya?

6. Apakah admin perlu Bahasa Indonesia penuh sejak awal? Rekomendasi: ya, karena target UMKM lokal.

7. Apakah KreaKit akan dijual sebagai source code sekali bayar atau paket setup per client? Ini memengaruhi dokumentasi lisensi, update, dan support.

## Implementation Guidance

### Urutan Implementasi Direkomendasikan

1. **Project skeleton**
   - Buat struktur folder.
   - Buat bootstrap/config loader.
   - Buat PDO database wrapper.
   - Buat helper escape, redirect, flash message, CSRF, session.

2. **Database schema & seed**
   - Implement `schema.sql`.
   - Implement `seed.sql` untuk:
     - admin awal,
     - 3 template,
     - business profile sample,
     - kategori dan item sample.

3. **Authentication admin**
   - Login/logout.
   - Session guard.
   - Ubah password.
   - CSRF di semua form.

4. **Admin CRUD content**
   - Profil bisnis.
   - Kategori.
   - Produk/layanan.
   - Kontak dan sosial media.
   - Settings warna/SEO sederhana jika disetujui.

5. **Upload image**
   - Logo bisnis.
   - Hero image.
   - Gambar item.
   - Validasi upload dan `.htaccess` upload protection.

6. **Template system**
   - Template registry dari database/folder metadata.
   - Template picker admin.
   - Public renderer yang mengirim kontrak data ke template.

7. **Public templates**
   - Buat 3 template awal responsive.
   - Pastikan semua template menampilkan data wajib:
     - nama bisnis,
     - deskripsi,
     - logo/hero,
     - produk/layanan,
     - harga,
     - CTA WhatsApp,
     - alamat/maps,
     - sosial media.

8. **Preview flow**
   - Tombol preview dari admin.
   - Untuk MVP, preview bisa membuka public site setelah data disimpan.
   - Preview draft tanpa save ditunda kecuali sangat mudah.

9. **Documentation**
   - Tulis dokumen hosting/deployment sebelum QA final agar developer menguji sesuai instruksi nyata.

10. **QA shared-hosting compatibility**
   - Test lokal dengan PHP built-in server.
   - Test Apache `.htaccess` jika tersedia.
   - Test mode fallback `.php` route.
   - Test fresh install dari SQL import.

### Acceptance Checklist untuk Developer Agents

- Aplikasi berjalan dengan PHP 8.1+ dan MySQL/MariaDB.
- Tidak ada dependency Node/build wajib untuk menjalankan MVP.
- Admin bisa login/logout.
- Admin bisa mengubah password.
- Admin bisa edit profil bisnis.
- Admin bisa CRUD kategori.
- Admin bisa CRUD produk/layanan.
- Admin bisa upload logo/hero/item image dengan validasi.
- Admin bisa pilih salah satu dari minimal 3 template.
- Public site menampilkan konten sesuai database dan template aktif.
- CTA WhatsApp terbentuk benar.
- Social links dan maps tampil jika diisi.
- Semua form POST admin memakai CSRF.
- Semua query memakai prepared statements.
- Output user-generated content di-escape.
- Folder upload tidak mengeksekusi PHP.
- Dokumentasi cPanel tersedia dan bisa diikuti dari fresh hosting.

### Docs to Write

1. `README.md`
   - Ringkasan produk.
   - Requirement server.
   - Cara menjalankan lokal.
   - Cara deploy singkat.

2. `docs/installation.md`
   - Setup database.
   - Import SQL.
   - Konfigurasi `config.php`.
   - Login admin awal.

3. `docs/hosting-cpanel.md`
   - Langkah detail di cPanel.
   - File Manager/FTP.
   - phpMyAdmin import.
   - SSL/AutoSSL.
   - Permission upload.
   - Troubleshooting umum.

4. `docs/admin-user-guide.md`
   - Cara login.
   - Edit profil bisnis.
   - Tambah produk/layanan.
   - Pilih template.
   - Preview website.
   - Ubah password.

5. `docs/template-development.md`
   - Struktur folder template.
   - Format `template.json`.
   - Kontrak data template.
   - Cara membuat template baru.

6. `docs/backup-restore.md`
   - Backup database.
   - Backup uploads.
   - Restore di hosting baru.

7. `docs/troubleshooting.md`
   - Error koneksi database.
   - Upload gagal.
   - Blank page/PHP error.
   - Rewrite tidak jalan.
   - Permission folder.

### Ownership Boundaries untuk Agent Berikutnya

- Backend developer:
  - Core PHP structure, database, auth, admin handlers, upload validation, SQL scripts.

- Frontend developer:
  - Admin UI sederhana, 3 public templates, responsive CSS, template preview cards.

- QA reviewer:
  - Fresh install test, security smoke test, form validation test, upload test, template switching test, shared-hosting compatibility review.

- Documentation agent/product agent:
  - cPanel guide, admin user guide, backup/restore, template development guide.

### Non-Goals yang Harus Dijaga Saat Implementasi

- Jangan menambahkan checkout/cart/payment gateway.
- Jangan membuat multi-tenant SaaS.
- Jangan menambahkan role permission kompleks.
- Jangan mewajibkan Composer/Node/Docker untuk produksi shared hosting.
- Jangan mengubah MVP menjadi WordPress clone.
- Jangan membuat custom domain automation.

### Definition of Done Arsitektur MVP

MVP teknis dianggap siap dibangun bila developer agents bisa mulai dari dokumen ini untuk membuat:

- skema database,
- struktur folder,
- admin login,
- editor konten,
- sistem template,
- public renderer,
- dan dokumentasi deployment cPanel

Tanpa perlu keputusan arsitektur besar tambahan selain open questions yang bersifat product/detail scope.
