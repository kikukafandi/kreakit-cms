<?php

declare(strict_types=1);

use KreaKit\Core\Database;

function nullable_string(?string $value): ?string
{
    $value = trim((string) $value);
    return $value === '' ? null : $value;
}

function sanitize_text(?string $value, int $max): string
{
    $value = trim((string) $value);
    $value = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/u', '', $value) ?? '';
    return mb_substr($value, 0, $max);
}

function sanitize_text_or_null(?string $value, int $max): ?string
{
    $value = sanitize_text($value, $max);
    return $value === '' ? null : $value;
}

function sanitize_multiline_or_null(?string $value, int $max): ?string
{
    $value = trim((string) $value);
    $value = str_replace(["\r\n", "\r"], "\n", $value);
    $value = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/u', '', $value) ?? '';
    $value = mb_substr($value, 0, $max);
    return $value === '' ? null : $value;
}

function sanitize_http_url(?string $value, int $max = 500): ?string
{
    $value = sanitize_text($value, $max);
    if ($value === '') {
        return null;
    }

    $url = filter_var($value, FILTER_SANITIZE_URL);
    if (!is_string($url) || $url === '') {
        return null;
    }

    $scheme = strtolower((string) parse_url($url, PHP_URL_SCHEME));
    if (!in_array($scheme, ['http', 'https'], true) || filter_var($url, FILTER_VALIDATE_URL) === false) {
        return null;
    }

    return $url;
}

function valid_whatsapp_number(?string $number): ?string
{
    $number = normalize_whatsapp($number);
    if ($number === '') {
        return null;
    }

    if (!preg_match('/^[1-9][0-9]{7,14}$/', $number)) {
        return null;
    }

    return $number;
}

function checkbox_bool(string $name): int
{
    return isset($_POST[$name]) ? 1 : 0;
}

function int_input(string $name, int $default = 0): int
{
    $value = filter_input(INPUT_POST, $name, FILTER_VALIDATE_INT);
    return is_int($value) ? $value : $default;
}

function id_input(string $name = 'id'): int
{
    $value = filter_input(INPUT_POST, $name, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
    return is_int($value) ? $value : 0;
}

function nullable_decimal_input(string $name): ?string
{
    $raw = trim((string) ($_POST[$name] ?? ''));
    if ($raw === '') {
        return null;
    }

    $normalized = str_replace(',', '.', $raw);
    if (!preg_match('/^\d{1,10}(\.\d{1,2})?$/', $normalized)) {
        return null;
    }

    return number_format((float) $normalized, 2, '.', '');
}

function slug_input_or_name(string $slug, string $name, int $max = 170): string
{
    $slug = slugify($slug !== '' ? $slug : $name);
    return mb_substr($slug, 0, $max);
}

function ensure_unique_slug(Database $database, string $table, string $slug, ?int $ignoreId = null): bool
{
    $allowedTables = ['categories', 'items'];
    if (!in_array($table, $allowedTables, true)) {
        return false;
    }

    $sql = "SELECT id FROM {$table} WHERE slug = :slug";
    $params = ['slug' => $slug];
    if ($ignoreId !== null) {
        $sql .= ' AND id <> :id';
        $params['id'] = $ignoreId;
    }
    $sql .= ' LIMIT 1';

    return $database->selectOne($sql, $params) === null;
}

function social_platform_options(): array
{
    return [
        'instagram' => 'Instagram',
        'facebook' => 'Facebook',
        'tiktok' => 'TikTok',
        'whatsapp' => 'WhatsApp',
        'marketplace' => 'Marketplace',
        'website' => 'Website',
        'other' => 'Lainnya',
    ];
}

function admin_flash_errors(array $errors): void
{
    KreaKit\Core\Session::flash('error', implode(' ', array_map('strval', $errors)));
}
