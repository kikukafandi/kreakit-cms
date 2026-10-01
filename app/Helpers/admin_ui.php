<?php

declare(strict_types=1);

/**
 * Compiled admin stylesheet (see tools/admin-css). Kept under the old name so existing callers keep working.
 */
function admin_tailwind_cdn(): string
{
    $file = base_path('public/assets/admin/admin.css');
    $version = is_file($file) ? (string) filemtime($file) : '1';
    return '<link rel="preload" href="' . e(url('/assets/fonts/manrope.woff2')) . '" as="font" type="font/woff2" crossorigin>' . "\n"
        . '<link rel="stylesheet" href="' . e(url('/assets/admin/admin.css?v=' . $version)) . '">';
}

/**
 * Phosphor icon shared with the public templates (templates/_shared/icons.php).
 */
function admin_icon(string $name, string $class = 'admin-icon'): string
{
    static $icons;
    $icons ??= require base_path('templates/_shared/icons.php');

    return '<svg class="' . e($class) . '" viewBox="0 0 256 256" fill="currentColor" aria-hidden="true">' . ($icons[$name] ?? '') . '</svg>';
}

function admin_head(string $title): void
{
    ?>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex">
    <title><?= e($title) ?> - <?= e(app_config('app.name', 'KreaKit CMS')) ?></title>
    <?= admin_tailwind_cdn() ?>
    <?php
}

function admin_nav_items(): array
{
    return [
        'dashboard' => ['label' => 'Dashboard', 'href' => url('/admin/dashboard.php'), 'icon' => 'house'],
        'business' => ['label' => 'Profil Bisnis', 'href' => url('/admin/business.php'), 'icon' => 'bag'],
        'templates' => ['label' => 'Template', 'href' => url('/admin/templates.php'), 'icon' => 'palette'],
        'sections' => ['label' => 'Konten Halaman', 'href' => url('/admin/sections.php'), 'icon' => 'article'],
        'categories' => ['label' => 'Kategori', 'href' => url('/admin/categories.php'), 'icon' => 'squares'],
        'items' => ['label' => 'Produk/Layanan', 'href' => url('/admin/items.php'), 'icon' => 'package'],
        'password' => ['label' => 'Password', 'href' => url('/admin/password.php'), 'icon' => 'lock'],
    ];
}

function admin_layout_start(string $title, string $active, ?string $subtitle = null): void
{
    $admin = \KreaKit\Core\Auth::admin();
    $csrfKey = (string) app_config('security.csrf_key', '_csrf_token');
    $name = (string) ($admin['name'] ?? 'Admin');
    ?>
    <!doctype html>
    <html lang="id">
    <head><?php admin_head($title); ?></head>
    <body class="min-h-screen bg-slate-50 text-slate-900">
        <div class="lg:flex">
            <aside class="border-b border-slate-200 bg-white lg:fixed lg:inset-y-0 lg:left-0 lg:flex lg:w-64 lg:flex-col lg:border-b-0 lg:border-r">
                <div class="flex h-16 items-center justify-between gap-3 px-5 lg:h-[72px]">
                    <a href="<?= e(url('/admin/dashboard.php')) ?>" class="flex items-center gap-3">
                        <span class="grid size-9 place-items-center rounded-xl bg-brand-700 text-white"><?= admin_icon('sparkle') ?></span>
                        <span class="leading-tight"><span class="block text-[15px] font-extrabold tracking-tight text-slate-950">KreaKit</span><span class="block text-xs font-medium text-slate-500">Panel website</span></span>
                    </a>
                    <a href="<?= e(url('/')) ?>" target="_blank" rel="noopener" class="inline-flex items-center gap-1.5 rounded-lg px-2.5 py-1.5 text-sm font-semibold text-brand-700 hover:bg-brand-50 lg:hidden"><?= admin_icon('external') ?> Website</a>
                </div>
                <nav aria-label="Navigasi admin" class="flex gap-1 overflow-x-auto px-3 pb-3 [scrollbar-width:none] lg:flex-1 lg:flex-col lg:overflow-visible lg:pb-0 lg:pt-2">
                    <?php foreach (admin_nav_items() as $key => $item): $isActive = $key === $active; ?>
                        <a href="<?= e($item['href']) ?>" <?= $isActive ? 'aria-current="page"' : '' ?> class="flex shrink-0 items-center gap-3 whitespace-nowrap rounded-xl px-3 py-2.5 text-sm font-semibold transition <?= $isActive ? 'bg-brand-50 text-brand-800' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' ?>">
                            <span class="text-[18px] <?= $isActive ? 'text-brand-700' : 'text-slate-400' ?>"><?= admin_icon($item['icon']) ?></span><?= e($item['label']) ?>
                        </a>
                    <?php endforeach; ?>
                </nav>
                <div class="hidden border-t border-slate-200 p-4 lg:block">
                    <div class="flex items-center gap-3">
                        <span class="grid size-9 shrink-0 place-items-center rounded-full bg-slate-100 text-sm font-bold text-slate-600"><?= e(mb_strtoupper(mb_substr($name, 0, 1))) ?></span>
                        <div class="min-w-0 flex-1 leading-tight">
                            <p class="truncate text-sm font-semibold text-slate-800"><?= e($name) ?></p>
                            <p class="truncate text-xs text-slate-500"><?= e($admin['email'] ?? '') ?></p>
                        </div>
                        <form method="post" action="<?= e(url('/admin/logout.php')) ?>">
                            <?= \KreaKit\Core\Csrf::field($csrfKey) ?>
                            <button type="submit" class="grid size-9 place-items-center rounded-lg text-slate-500 hover:bg-slate-100 hover:text-slate-900" aria-label="Logout" title="Logout"><?= admin_icon('sign-out') ?></button>
                        </form>
                    </div>
                </div>
            </aside>
            <main class="min-w-0 flex-1 px-4 py-6 sm:px-6 lg:ml-64 lg:px-10 lg:py-9">
                <div class="mx-auto max-w-6xl">
                    <header class="mb-7 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                        <div>
                            <h1 class="text-2xl font-extrabold tracking-tight text-slate-950 sm:text-[28px]"><?= e($title) ?></h1>
                            <?php if ($subtitle): ?><p class="mt-1.5 max-w-2xl text-sm leading-6 text-slate-500"><?= e($subtitle) ?></p><?php endif; ?>
                        </div>
                        <a href="<?= e(url('/')) ?>" target="_blank" rel="noopener" class="hidden shrink-0 items-center gap-2 rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 hover:border-slate-400 hover:text-slate-950 sm:inline-flex"><?= admin_icon('external') ?> Lihat website</a>
                    </header>
    <?php
}

function admin_layout_end(): void
{
    ?>
                    <form method="post" action="<?= e(url('/admin/logout.php')) ?>" class="mt-10 border-t border-slate-200 pt-6 lg:hidden">
                        <?= \KreaKit\Core\Csrf::field((string) app_config('security.csrf_key', '_csrf_token')) ?>
                        <button type="submit" class="inline-flex items-center gap-2 text-sm font-semibold text-slate-500 hover:text-slate-900"><?= admin_icon('sign-out') ?> Logout</button>
                    </form>
                </div>
            </main>
        </div>
    </body>
    </html>
    <?php
}

function admin_card(string $extra = ''): string
{
    return 'rounded-2xl border border-slate-200 bg-white p-5 sm:p-6 ' . $extra;
}

function admin_label_class(): string
{
    return 'block text-sm font-semibold text-slate-700';
}

function admin_input_class(string $extra = ''): string
{
    return 'mt-1.5 w-full rounded-xl border border-slate-300 bg-white px-3.5 py-2.5 text-sm text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-brand-600 focus:ring-4 focus:ring-brand-100 file:mr-3 file:rounded-lg file:border-0 file:bg-slate-100 file:px-3 file:py-1.5 file:text-sm file:font-semibold file:text-slate-700 hover:file:bg-slate-200 ' . $extra;
}

function admin_help_class(): string
{
    return 'mt-1.5 text-xs leading-5 text-slate-500';
}

function admin_primary_button(string $extra = ''): string
{
    return 'inline-flex items-center justify-center gap-2 rounded-xl bg-brand-700 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-brand-800 active:scale-[.98] disabled:cursor-not-allowed disabled:bg-slate-300 ' . $extra;
}

function admin_secondary_button(string $extra = ''): string
{
    return 'inline-flex items-center justify-center gap-2 rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 transition hover:border-slate-400 hover:text-slate-950 active:scale-[.98] ' . $extra;
}

function admin_danger_button(): string
{
    return 'inline-flex items-center justify-center rounded-lg border border-red-200 bg-white px-3 py-1.5 text-xs font-semibold text-red-700 hover:bg-red-50';
}

function admin_status_badge(bool $active, string $trueLabel = 'Aktif', string $falseLabel = 'Nonaktif'): string
{
    $class = $active ? 'bg-emerald-50 text-emerald-700 ring-emerald-200' : 'bg-slate-100 text-slate-500 ring-slate-200';
    return '<span class="inline-flex rounded-full px-2.5 py-0.5 text-xs font-semibold ring-1 ' . $class . '">' . e($active ? $trueLabel : $falseLabel) . '</span>';
}

function admin_flash_block(?string $success, ?string $error): void
{
    if ($success) {
        echo '<div role="status" class="mb-5 flex items-center gap-2.5 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-800">' . admin_icon('check-circle', 'admin-icon text-lg') . e($success) . '</div>';
    }
    if ($error) {
        echo '<div role="alert" class="mb-5 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm font-semibold text-red-800">' . e($error) . '</div>';
    }
}

function admin_empty_state(string $title, string $body): void
{
    ?>
    <div class="rounded-2xl border border-dashed border-slate-300 bg-white px-6 py-10 text-center">
        <p class="text-base font-bold text-slate-900"><?= e($title) ?></p>
        <p class="mt-1.5 text-sm text-slate-500"><?= e($body) ?></p>
    </div>
    <?php
}

function template_preview_image_url(string $slug): ?string
{
    if (!preg_match('/^[a-z0-9-]{1,100}$/', $slug)) {
        return null;
    }
    foreach (['preview.png', 'preview.jpg', 'preview.webp'] as $file) {
        if (is_file(base_path('templates/' . $slug . '/' . $file))) {
            return url('/admin/template-preview.php?slug=' . rawurlencode($slug));
        }
    }
    return null;
}
