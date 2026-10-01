<?php
/** @var array $business @var array $items @var array $categories @var array $socialLinks @var array $settings @var array $themeSettings */
require_once __DIR__ . '/../_shared/kit.php';

$kit = kit_prepare($business, $items, $categories, $socialLinks, $settings, $themeSettings, ['primary' => '#be123c', 'secondary' => '#27272a']);
$spotlight = $kit['featured'][0] ?? ($kit['items'][0] ?? null);
$noun = mb_strtolower($kit['noun']);
$chat = 'Chat WhatsApp';
?>
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($kit['metaTitle']) ?></title>
    <?php if ($kit['metaDescription'] !== ''): ?><meta name="description" content="<?= e($kit['metaDescription']) ?>"><?php endif; ?>
    <meta name="theme-color" content="<?= e($kit['primary']) ?>">
    <link rel="preload" href="<?= e(url('/assets/fonts/outfit.woff2')) ?>" as="font" type="font/woff2" crossorigin>
    <style>
        <?= kit_font_face('Outfit', 'outfit') ?>
        /* Monochrome storefront with a single saturated accent (the admin's primary colour). */
        :root{color-scheme:light dark;--accent:<?= e($kit['primary']) ?>;--radius:<?= e($kit['radius']) ?>;--card-radius:<?= e($kit['cardRadius']) ?>;--wa:#15803d;
            --bg:#fafafa;--surface:#fff;--ink:#18181b;--muted:#5f5f69;--line:#e7e7ea;--tint:color-mix(in srgb,var(--accent) 8%,var(--surface));--panel:#f1f1f3}
        @media (prefers-color-scheme: dark){:root{--bg:#0d0d10;--surface:#17171b;--ink:#f2f2f4;--muted:#a1a1aa;--line:#2a2a30;--accent:color-mix(in srgb,<?= e($kit['primary']) ?> 72%,#fff);--tint:color-mix(in srgb,var(--accent) 12%,var(--surface));--panel:#1d1d22}}
        *{box-sizing:border-box}html{scroll-behavior:smooth}
        body{margin:0;font-family:'Outfit',system-ui,sans-serif;color:var(--ink);background:var(--bg);-webkit-font-smoothing:antialiased;font-size:16px}
        a{color:inherit}img{display:block;max-width:100%}button,input{font:inherit;color:inherit}
        h1,h2,h3{letter-spacing:-.025em;font-weight:600}
        .wrap{width:min(1240px,100% - 40px);margin-inline:auto}
        .btn{display:inline-flex;align-items:center;justify-content:center;gap:8px;padding:13px 20px;border-radius:var(--radius);font-weight:600;font-size:15px;text-decoration:none;border:0;cursor:pointer;white-space:nowrap;transition:transform .2s cubic-bezier(.16,1,.3,1)}
        .btn:hover{transform:translateY(-1px)}.btn:active{transform:scale(.98)}
        .btn-wa{background:var(--wa);color:#fff}.btn-dark{background:var(--ink);color:var(--bg)}
        /* Header with search */
        .nav{position:sticky;top:0;z-index:20;background:color-mix(in srgb,var(--bg) 88%,transparent);backdrop-filter:blur(14px);border-bottom:1px solid var(--line)}
        .nav .wrap{display:grid;grid-template-columns:auto minmax(0,520px) auto;justify-content:space-between;align-items:center;gap:20px;height:70px}
        .brand{display:flex;align-items:center;gap:12px;text-decoration:none;font-weight:600;font-size:19px;min-width:0}.brand span:last-child{overflow:hidden;text-overflow:ellipsis;white-space:nowrap}
        .brand img,.monogram{width:40px;height:40px;border-radius:var(--radius);object-fit:cover;flex:none}
        .monogram{display:grid;place-items:center;background:var(--ink);color:var(--bg);font-size:15px;font-weight:600}
        .search{display:flex;align-items:center;gap:10px;background:var(--panel);border-radius:999px;padding:0 18px;color:var(--muted)}
        .search input{border:0;outline:0;background:transparent;font-size:15px;padding:12px 0;width:100%;color:var(--ink)}.search input::placeholder{color:var(--muted)}
        .search:focus-within{box-shadow:inset 0 0 0 2px var(--accent)}
        .nav .btn{padding:10px 16px}
        /* Hero */
        .hero{padding:28px 0 8px}
        .banner{display:grid;grid-template-columns:1.25fr .75fr;gap:40px;align-items:center;background:var(--panel);border-radius:calc(var(--card-radius) + 10px);padding:52px 56px;overflow:hidden}
        .banner h1{font-size:clamp(34px,4vw,52px);line-height:1.06;margin:0 0 16px;max-width:18ch;text-wrap:balance}
        .lead{font-size:18px;line-height:1.6;color:var(--muted);margin:0 0 28px;max-width:48ch;display:-webkit-box;-webkit-line-clamp:3;-webkit-box-orient:vertical;overflow:hidden}
        .hero-actions{display:flex;flex-wrap:wrap;gap:12px}
        .spot{background:var(--surface);border-radius:var(--card-radius);overflow:hidden;box-shadow:0 26px 60px color-mix(in srgb,var(--ink) 12%,transparent);cursor:pointer;transform:rotate(1.5deg);transition:transform .4s cubic-bezier(.16,1,.3,1)}
        .spot:hover{transform:rotate(0)}
        .spot .media{aspect-ratio:1}.spot .meta{padding:16px 18px;display:flex;justify-content:space-between;gap:12px;align-items:baseline}
        .spot b{font-weight:600}.spot span{color:var(--accent);font-weight:600;white-space:nowrap}
        .hero-photo{width:100%;aspect-ratio:1;object-fit:cover;border-radius:var(--card-radius)}
        /* Catalog */
        .catalog{padding:48px 0 80px}
        .catalog-head{display:flex;flex-wrap:wrap;align-items:center;justify-content:space-between;gap:14px;margin-bottom:24px}
        .catalog-head h2{font-size:clamp(28px,3vw,36px);margin:0}
        .chips{display:flex;gap:8px;overflow-x:auto;scrollbar-width:none;padding:2px;max-width:100%}
        .chip{white-space:nowrap;border:0;box-shadow:inset 0 0 0 1px var(--line);background:var(--surface);padding:9px 16px;border-radius:999px;font-weight:500;font-size:14.5px;cursor:pointer}
        .chip[aria-pressed=true]{background:var(--ink);color:var(--bg);box-shadow:none}
        .grid{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:22px 18px}
        .product{display:flex;flex-direction:column;cursor:pointer}
        .media{aspect-ratio:1;border-radius:var(--card-radius);overflow:hidden;background:var(--panel);display:grid;place-items:center;font-size:44px;font-weight:600;color:var(--accent)}
        .media img{width:100%;height:100%;object-fit:cover;transition:transform .5s cubic-bezier(.16,1,.3,1)}
        .product:hover .media img{transform:scale(1.04)}
        .product .info{padding:14px 2px 0;display:flex;flex-direction:column;gap:4px;flex:1}
        .tagline{font-size:13px;color:var(--muted);font-weight:500}.tagline .hot{color:var(--accent);font-weight:600}
        .product h3{margin:0;font-size:17px;font-weight:500;line-height:1.35}
        .product .price{font-weight:600;font-size:17px}
        .product .order{margin-top:12px;display:flex;align-items:center;justify-content:center;gap:8px;padding:11px;border-radius:var(--radius);box-shadow:inset 0 0 0 1px var(--line);text-decoration:none;font-weight:600;font-size:14.5px;transition:background .2s,color .2s}
        .product .order:hover{background:var(--ink);color:var(--bg);box-shadow:none}
        .empty{text-align:center;padding:56px 20px;color:var(--muted);border-radius:var(--card-radius);background:var(--panel)}
        /* How to order */
        .how{border-block:1px solid var(--line)}
        .how .wrap{display:grid;grid-template-columns:auto 1fr;gap:40px;align-items:center;padding:36px 0}
        .how h2{font-size:24px;margin:0}
        .how ol{list-style:none;margin:0;padding:0;display:flex;flex-wrap:wrap;gap:14px 36px;justify-content:flex-end}
        .how li{display:flex;align-items:center;gap:12px;font-weight:500}.how .icon{width:38px;height:38px;padding:9px;border-radius:50%;background:var(--tint);color:var(--accent)}
        /* Footer */
        .footer{padding:56px 0 112px}
        .footer .wrap{display:grid;grid-template-columns:1.3fr 1fr 1fr;gap:40px}
        .footer h3{font-size:15px;color:var(--muted);font-weight:500;margin:0 0 14px;letter-spacing:0}
        .footer .about p{color:var(--muted);line-height:1.6;margin:12px 0 22px;max-width:40ch}
        .contact-list{display:grid;gap:12px}.contact-list a,.contact-list div{display:flex;gap:10px;align-items:flex-start;text-decoration:none;line-height:1.5}.contact-list .ks-hours div{display:block}.contact-list .icon{margin-top:2px;color:var(--accent)}
        .socials{display:grid;gap:10px}.social{display:inline-flex;align-items:center;gap:10px;text-decoration:none;font-weight:500}.social:hover{color:var(--accent)}
        .copyright{grid-column:1/-1;padding-top:24px;border-top:1px solid var(--line);color:var(--muted);font-size:14px}
        .float-wa{position:fixed;right:20px;bottom:20px;z-index:30;display:inline-flex;align-items:center;gap:10px;padding:14px 20px;border-radius:999px;background:var(--wa);color:#fff;font-weight:600;text-decoration:none;box-shadow:0 14px 34px color-mix(in srgb,var(--wa) 38%,transparent)}.float-wa .icon{width:22px;height:22px}
        <?= kit_base_css() ?>
        <?= kit_sections_css() ?>
        @media(max-width:1080px){.grid{grid-template-columns:repeat(3,minmax(0,1fr))}}
        @media(max-width:860px){.nav .wrap{grid-template-columns:1fr auto;height:auto;padding:12px 0}.nav .search{grid-column:1/-1;grid-row:2}.banner{grid-template-columns:1fr;padding:34px 26px}.spot{max-width:340px}.grid{grid-template-columns:repeat(2,minmax(0,1fr));gap:20px 14px}
            .how .wrap{grid-template-columns:1fr;gap:18px}.how ol{justify-content:flex-start;flex-direction:column}.footer .wrap{grid-template-columns:1fr;gap:30px}}
        @media(max-width:560px){.wrap{width:calc(100% - 32px)}.float-wa span{display:none}.float-wa{padding:16px}.nav .btn span{display:none}.nav .btn{padding:10px 12px}.product h3{font-size:15.5px}.media{font-size:34px}}
    </style>
</head>
<body>
<header class="nav">
    <div class="wrap">
        <a class="brand" href="#">
            <?php if ($kit['logo'] !== ''): ?><img src="<?= e($kit['logo']) ?>" alt=""><?php else: ?><span class="monogram"><?= e(kit_initials($kit['name'])) ?></span><?php endif; ?>
            <span><?= e($kit['name']) ?></span>
        </a>
        <label class="search"><?= kit_icon('search') ?><input type="search" placeholder="Cari <?= e($noun) ?>" data-search-input aria-label="Cari <?= e($noun) ?>"></label>
        <?php if ($kit['wa'] !== ''): ?><a class="btn btn-wa" href="<?= e($kit['wa']) ?>" target="_blank" rel="noopener"><?= kit_icon('whatsapp') ?><span><?= e($chat) ?></span></a><?php endif; ?>
    </div>
</header>

<main>
    <section class="hero">
        <div class="wrap banner">
            <div>
                <h1><?= e($kit['tagline'] ?: $kit['name']) ?></h1>
                <?php if ($kit['description'] !== ''): ?><p class="lead"><?= e($kit['description']) ?></p><?php endif; ?>
                <div class="hero-actions">
                    <a class="btn btn-dark" href="#katalog">Lihat katalog <?= kit_icon('arrow') ?></a>
                    <?php if ($kit['wa'] !== ''): ?><a class="btn btn-wa" href="<?= e($kit['wa']) ?>" target="_blank" rel="noopener"><?= kit_icon('whatsapp') ?> <?= e($chat) ?></a><?php endif; ?>
                </div>
            </div>
            <?php if ($kit['hero'] !== ''): ?>
                <img class="hero-photo" src="<?= e($kit['hero']) ?>" alt="<?= e($kit['name']) ?>" fetchpriority="high">
            <?php elseif ($spotlight !== null): ?>
                <article class="spot" <?= kit_item_attributes($spotlight, true) ?>>
                    <div class="media"><?php if ($spotlight['image'] !== ''): ?><img src="<?= e($spotlight['image']) ?>" alt="<?= e($spotlight['name']) ?>"><?php else: ?><?= e($spotlight['initials']) ?><?php endif; ?></div>
                    <div class="meta"><b><?= e($spotlight['name']) ?></b><span><?= e($spotlight['price']) ?></span></div>
                </article>
            <?php endif; ?>
        </div>
    </section>

    <section class="catalog" id="katalog">
        <div class="wrap" data-reveal>
            <div class="catalog-head">
                <h2>Katalog <?= e($noun) ?></h2>
                <div class="chips" role="group" aria-label="Kategori">
                    <button class="chip" type="button" data-filter="" aria-pressed="true">Semua</button>
                    <?php foreach ($kit['categories'] as $category): ?><button class="chip" type="button" data-filter="<?= e($category['slug']) ?>" aria-pressed="false"><?= e($category['name']) ?></button><?php endforeach; ?>
                </div>
            </div>
            <div class="grid">
                <?php foreach ($kit['items'] as $item): ?>
                    <article class="product" <?= kit_item_attributes($item) ?>>
                        <div class="media"><?php if ($item['image'] !== ''): ?><img src="<?= e($item['image']) ?>" alt="<?= e($item['name']) ?>" loading="lazy"><?php else: ?><?= e($item['initials']) ?><?php endif; ?></div>
                        <div class="info">
                            <span class="tagline"><?php if ($item['featured']): ?><span class="hot">Unggulan</span> · <?php endif; ?><?= e($item['categoryName']) ?></span>
                            <h3><?= e($item['name']) ?></h3>
                            <span class="price"><?= e($item['price']) ?></span>
                            <?php if ($item['wa'] !== ''): ?><a class="order" href="<?= e($item['wa']) ?>" target="_blank" rel="noopener"><?= kit_icon('whatsapp') ?> <?= e($kit['action']) ?></a><?php endif; ?>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
            <div class="empty" data-empty <?= $kit['items'] === [] ? '' : 'hidden' ?>><?= e($kit['noun']) ?> yang dicari belum ada. Tanyakan stoknya lewat WhatsApp.</div>
        </div>
    </section>

    <?php kit_about($kit); ?>
    <?php kit_highlights($kit); ?>
    <?php kit_gallery($kit); ?>
    <?php kit_testimonials($kit); ?>
    <?php kit_faq($kit); ?>

    <section class="how">
        <div class="wrap">
            <h2>Cara <?= e(mb_strtolower($kit['action'])) ?></h2>
            <ol>
                <li><?= kit_icon('search') ?> Pilih <?= e($noun) ?> di katalog</li>
                <li><?= kit_icon('whatsapp') ?> Tekan <?= e($kit['action']) ?>, pesan terisi otomatis</li>
                <li><?= kit_icon('check') ?> Konfirmasi ongkir dan pembayaran</li>
            </ol>
        </div>
    </section>
</main>

<footer class="footer">
    <div class="wrap">
        <div class="about">
            <a class="brand" href="#">
                <?php if ($kit['logo'] !== ''): ?><img src="<?= e($kit['logo']) ?>" alt=""><?php else: ?><span class="monogram"><?= e(kit_initials($kit['name'])) ?></span><?php endif; ?>
                <span><?= e($kit['name']) ?></span>
            </a>
            <?php if ($kit['tagline'] !== ''): ?><p><?= e($kit['tagline']) ?></p><?php endif; ?>
            <?php if ($kit['wa'] !== ''): ?><a class="btn btn-wa" href="<?= e($kit['wa']) ?>" target="_blank" rel="noopener"><?= kit_icon('whatsapp') ?> <?= e($chat) ?></a><?php endif; ?>
        </div>
        <div>
            <h3>Kontak</h3>
            <div class="contact-list">
                <?php if ($kit['address'] !== ''): ?><div><?= kit_icon('pin') ?><span><?= nl2br(e($kit['address'])) ?><?php if ($kit['maps'] !== ''): ?><br><a href="<?= e($kit['maps']) ?>" target="_blank" rel="noopener" style="text-decoration:underline">Buka Google Maps</a><?php endif; ?></span></div><?php endif; ?>
                <?= kit_hours($kit) ?>
                <?php if ($kit['phone'] !== ''): ?><a href="tel:<?= e(preg_replace('/[^0-9+]/', '', $kit['phone'])) ?>"><?= kit_icon('phone') ?> <?= e($kit['phone']) ?></a><?php endif; ?>
                <?php if ($kit['email'] !== ''): ?><a href="mailto:<?= e($kit['email']) ?>"><?= kit_icon('mail') ?> <?= e($kit['email']) ?></a><?php endif; ?>
            </div>
        </div>
        <div>
            <?php if ($kit['socials'] !== []): ?>
                <h3>Temukan kami</h3>
                <div class="socials"><?php foreach ($kit['socials'] as $social): ?><a class="social" href="<?= e($social['url']) ?>" target="_blank" rel="noopener"><?= kit_icon($social['icon']) ?> <?= e($social['label']) ?></a><?php endforeach; ?></div>
            <?php endif; ?>
        </div>
        <div class="copyright">© <?= e($kit['year']) ?> <?= e($kit['name']) ?></div>
    </div>
</footer>
<?php if ($kit['wa'] !== ''): ?><a class="float-wa" href="<?= e($kit['wa']) ?>" target="_blank" rel="noopener" aria-label="<?= e($chat) ?>"><?= kit_icon('whatsapp') ?><span><?= e($chat) ?></span></a><?php endif; ?>
<?php kit_interactions($kit); ?>
</body>
</html>
