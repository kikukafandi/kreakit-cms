<?php
/** @var array $business @var array $items @var array $categories @var array $socialLinks @var array $settings @var array $themeSettings */
require_once __DIR__ . '/../_shared/kit.php';

$kit = kit_prepare($business, $items, $categories, $socialLinks, $settings, $themeSettings, ['primary' => '#1d4ed8', 'secondary' => '#0f766e']);
$highlights = array_slice($kit['featured'] ?: $kit['items'], 0, 4);
$noun = mb_strtolower($kit['noun']);
$action = mb_strtolower($kit['action']);
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
    <link rel="preload" href="<?= e(url('/assets/fonts/manrope.woff2')) ?>" as="font" type="font/woff2" crossorigin>
    <style>
        <?= kit_font_face('Manrope', 'manrope') ?>
        /* Cobalt accent on cool neutrals, built to read as dependable rather than flashy. */
        :root{color-scheme:light dark;--accent:<?= e($kit['primary']) ?>;--radius:<?= e($kit['radius']) ?>;--card-radius:<?= e($kit['cardRadius']) ?>;--wa:#15803d;
            --bg:#f4f6fa;--surface:#fff;--ink:#0b1324;--muted:#566175;--line:#e1e6ee;--tint:color-mix(in srgb,var(--accent) 9%,var(--surface))}
        @media (prefers-color-scheme: dark){:root{--bg:#090e1a;--surface:#111829;--ink:#edf1f8;--muted:#9aa5b8;--line:#212a3e;--accent:color-mix(in srgb,<?= e($kit['primary']) ?> 70%,#fff);--tint:color-mix(in srgb,var(--accent) 13%,var(--surface))}}
        *{box-sizing:border-box}html{scroll-behavior:smooth}
        body{margin:0;font-family:'Manrope',system-ui,sans-serif;color:var(--ink);background:var(--bg);-webkit-font-smoothing:antialiased;font-size:16px}
        a{color:inherit}img{display:block;max-width:100%}button,input{font:inherit;color:inherit}
        h1,h2,h3{letter-spacing:-.03em;font-weight:800}
        .wrap{width:min(1160px,100% - 40px);margin-inline:auto}
        .btn{display:inline-flex;align-items:center;justify-content:center;gap:8px;padding:14px 22px;border-radius:var(--radius);font-weight:700;font-size:15px;text-decoration:none;border:0;cursor:pointer;white-space:nowrap;transition:transform .2s cubic-bezier(.16,1,.3,1)}
        .btn:hover{transform:translateY(-1px)}.btn:active{transform:scale(.98)}
        .btn-wa{background:var(--wa);color:#fff}.btn-ghost{background:var(--surface);color:var(--ink);box-shadow:inset 0 0 0 1px var(--line)}
        /* Header */
        .nav{position:sticky;top:0;z-index:20;background:color-mix(in srgb,var(--surface) 86%,transparent);backdrop-filter:blur(14px);border-bottom:1px solid var(--line)}
        .nav .wrap{display:flex;align-items:center;justify-content:space-between;gap:16px;height:68px}
        .brand{display:flex;align-items:center;gap:12px;text-decoration:none;font-weight:800;font-size:17px;min-width:0}.brand span:last-child{overflow:hidden;text-overflow:ellipsis;white-space:nowrap}
        .brand img,.monogram{width:40px;height:40px;border-radius:var(--radius);object-fit:cover;flex:none}
        .monogram{display:grid;place-items:center;background:var(--accent);color:#fff;font-size:14px;font-weight:800}
        .nav-links{display:flex;align-items:center;gap:26px;font-weight:600;font-size:14.5px}.nav-links a:not(.btn){text-decoration:none;color:var(--muted)}.nav-links a:not(.btn):hover{color:var(--accent)}
        .nav .btn{padding:10px 16px}
        /* Hero */
        .hero{padding:72px 0 56px}.hero .wrap{display:grid;grid-template-columns:1.1fr .9fr;gap:64px;align-items:center}
        .hero h1{font-size:clamp(36px,4.4vw,56px);line-height:1.06;margin:0 0 20px;max-width:18ch;text-wrap:balance}
        .lead{font-size:18px;line-height:1.65;color:var(--muted);margin:0 0 32px;max-width:50ch;display:-webkit-box;-webkit-line-clamp:3;-webkit-box-orient:vertical;overflow:hidden}
        .hero-actions{display:flex;flex-wrap:wrap;gap:12px}
        .hero-photo{width:100%;aspect-ratio:1;object-fit:cover;border-radius:var(--card-radius);box-shadow:0 30px 70px color-mix(in srgb,var(--ink) 16%,transparent)}
        .picks{background:var(--surface);border-radius:var(--card-radius);padding:10px 28px;box-shadow:0 30px 70px color-mix(in srgb,var(--ink) 10%,transparent),inset 0 0 0 1px var(--line)}
        .picks h2{font-size:15px;font-weight:700;color:var(--muted);letter-spacing:0;margin:18px 0 6px}
        .pick{display:flex;align-items:center;gap:16px;padding:16px 0;border-top:1px solid var(--line)}.pick:first-of-type{border-top:0}
        .pick .ic{width:46px;height:46px;border-radius:var(--radius);display:grid;place-items:center;background:var(--tint);color:var(--accent);font-weight:800;font-size:14px;flex:none;overflow:hidden}
        .pick .ic img{width:100%;height:100%;object-fit:cover}
        .pick div{flex:1;min-width:0}.pick b{display:block;font-size:16px}.pick small{color:var(--muted);font-size:13.5px}
        .pick strong{color:var(--accent);font-size:15px;white-space:nowrap}
        /* Proof strip */
        .proof{border-block:1px solid var(--line);background:var(--surface)}
        .proof .wrap{display:flex;flex-wrap:wrap;justify-content:space-between;gap:16px 32px;padding:22px 0;font-weight:700;font-size:15px}
        .proof span{display:inline-flex;align-items:center;gap:10px}.proof .icon{width:22px;height:22px;color:var(--accent)}
        /* Services */
        .services-section{padding:88px 0}
        .services-section h2,.steps-section h2{font-size:clamp(30px,3.4vw,42px);margin:0 0 10px}.intro{color:var(--muted);margin:0 0 28px;max-width:58ch;line-height:1.6}
        .toolbar{display:flex;flex-wrap:wrap;gap:12px;align-items:center;justify-content:space-between;margin-bottom:22px}
        .chips{display:flex;gap:8px;overflow-x:auto;scrollbar-width:none;padding:2px;max-width:100%}
        .chip{white-space:nowrap;border:0;box-shadow:inset 0 0 0 1px var(--line);background:var(--surface);color:var(--muted);padding:10px 16px;border-radius:999px;font-weight:700;font-size:14px;cursor:pointer}
        .chip[aria-pressed=true]{background:var(--accent);color:#fff;box-shadow:none}
        .search{display:flex;align-items:center;gap:10px;background:var(--surface);box-shadow:inset 0 0 0 1px var(--line);border-radius:999px;padding:0 16px;min-width:280px;color:var(--muted)}
        .search input{border:0;outline:0;background:transparent;font-weight:600;font-size:15px;padding:12px 0;width:100%;color:var(--ink)}.search input::placeholder{color:var(--muted)}
        .search:focus-within{box-shadow:inset 0 0 0 2px var(--accent)}
        .services{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:14px}
        .service{display:grid;grid-template-columns:auto 1fr auto;gap:18px;align-items:center;background:var(--surface);box-shadow:inset 0 0 0 1px var(--line);border-radius:var(--card-radius);padding:16px 18px;cursor:pointer;transition:box-shadow .25s,transform .25s cubic-bezier(.16,1,.3,1)}
        .service:hover{transform:translateY(-2px);box-shadow:inset 0 0 0 1px color-mix(in srgb,var(--accent) 45%,var(--line)),0 16px 36px color-mix(in srgb,var(--ink) 8%,transparent)}
        .thumb{width:76px;height:76px;border-radius:var(--radius);overflow:hidden;display:grid;place-items:center;background:var(--tint);color:var(--accent);font-weight:800;font-size:20px}
        .thumb img{width:100%;height:100%;object-fit:cover}
        .service h3{margin:0 0 4px;font-size:17px;letter-spacing:-.015em}.service p{margin:0;color:var(--muted);font-size:14px;line-height:1.55;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden}
        .popular{display:inline-flex;align-items:center;gap:4px;margin-bottom:4px;font-size:12.5px;font-weight:700;color:var(--accent)}
        .service-side{display:grid;gap:8px;justify-items:end;text-align:right}.service-side strong{font-size:16px;white-space:nowrap}
        .book{display:inline-flex;align-items:center;gap:6px;padding:8px 14px;border-radius:var(--radius);background:var(--tint);color:var(--accent);font-weight:700;font-size:13.5px;text-decoration:none;white-space:nowrap}
        .empty{text-align:center;padding:56px 20px;color:var(--muted);border-radius:var(--card-radius);box-shadow:inset 0 0 0 1px var(--line)}
        /* Steps: one horizontal track, not three identical cards */
        .steps-section{padding:0 0 88px}
        .track{list-style:none;margin:36px 0 0;padding:0;display:grid;grid-template-columns:repeat(3,1fr);gap:32px;position:relative}
        .track::before{content:"";position:absolute;top:27px;left:28px;right:28px;height:2px;background:linear-gradient(90deg,var(--accent),color-mix(in srgb,var(--accent) 20%,var(--line)))}
        .track li{position:relative}.track .ic{width:56px;height:56px;border-radius:50%;display:grid;place-items:center;background:var(--accent);color:#fff;box-shadow:0 0 0 8px var(--bg);font-size:22px;margin-bottom:20px}
        .track h3{margin:0 0 6px;font-size:19px}.track p{margin:0;color:var(--muted);line-height:1.6;max-width:32ch}
        /* Contact */
        .contact{background:var(--accent);color:#fff;border-radius:calc(var(--card-radius) + 10px);padding:52px;display:grid;grid-template-columns:1fr 1fr;gap:44px;align-items:start}
        .contact h2{font-size:clamp(28px,3vw,40px);margin:0 0 12px;line-height:1.1}.contact > div > p{margin:0 0 26px;color:color-mix(in srgb,#fff 82%,transparent);line-height:1.6;max-width:42ch}
        .contact .btn-wa{background:#fff;color:#14532d}
        .info{display:grid;gap:18px;background:color-mix(in srgb,#000 16%,transparent);border-radius:var(--card-radius);padding:26px}
        .info-row{display:flex;gap:14px;align-items:flex-start;line-height:1.55}.info-row .icon{width:22px;height:22px;margin-top:1px;opacity:.9}.info-row a{font-weight:700}
        .contact .ks-hours span,.contact .ks-hours .icon{color:inherit}
        .socials{display:flex;flex-wrap:wrap;gap:8px}.social{display:inline-flex;align-items:center;gap:8px;padding:9px 14px;border-radius:999px;background:color-mix(in srgb,#fff 14%,transparent);text-decoration:none;font-weight:600;font-size:14px}
        footer{padding:40px 0 112px;color:var(--muted);font-size:14px}footer .wrap{display:flex;justify-content:space-between;gap:12px;flex-wrap:wrap}
        .float-wa{position:fixed;right:20px;bottom:20px;z-index:30;display:inline-flex;align-items:center;gap:10px;padding:14px 20px;border-radius:999px;background:var(--wa);color:#fff;font-weight:700;text-decoration:none;box-shadow:0 14px 34px color-mix(in srgb,var(--wa) 38%,transparent)}.float-wa .icon{width:22px;height:22px}
        <?= kit_base_css() ?>
        <?= kit_sections_css() ?>
        @media(max-width:960px){.hero .wrap,.contact{grid-template-columns:1fr}.services{grid-template-columns:1fr}.nav-links a:not(.btn){display:none}.search{min-width:0;flex:1}.contact{padding:36px 26px}
            .track{grid-template-columns:1fr;gap:26px}.track::before{top:28px;bottom:28px;left:27px;right:auto;width:2px;height:auto;background:linear-gradient(180deg,var(--accent),color-mix(in srgb,var(--accent) 20%,var(--line)))}
            .track li{display:grid;grid-template-columns:56px 1fr;gap:0 18px}.track .ic{grid-row:span 2;margin:0}}
        @media(max-width:560px){.wrap{width:calc(100% - 32px)}.hero{padding:40px 0 44px}.service{grid-template-columns:auto 1fr;padding:14px}.service-side{grid-column:1/-1;grid-template-columns:1fr auto;align-items:center;justify-items:start;text-align:left}.thumb{width:60px;height:60px;font-size:17px}.float-wa span{display:none}.float-wa{padding:16px}.nav .btn span{display:none}.nav .btn{padding:10px 12px}.proof .wrap{justify-content:flex-start}}
    </style>
</head>
<body>
<header class="nav">
    <div class="wrap">
        <a class="brand" href="#">
            <?php if ($kit['logo'] !== ''): ?><img src="<?= e($kit['logo']) ?>" alt=""><?php else: ?><span class="monogram"><?= e(kit_initials($kit['name'])) ?></span><?php endif; ?>
            <span><?= e($kit['name']) ?></span>
        </a>
        <nav class="nav-links">
            <a href="#layanan"><?= e($kit['noun']) ?></a>
            <a href="#cara">Cara <?= e($action) ?></a>
            <a href="#kontak">Kontak</a>
            <?php if ($kit['wa'] !== ''): ?><a class="btn btn-wa" href="<?= e($kit['wa']) ?>" target="_blank" rel="noopener"><?= kit_icon('whatsapp') ?><span><?= e($chat) ?></span></a><?php endif; ?>
        </nav>
    </div>
</header>

<main>
    <section class="hero">
        <div class="wrap">
            <div>
                <h1><?= e($kit['tagline'] ?: $kit['name']) ?></h1>
                <?php if ($kit['description'] !== ''): ?><p class="lead"><?= e($kit['description']) ?></p><?php endif; ?>
                <div class="hero-actions">
                    <?php if ($kit['wa'] !== ''): ?><a class="btn btn-wa" href="<?= e($kit['wa']) ?>" target="_blank" rel="noopener"><?= kit_icon('whatsapp') ?> <?= e($chat) ?></a><?php endif; ?>
                    <a class="btn btn-ghost" href="#layanan">Lihat harga <?= kit_icon('arrow') ?></a>
                </div>
            </div>
            <?php if ($kit['hero'] !== ''): ?>
                <img class="hero-photo" src="<?= e($kit['hero']) ?>" alt="<?= e($kit['name']) ?>" fetchpriority="high">
            <?php elseif ($highlights !== []): ?>
                <div class="picks">
                    <h2><?= e($kit['noun']) ?> yang paling sering dipilih</h2>
                    <?php foreach ($highlights as $item): ?>
                        <div class="pick">
                            <span class="ic"><?php if ($item['image'] !== ''): ?><img src="<?= e($item['image']) ?>" alt=""><?php else: ?><?= e($item['initials']) ?><?php endif; ?></span>
                            <div><b><?= e($item['name']) ?></b><small><?= e($item['categoryName']) ?></small></div>
                            <strong><?= e($item['price']) ?></strong>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <div class="proof">
        <div class="wrap">
            <span><?= kit_icon('check') ?> <?= count($kit['items']) ?> pilihan <?= e($noun) ?></span>
            <span><?= kit_icon('check') ?> Harga tercantum sebelum <?= e($action) ?></span>
            <?php if ($kit['wa'] !== ''): ?><span><?= kit_icon('chat') ?> Konsultasi lewat WhatsApp</span><?php endif; ?>
            <?php if ($kit['address'] !== ''): ?><span><?= kit_icon('pin') ?> <?= e(strtok($kit['address'], ',')) ?></span><?php endif; ?>
        </div>
    </div>

    <?php kit_about($kit); ?>

    <section class="services-section" id="layanan">
        <div class="wrap" data-reveal>
            <h2><?= e($kit['noun']) ?> dan harga</h2>
            <p class="intro">Klik untuk melihat detail, lalu <?= e($action) ?> langsung lewat WhatsApp.</p>
            <div class="toolbar">
                <div class="chips" role="group" aria-label="Kategori">
                    <button class="chip" type="button" data-filter="" aria-pressed="true">Semua</button>
                    <?php foreach ($kit['categories'] as $category): ?><button class="chip" type="button" data-filter="<?= e($category['slug']) ?>" aria-pressed="false"><?= e($category['name']) ?></button><?php endforeach; ?>
                </div>
                <label class="search"><?= kit_icon('search') ?><input type="search" placeholder="Cari <?= e($noun) ?>" data-search-input aria-label="Cari <?= e($noun) ?>"></label>
            </div>
            <div class="services">
                <?php foreach ($kit['items'] as $item): ?>
                    <article class="service" <?= kit_item_attributes($item) ?>>
                        <div class="thumb"><?php if ($item['image'] !== ''): ?><img src="<?= e($item['image']) ?>" alt="<?= e($item['name']) ?>" loading="lazy"><?php else: ?><?= e($item['initials']) ?><?php endif; ?></div>
                        <div>
                            <?php if ($item['featured']): ?><span class="popular"><?= kit_icon('star') ?> Populer</span><?php endif; ?>
                            <h3><?= e($item['name']) ?></h3>
                            <?php if ($item['summary'] !== ''): ?><p><?= e($item['summary']) ?></p><?php endif; ?>
                        </div>
                        <div class="service-side">
                            <strong><?= e($item['price']) ?></strong>
                            <?php if ($item['wa'] !== ''): ?><a class="book" href="<?= e($item['wa']) ?>" target="_blank" rel="noopener"><?= e($kit['action']) ?> <?= kit_icon('arrow') ?></a><?php endif; ?>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
            <div class="empty" data-empty <?= $kit['items'] === [] ? '' : 'hidden' ?>><?= e($kit['noun']) ?> belum tersedia. Tanya langsung lewat WhatsApp.</div>
        </div>
    </section>

    <?php kit_highlights($kit); ?>

    <section class="steps-section" id="cara">
        <div class="wrap" data-reveal>
            <h2>Cara <?= e($action) ?></h2>
            <ol class="track">
                <li><span class="ic"><?= kit_icon('search') ?></span><h3>Pilih <?= e($noun) ?></h3><p>Lihat daftar dan harga di atas, pilih yang paling sesuai kebutuhanmu.</p></li>
                <li><span class="ic"><?= kit_icon('whatsapp') ?></span><h3>Kirim pesan</h3><p>Tekan tombol <?= e($kit['action']) ?>. Pesan sudah terisi, tinggal kirim dan sepakati jadwal.</p></li>
                <li><span class="ic"><?= kit_icon('check') ?></span><h3>Kami kerjakan</h3><p>Pesananmu kami konfirmasi lalu kerjakan sesuai jadwal yang disepakati.</p></li>
            </ol>
        </div>
    </section>

    <?php kit_gallery($kit); ?>
    <?php kit_testimonials($kit); ?>
    <?php kit_faq($kit); ?>

    <section id="kontak">
        <div class="wrap contact" data-reveal>
            <div>
                <h2>Masih ragu pilih yang mana?</h2>
                <p>Ceritakan kebutuhanmu lewat WhatsApp. Kami bantu pilihkan <?= e($noun) ?> yang paling pas.</p>
                <?php if ($kit['wa'] !== ''): ?><a class="btn btn-wa" href="<?= e($kit['wa']) ?>" target="_blank" rel="noopener"><?= kit_icon('whatsapp') ?> <?= e($chat) ?></a><?php endif; ?>
            </div>
            <div class="info">
                <?php if ($kit['address'] !== ''): ?><div class="info-row"><?= kit_icon('pin') ?><div><?= nl2br(e($kit['address'])) ?><?php if ($kit['maps'] !== ''): ?><br><a href="<?= e($kit['maps']) ?>" target="_blank" rel="noopener">Petunjuk arah</a><?php endif; ?></div></div><?php endif; ?>
                <?= kit_hours($kit) ?>
                <?php if ($kit['phone'] !== ''): ?><div class="info-row"><?= kit_icon('phone') ?><a href="tel:<?= e(preg_replace('/[^0-9+]/', '', $kit['phone'])) ?>"><?= e($kit['phone']) ?></a></div><?php endif; ?>
                <?php if ($kit['email'] !== ''): ?><div class="info-row"><?= kit_icon('mail') ?><a href="mailto:<?= e($kit['email']) ?>"><?= e($kit['email']) ?></a></div><?php endif; ?>
                <?php if ($kit['socials'] !== []): ?><div class="socials"><?php foreach ($kit['socials'] as $social): ?><a class="social" href="<?= e($social['url']) ?>" target="_blank" rel="noopener"><?= kit_icon($social['icon']) ?> <?= e($social['label']) ?></a><?php endforeach; ?></div><?php endif; ?>
            </div>
        </div>
    </section>
</main>

<footer><div class="wrap"><span>© <?= e($kit['year']) ?> <?= e($kit['name']) ?></span><span><?= e($kit['tagline']) ?></span></div></footer>
<?php if ($kit['wa'] !== ''): ?><a class="float-wa" href="<?= e($kit['wa']) ?>" target="_blank" rel="noopener" aria-label="<?= e($chat) ?>"><?= kit_icon('whatsapp') ?><span><?= e($chat) ?></span></a><?php endif; ?>
<?php kit_interactions($kit); ?>
</body>
</html>
