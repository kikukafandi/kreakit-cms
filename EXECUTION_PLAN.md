# Execution Plan

## Milestones

### M0 — Persiapan Eksekusi Lokal
Tujuan: menyiapkan alur kerja aman sebelum coding dimulai.
- Scope tetap: 1 website per instalasi, 1 admin user, native PHP + MySQL/MariaDB, shared hosting/cPanel compatible.
- Tidak ada checkout/payment, multi-tenant SaaS, permission kompleks, Composer/Node/Docker wajib produksi.
- Setiap agent coding memakai branch/worktree sendiri di bawah `/workspace/worktrees` saat implementasi dimulai.

### M1 — Fondasi Backend & Database
Tujuan: aplikasi bisa boot, terkoneksi DB, punya schema/seed, session, CSRF, auth, dan helper dasar.

### M2 — Admin CMS Content Management
Tujuan: admin bisa login, mengedit profil bisnis, kategori, produk/layanan, kontak/sosial, settings dasar, dan upload gambar.

### M3 — Template System & Public Renderer
Tujuan: public site membaca data dari database, memilih template aktif, dan merender katalog UMKM melalui kontrak data standar.

### M4 — UI Admin & 3 Template Publik
Tujuan: admin panel usable oleh non-teknis, dan 3 template awal siap demo: `kuliner-simple`, `jasa-lokal`, `produk-katalog`.

### M5 — Dokumentasi Install, cPanel, Admin, Backup
Tujuan: MVP dapat dijalankan lokal dan dipasang di shared hosting dengan panduan tertulis.

### M6 — QA, Security Smoke Test, Fresh Install Test
Tujuan: verifikasi end-to-end sebelum human review dan integrasi GitHub nanti.

## Tasks

### KREACMS-PM-001
- **Task ID:** KREACMS-PM-001
- **Task Name:** MVP execution plan and task breakdown
- **Objective:** Mengubah project brief dan technical design menjadi rencana eksekusi atomic dengan owner, dependensi, dan parallelization jelas.
- **Assigned Agent:** Project Manager Agent
- **Thread ID:** 4
- **Priority:** HIGH
- **Dependencies:** `PROJECT_BRIEF.md`, `TECHNICAL_DESIGN.md`
- **Status:** DONE
- **Acceptance Criteria:**
  - Milestone MVP jelas.
  - Task punya satu owner.
  - Dependensi eksplisit.
  - QA dan dokumentasi masuk rencana.
  - Scope tidak melebar dari native PHP shared-hosting CMS.
- **Expected Output:** `EXECUTION_PLAN.md`

### KREACMS-BE-001
- **Task ID:** KREACMS-BE-001
- **Task Name:** Project skeleton, bootstrap, config loader
- **Objective:** Membuat struktur folder aplikasi sesuai technical design, bootstrap awal, config example, autoload/manual include ringan, helper path/base URL, dan entrypoint public/admin dasar.
- **Assigned Agent:** Backend Developer
- **Thread ID:** 8
- **Priority:** HIGH
- **Dependencies:** KREACMS-PM-001
- **Status:** TODO
- **Acceptance Criteria:**
  - Struktur folder minimal tersedia: `app/`, `config/`, `database/`, `docs/`, `public/`, `templates/`, `storage/`.
  - `config/config.example.php` tersedia tanpa secret nyata.
  - `public/index.php` dan `public/admin/index.php` bisa memuat bootstrap tanpa error.
  - Tidak ada Composer/Node/Docker wajib untuk produksi.
- **Expected Output:** Skeleton aplikasi native PHP siap dikembangkan.

### KREACMS-BE-002
- **Task ID:** KREACMS-BE-002
- **Task Name:** Database schema and seed data
- **Objective:** Membuat SQL schema dan seed untuk admin awal, profil bisnis sample, kategori, item sample, settings, social links, dan 3 template awal.
- **Assigned Agent:** Backend Developer
- **Thread ID:** 8
- **Priority:** HIGH
- **Dependencies:** KREACMS-BE-001
- **Status:** TODO
- **Acceptance Criteria:**
  - `database/schema.sql` memakai InnoDB dan `utf8mb4`.
  - Tabel minimal: `admins`, `business_profiles`, `categories`, `items`, `templates`, `settings`, `social_links`, `media_files`.
  - `database/seed.sql` membuat 1 admin awal dengan password hash, 3 template, dan sample data demo.
  - Script bisa diimpor via phpMyAdmin/MySQL tanpa migration tool.
- **Expected Output:** SQL import manual untuk fresh install MVP.

### KREACMS-BE-003
- **Task ID:** KREACMS-BE-003
- **Task Name:** Core database, session, CSRF, validation helpers
- **Objective:** Membuat wrapper PDO, helper session, flash message, CSRF token, escaping, redirect, slug, URL, dan formatting dasar.
- **Assigned Agent:** Backend Developer
- **Thread ID:** 8
- **Priority:** HIGH
- **Dependencies:** KREACMS-BE-001, KREACMS-BE-002
- **Status:** TODO
- **Acceptance Criteria:**
  - Semua query memakai prepared statements.
  - CSRF token bisa dibuat dan diverifikasi.
  - Output helper memakai `htmlspecialchars()`.
  - Session cookie disetel aman sesuai kemampuan HTTPS/local.
  - Error DB tidak membocorkan secret ke UI publik.
- **Expected Output:** Core backend reusable untuk admin dan public renderer.

### KREACMS-BE-004
- **Task ID:** KREACMS-BE-004
- **Task Name:** Admin authentication and password update
- **Objective:** Implement login/logout admin, guard halaman admin, session regeneration, rate limit sederhana, dan ubah password.
- **Assigned Agent:** Backend Developer
- **Thread ID:** 8
- **Priority:** HIGH
- **Dependencies:** KREACMS-BE-003
- **Status:** TODO
- **Acceptance Criteria:**
  - Admin bisa login dengan email/password dari seed.
  - Password diverifikasi dengan `password_verify()`.
  - Login sukses melakukan regenerate session ID.
  - Logout menghancurkan session.
  - Semua halaman admin terlindungi `require_admin()`.
  - Form login/password memakai CSRF dan pesan gagal generik.
- **Expected Output:** Auth admin MVP aman dan usable.

### KREACMS-BE-005
- **Task ID:** KREACMS-BE-005
- **Task Name:** Business profile, settings, contact, social handlers
- **Objective:** Membuat handler/form processing untuk profil bisnis, kontak WhatsApp, alamat/maps, social links, warna dasar, mode katalog, dan SEO sederhana jika field sudah tersedia.
- **Assigned Agent:** Backend Developer
- **Thread ID:** 8
- **Priority:** HIGH
- **Dependencies:** KREACMS-BE-004
- **Status:** TODO
- **Acceptance Criteria:**
  - Admin bisa menyimpan nama bisnis, tagline, deskripsi, WhatsApp, alamat, maps URL, email/phone.
  - Admin bisa CRUD social links sederhana.
  - URL divalidasi `http/https`.
  - Nomor WhatsApp disanitasi dan kompatibel dengan `wa.me`.
  - Semua form POST memakai CSRF.
- **Expected Output:** Handler konten bisnis dan kontak siap dipakai UI admin/public renderer.

### KREACMS-BE-006
- **Task ID:** KREACMS-BE-006
- **Task Name:** Category CRUD backend
- **Objective:** Membuat backend kategori produk/layanan dengan slug, sort order, active flag, create/update/delete aman.
- **Assigned Agent:** Backend Developer
- **Thread ID:** 8
- **Priority:** HIGH
- **Dependencies:** KREACMS-BE-004
- **Status:** TODO
- **Acceptance Criteria:**
  - Admin bisa create, list, edit, deactivate/delete kategori.
  - Slug unik dan aman.
  - Delete kategori tidak merusak item; item menjadi uncategorized atau dicegah sesuai schema.
  - Semua operasi POST memakai CSRF dan prepared statements.
- **Expected Output:** Backend kategori siap dihubungkan ke UI.

### KREACMS-BE-007
- **Task ID:** KREACMS-BE-007
- **Task Name:** Item/product-service CRUD backend
- **Objective:** Membuat backend CRUD produk/layanan dengan kategori, harga, price label, image path, featured, active, sort order, dan pesan WhatsApp opsional.
- **Assigned Agent:** Backend Developer
- **Thread ID:** 8
- **Priority:** HIGH
- **Dependencies:** KREACMS-BE-004, KREACMS-BE-006
- **Status:** TODO
- **Acceptance Criteria:**
  - Admin bisa create/list/edit/delete atau deactivate item.
  - Field harga angka dan price label tervalidasi.
  - Slug unik.
  - Item dapat dikaitkan ke kategori atau tanpa kategori.
  - Semua operasi memakai prepared statements dan CSRF.
- **Expected Output:** Backend katalog produk/layanan siap untuk UI dan public site.

### KREACMS-BE-008
- **Task ID:** KREACMS-BE-008
- **Task Name:** Secure media upload backend
- **Objective:** Membuat upload logo, hero image, dan item image dengan validasi ketat untuk shared hosting.
- **Assigned Agent:** Backend Developer
- **Thread ID:** 8
- **Priority:** HIGH
- **Dependencies:** KREACMS-BE-003, KREACMS-BE-005, KREACMS-BE-007
- **Status:** TODO
- **Acceptance Criteria:**
  - Allowlist ekstensi: jpg, jpeg, png, webp.
  - MIME divalidasi via `finfo_file()`.
  - Batas ukuran default 2 MB.
  - File diganti nama unik dan disimpan di `public/uploads/YYYY/MM/`.
  - `.htaccess` upload protection tersedia.
  - Metadata tersimpan di `media_files` bila tabel tersedia.
  - Upload PHP/SVG ditolak.
- **Expected Output:** Upload image lokal aman untuk demo dan shared hosting.

### KREACMS-BE-009
- **Task ID:** KREACMS-BE-009
- **Task Name:** Template registry and active template backend
- **Objective:** Membaca template metadata, menyimpan template aktif, dan menyediakan data template untuk admin picker dan public renderer.
- **Assigned Agent:** Backend Developer
- **Thread ID:** 8
- **Priority:** HIGH
- **Dependencies:** KREACMS-BE-002, KREACMS-BE-003
- **Status:** TODO
- **Acceptance Criteria:**
  - 3 template seed tersedia di tabel `templates`.
  - Active template disimpan di `settings.active_template_slug`.
  - Template slug divalidasi terhadap template aktif/tersedia.
  - Template tidak query database langsung.
- **Expected Output:** Backend pemilihan template dan kontrak data awal.

### KREACMS-BE-010
- **Task ID:** KREACMS-BE-010
- **Task Name:** Public renderer and preview flow
- **Objective:** Membuat public renderer yang mengambil data standar dan mengirimnya ke template aktif; preview MVP membuka public site setelah data disimpan.
- **Assigned Agent:** Backend Developer
- **Thread ID:** 8
- **Priority:** HIGH
- **Dependencies:** KREACMS-BE-005, KREACMS-BE-007, KREACMS-BE-009
- **Status:** TODO
- **Acceptance Criteria:**
  - `/` merender template aktif dengan `$business`, `$items`, `$categories`, `$socialLinks`, `$settings`, `$themeSettings`.
  - CTA WhatsApp terbentuk benar.
  - Social links dan maps tampil hanya jika diisi.
  - User-generated content di-escape.
  - Fallback aman jika template hilang/tidak aktif.
- **Expected Output:** Public website/katalog siap dihubungkan dengan template frontend.

### KREACMS-FE-001
- **Task ID:** KREACMS-FE-001
- **Task Name:** Admin UI layout and navigation
- **Objective:** Membuat layout admin sederhana, Bahasa Indonesia, responsive, dengan navigasi Dashboard, Profil Bisnis, Template, Produk/Layanan, Kategori, Kontak/Sosial, Pengaturan Admin.
- **Assigned Agent:** Frontend Developer
- **Thread ID:** 7
- **Priority:** HIGH
- **Dependencies:** KREACMS-BE-001, kontrak route sementara dari Backend
- **Status:** TODO
- **Acceptance Criteria:**
  - UI admin dapat dipakai tanpa build step.
  - CSS vanilla/Bootstrap ringan diperbolehkan, tetapi tidak wajib Node.
  - Navigasi konsisten dan jelas untuk user non-teknis.
  - Flash message, error form, dan tombol preview punya pola visual konsisten.
- **Expected Output:** Admin shell dan style dasar.

### KREACMS-FE-002
- **Task ID:** KREACMS-FE-002
- **Task Name:** Admin forms for profile, settings, category, item, social
- **Objective:** Membuat markup form admin untuk semua fitur konten dan menghubungkannya ke handler backend.
- **Assigned Agent:** Frontend Developer
- **Thread ID:** 7
- **Priority:** HIGH
- **Dependencies:** KREACMS-FE-001, KREACMS-BE-005, KREACMS-BE-006, KREACMS-BE-007
- **Status:** TODO
- **Acceptance Criteria:**
  - Form profil bisnis mudah dipahami.
  - Form kategori dan item mendukung create/edit/list.
  - Field wajib diberi label dan validasi UI dasar.
  - Upload field tersedia untuk logo, hero, dan gambar item.
  - Tidak ada fitur checkout/cart/payment.
- **Expected Output:** Admin CMS usable untuk mengisi data demo.

### KREACMS-FE-003
- **Task ID:** KREACMS-FE-003
- **Task Name:** Template picker UI
- **Objective:** Membuat halaman pilih template dengan preview card untuk 3 template awal dan status template aktif.
- **Assigned Agent:** Frontend Developer
- **Thread ID:** 7
- **Priority:** HIGH
- **Dependencies:** KREACMS-FE-001, KREACMS-BE-009
- **Status:** TODO
- **Acceptance Criteria:**
  - Admin melihat nama, deskripsi, niche, preview image tiap template.
  - Admin bisa memilih template aktif.
  - Template aktif ditandai jelas.
  - Jika preview image belum ada, ada fallback visual.
- **Expected Output:** UI template selection siap demo.

### KREACMS-FE-004
- **Task ID:** KREACMS-FE-004
- **Task Name:** Public template `kuliner-simple`
- **Objective:** Membuat template publik untuk bisnis makanan/minuman dengan fokus menu, hero, harga, dan CTA WhatsApp.
- **Assigned Agent:** Frontend Developer
- **Thread ID:** 7
- **Priority:** HIGH
- **Dependencies:** KREACMS-BE-010, kontrak data template
- **Status:** TODO
- **Acceptance Criteria:**
  - Folder `templates/kuliner-simple` berisi `template.json`, `index.php`, `style.css`, `preview.png` atau placeholder.
  - Menampilkan nama bisnis, deskripsi, logo/hero, kategori, item, harga, CTA WhatsApp, alamat/maps, social links.
  - Responsive mobile-first.
  - Tidak query database langsung.
- **Expected Output:** Template kuliner siap demo.

### KREACMS-FE-005
- **Task ID:** KREACMS-FE-005
- **Task Name:** Public template `jasa-lokal`
- **Objective:** Membuat template publik untuk jasa lokal seperti laundry, salon, barbershop, klinik kecil, service AC.
- **Assigned Agent:** Frontend Developer
- **Thread ID:** 7
- **Priority:** HIGH
- **Dependencies:** KREACMS-BE-010, kontrak data template
- **Status:** TODO
- **Acceptance Criteria:**
  - Folder `templates/jasa-lokal` lengkap sesuai kontrak.
  - Fokus pada benefit layanan, daftar layanan, kontak cepat, maps, WhatsApp.
  - Responsive dan mudah dibaca.
  - Tidak query database langsung.
- **Expected Output:** Template jasa lokal siap demo.

### KREACMS-FE-006
- **Task ID:** KREACMS-FE-006
- **Task Name:** Public template `produk-katalog`
- **Objective:** Membuat template publik untuk katalog produk fisik/seller online dengan grid item dan CTA WhatsApp per item.
- **Assigned Agent:** Frontend Developer
- **Thread ID:** 7
- **Priority:** HIGH
- **Dependencies:** KREACMS-BE-010, kontrak data template
- **Status:** TODO
- **Acceptance Criteria:**
  - Folder `templates/produk-katalog` lengkap sesuai kontrak.
  - Menampilkan grid produk, kategori, harga/label harga, gambar, CTA WhatsApp.
  - Responsive dan ringan.
  - Tidak query database langsung.
- **Expected Output:** Template katalog produk siap demo.

### KREACMS-FE-007
- **Task ID:** KREACMS-FE-007
- **Task Name:** Public responsive polish and accessibility pass
- **Objective:** Merapikan tampilan admin/public agar demo layak jual dan mudah dipakai UMKM.
- **Assigned Agent:** Frontend Developer
- **Thread ID:** 7
- **Priority:** MEDIUM
- **Dependencies:** KREACMS-FE-002, KREACMS-FE-004, KREACMS-FE-005, KREACMS-FE-006
- **Status:** TODO
- **Acceptance Criteria:**
  - Tampilan mobile tidak rusak di 360px–430px width.
  - Form label jelas dan tombol utama konsisten.
  - Kontras teks cukup untuk penggunaan umum.
  - Empty state tersedia untuk kategori/item/social kosong.
- **Expected Output:** UI MVP lebih siap demo ke calon pembeli.

### KREACMS-DOC-001
- **Task ID:** KREACMS-DOC-001
- **Task Name:** README and local run guide
- **Objective:** Menulis README dengan ringkasan produk, requirement server, setup lokal, import database, dan cara menjalankan PHP built-in server.
- **Assigned Agent:** Documentation Agent
- **Thread ID:** 4
- **Priority:** HIGH
- **Dependencies:** KREACMS-BE-001, KREACMS-BE-002
- **Status:** TODO
- **Acceptance Criteria:**
  - Requirement PHP 8.1+, MySQL 5.7+/MariaDB 10.3+ jelas.
  - Instruksi `php -S localhost:8000 -t public` tersedia.
  - Instruksi import `schema.sql` dan `seed.sql` tersedia.
  - Menekankan tidak ada dependency produksi Composer/Node/Docker wajib.
- **Expected Output:** `README.md`

### KREACMS-DOC-002
- **Task ID:** KREACMS-DOC-002
- **Task Name:** Installation and cPanel hosting guide
- **Objective:** Menulis panduan install dan deployment shared hosting/cPanel, termasuk phpMyAdmin, config, permission upload, SSL, dan fallback route `.php`.
- **Assigned Agent:** Documentation Agent
- **Thread ID:** 4
- **Priority:** HIGH
- **Dependencies:** KREACMS-DOC-001, KREACMS-BE-008, KREACMS-BE-010
- **Status:** TODO
- **Acceptance Criteria:**
  - Langkah fresh install dapat diikuti dari hosting kosong.
  - Menjelaskan opsi document root `public/` dan fallback jika semua harus di `public_html`.
  - Menjelaskan permission upload dan SSL/AutoSSL.
  - Tidak meminta Docker/Node/Composer untuk produksi.
- **Expected Output:** `docs/installation.md`, `docs/hosting-cpanel.md`

### KREACMS-DOC-003
- **Task ID:** KREACMS-DOC-003
- **Task Name:** Admin user guide
- **Objective:** Menulis panduan penggunaan admin untuk user non-teknis.
- **Assigned Agent:** Documentation Agent
- **Thread ID:** 4
- **Priority:** MEDIUM
- **Dependencies:** KREACMS-FE-002, KREACMS-FE-003
- **Status:** TODO
- **Acceptance Criteria:**
  - Menjelaskan login/logout, ubah password, edit profil, tambah produk/layanan, pilih template, preview website.
  - Bahasa Indonesia sederhana.
  - Menyebut wajib ubah password default setelah login pertama.
- **Expected Output:** `docs/admin-user-guide.md`

### KREACMS-DOC-004
- **Task ID:** KREACMS-DOC-004
- **Task Name:** Template development and backup/restore docs
- **Objective:** Menulis panduan membuat template baru dan backup/restore data client.
- **Assigned Agent:** Documentation Agent
- **Thread ID:** 4
- **Priority:** MEDIUM
- **Dependencies:** KREACMS-BE-009, KREACMS-FE-004, KREACMS-FE-005, KREACMS-FE-006
- **Status:** TODO
- **Acceptance Criteria:**
  - Menjelaskan struktur folder template, `template.json`, kontrak data, dan larangan query DB langsung di template.
  - Menjelaskan backup database, uploads, dan config.
  - Menjelaskan restore di hosting baru.
- **Expected Output:** `docs/template-development.md`, `docs/backup-restore.md`, `docs/troubleshooting.md`

### KREACMS-QA-001
- **Task ID:** KREACMS-QA-001
- **Task Name:** Backend security and code review smoke test
- **Objective:** Review implementasi backend untuk prepared statements, CSRF, session security, escaping, upload safety, dan secret handling.
- **Assigned Agent:** QA/Reviewer
- **Thread ID:** 9
- **Priority:** HIGH
- **Dependencies:** KREACMS-BE-004, KREACMS-BE-005, KREACMS-BE-006, KREACMS-BE-007, KREACMS-BE-008
- **Status:** TODO
- **Acceptance Criteria:**
  - Tidak ada query input user tanpa prepared statements.
  - Semua form POST admin punya CSRF.
  - Upload PHP/SVG ditolak.
  - Password tidak disimpan plaintext.
  - Tidak ada credential nyata di repo.
  - Temuan dicatat dengan severity dan rekomendasi.
- **Expected Output:** QA security review report.

### KREACMS-QA-002
- **Task ID:** KREACMS-QA-002
- **Task Name:** Fresh install and local run verification
- **Objective:** Menguji fresh install dari SQL import dan menjalankan aplikasi lokal dengan PHP built-in server.
- **Assigned Agent:** QA/Reviewer
- **Thread ID:** 9
- **Priority:** HIGH
- **Dependencies:** KREACMS-BE-002, KREACMS-BE-010, KREACMS-DOC-001
- **Status:** TODO
- **Acceptance Criteria:**
  - Database fresh bisa dibuat dari `schema.sql` + `seed.sql`.
  - Aplikasi berjalan dengan `php -S localhost:8000 -t public`.
  - Admin bisa login/logout.
  - Public URL bisa dibuka.
  - Instruksi README sesuai hasil test nyata.
- **Expected Output:** Fresh install test report.

### KREACMS-QA-003
- **Task ID:** KREACMS-QA-003
- **Task Name:** Admin content flow QA
- **Objective:** Menguji alur edit konten utama dari admin sampai tampil di public site.
- **Assigned Agent:** QA/Reviewer
- **Thread ID:** 9
- **Priority:** HIGH
- **Dependencies:** KREACMS-FE-002, KREACMS-BE-010
- **Status:** TODO
- **Acceptance Criteria:**
  - Edit profil bisnis tampil di public site.
  - CRUD kategori dan produk/layanan bekerja.
  - Upload logo/hero/item image bekerja dan file tampil.
  - Social links, maps, dan CTA WhatsApp bekerja.
  - Preview membuka website dengan data terbaru setelah save.
- **Expected Output:** Admin flow QA report dengan bug list bila ada.

### KREACMS-QA-004
- **Task ID:** KREACMS-QA-004
- **Task Name:** Template switching and responsive QA
- **Objective:** Menguji 3 template awal, switching template, responsive layout, dan kontrak data template.
- **Assigned Agent:** QA/Reviewer
- **Thread ID:** 9
- **Priority:** HIGH
- **Dependencies:** KREACMS-FE-003, KREACMS-FE-004, KREACMS-FE-005, KREACMS-FE-006, KREACMS-BE-010
- **Status:** TODO
- **Acceptance Criteria:**
  - Ketiga template bisa dipilih dari admin.
  - Public site berubah sesuai template aktif.
  - Semua template menampilkan data wajib.
  - Layout mobile tidak rusak pada width kecil.
  - Template tidak melakukan query database langsung.
- **Expected Output:** Template QA report.

### KREACMS-QA-005
- **Task ID:** KREACMS-QA-005
- **Task Name:** Documentation validation and release readiness
- **Objective:** Memvalidasi dokumentasi terhadap implementasi nyata dan menyusun status kesiapan MVP untuk human review.
- **Assigned Agent:** QA/Reviewer
- **Thread ID:** 9
- **Priority:** HIGH
- **Dependencies:** KREACMS-QA-001, KREACMS-QA-002, KREACMS-QA-003, KREACMS-QA-004, KREACMS-DOC-002, KREACMS-DOC-003, KREACMS-DOC-004
- **Status:** TODO
- **Acceptance Criteria:**
  - Panduan install diuji minimal secara lokal.
  - Checklist acceptance developer terpenuhi atau gap dicatat.
  - Risiko/blocker tersisa jelas.
  - Tidak ada rekomendasi merge/deploy tanpa approval founder.
- **Expected Output:** Final MVP readiness report.

## Safe Parallel Work

### Bisa berjalan paralel setelah M0
- Backend mulai KREACMS-BE-001 sampai KREACMS-BE-003.
- Documentation mulai draft KREACMS-DOC-001 berdasarkan technical design.
- Frontend dapat menyiapkan desain admin shell KREACMS-FE-001 setelah skeleton/route sementara tersedia.

### Bisa berjalan paralel setelah core backend stabil
- Backend mengerjakan KREACMS-BE-005, BE-006, BE-007 secara berurutan ringan; kategori harus siap sebelum item final.
- Frontend mengerjakan admin forms mengikuti kontrak route dari Backend.
- Documentation memperbarui README dan installation sesuai struktur nyata.

### Bisa berjalan paralel setelah public renderer contract final
- Frontend mengerjakan 3 template publik secara paralel bila memakai worktree/branch terpisah atau koordinasi file jelas.
- Backend menyelesaikan template registry/public renderer.
- Documentation menulis template development guide.

### Harus serial / tidak boleh paralel sembarangan
- QA final hanya setelah fitur terkait selesai.
- Frontend dan Backend tidak boleh mengedit working tree yang sama secara bersamaan.
- Template contracts harus disepakati sebelum 3 template dikerjakan penuh.
- Dokumentasi cPanel final harus divalidasi setelah implementasi nyata, bukan hanya dari desain.

## Risks

1. **Scope creep ke SaaS/ecommerce**
   - Mitigasi: semua task menolak checkout/payment, multi-tenant, permission kompleks, subscription billing.
2. **Variasi shared hosting/cPanel**
   - Mitigasi: route `.php` fallback wajib, dependency minim, dokumentasi dua mode document root.
3. **Upload security lemah**
   - Mitigasi: allowlist ekstensi, MIME validation, rename file, `.htaccess` disable PHP execution, batas 2 MB.
4. **Template terlalu bebas dan sulit maintain**
   - Mitigasi: template hanya menerima kontrak data; query DB hanya di renderer/controller.
5. **Default admin credential dipakai produksi**
   - Mitigasi: dokumentasi wajib ubah password, admin settings password update masuk MUST.
6. **Dokumentasi tidak sesuai implementasi**
   - Mitigasi: QA dokumentasi dilakukan dari fresh install nyata.
7. **Desain template kurang menjual untuk demo**
   - Mitigasi: frontend polish dan sample data harus cukup rapi untuk 5–10 calon pembeli awal.

## Blockers

- Belum ada repository/working tree implementasi yang dibuat khusus untuk coding; eksekusi berikutnya perlu menetapkan lokasi repo/worktree lokal.
- Open product detail yang belum final tetapi tidak menghambat MVP:
  - Model hosting utama: client hosting sendiri, dikelola KreaByte, atau keduanya.
  - Detail produk perlu halaman sendiri atau cukup one-page katalog. Rencana MVP: cukup one-page katalog.
  - SEO settings sederhana masuk sebagai field ringan bila tidak memperlambat core CMS.
- GitHub integration ditunda sesuai keputusan founder; semua pekerjaan awal tetap lokal.

## Current Progress

- Project brief selesai dan scope MVP jelas.
- Technical design selesai: native PHP + MySQL/MariaDB, 1 website per instalasi, 1 admin, shared hosting/cPanel compatible.
- Execution plan dan task breakdown selesai di dokumen ini.
- Status implementasi kode: belum dimulai.

## Next Actions

1. Founder/Coordinator menyetujui execution plan ini.
2. Buat repo/worktree lokal implementasi sesuai aturan workspace.
3. Assign Backend Developer untuk KREACMS-BE-001 sampai KREACMS-BE-003.
4. Assign Frontend Developer untuk KREACMS-FE-001 setelah route/skeleton awal tersedia.
5. Assign Documentation Agent untuk draft README dan installation guide sejak awal.
6. Jalankan QA bertahap setelah milestone fitur selesai; jangan tunggu semua selesai untuk review security dasar.
