<?php

declare(strict_types=1);

use KreaKit\Core\Database;

function settings_array(Database $database): array
{
    $rows = $database->select('SELECT setting_key, setting_value FROM settings');
    $settings = [];
    foreach ($rows as $row) {
        $settings[(string) $row['setting_key']] = $row['setting_value'];
    }
    return $settings;
}

function theme_settings_from(array $settings): array
{
    return [
        'primary_color' => (string) ($settings['primary_color'] ?? '#0f766e'),
        'secondary_color' => (string) ($settings['secondary_color'] ?? '#f97316'),
        'button_style' => (string) ($settings['button_style'] ?? 'rounded'),
        'catalog_mode' => (string) ($settings['catalog_mode'] ?? 'products'),
        'site_meta_title' => (string) ($settings['site_meta_title'] ?? ''),
        'site_meta_description' => (string) ($settings['site_meta_description'] ?? ''),
    ];
}

function template_file_for_slug(string $slug): ?string
{
    if (!preg_match('/^[a-z0-9-]{1,100}$/', $slug)) {
        return null;
    }
    $file = base_path('templates/' . $slug . '/index.php');
    return is_file($file) ? $file : null;
}

function active_template_row(Database $database, array $settings): ?array
{
    $activeSlug = (string) ($settings['active_template_slug'] ?? '');
    if ($activeSlug !== '') {
        $template = $database->selectOne('SELECT * FROM templates WHERE slug = :slug AND is_active = 1 LIMIT 1', ['slug' => $activeSlug]);
        if ($template !== null && template_file_for_slug((string) $template['slug']) !== null) {
            return $template;
        }
    }

    foreach ($database->select('SELECT * FROM templates WHERE is_active = 1 ORDER BY id ASC') as $template) {
        if (template_file_for_slug((string) $template['slug']) !== null) {
            return $template;
        }
    }

    return null;
}

function upsert_setting(Database $database, string $key, ?string $value): void
{
    $database->execute(
        'INSERT INTO settings (setting_key, setting_value) VALUES (:setting_key, :setting_value) ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)',
        ['setting_key' => $key, 'setting_value' => $value]
    );
}

function render_safe_public_fallback(?array $business, array $items = []): void
{
    $businessName = (string) ($business['business_name'] ?? app_config('app.name', 'KreaKit CMS'));
    ?>
    <!doctype html>
    <html lang="id">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title><?= e($businessName) ?></title>
    </head>
    <body>
        <main>
            <h1><?= e($businessName) ?></h1>
            <p><?= e($business['description'] ?? 'Website sedang disiapkan.') ?></p>
            <?php if ($items !== []): ?>
                <ul>
                    <?php foreach ($items as $item): ?>
                        <li><?= e($item['name'] ?? '') ?><?= !empty($item['price_label']) ? ' - ' . e($item['price_label']) : '' ?></li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </main>
    </body>
    </html>
    <?php
}

/**
 * Company-profile sections edited in /admin/sections.php, stored as JSON in the settings table.
 * Always returns every key, with empty values for sections that were never filled.
 */
function page_sections(array $settings): array
{
    $json = static function (string $key) use ($settings): array {
        $decoded = json_decode((string) ($settings[$key] ?? ''), true);
        return is_array($decoded) ? $decoded : [];
    };
    $rows = static fn (array $list, array $fields): array => array_values(array_filter(
        array_map(static fn ($row): array => array_map(static fn (string $field): string => is_array($row) ? trim((string) ($row[$field] ?? '')) : '', array_combine($fields, $fields)), $list),
        static fn (array $row): bool => implode('', $row) !== ''
    ));
    $about = $json('section_about');

    return [
        'about' => [
            'title' => trim((string) ($about['title'] ?? '')),
            'body' => trim((string) ($about['body'] ?? '')),
            'image' => validate_local_upload_path((string) ($about['image'] ?? '')),
        ],
        'highlights' => $rows($json('section_highlights'), ['title', 'body']),
        'stats' => $rows($json('section_stats'), ['value', 'label']),
        'gallery' => array_values(array_filter(array_map(static fn ($path): ?string => validate_local_upload_path((string) $path), $json('section_gallery')))),
        'testimonials' => $rows($json('section_testimonials'), ['name', 'role', 'quote']),
        'faq' => $rows($json('section_faq'), ['question', 'answer']),
        'hours' => trim((string) ($settings['section_hours'] ?? '')),
    ];
}
