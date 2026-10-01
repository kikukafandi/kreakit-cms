<?php
/** @var array $business @var array $items @var array $categories @var array $socialLinks @var array $settings @var array $themeSettings */
$businessName = (string) ($business['business_name'] ?? 'KreaKit CMS');
$metaTitle = (string) ($settings['site_meta_title'] ?? $businessName) ?: $businessName;
$metaDescription = (string) ($settings['site_meta_description'] ?? ($business['tagline'] ?? ''));
$primary = preg_match('/^#[0-9a-fA-F]{6}$/', (string) ($themeSettings['primary_color'] ?? '')) ? (string) $themeSettings['primary_color'] : '#0f766e';
$secondary = preg_match('/^#[0-9a-fA-F]{6}$/', (string) ($themeSettings['secondary_color'] ?? '')) ? (string) $themeSettings['secondary_color'] : '#f97316';
$logoUrl = public_upload_url($business['logo_path'] ?? null);
$heroUrl = public_upload_url($business['hero_image_path'] ?? null);
?>
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($metaTitle) ?></title>
    <?php if ($metaDescription !== ''): ?><meta name="description" content="<?= e($metaDescription) ?>"><?php endif; ?>
    <style>
        :root{--primary:<?= e($primary) ?>;--secondary:<?= e($secondary) ?>}body{font-family:Arial,sans-serif;margin:0;color:#17202a;background:#fffaf2}.wrap{max-width:1040px;margin:0 auto;padding:24px}.hero{background:linear-gradient(135deg,var(--primary),#111827);color:white}.logo{max-width:96px;max-height:96px;border-radius:18px;background:white}.hero-img,.item img{max-width:100%;border-radius:14px}.cta{display:inline-block;background:var(--secondary);color:white;padding:10px 14px;border-radius:999px;text-decoration:none}.grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:16px}.card{background:white;border:1px solid #eee;border-radius:16px;padding:16px;box-shadow:0 4px 18px #0001}.muted{color:#5b6470}.social a{margin-right:12px}
    </style>
</head>
<body>
<header class="hero"><div class="wrap">
    <?php if ($logoUrl !== ''): ?><img class="logo" src="<?= e($logoUrl) ?>" alt="Logo <?= e($businessName) ?>"><?php endif; ?>
    <h1><?= e($businessName) ?></h1>
    <?php if (!empty($business['tagline'])): ?><p><?= e($business['tagline']) ?></p><?php endif; ?>
    <?php if (!empty($business['description'])): ?><p><?= nl2br(e($business['description'])) ?></p><?php endif; ?>
    <?php if (!empty($business['whatsapp_number'])): ?><a class="cta" href="<?= e(whatsapp_url($business['whatsapp_number'], 'Halo, saya ingin tanya menu ' . $businessName)) ?>">Pesan via WhatsApp</a><?php endif; ?>
    <?php if ($heroUrl !== ''): ?><p><img class="hero-img" src="<?= e($heroUrl) ?>" alt="Hero <?= e($businessName) ?>"></p><?php endif; ?>
</div></header>
<main class="wrap">
    <h2>Menu Favorit</h2>
    <?php if ($categories !== []): ?><p class="muted"><?php foreach ($categories as $category): ?><span><?= e($category['name']) ?></span> · <?php endforeach; ?></p><?php endif; ?>
    <section class="grid">
        <?php foreach ($items as $item): ?>
            <?php $imageUrl = public_upload_url($item['image_path'] ?? null); ?>
            <article class="card item">
                <?php if ($imageUrl !== ''): ?><img src="<?= e($imageUrl) ?>" alt="<?= e($item['name'] ?? '') ?>"><?php endif; ?>
                <h3><?= e($item['name'] ?? '') ?></h3>
                <p class="muted"><?= e($item['short_description'] ?? '') ?></p>
                <strong><?= e($item['price_label'] ?: rupiah($item['price'] ?? null)) ?></strong>
                <?php if (!empty($business['whatsapp_number'])): ?><p><a class="cta" href="<?= e(whatsapp_url($business['whatsapp_number'], (string) ($item['whatsapp_message'] ?? ('Halo, saya tertarik ' . ($item['name'] ?? ''))))) ?>">Tanya item</a></p><?php endif; ?>
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
