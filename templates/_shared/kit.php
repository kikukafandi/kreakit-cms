<?php
/**
 * Shared view helpers for the public templates.
 * Templates only format data here; nothing in this file writes to the database.
 */

declare(strict_types=1);

require_once __DIR__ . '/sections.php';

/**
 * Normalises everything a template needs from the variables public/index.php provides.
 */
function kit_prepare(array $business, array $items, array $categories, array $socialLinks, array $settings, array $themeSettings, array $defaults): array
{
    $color = static fn (string $key, string $fallback): string => preg_match('/^#[0-9a-fA-F]{6}$/', (string) ($themeSettings[$key] ?? '')) ? (string) $themeSettings[$key] : $fallback;
    $name = (string) ($business['business_name'] ?? '') ?: 'Nama Bisnis';
    $isServices = ($themeSettings['catalog_mode'] ?? 'products') === 'services';
    $whatsapp = (string) ($business['whatsapp_number'] ?? '');
    // One shape rule per page: the admin's button style also decides card and chip corners.
    $shape = ['square' => ['4px', '8px'], 'pill' => ['999px', '24px']][$themeSettings['button_style'] ?? ''] ?? ['12px', '18px'];

    $usedCategories = [];
    $prepared = [];
    foreach ($items as $item) {
        $itemName = (string) ($item['name'] ?? '');
        $prepared[] = [
            'name' => $itemName,
            'summary' => (string) ($item['short_description'] ?? ''),
            'description' => (string) ($item['description'] ?? ''),
            'price' => (string) (($item['price_label'] ?? '') ?: rupiah($item['price'] ?? null)),
            'image' => public_upload_url($item['image_path'] ?? null),
            'initials' => kit_initials($itemName),
            'category' => (string) ($item['category_slug'] ?? ''),
            'categoryName' => (string) ($item['category_name'] ?? ''),
            'featured' => !empty($item['is_featured']),
            'wa' => $whatsapp !== '' ? whatsapp_url($whatsapp, (string) (($item['whatsapp_message'] ?? '') ?: 'Halo ' . $name . ', saya tertarik dengan ' . $itemName)) : '',
        ];
        $usedCategories[(string) ($item['category_slug'] ?? '')] = true;
    }

    return [
        'name' => $name,
        'tagline' => (string) ($business['tagline'] ?? ''),
        'description' => (string) ($business['description'] ?? ''),
        'metaTitle' => (string) (($settings['site_meta_title'] ?? '') ?: $name),
        'metaDescription' => (string) (($settings['site_meta_description'] ?? '') ?: ($business['tagline'] ?? '')),
        'primary' => $color('primary_color', $defaults['primary']),
        'secondary' => $color('secondary_color', $defaults['secondary']),
        'radius' => $shape[0],
        'cardRadius' => $shape[1],
        'logo' => public_upload_url($business['logo_path'] ?? null),
        'hero' => public_upload_url($business['hero_image_path'] ?? null),
        'wa' => $whatsapp !== '' ? whatsapp_url($whatsapp, 'Halo ' . $name . ', saya mau tanya.') : '',
        'address' => (string) ($business['address'] ?? ''),
        'maps' => (string) ($business['maps_url'] ?? ''),
        'phone' => (string) ($business['phone'] ?? ''),
        'email' => (string) ($business['email'] ?? ''),
        'noun' => $isServices ? 'Layanan' : 'Produk',
        'action' => $isServices ? 'Booking' : 'Pesan',
        'items' => $prepared,
        'featured' => array_values(array_filter($prepared, static fn (array $item): bool => $item['featured'])),
        // Only categories that actually hold an item get a filter chip.
        'categories' => array_values(array_filter($categories, static fn (array $category): bool => isset($usedCategories[(string) $category['slug']]))),
        'socials' => array_map(static fn (array $link): array => [
            'url' => (string) $link['url'],
            'label' => (string) (($link['label'] ?? '') ?: ucfirst((string) $link['platform'])),
            'icon' => in_array($link['platform'], ['instagram', 'facebook', 'tiktok', 'whatsapp'], true) ? (string) $link['platform'] : ($link['platform'] === 'marketplace' ? 'bag' : 'globe'),
        ], $socialLinks),
        'sections' => kit_sections_data($settings),
        'year' => date('Y'),
    ];
}

function kit_initials(string $name): string
{
    $words = preg_split('/\s+/', trim($name)) ?: [];
    $letters = array_map(static fn (string $word): string => mb_substr($word, 0, 1), array_slice($words, 0, 2));
    return mb_strtoupper(implode('', $letters)) ?: '?';
}

/**
 * Phosphor icon (regular weight) as inline SVG, so templates need no icon font or CDN.
 */
function kit_icon(string $name, string $class = 'icon'): string
{
    static $icons;
    $icons ??= require __DIR__ . '/icons.php';

    return '<svg class="' . e($class) . '" viewBox="0 0 256 256" fill="currentColor" aria-hidden="true">' . ($icons[$name] ?? $icons['globe']) . '</svg>';
}

/**
 * Self-hosted variable font from public/assets/fonts (see LICENSE.txt there).
 */
function kit_font_face(string $family, string $file): string
{
    return "@font-face{font-family:'" . $family . "';src:url('" . e(url('/assets/fonts/' . $file . '.woff2')) . "') format('woff2');font-weight:400 800;font-display:swap}";
}

/**
 * Item card data attributes read by the shared script (filter, search, detail dialog).
 */
function kit_item_attributes(array $item, bool $pinned = false): string
{
    // Pinned items (e.g. a hero spotlight) open the dialog but are never hidden by filters.
    return 'data-item' . ($pinned ? ' data-pinned' : '') . ' data-category="' . e($item['category']) . '" data-search="' . e(mb_strtolower($item['name'] . ' ' . $item['summary'] . ' ' . $item['categoryName'])) . '"'
        . ' data-name="' . e($item['name']) . '" data-price="' . e($item['price']) . '" data-summary="' . e($item['summary']) . '"'
        . ' data-description="' . e($item['description']) . '" data-image="' . e($item['image']) . '" data-initials="' . e($item['initials']) . '" data-wa="' . e($item['wa']) . '"'
        . ' tabindex="0" role="button" aria-label="Lihat detail ' . e($item['name']) . '"';
}

/**
 * Item detail dialog plus the small script behind category chips, search, the dialog and scroll reveal.
 * Templates style it through the .kit-* classes and their own CSS variables.
 */
function kit_interactions(array $kit): void
{
    ?>
    <dialog class="kit-dialog" id="kitDialog" aria-labelledby="kitDialogName">
        <div class="kit-dialog-media" id="kitDialogMedia"></div>
        <div class="kit-dialog-body">
            <h3 id="kitDialogName"></h3>
            <p class="kit-dialog-price" id="kitDialogPrice"></p>
            <p class="kit-dialog-text" id="kitDialogText"></p>
            <a class="kit-dialog-cta" id="kitDialogCta" target="_blank" rel="noopener"><?= kit_icon('whatsapp') ?> <?= e($kit['action']) ?> via WhatsApp</a>
        </div>
        <button type="button" class="kit-dialog-close" id="kitDialogClose" aria-label="Tutup"><?= kit_icon('close') ?></button>
    </dialog>
    <script>
    (() => {
        const items = [...document.querySelectorAll('[data-item]')];
        const empty = document.querySelector('[data-empty]');
        let category = '', query = '';

        const apply = () => {
            let shown = 0;
            items.filter(item => !('pinned' in item.dataset)).forEach(item => {
                const visible = (!category || item.dataset.category === category) && item.dataset.search.includes(query);
                item.hidden = !visible;
                shown += visible;
            });
            if (empty) empty.hidden = shown > 0;
        };

        document.querySelectorAll('[data-filter]').forEach(chip => chip.addEventListener('click', () => {
            category = chip.dataset.filter;
            document.querySelectorAll('[data-filter]').forEach(other => other.setAttribute('aria-pressed', String(other === chip)));
            apply();
        }));
        document.querySelectorAll('[data-search-input]').forEach(input => input.addEventListener('input', () => {
            query = input.value.trim().toLowerCase();
            apply();
        }));

        const dialog = document.getElementById('kitDialog');
        const fill = (id, text) => { const node = document.getElementById(id); node.textContent = text; node.hidden = !text; };
        const open = item => {
            const data = item.dataset;
            const media = document.getElementById('kitDialogMedia');
            media.replaceChildren();
            if (data.image) {
                const image = new Image();
                image.src = data.image;
                image.alt = data.name;
                media.append(image);
            } else {
                media.textContent = data.initials;
            }
            fill('kitDialogName', data.name);
            fill('kitDialogPrice', data.price);
            fill('kitDialogText', data.description || data.summary);
            const cta = document.getElementById('kitDialogCta');
            cta.href = data.wa;
            cta.hidden = !data.wa;
            dialog.showModal();
        };
        items.forEach(item => {
            item.addEventListener('click', event => { if (!event.target.closest('a')) open(item); });
            item.addEventListener('keydown', event => { if (event.key === 'Enter' && event.target === item) open(item); });
        });
        document.getElementById('kitDialogClose').addEventListener('click', () => dialog.close());
        dialog.addEventListener('click', event => { if (event.target === dialog) dialog.close(); });

        // Reveal sections once as they enter the viewport; skipped entirely for reduced motion.
        if (!matchMedia('(prefers-reduced-motion: reduce)').matches && 'IntersectionObserver' in window) {
            const observer = new IntersectionObserver(entries => entries.forEach(entry => {
                if (!entry.isIntersecting) return;
                entry.target.classList.add('is-in');
                observer.unobserve(entry.target);
            }), { rootMargin: '0px 0px -8% 0px' }); // no threshold ratio: a long catalog may never reach it
            document.querySelectorAll('[data-reveal]').forEach(node => { node.classList.add('reveal'); observer.observe(node); });
        }
    })();
    </script>
    <?php
}

/**
 * Styles shared by every template: dialog, reveal motion, focus ring. Colours come from each template's variables.
 */
function kit_base_css(): string
{
    return '[hidden]{display:none!important}.icon{width:1.15em;height:1.15em;flex:none}'
        . ':focus-visible{outline:2px solid var(--accent);outline-offset:3px}'
        . '.reveal{opacity:0;transform:translateY(18px);transition:opacity .7s cubic-bezier(.16,1,.3,1),transform .7s cubic-bezier(.16,1,.3,1)}.reveal.is-in{opacity:1;transform:none}'
        . '.kit-dialog{border:0;padding:0;width:min(540px,calc(100% - 32px));border-radius:var(--card-radius);overflow:hidden;color:var(--ink);background:var(--surface);box-shadow:0 40px 120px color-mix(in srgb,var(--ink) 35%,transparent)}'
        . '.kit-dialog::backdrop{background:color-mix(in srgb,var(--ink) 55%,transparent);backdrop-filter:blur(4px)}'
        . '.kit-dialog-media{aspect-ratio:4/3;display:grid;place-items:center;font-size:64px;font-weight:700;color:var(--accent);background:var(--tint)}'
        . '.kit-dialog-media img{width:100%;height:100%;object-fit:cover}'
        . '.kit-dialog-body{padding:24px 26px 28px;display:grid;gap:10px}.kit-dialog-body h3{margin:0;font-size:24px;line-height:1.2;letter-spacing:-.02em}'
        . '.kit-dialog-price{margin:0;font-weight:700;font-size:18px;color:var(--accent)}.kit-dialog-text{margin:0;color:var(--muted);line-height:1.65;white-space:pre-line}'
        . '.kit-dialog-cta{margin-top:8px;display:inline-flex;align-items:center;justify-content:center;gap:8px;padding:14px 18px;border-radius:var(--radius);background:var(--wa);color:#fff;font-weight:700;text-decoration:none}'
        . '.kit-dialog-close{position:absolute;top:14px;right:14px;width:40px;height:40px;border-radius:50%;border:0;background:color-mix(in srgb,var(--surface) 92%,transparent);display:grid;place-items:center;cursor:pointer;color:var(--ink);font-size:18px}'
        . '@media (prefers-reduced-motion: reduce){*,*::before,*::after{transition:none!important;animation:none!important;scroll-behavior:auto!important}}';
}
