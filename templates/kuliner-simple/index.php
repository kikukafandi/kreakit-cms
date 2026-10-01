<?php
/** @var array $business @var array $items @var array $categories @var array $socialLinks @var array $settings @var array $themeSettings */
require_once __DIR__ . '/../_shared/kit.php';

$kit = kit_prepare($business, $items, $categories, $socialLinks, $settings, $themeSettings, ['primary' => '#c2410c', 'secondary' => '#475569']);
$board = array_slice($kit['featured'] ?: $kit['items'], 0, 4);
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
    <link rel="preload" href="<?= e(url('/assets/fonts/bricolage-grotesque.woff2')) ?>" as="font" type="font/woff2" crossorigin>
    <style>
        <?= kit_font_face('Bricolage Grotesque', 'bricolage-grotesque') ?>
        /* Terracotta accent on a cool slate base. The accent is the admin's primary colour. */
        :root{color-scheme:light dark;--accent:<?= e($kit['primary']) ?>;--radius:<?= e($kit['radius']) ?>;--card-radius:<?= e($kit['cardRadius']) ?>;--wa:#15803d;
            --bg:#f6f7f9;--surface:#fff;--ink:#0f172a;--muted:#5b6476;--line:#e2e6ec;--tint:color-mix(in srgb,var(--accent) 10%,var(--surface));--board:#0f172a;--board-ink:#f8fafc}
        @media (prefers-color-scheme: dark){:root{--bg:#0b1120;--surface:#131b2e;--ink:#eef2f7;--muted:#98a2b5;--line:#232d42;--accent:color-mix(in srgb,<?= e($kit['primary']) ?> 78%,#fff);--tint:color-mix(in srgb,var(--accent) 14%,var(--surface));--board:#1b2540}}
        *{box-sizing:border-box}html{scroll-behavior:smooth}
        body{margin:0;font-family:'Bricolage Grotesque',system-ui,sans-serif;color:var(--ink);background:var(--bg);-webkit-font-smoothing:antialiased;font-size:16px}
        a{color:inherit}img{display:block;max-width:100%}button,input{font:inherit;color:inherit}
        h1,h2,h3{letter-spacing:-.025em;font-weight:700}
        .wrap{width:min(1180px,100% - 40px);margin-inline:auto}
        .btn{display:inline-flex;align-items:center;justify-content:center;gap:8px;padding:13px 20px;border-radius:var(--radius);font-weight:650;font-size:15px;text-decoration:none;border:0;cursor:pointer;white-space:nowrap;transition:transform .2s cubic-bezier(.16,1,.3,1),background .2s}
        .btn:hover{transform:translateY(-1px)}.btn:active{transform:scale(.98)}
        .btn-wa{background:var(--wa);color:#fff}.btn-ghost{background:var(--surface);color:var(--ink);box-shadow:inset 0 0 0 1px var(--line)}
        /* Header */
        .nav{position:sticky;top:0;z-index:20;background:color-mix(in srgb,var(--bg) 86%,transparent);backdrop-filter:blur(12px);border-bottom:1px solid var(--line)}
        .nav .wrap{display:flex;align-items:center;justify-content:space-between;gap:16px;height:68px}
        .brand{display:flex;align-items:center;gap:12px;text-decoration:none;font-weight:700;font-size:18px;min-width:0}
        .brand span:last-child{overflow:hidden;text-overflow:ellipsis;white-space:nowrap}
        .brand img,.monogram{width:40px;height:40px;border-radius:var(--radius);object-fit:cover;flex:none}
        .monogram{display:grid;place-items:center;background:var(--accent);color:#fff;font-size:15px;font-weight:700}
        .nav-links{display:flex;align-items:center;gap:24px;font-weight:550;font-size:15px}.nav-links a:not(.btn){text-decoration:none;color:var(--muted)}.nav-links a:not(.btn):hover{color:var(--ink)}
        .nav .btn{padding:10px 16px}
        /* Hero */
        .hero{padding:72px 0 80px}.hero .wrap{display:grid;grid-template-columns:1.15fr .85fr;gap:64px;align-items:center}
        .hero h1{font-size:clamp(36px,4.2vw,56px);line-height:1.06;margin:0 0 20px;max-width:20ch;text-wrap:balance}
        .lead{font-size:18px;line-height:1.65;color:var(--muted);margin:0 0 32px;max-width:52ch;display:-webkit-box;-webkit-line-clamp:3;-webkit-box-orient:vertical;overflow:hidden}
        .hero-actions{display:flex;flex-wrap:wrap;gap:12px}
        .hero-photo{aspect-ratio:4/5;width:100%;object-fit:cover;border-radius:var(--card-radius);box-shadow:0 30px 70px color-mix(in srgb,var(--ink) 18%,transparent)}
        .board{background:var(--board);color:var(--board-ink);border-radius:var(--card-radius);padding:32px 32px 18px;box-shadow:0 30px 70px color-mix(in srgb,var(--ink) 20%,transparent)}
        .board h2{font-size:22px;margin:0 0 6px}.board p{margin:0 0 14px;color:color-mix(in srgb,var(--board-ink) 62%,transparent);font-size:14px}
        .board-row{display:flex;align-items:baseline;gap:12px;padding:16px 0;border-top:1px solid color-mix(in srgb,var(--board-ink) 12%,transparent)}
        .board-row b{font-weight:600}.board-row i{flex:1;border-bottom:1px dotted color-mix(in srgb,var(--board-ink) 28%,transparent);transform:translateY(-4px)}
        .board-row span{font-weight:700;color:color-mix(in srgb,var(--accent) 55%,#fff);white-space:nowrap}
        /* Menu */
        .menu{padding:24px 0 88px}
        .menu h2{font-size:clamp(30px,3.4vw,42px);margin:0 0 8px}.menu-intro{color:var(--muted);margin:0 0 28px;max-width:60ch;line-height:1.6}
        .toolbar{display:flex;flex-wrap:wrap;gap:12px;align-items:center;justify-content:space-between;margin-bottom:24px}
        .chips{display:flex;gap:8px;overflow-x:auto;scrollbar-width:none;padding:2px;max-width:100%}
        .chip{white-space:nowrap;border:0;box-shadow:inset 0 0 0 1px var(--line);background:var(--surface);padding:10px 16px;border-radius:999px;font-weight:550;font-size:14px;cursor:pointer;transition:background .2s}
        .chip[aria-pressed=true]{background:var(--ink);color:var(--bg);box-shadow:none}
        .search{display:flex;align-items:center;gap:10px;background:var(--surface);box-shadow:inset 0 0 0 1px var(--line);border-radius:999px;padding:0 16px;min-width:270px;color:var(--muted)}
        .search input{border:0;outline:0;background:transparent;font-size:15px;padding:12px 0;width:100%;color:var(--ink)}.search input::placeholder{color:var(--muted)}
        .search:focus-within{box-shadow:inset 0 0 0 2px var(--accent)}
        .grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(250px,1fr));gap:20px}
        .card{background:var(--surface);border-radius:var(--card-radius);overflow:hidden;box-shadow:inset 0 0 0 1px var(--line);cursor:pointer;display:flex;flex-direction:column;transition:transform .3s cubic-bezier(.16,1,.3,1),box-shadow .3s}
        .card:hover{transform:translateY(-4px);box-shadow:inset 0 0 0 1px var(--line),0 22px 44px color-mix(in srgb,var(--ink) 10%,transparent)}
        .media{aspect-ratio:4/3;background:var(--tint);display:grid;place-items:center;font-size:42px;font-weight:700;color:var(--accent)}
        .media img{width:100%;height:100%;object-fit:cover}
        .card-body{padding:18px 20px 20px;display:flex;flex-direction:column;gap:6px;flex:1}
        .card-meta{display:flex;align-items:center;gap:6px;color:var(--muted);font-size:13px;font-weight:550}
        .card-meta .fav{display:inline-flex;align-items:center;gap:4px;color:var(--accent)}
        .card-body h3{margin:0;font-size:19px;line-height:1.3}.card-body p{margin:0;color:var(--muted);font-size:14.5px;line-height:1.55;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden}
        .card-foot{margin-top:auto;padding-top:14px;display:flex;align-items:center;justify-content:space-between;gap:10px}
        .price{font-weight:700;font-size:18px}
        .order{display:inline-flex;align-items:center;gap:6px;padding:9px 14px;border-radius:var(--radius);background:var(--tint);color:var(--accent);font-weight:650;font-size:14px;text-decoration:none;white-space:nowrap}
        .empty{text-align:center;padding:56px 20px;color:var(--muted);border-radius:var(--card-radius);box-shadow:inset 0 0 0 1px var(--line)}
        /* Visit band */
        .visit{background:var(--tint);border-radius:calc(var(--card-radius) + 10px);padding:56px;display:grid;grid-template-columns:1fr 1fr;gap:48px;align-items:start}
        .visit h2{font-size:clamp(28px,3vw,40px);margin:0 0 12px;line-height:1.1}.visit > div > p{margin:0 0 26px;color:var(--muted);line-height:1.6;max-width:42ch}
        .info{display:grid;gap:18px}.info-row{display:flex;gap:14px;align-items:flex-start;line-height:1.55}
        .info-row .icon{width:22px;height:22px;color:var(--accent);margin-top:1px}.info-row a{font-weight:600}
        .socials{display:flex;flex-wrap:wrap;gap:8px}.social{display:inline-flex;align-items:center;gap:8px;padding:9px 14px;border-radius:999px;background:var(--surface);text-decoration:none;font-weight:550;font-size:14px}.social:hover{color:var(--accent)}
        footer{padding:40px 0 112px;color:var(--muted);font-size:14px}footer .wrap{display:flex;justify-content:space-between;gap:12px;flex-wrap:wrap}
        .float-wa{position:fixed;right:20px;bottom:20px;z-index:30;display:inline-flex;align-items:center;gap:10px;padding:14px 20px;border-radius:999px;background:var(--wa);color:#fff;font-weight:650;text-decoration:none;box-shadow:0 14px 34px color-mix(in srgb,var(--wa) 38%,transparent)}.float-wa .icon{width:22px;height:22px}
        <?= kit_base_css() ?>
        <?= kit_sections_css() ?>
        @media(max-width:900px){.hero .wrap,.visit{grid-template-columns:1fr}.hero{padding:40px 0 56px}.hero .wrap{gap:40px}.nav-links a:not(.btn){display:none}.visit{padding:36px 28px;gap:32px}.search{min-width:0;flex:1}}
        @media(max-width:560px){.wrap{width:calc(100% - 32px)}.float-wa span{display:none}.float-wa{padding:16px}.board{padding:26px 24px 12px}.nav .btn span{display:none}.nav .btn{padding:10px 12px}}
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
            <a href="#menu">Menu</a>
            <a href="#lokasi">Lokasi</a>
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
                    <a class="btn btn-ghost" href="#menu">Lihat menu <?= kit_icon('arrow') ?></a>
                </div>
            </div>
            <?php if ($kit['hero'] !== ''): ?>
                <img class="hero-photo" src="<?= e($kit['hero']) ?>" alt="<?= e($kit['name']) ?>" fetchpriority="high">
            <?php elseif ($board !== []): ?>
                <div class="board">
                    <h2>Favorit pelanggan</h2>
                    <p>Paling sering dipesan di <?= e($kit['name']) ?></p>
                    <?php foreach ($board as $item): ?>
                        <div class="board-row"><b><?= e($item['name']) ?></b><i></i><span><?= e($item['price']) ?></span></div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <?php kit_about($kit); ?>

    <section class="menu" id="menu">
        <div class="wrap" data-reveal>
            <h2>Menu</h2>
            <p class="menu-intro">Klik menu untuk melihat detailnya, lalu <?= e(mb_strtolower($kit['action'])) ?> langsung lewat WhatsApp.</p>
            <div class="toolbar">
                <div class="chips" role="group" aria-label="Kategori">
                    <button class="chip" type="button" data-filter="" aria-pressed="true">Semua</button>
                    <?php foreach ($kit['categories'] as $category): ?><button class="chip" type="button" data-filter="<?= e($category['slug']) ?>" aria-pressed="false"><?= e($category['name']) ?></button><?php endforeach; ?>
                </div>
                <label class="search"><?= kit_icon('search') ?><input type="search" placeholder="Cari menu" data-search-input aria-label="Cari menu"></label>
            </div>
            <div class="grid">
                <?php foreach ($kit['items'] as $item): ?>
                    <article class="card" <?= kit_item_attributes($item) ?>>
                        <div class="media"><?php if ($item['image'] !== ''): ?><img src="<?= e($item['image']) ?>" alt="<?= e($item['name']) ?>" loading="lazy"><?php else: ?><?= e($item['initials']) ?><?php endif; ?></div>
                        <div class="card-body">
                            <div class="card-meta">
                                <?php if ($item['featured']): ?><span class="fav"><?= kit_icon('star') ?> Favorit</span><?php endif; ?>
                                <?php if ($item['featured'] && $item['categoryName'] !== ''): ?><span>·</span><?php endif; ?>
                                <span><?= e($item['categoryName']) ?></span>
                            </div>
                            <h3><?= e($item['name']) ?></h3>
                            <?php if ($item['summary'] !== ''): ?><p><?= e($item['summary']) ?></p><?php endif; ?>
                            <div class="card-foot">
                                <span class="price"><?= e($item['price']) ?></span>
                                <?php if ($item['wa'] !== ''): ?><a class="order" href="<?= e($item['wa']) ?>" target="_blank" rel="noopener"><?= e($kit['action']) ?> <?= kit_icon('arrow') ?></a><?php endif; ?>
                            </div>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
            <div class="empty" data-empty <?= $kit['items'] === [] ? '' : 'hidden' ?>>Menu belum tersedia. Tanya langsung lewat WhatsApp.</div>
        </div>
    </section>

    <?php kit_highlights($kit); ?>
    <?php kit_gallery($kit); ?>
    <?php kit_testimonials($kit); ?>
    <?php kit_faq($kit); ?>

    <section id="lokasi">
        <div class="wrap visit" data-reveal>
            <div>
                <h2>Mampir atau pesan antar</h2>
                <p>Datang langsung ke tempat kami, atau kirim pesanan lewat WhatsApp dan kami siapkan.</p>
                <?php if ($kit['wa'] !== ''): ?><a class="btn btn-wa" href="<?= e($kit['wa']) ?>" target="_blank" rel="noopener"><?= kit_icon('whatsapp') ?> <?= e($chat) ?></a><?php endif; ?>
            </div>
            <div class="info">
                <?php if ($kit['address'] !== ''): ?><div class="info-row"><?= kit_icon('pin') ?><div><?= nl2br(e($kit['address'])) ?><?php if ($kit['maps'] !== ''): ?><br><a href="<?= e($kit['maps']) ?>" target="_blank" rel="noopener">Buka Google Maps</a><?php endif; ?></div></div><?php endif; ?>
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
