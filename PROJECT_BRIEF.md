# Project Brief — KreaKit CMS

## Project Summary

KreaKit CMS adalah produk digital KreaByte berupa CMS sederhana untuk membuat website/katalog UMKM dengan pilihan desain template yang sudah tersedia. User dapat memilih template, mengedit konten sendiri, lalu menghasilkan website/katalog siap pakai.

## Business Goals

- Membuat produk digital yang bisa dijual sebagai paket siap pakai.
- Memudahkan UMKM punya website/katalog tanpa proses custom development panjang.
- Menjadi pintu masuk untuk upsell jasa setup, custom design, domain, hosting, dan maintenance.
- Membuat aset produk reusable untuk KreaByte.

## Target Users

- UMKM lokal.
- Pemilik bisnis kecil.
- Seller online.
- Jasa lokal seperti laundry, barbershop, klinik, kuliner, salon, service AC, properti kecil.

## In Scope — MVP

- CMS sederhana untuk edit konten website/katalog.
- Pilihan desain template yang sudah disediakan.
- Konten dapat diedit sendiri oleh user.
- Minimal konten yang bisa diedit:
  - nama bisnis
  - deskripsi bisnis
  - logo/gambar utama
  - daftar produk/layanan
  - harga
  - kontak WhatsApp
  - alamat/link maps
  - sosial media
- Preview website/katalog.
- Output website/katalog siap digunakan.
- Template awal untuk beberapa niche UMKM.

## Out of Scope — MVP

- Payment gateway penuh.
- Checkout ecommerce kompleks.
- Multi-user/team permission kompleks.
- Inventory real-time.
- POS system.
- CRM lengkap.
- Custom domain otomatis jika belum disetujui.
- Subscription billing jika fokus awal masih jual putus.

## Functional Requirements

- User bisa memilih template desain.
- User bisa mengisi dan mengedit konten bisnis.
- User bisa melihat preview hasil.
- User bisa menyimpan perubahan.
- Sistem menampilkan website/katalog berdasarkan data konten user.
- Admin/KreaByte bisa menambah template baru.
- Template harus reusable untuk banyak bisnis.

## Non-Functional Requirements

- Mudah digunakan oleh non-teknis.
- Cepat dibuat sebagai MVP.
- Aman untuk data bisnis user.
- Struktur template mudah dikembangkan.
- Biaya hosting rendah.
- Maintenance rendah.

## Priorities

### MUST

- Template pilihan desain.
- Editor konten sederhana.
- Preview hasil.
- Website/katalog publik sederhana.
- CTA WhatsApp.
- Minimal 3 template awal.

### SHOULD

- Upload logo/gambar.
- Kategori produk/layanan.
- Pengaturan warna dasar.
- Panduan penggunaan.
- Demo template publik.

### COULD

- Export static site.
- Custom domain.
- SEO settings.
- Analytics sederhana.
- QR code katalog.
- Paket setup sekali bayar.

## Constraints

- Produk harus tetap simple.
- MVP jangan berubah menjadi SaaS besar.
- Fokus awal pada jual putus atau paket setup sekali bayar.
- Hindari fitur yang menambah support berat di awal.

## Success Criteria

- MVP bisa didemokan dengan minimal 3 template.
- User non-teknis bisa mengedit konten dasar sendiri.
- Website/katalog hasil edit bisa dibuka publik.
- Produk bisa dijelaskan dalam 1 kalimat: “Pilih desain, isi konten, website UMKM langsung jadi.”
- Bisa diuji ke 5–10 calon pembeli awal.

## Confirmed Decisions

- Produk yang dipilih adalah CMS sederhana dengan template desain siap pakai.
- Konten harus bisa diedit sendiri oleh user.
- Target awal adalah UMKM/bisnis kecil.
- Produk harus simple dibuat dan simple dijual.

## Assumptions

- KreaByte ingin menjual produk ini sebagai produk digital/paket siap pakai, bukan custom project penuh.
- Model bisnis awal bisa jual putus + upsell setup.
- MVP akan dibuat sebagai web app sederhana.

## Open Questions

- Apakah user harus login, atau cukup admin panel sederhana per client?
- Apakah hasil website di-host oleh KreaByte atau diexport/deploy terpisah?
- Apakah model jualnya benar-benar jual putus, atau paket setup sekali bayar dengan hosting tahunan?
- Niche template awal apa saja yang dipilih?
- Apakah perlu payment/checkout di MVP?

## Recommended Handoff

- Technical Architect Agent — Thread 6: desain arsitektur MVP CMS, data model, template system, deployment strategy.
- Project Manager Agent — Thread 4: setelah arsitektur awal jelas, pecah pekerjaan menjadi task frontend, backend, dan QA.
