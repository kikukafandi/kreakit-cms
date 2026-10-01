# KreaKit CMS

CMS native PHP ringan untuk website/katalog UMKM. Fondasi ini mencakup autentikasi admin, admin panel konten, template publik, dan installer wizard untuk shared hosting/cPanel.

## Requirement

- PHP 8.1+
- MySQL 5.7+ atau MariaDB 10.3+
- Apache/cPanel shared hosting kompatibel
- Tidak wajib Composer, Node, atau Docker

## Setup via Installer

1. Upload project ke hosting dan arahkan document root ke folder `public` jika memungkinkan.
2. Buat database dan user MySQL dari cPanel/MySQL Wizard, lalu beri privilege untuk database tersebut.
3. Buka `/install/index.php`.
4. Pastikan requirement check hijau.
5. Isi credential database, klik **Test Koneksi**, lalu isi admin pertama dan klik **Jalankan Install**.
6. Installer akan membuat `config/config.php`, menjalankan `database/schema.sql` + `database/seed.sql`, membuat/menimpa admin pertama dengan hash password baru, lalu membuat `storage/installed.lock`.

Advanced create database hanya perlu dicentang jika user database memang punya privilege `CREATE DATABASE`. Pada shared hosting biasa, biarkan tidak dicentang dan buat database dari cPanel.

Setelah install, `/install/index.php` akan menolak berjalan selama `storage/installed.lock` ada. Hapus lock hanya jika sengaja ingin install ulang setelah backup database/file.

## Setup Manual

1. Import `database/schema.sql` ke database kosong.
2. Import `database/seed.sql` untuk data demo dan 3 template awal.
3. Salin `config/config.example.php` menjadi `config/config.php`.
4. Isi kredensial database di `config/config.php` dan set `database.enabled` menjadi `true`.
5. Jalankan lokal: `php -S localhost:8000 -t public`.

Admin seed awal untuk setup manual: `admin@example.test` / `ChangeMe123!`. Wajib ubah setelah login pertama melalui `/admin/password.php`.

## Mengisi Website

Setelah login admin, isi berurutan:

1. **Profil Bisnis**: nama, deskripsi, WhatsApp, alamat, logo, foto utama, warna, dan sosial media.
2. **Template**: pilih Kuliner Simple, Jasa Lokal, atau Produk Katalog. Bisa diganti kapan saja tanpa kehilangan data.
3. **Kategori** dan **Produk/Layanan**: isi katalog, harga, dan foto.
4. **Konten Halaman**: section company profile (tentang kami, angka pencapaian, keunggulan, galeri, testimoni, FAQ, jam buka). Bagian yang dikosongkan tidak tampil di website.

Dashboard menampilkan checklist kelengkapan website supaya tidak ada yang terlewat.

## Lisensi Aset

- Foto demo di `public/uploads/2000/01/` berlisensi CC0 (public domain), daftar sumbernya ada di `CREDITS.txt` pada folder yang sama.
- Ikon: Phosphor Icons (MIT), lihat `templates/_shared/LICENSE-phosphor.txt`.
- Font: Bricolage Grotesque, Manrope, dan Outfit (SIL Open Font License), lihat `public/assets/fonts/LICENSE.txt`.

## Butuh Bantuan atau Fitur Tambahan?

Butuh blog, halaman tambahan, desain custom, atau dipasangkan sekalian ke hosting? Hubungi tim KreaByte di [kreabyte.com](https://kreabyte.com).
