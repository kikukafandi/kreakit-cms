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
    (1, 'Dapur Nusantara', 'Menu rumahan siap antar', 'Contoh profil bisnis UMKM untuk demo KreaKit CMS. Ganti data ini sesuai bisnis client.', '6281234567890', 'Jl. Contoh UMKM No. 10, Jakarta', 'https://maps.google.com/?q=Jakarta', 'halo@example.test', '021-123456')
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
    (1, 'Menu Favorit', 'menu-favorit', 'Produk atau layanan unggulan.', 10, 1),
    (2, 'Paket Hemat', 'paket-hemat', 'Pilihan paket dengan harga hemat.', 20, 1),
    (3, 'Layanan Tambahan', 'layanan-tambahan', 'Tambahan yang bisa dipesan client.', 30, 1)
ON DUPLICATE KEY UPDATE
    name = VALUES(name),
    description = VALUES(description),
    sort_order = VALUES(sort_order),
    is_active = VALUES(is_active);

INSERT INTO items (id, category_id, name, slug, short_description, description, price, price_label, whatsapp_message, sort_order, is_featured, is_active)
VALUES
    (1, 1, 'Nasi Ayam Rempah', 'nasi-ayam-rempah', 'Nasi ayam dengan bumbu rempah khas.', 'Sample item untuk demo katalog UMKM.', 28000.00, 'Rp28.000', 'Halo, saya mau pesan Nasi Ayam Rempah', 10, 1, 1),
    (2, 2, 'Paket Hemat Berdua', 'paket-hemat-berdua', 'Paket hemat untuk dua orang.', 'Sample paket hemat yang bisa diganti sesuai bisnis.', 52000.00, 'Mulai Rp52.000', 'Halo, saya tertarik Paket Hemat Berdua', 20, 1, 1),
    (3, 3, 'Extra Sambal', 'extra-sambal', 'Tambahan sambal rumahan.', 'Sample add-on kecil untuk katalog.', 5000.00, 'Rp5.000', 'Halo, saya mau tambah Extra Sambal', 30, 0, 1)
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
    ('primary_color', '#0f766e'),
    ('secondary_color', '#f97316'),
    ('button_style', 'rounded'),
    ('site_meta_title', 'Dapur Nusantara'),
    ('site_meta_description', 'Website katalog UMKM demo dari KreaKit CMS.'),
    ('catalog_mode', 'products')
ON DUPLICATE KEY UPDATE
    setting_value = VALUES(setting_value);

INSERT INTO social_links (id, platform, label, url, sort_order, is_active)
VALUES
    (1, 'instagram', 'Instagram', 'https://instagram.com/example', 10, 1),
    (2, 'facebook', 'Facebook', 'https://facebook.com/example', 20, 1),
    (3, 'website', 'Website', 'https://example.test', 30, 1)
ON DUPLICATE KEY UPDATE
    platform = VALUES(platform),
    label = VALUES(label),
    url = VALUES(url),
    sort_order = VALUES(sort_order),
    is_active = VALUES(is_active);
