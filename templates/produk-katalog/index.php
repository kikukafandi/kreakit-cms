<?php
/** @var array $business @var array $items @var array $categories @var array $socialLinks @var array $settings @var array $themeSettings */
$businessName = (string) ($business['business_name'] ?? 'KreaKit CMS');
$metaTitle = (string) ($settings['site_meta_title'] ?? $businessName) ?: $businessName;
$metaDescription = (string) ($settings['site_meta_description'] ?? ($business['tagline'] ?? ''));
$primary = preg_match('/^#[0-9a-fA-F]{6}$/', (string) ($themeSettings['primary_color'] ?? '')) ? (string) $themeSettings['primary_color'] : '#7c3aed';
$secondary = preg_match('/^#[0-9a-fA-F]{6}$/', (string) ($themeSettings['secondary_color'] ?? '')) ? (string) $themeSettings['secondary_color'] : '#f59e0b';
$logoUrl = public_upload_url($business['logo_path'] ?? null);
?>
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($metaTitle) ?></title>
    <?php if ($metaDescription !== ''): ?><meta name="description" content="<?= e($metaDescription) ?>"><?php endif; ?>
    <style>
        :root{--primary:<?= e($primary) ?>;--secondary:<?= e($secondary) ?>}*{box-sizing:border-box}body{font-family:Inter,Arial,sans-serif;margin:0;background:#fbfbff;color:#18181b}.wrap{max-width:1160px;margin:0 auto;padding:24px}.header{background:#fff;border-bottom:1px solid #e4e4e7;position:sticky;top:0}.brand{display:flex;align-items:center;gap:14px}.logo{width:56px;height:56px;object-fit:cover;border-radius:50%}.hero{background:radial-gradient(circle at top right,#ede9fe,transparent 45%),linear-gradient(135deg,#fff,#faf5ff);padding:38px 0}.cta{display:inline-block;background:var(--primary);color:#fff;padding:10px 14px;border-radius:999px;text-decoration:none;font-weight:700}.cats span{display:inline-block;border:1px solid #ddd6fe;border-radius:999px;padding:6px 10px;margin:4px;background:#fff}.grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:18px}.product{background:white;border:1px solid #e4e4e7;border-radius:20px;overflow:hidden;box-shadow:0 10px 30px #0000000d}.product img{width:100%;aspect-ratio:4/3;object-fit:cover;background:#f4f4f5}.product-body{padding:16px}.price{color:var(--secondary);font-weight:900}.muted{color:#71717a}.social a{margin-right:12px;color:var(--primary)}
    </style>
</head>
<body>
<header class="header"><div class="wrap brand">
    <?php if ($logoUrl !== ''): ?><img class="logo" src="<?= e($logoUrl) ?>" alt="Logo <?= e($businessName) ?>"><?php endif; ?>
    <div><strong><?= e($businessName) ?></strong><?php if (!empty($business['tagline'])): ?><div class="muted"><?= e($business['tagline']) ?></div><?php endif; ?></div>
</div></header>
<section class="hero"><div class="wrap">
    <h1><?= e($businessName) ?></h1>
    <?php if (!empty($business['description'])): ?><p><?= nl2br(e($business['description'])) ?></p><?php endif; ?>
    <?php if (!empty($business['whatsapp_number'])): ?><a class="cta" href="<?= e(whatsapp_url($business['whatsapp_number'], 'Halo, saya ingin tanya katalog ' . $businessName)) ?>">Chat Penjual</a><?php endif; ?>
    <?php if ($categories !== []): ?><p class="cats"><?php foreach ($categories as $category): ?><span><?= e($category['name']) ?></span><?php endforeach; ?></p><?php endif; ?>
</div></section>
<main class="wrap">
    <h2>Katalog Produk</h2>
    <section class="grid">
        <?php foreach ($items as $item): ?>
            <?php $imageUrl = public_upload_url($item['image_path'] ?? null); ?>
            <article class="product">
                <?php if ($imageUrl !== ''): ?><img src="<?= e($imageUrl) ?>" alt="<?= e($item['name'] ?? '') ?>"><?php endif; ?>
                <div class="product-body">
                    <h3><?= e($item['name'] ?? '') ?></h3>
                    <?php if (!empty($item['category_name'])): ?><p class="muted"><?= e($item['category_name']) ?></p><?php endif; ?>
                    <?php if (!empty($item['short_description'])): ?><p><?= e($item['short_description']) ?></p><?php endif; ?>
                    <p class="price"><?= e($item['price_label'] ?: rupiah($item['price'] ?? null)) ?></p>
                    <?php if (!empty($business['whatsapp_number'])): ?><a class="cta" href="<?= e(whatsapp_url($business['whatsapp_number'], (string) ($item['whatsapp_message'] ?? ('Halo, saya ingin pesan ' . ($item['name'] ?? ''))))) ?>">Pesan Produk</a><?php endif; ?>
                </div>
            </article>
        <?php endforeach; ?>
    </section>
</main>
<footer class="wrap">
    <?php if (!empty($business['address'])): ?><p><?= nl2br(e($business['address'])) ?></p><?php endif; ?>
    <?php if (!empty($business['maps_url'])): ?><p><a href="<?= e($business['maps_url']) ?>" target="_blank" rel="noopener">Buka Maps</a></p><?php endif; ?>
    <?php if ($socialLinks !== []): ?><p class="social"><?php foreach ($socialLinks as $link): ?><a href="<?= e($link['url']) ?>" target="_blank" rel="noopener"><?= e($link['label'] ?: $link['platform']) ?></a><?php endforeach; ?></p><?php endif; ?>
</footer>
</body>
</html>
