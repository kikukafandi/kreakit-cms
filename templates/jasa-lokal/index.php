<?php
/** @var array $business @var array $items @var array $categories @var array $socialLinks @var array $settings @var array $themeSettings */
$businessName = (string) ($business['business_name'] ?? 'KreaKit CMS');
$metaTitle = (string) ($settings['site_meta_title'] ?? $businessName) ?: $businessName;
$metaDescription = (string) ($settings['site_meta_description'] ?? ($business['tagline'] ?? ''));
$primary = preg_match('/^#[0-9a-fA-F]{6}$/', (string) ($themeSettings['primary_color'] ?? '')) ? (string) $themeSettings['primary_color'] : '#2563eb';
$secondary = preg_match('/^#[0-9a-fA-F]{6}$/', (string) ($themeSettings['secondary_color'] ?? '')) ? (string) $themeSettings['secondary_color'] : '#16a34a';
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
        :root{--primary:<?= e($primary) ?>;--secondary:<?= e($secondary) ?>}*{box-sizing:border-box}body{font-family:system-ui,-apple-system,Segoe UI,sans-serif;margin:0;color:#0f172a;background:#f8fafc}.wrap{max-width:1080px;margin:0 auto;padding:28px}.top{background:white;border-bottom:1px solid #e2e8f0}.brand{display:flex;gap:14px;align-items:center}.logo{width:64px;height:64px;object-fit:cover;border-radius:14px}.hero{padding:44px 0;background:linear-gradient(135deg,#eff6ff,#f0fdf4)}.hero-grid{display:grid;grid-template-columns:1.2fr .8fr;gap:28px;align-items:center}.hero-img{width:100%;border-radius:22px;box-shadow:0 20px 45px #0002}.cta{display:inline-block;background:var(--primary);color:#fff;padding:12px 16px;border-radius:12px;text-decoration:none;font-weight:700}.secondary{background:var(--secondary)}.grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(240px,1fr));gap:16px}.card{background:white;border:1px solid #e2e8f0;border-radius:18px;padding:18px}.muted{color:#64748b}.price{color:var(--primary);font-weight:800}.social a{margin-right:12px;color:var(--primary)}@media(max-width:760px){.hero-grid{grid-template-columns:1fr}.wrap{padding:20px}}
    </style>
</head>
<body>
<header class="top"><div class="wrap brand">
    <?php if ($logoUrl !== ''): ?><img class="logo" src="<?= e($logoUrl) ?>" alt="Logo <?= e($businessName) ?>"><?php endif; ?>
    <div><strong><?= e($businessName) ?></strong><?php if (!empty($business['tagline'])): ?><div class="muted"><?= e($business['tagline']) ?></div><?php endif; ?></div>
</div></header>
<section class="hero"><div class="wrap hero-grid">
    <div>
        <h1><?= e($businessName) ?></h1>
        <?php if (!empty($business['description'])): ?><p><?= nl2br(e($business['description'])) ?></p><?php endif; ?>
        <?php if (!empty($business['whatsapp_number'])): ?><a class="cta" href="<?= e(whatsapp_url($business['whatsapp_number'], 'Halo, saya ingin konsultasi layanan ' . $businessName)) ?>">Konsultasi via WhatsApp</a><?php endif; ?>
        <?php if (!empty($business['maps_url'])): ?> <a class="cta secondary" href="<?= e($business['maps_url']) ?>" target="_blank" rel="noopener">Lihat Lokasi</a><?php endif; ?>
    </div>
    <div><?php if ($heroUrl !== ''): ?><img class="hero-img" src="<?= e($heroUrl) ?>" alt="<?= e($businessName) ?>"><?php endif; ?></div>
</div></section>
<main class="wrap">
    <h2>Layanan Kami</h2>
    <section class="grid">
        <?php foreach ($items as $item): ?>
            <article class="card">
                <h3><?= e($item['name'] ?? '') ?></h3>
                <?php if (!empty($item['short_description'])): ?><p class="muted"><?= e($item['short_description']) ?></p><?php endif; ?>
                <?php if (!empty($item['description'])): ?><p><?= nl2br(e($item['description'])) ?></p><?php endif; ?>
                <p class="price"><?= e($item['price_label'] ?: rupiah($item['price'] ?? null)) ?></p>
                <?php if (!empty($business['whatsapp_number'])): ?><a class="cta" href="<?= e(whatsapp_url($business['whatsapp_number'], (string) ($item['whatsapp_message'] ?? ('Halo, saya tertarik layanan ' . ($item['name'] ?? ''))))) ?>">Tanya Layanan</a><?php endif; ?>
            </article>
        <?php endforeach; ?>
    </section>
</main>
<footer class="wrap">
    <?php if (!empty($business['address'])): ?><p><?= nl2br(e($business['address'])) ?></p><?php endif; ?>
    <?php if ($socialLinks !== []): ?><p class="social"><?php foreach ($socialLinks as $link): ?><a href="<?= e($link['url']) ?>" target="_blank" rel="noopener"><?= e($link['label'] ?: $link['platform']) ?></a><?php endforeach; ?></p><?php endif; ?>
</footer>
</body>
</html>
