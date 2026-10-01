-- KreaKit CMS seed data
-- Admin awal: admin@example.test / ChangeMe123!
-- Wajib ubah email dan password setelah install pertama.

SET NAMES utf8mb4;

INSERT INTO admins (id, name, email, password_hash, is_active)
VALUES
    (1, 'Admin KreaKit', 'admin@example.test', '$2y$12$05z7BW5YEU01r2Mm6P.yW.m9OBJ7aOQMcPZ3rHjm2BMbrRF/Klzo2', 1)
ON DUPLICATE KEY UPDATE
    name = VALUES(name),
    password_hash = VALUES(password_hash),
    is_active = VALUES(is_active);

INSERT INTO templates (slug, name, description, niche, preview_image, is_active)
VALUES
    ('kuliner-simple', 'Kuliner Simple', 'Template ringan untuk menu makanan/minuman dan CTA WhatsApp order.', 'kuliner', 'templates/kuliner-simple/preview.png', 1),
    ('jasa-lokal', 'Jasa Lokal', 'Template untuk laundry, salon, service AC, barbershop, dan jasa lokal lain.', 'jasa', 'templates/jasa-lokal/preview.png', 1),
    ('produk-katalog', 'Produk Katalog', 'Template grid katalog untuk seller online dan produk fisik.', 'katalog', 'templates/produk-katalog/preview.png', 1)
ON DUPLICATE KEY UPDATE
    name = VALUES(name),
    description = VALUES(description),
    niche = VALUES(niche),
    preview_image = VALUES(preview_image),
    is_active = VALUES(is_active);

INSERT INTO business_profiles (id, business_name, tagline, description, whatsapp_number, address, maps_url, email, phone)
VALUES
    (1, 'Dapur Nusantara', 'Masakan rumahan, rasa yang bikin kangen', 'Dimasak setiap pagi dari bahan segar pasar. Cocok untuk makan siang kantor, acara keluarga, sampai pesanan nasi box dadakan.', '6281234567890', 'Jl. Melati No. 10, Kebayoran Baru, Jakarta Selatan', 'https://maps.google.com/?q=Kebayoran+Baru+Jakarta', 'halo@dapurnusantara.id', '021-7654321')
ON DUPLICATE KEY UPDATE
    business_name = VALUES(business_name),
    tagline = VALUES(tagline),
    description = VALUES(description),
    whatsapp_number = VALUES(whatsapp_number),
    address = VALUES(address),
    maps_url = VALUES(maps_url),
    email = VALUES(email),
    phone = VALUES(phone);

INSERT INTO categories (id, name, slug, description, sort_order, is_active)
VALUES
    (1, 'Makanan Utama', 'makanan-utama', 'Menu nasi dan lauk andalan.', 10, 1),
    (2, 'Paket Hemat', 'paket-hemat', 'Paket untuk berdua, keluarga, dan kantor.', 20, 1),
    (3, 'Minuman & Camilan', 'minuman-camilan', 'Pelengkap makan siangmu.', 30, 1)
ON DUPLICATE KEY UPDATE
    name = VALUES(name),
    description = VALUES(description),
    sort_order = VALUES(sort_order),
    is_active = VALUES(is_active);

INSERT INTO items (id, category_id, name, slug, short_description, description, price, price_label, whatsapp_message, sort_order, is_featured, is_active)
VALUES
    (1, 1, 'Nasi Ayam Rempah', 'nasi-ayam-rempah', 'Ayam ungkep 12 bumbu, sambal matah, dan lalapan segar.', 'Ayam kampung diungkep dengan 12 rempah lalu digoreng kering. Disajikan dengan nasi pulen, sambal matah, tempe, dan lalapan.', 28000.00, 'Rp28.000', 'Halo Dapur Nusantara, saya mau pesan Nasi Ayam Rempah', 10, 1, 1),
    (2, 1, 'Rendang Sapi Padang', 'rendang-sapi-padang', 'Rendang dimasak 6 jam sampai bumbunya meresap.', 'Daging sapi pilihan dimasak perlahan dengan santan dan bumbu rendang khas Padang. Disajikan dengan nasi, daun singkong, dan sambal hijau.', 38000.00, 'Rp38.000', 'Halo Dapur Nusantara, saya mau pesan Rendang Sapi Padang', 20, 1, 1),
    (3, 1, 'Soto Betawi Susu', 'soto-betawi-susu', 'Kuah susu gurih dengan daging dan emping renyah.', 'Soto Betawi kuah susu dan santan dengan potongan daging sapi, kentang, tomat, dan emping. Nasi terpisah.', 32000.00, 'Rp32.000', 'Halo Dapur Nusantara, saya mau pesan Soto Betawi Susu', 30, 0, 1),
    (4, 1, 'Ikan Bakar Jimbaran', 'ikan-bakar-jimbaran', 'Ikan kakap bakar bumbu Bali dan sambal tomat.', 'Ikan kakap segar dibakar dengan bumbu khas Jimbaran, disajikan dengan nasi, plecing kangkung, dan sambal tomat.', 45000.00, 'Rp45.000', 'Halo Dapur Nusantara, saya mau pesan Ikan Bakar Jimbaran', 40, 0, 1),
    (5, 2, 'Paket Hemat Berdua', 'paket-hemat-berdua', '2 nasi ayam rempah, 2 es teh, dan kerupuk.', 'Paket makan berdua: 2 porsi Nasi Ayam Rempah, 2 Es Teh Manis, dan kerupuk udang.', 62000.00, 'Rp62.000', 'Halo Dapur Nusantara, saya tertarik Paket Hemat Berdua', 50, 1, 1),
    (6, 2, 'Nasi Box Kantor', 'nasi-box-kantor', 'Minimal 20 box, gratis ongkir area Jakarta Selatan.', 'Isi nasi, lauk utama pilihan, sayur, sambal, kerupuk, dan buah. Pesan H-1 untuk minimal 20 box.', 30000.00, 'Mulai Rp30.000/box', 'Halo Dapur Nusantara, saya mau tanya Nasi Box Kantor', 60, 0, 1),
    (7, 2, 'Tumpeng Mini Syukuran', 'tumpeng-mini-syukuran', 'Untuk 5 orang, lengkap 7 lauk tradisional.', 'Tumpeng nasi kuning untuk 5 orang dengan ayam goreng, perkedel, telur balado, urap, sambal goreng kentang, tempe orek, dan kerupuk.', 275000.00, 'Rp275.000', 'Halo Dapur Nusantara, saya mau pesan Tumpeng Mini Syukuran', 70, 0, 1),
    (8, 3, 'Es Kopi Gula Aren', 'es-kopi-gula-aren', 'Espresso, susu segar, dan gula aren asli.', 'Kopi susu kekinian dengan gula aren asli dari Banten. Tersedia versi less sugar.', 18000.00, 'Rp18.000', 'Halo Dapur Nusantara, saya mau pesan Es Kopi Gula Aren', 80, 0, 1),
    (9, 3, 'Es Cendol Durian', 'es-cendol-durian', 'Cendol pandan, santan, dan daging durian Medan.', 'Cendol pandan buatan sendiri dengan santan segar, gula merah, dan daging durian Medan.', 22000.00, 'Rp22.000', 'Halo Dapur Nusantara, saya mau pesan Es Cendol Durian', 90, 0, 1)
ON DUPLICATE KEY UPDATE
    category_id = VALUES(category_id),
    name = VALUES(name),
    short_description = VALUES(short_description),
    description = VALUES(description),
    price = VALUES(price),
    price_label = VALUES(price_label),
    whatsapp_message = VALUES(whatsapp_message),
    sort_order = VALUES(sort_order),
    is_featured = VALUES(is_featured),
    is_active = VALUES(is_active);

INSERT INTO settings (setting_key, setting_value)
VALUES
    ('active_template_slug', 'kuliner-simple'),
    ('primary_color', '#c2410c'),
    ('secondary_color', '#475569'),
    ('button_style', 'rounded'),
    ('site_meta_title', 'Dapur Nusantara'),
    ('site_meta_description', 'Dapur Nusantara: masakan rumahan, nasi box kantor, dan tumpeng. Pesan langsung lewat WhatsApp.'),
    ('catalog_mode', 'products')
ON DUPLICATE KEY UPDATE
    setting_value = VALUES(setting_value);

INSERT INTO social_links (id, platform, label, url, sort_order, is_active)
VALUES
    (1, 'instagram', '@dapurnusantara', 'https://instagram.com/dapurnusantara', 10, 1),
    (2, 'tiktok', 'TikTok', 'https://tiktok.com/@dapurnusantara', 20, 1),
    (3, 'marketplace', 'GoFood', 'https://gofood.co.id', 30, 1)
ON DUPLICATE KEY UPDATE
    platform = VALUES(platform),
    label = VALUES(label),
    url = VALUES(url),
    sort_order = VALUES(sort_order),
    is_active = VALUES(is_active);

-- Demo photos are CC0 (see public/uploads/2000/01/CREDITS.txt).
-- JSON newlines are written as \\n because MySQL string literals unescape backslashes.
UPDATE business_profiles SET hero_image_path = 'uploads/2000/01/c4c970eaadee04ac0944176ef16eac74.webp' WHERE id = 1;
UPDATE items SET image_path = 'uploads/2000/01/c08953ee08b2d50e47d3a2b3efb88a0e.webp' WHERE slug = 'nasi-ayam-rempah';
UPDATE items SET image_path = 'uploads/2000/01/153e1e0d891530bf1f46f1649229bcbb.webp' WHERE slug = 'rendang-sapi-padang';
UPDATE items SET image_path = 'uploads/2000/01/f3df053e586b7328f40ad58c26a837e6.webp' WHERE slug = 'soto-betawi-susu';
UPDATE items SET image_path = 'uploads/2000/01/052f2f3701a5abc24f4bc1a25f96e5c8.webp' WHERE slug = 'ikan-bakar-jimbaran';
UPDATE items SET image_path = 'uploads/2000/01/1c59c2fe05ff27ddf59287d52e62f2ce.webp' WHERE slug = 'paket-hemat-berdua';
UPDATE items SET image_path = 'uploads/2000/01/7f42f661ab822a4b5ba6dedc450dccd9.webp' WHERE slug = 'nasi-box-kantor';
UPDATE items SET image_path = 'uploads/2000/01/5e07cdf605a6bade501fc238b45504cf.webp' WHERE slug = 'tumpeng-mini-syukuran';
UPDATE items SET image_path = 'uploads/2000/01/b52b59f9874afb1253074365c8a9b2ff.webp' WHERE slug = 'es-kopi-gula-aren';
UPDATE items SET image_path = 'uploads/2000/01/4900d094adc19938f3a16f11c7045937.webp' WHERE slug = 'es-cendol-durian';

INSERT INTO settings (setting_key, setting_value)
VALUES
    ('section_about', '{"title": "Masakan rumahan sejak 2014", "image": "uploads/2000/01/6cbf51c0af909b0e32e06be17342ca4b.webp", "body": "Dapur Nusantara berawal dari dapur rumah di Kebayoran Baru. Resepnya turun dari keluarga, dimasak dengan rempah yang diulek sendiri.\\n\\nSekarang kami melayani makan siang kantor, pesanan nasi box, sampai tumpeng syukuran. Rasanya tetap sama seperti masakan di rumah."}'),
    ('section_stats', '[{"value": "2014", "label": "Mulai memasak"}, {"value": "4.800+", "label": "Pelanggan dilayani"}, {"value": "4,8/5", "label": "Rating di GoFood"}]'),
    ('section_highlights', '[{"title": "Bahan segar setiap pagi", "body": "Sayur, daging, dan ikan dibeli langsung dari pasar setiap subuh."}, {"title": "Bumbu diulek sendiri", "body": "Tanpa bumbu instan, jadi rasa rempahnya lebih kuat dan wangi."}, {"title": "Antar tepat waktu", "body": "Pesanan kantor sampai sebelum jam makan siang."}, {"title": "Siap pesanan besar", "body": "Nasi box dan tumpeng untuk 20 sampai 500 orang."}]'),
    ('section_gallery', '["uploads/2000/01/133d01f96fdec05fb2ad254281eaee25.webp", "uploads/2000/01/f316ac13d2ebf1310f0cfb46cf9f8484.webp", "uploads/2000/01/1f719c608cdbdc671b7cc19d12c74061.webp", "uploads/2000/01/0a1ceb59386e0d25c271370385340e89.webp", "uploads/2000/01/83afe1ebfb9342db4cbe6d234d1bee14.webp", "uploads/2000/01/6f7e951c05e7ed835cc92cb48fd103d3.webp"]'),
    ('section_testimonials', '[{"name": "Rina Hapsari", "role": "Staf HR, pesan nasi box rutin", "quote": "Sudah 2 tahun langganan nasi box untuk rapat kantor. Selalu datang tepat waktu dan porsinya pas."}, {"name": "Bagas Wicaksono", "role": "Pelanggan GoFood", "quote": "Rendangnya empuk dan bumbunya meresap sampai dalam. Rasanya seperti masakan ibu sendiri."}, {"name": "Sri Lestari", "role": "Pesan tumpeng syukuran", "quote": "Tumpengnya cantik dan lauknya lengkap. Tamu banyak yang tanya pesan di mana."}]'),
    ('section_faq', '[{"question": "Berapa minimal pesanan nasi box?", "answer": "Minimal 20 box. Pesan paling lambat H-1 sebelum jam 15.00."}, {"question": "Area mana saja yang dilayani antar?", "answer": "Gratis ongkir untuk Jakarta Selatan. Area lain dihitung sesuai jarak."}, {"question": "Bisa pesan tumpeng untuk hari yang sama?", "answer": "Tumpeng perlu dipesan minimal 2 hari sebelumnya supaya lauknya dimasak segar."}, {"question": "Pembayarannya bagaimana?", "answer": "Bisa transfer bank, QRIS, atau bayar di tempat untuk pesanan di bawah Rp500.000."}]'),
    ('section_hours', 'Senin sampai Jumat: 08.00 - 20.00
Sabtu dan Minggu: 09.00 - 21.00')
ON DUPLICATE KEY UPDATE
    setting_value = VALUES(setting_value);
