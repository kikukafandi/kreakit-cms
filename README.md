# KreaKit CMS

CMS native PHP ringan untuk website/katalog UMKM. Fondasi ini mencakup autentikasi admin dasar (login/logout, proteksi halaman admin, dan ubah password), tetapi belum mencakup CRUD konten penuh; task berikutnya akan menambahkan fitur tersebut.

## Requirement

- PHP 8.1+
- MySQL 5.7+ atau MariaDB 10.3+
- Apache/cPanel shared hosting kompatibel
- Tidak wajib Composer, Node, atau Docker

## Setup Singkat

1. Import `database/schema.sql` ke database kosong.
2. Import `database/seed.sql` untuk data demo dan 3 template awal.
3. Salin `config/config.example.php` menjadi `config/config.php`.
4. Isi kredensial database di `config/config.php` dan set `database.enabled` menjadi `true`.
5. Jalankan lokal: `php -S localhost:8000 -t public`.

Admin seed awal: `admin@example.test` / `ChangeMe123!`. Wajib ubah setelah login pertama melalui `/admin/password.php`.
