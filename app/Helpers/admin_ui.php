<?php

declare(strict_types=1);

function admin_tailwind_cdn(): string
{
    return '<script src="https://cdn.tailwindcss.com"></script>' . "\n"
        . '<script>tailwind.config={theme:{extend:{fontFamily:{sans:[\'Inter\',\'ui-sans-serif\',\'system-ui\',\'sans-serif\']},colors:{brand:{50:\'#ecfeff\',100:\'#cffafe\',600:\'#0891b2\',700:\'#0e7490\',900:\'#164e63\'}}}}}</script>';
}

function admin_head(string $title): void
{
    ?>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($title) ?> — <?= e(app_config('app.name', 'KreaKit CMS')) ?></title>
    <?= admin_tailwind_cdn() ?>
    <?php
}

function admin_nav_items(): array
{
    return [
        'dashboard' => ['label' => 'Dashboard', 'href' => url('/admin/dashboard.php'), 'icon' => '🏠'],
        'business' => ['label' => 'Profil Bisnis', 'href' => url('/admin/business.php'), 'icon' => '🏪'],
        'templates' => ['label' => 'Template', 'href' => url('/admin/templates.php'), 'icon' => '🎨'],
        'categories' => ['label' => 'Kategori', 'href' => url('/admin/categories.php'), 'icon' => '🗂️'],
        'items' => ['label' => 'Produk/Layanan', 'href' => url('/admin/items.php'), 'icon' => '🛍️'],
        'password' => ['label' => 'Password', 'href' => url('/admin/password.php'), 'icon' => '🔐'],
    ];
}

function admin_layout_start(string $title, string $active, ?string $subtitle = null): void
{
    $admin = \KreaKit\Core\Auth::admin();
    $csrfKey = (string) app_config('security.csrf_key', '_csrf_token');
    ?>
    <!doctype html>
    <html lang="id">
    <head><?php admin_head($title); ?></head>
    <body class="min-h-screen bg-slate-100 text-slate-900 antialiased">
        <div class="flex min-h-screen flex-col lg:flex-row">
            <aside class="border-b border-slate-200 bg-white/95 shadow-sm lg:w-72 lg:border-b-0 lg:border-r">
                <div class="flex items-center justify-between gap-4 px-5 py-5 lg:block">
                    <a href="<?= e(url('/admin/dashboard.php')) ?>" class="block">
                        <span class="text-xs font-semibold uppercase tracking-[0.3em] text-brand-700">KreaKit</span>
                        <span class="mt-1 block text-2xl font-black text-slate-950">Admin CMS</span>
                    </a>
                    <a href="<?= e(url('/')) ?>" target="_blank" rel="noopener" class="rounded-full bg-brand-50 px-4 py-2 text-sm font-semibold text-brand-700 hover:bg-brand-100">Preview</a>
                </div>
                <nav aria-label="Navigasi admin" class="grid gap-1 px-3 pb-5 sm:grid-cols-2 lg:block">
                    <?php foreach (admin_nav_items() as $key => $item): ?>
                        <a href="<?= e($item['href']) ?>" class="flex items-center gap-3 rounded-2xl px-4 py-3 text-sm font-semibold <?= $key === $active ? 'bg-slate-950 text-white shadow-lg shadow-slate-300/50' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-950' ?>">
                            <span aria-hidden="true"><?= e($item['icon']) ?></span><span><?= e($item['label']) ?></span>
                        </a>
                    <?php endforeach; ?>
                </nav>
                <div class="border-t border-slate-100 px-5 py-5 text-sm text-slate-500">
                    <p class="font-semibold text-slate-700"><?= e($admin['name'] ?? 'Admin') ?></p>
                    <p class="break-all"><?= e($admin['email'] ?? '') ?></p>
                    <form method="post" action="<?= e(url('/admin/logout.php')) ?>" class="mt-4">
                        <?= \KreaKit\Core\Csrf::field($csrfKey) ?>
                        <button type="submit" class="w-full rounded-xl border border-slate-200 px-4 py-2 font-semibold text-slate-600 hover:bg-slate-50">Logout</button>
                    </form>
                </div>
            </aside>
            <main class="flex-1 px-4 py-6 sm:px-6 lg:px-8">
                <div class="mx-auto max-w-7xl">
                    <header class="mb-6 rounded-3xl bg-gradient-to-br from-slate-950 via-slate-900 to-brand-900 p-6 text-white shadow-xl shadow-slate-300/40 sm:p-8">
                        <div class="flex flex-col gap-4 md:flex-row md:items-end md:justify-between">
                            <div>
                                <p class="text-sm font-semibold uppercase tracking-[0.25em] text-brand-100">Panel UMKM</p>
                                <h1 class="mt-2 text-3xl font-black tracking-tight sm:text-4xl"><?= e($title) ?></h1>
                                <?php if ($subtitle): ?><p class="mt-3 max-w-2xl text-sm leading-6 text-slate-200 sm:text-base"><?= e($subtitle) ?></p><?php endif; ?>
                            </div>
                            <a href="<?= e(url('/')) ?>" target="_blank" rel="noopener" class="inline-flex items-center justify-center rounded-2xl bg-white px-5 py-3 text-sm font-bold text-slate-950 shadow hover:bg-brand-50">Lihat Website</a>
                        </div>
                    </header>
    <?php
}

function admin_layout_end(): void
{
    ?>
                </div>
            </main>
        </div>
    </body>
    </html>
    <?php
}

function admin_card(string $extra = ''): string
{
    return 'rounded-3xl border border-slate-200 bg-white p-5 shadow-sm shadow-slate-200/70 sm:p-6 ' . $extra;
}

function admin_label_class(): string
{
    return 'block text-sm font-bold text-slate-700';
}

function admin_input_class(): string
{
    return 'mt-2 w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-slate-900 shadow-sm outline-none transition placeholder:text-slate-400 focus:border-brand-600 focus:ring-4 focus:ring-brand-100';
}

function admin_help_class(): string
{
    return 'mt-1 text-xs leading-5 text-slate-500';
}

function admin_primary_button(string $extra = ''): string
{
    return 'inline-flex items-center justify-center rounded-2xl bg-slate-950 px-5 py-3 text-sm font-bold text-white shadow-lg shadow-slate-300/60 hover:bg-brand-700 disabled:cursor-not-allowed disabled:bg-slate-300 ' . $extra;
}

function admin_secondary_button(string $extra = ''): string
{
    return 'inline-flex items-center justify-center rounded-2xl border border-slate-200 bg-white px-5 py-3 text-sm font-bold text-slate-700 hover:bg-slate-50 ' . $extra;
}

function admin_danger_button(): string
{
    return 'inline-flex items-center justify-center rounded-xl border border-red-200 bg-red-50 px-3 py-2 text-xs font-bold text-red-700 hover:bg-red-100';
}

function admin_status_badge(bool $active, string $trueLabel = 'Aktif', string $falseLabel = 'Nonaktif'): string
{
    $class = $active ? 'bg-emerald-50 text-emerald-700 ring-emerald-200' : 'bg-slate-100 text-slate-500 ring-slate-200';
    return '<span class="inline-flex rounded-full px-3 py-1 text-xs font-bold ring-1 ' . $class . '">' . e($active ? $trueLabel : $falseLabel) . '</span>';
}

function admin_flash_block(?string $success, ?string $error): void
{
    if ($success) {
        echo '<div role="status" class="mb-4 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-800">' . e($success) . '</div>';
    }
    if ($error) {
        echo '<div role="alert" class="mb-4 rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm font-semibold text-red-800">' . e($error) . '</div>';
    }
}

function admin_empty_state(string $title, string $body): void
{
    ?>
    <div class="rounded-3xl border border-dashed border-slate-300 bg-white p-8 text-center">
        <p class="text-lg font-black text-slate-900"><?= e($title) ?></p>
        <p class="mt-2 text-sm text-slate-500"><?= e($body) ?></p>
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
