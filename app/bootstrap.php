<?php

declare(strict_types=1);

if (!defined('BASE_PATH')) {
    define('BASE_PATH', dirname(__DIR__));
}

$autoload = static function (string $class): void {
    $prefix = 'KreaKit\\';
    if (strncmp($class, $prefix, strlen($prefix)) !== 0) {
        return;
    }

    $relative = substr($class, strlen($prefix));
    $file = BASE_PATH . '/app/' . str_replace('\\', '/', $relative) . '.php';
    if (is_file($file)) {
        require_once $file;
    }
};
spl_autoload_register($autoload);

$helpers = [
    BASE_PATH . '/app/Helpers/url.php',
    BASE_PATH . '/app/Helpers/formatting.php',
    BASE_PATH . '/app/Helpers/validation.php',
    BASE_PATH . '/app/Helpers/whatsapp.php',
    BASE_PATH . '/app/Helpers/admin_content.php',
    BASE_PATH . '/app/Helpers/upload.php',
    BASE_PATH . '/app/Helpers/template.php',
];

foreach ($helpers as $helper) {
    if (is_file($helper)) {
        require_once $helper;
    }
}

$configFile = BASE_PATH . '/config/config.php';
$exampleConfigFile = BASE_PATH . '/config/config.example.php';
$config = is_file($configFile) ? require $configFile : require $exampleConfigFile;

if (!is_array($config)) {
    throw new RuntimeException('Konfigurasi aplikasi tidak valid.');
}

$timezone = $config['app']['timezone'] ?? 'Asia/Jakarta';
date_default_timezone_set($timezone);

$GLOBALS['kreakit_config'] = $config;

function app_config(?string $key = null, mixed $default = null): mixed
{
    $config = $GLOBALS['kreakit_config'] ?? [];
    if ($key === null) {
        return $config;
    }

    $value = $config;
    foreach (explode('.', $key) as $segment) {
        if (!is_array($value) || !array_key_exists($segment, $value)) {
            return $default;
        }
        $value = $value[$segment];
    }

    return $value;
}

function base_path(string $path = ''): string
{
    return BASE_PATH . ($path !== '' ? '/' . ltrim($path, '/') : '');
}

function public_path(string $path = ''): string
{
    return base_path('public' . ($path !== '' ? '/' . ltrim($path, '/') : ''));
}

function storage_path(string $path = ''): string
{
    return base_path('storage' . ($path !== '' ? '/' . ltrim($path, '/') : ''));
}

function db(): \KreaKit\Core\Database
{
    static $database = null;

    if (!$database instanceof \KreaKit\Core\Database) {
        $database = new \KreaKit\Core\Database((array) app_config('database', []));
    }

    return $database;
}

function require_admin(): void
{
    \KreaKit\Core\Auth::requireAdmin();
}
