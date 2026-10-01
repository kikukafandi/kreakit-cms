<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/app/bootstrap.php';

use KreaKit\Core\Session;

Session::start(app_config('session', []));

try {
    $database = db();
    $settings = settings_array($database);
    $business = $database->selectOne('SELECT * FROM business_profiles ORDER BY id ASC LIMIT 1') ?? [];
    $categories = $database->select('SELECT * FROM categories WHERE is_active = 1 ORDER BY sort_order ASC, name ASC');
    $items = $database->select('SELECT i.*, c.slug AS category_slug, c.name AS category_name FROM items i LEFT JOIN categories c ON c.id = i.category_id WHERE i.is_active = 1 ORDER BY i.sort_order ASC, i.name ASC');
    $socialLinks = $database->select('SELECT * FROM social_links WHERE is_active = 1 ORDER BY sort_order ASC, id ASC');
    $themeSettings = theme_settings_from($settings);
    $template = active_template_row($database, $settings);

    http_response_code(200);

    if ($template === null) {
        render_safe_public_fallback($business, $items);
        return;
    }

    $templateSlug = (string) $template['slug'];
    $templateFile = template_file_for_slug($templateSlug);
    if ($templateFile === null) {
        render_safe_public_fallback($business, $items);
        return;
    }

    include $templateFile;
} catch (Throwable) {
    http_response_code(503);
    render_safe_public_fallback(null);
}
