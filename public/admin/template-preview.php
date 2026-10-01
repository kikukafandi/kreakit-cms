<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/app/bootstrap.php';

use KreaKit\Core\Session;

Session::start(app_config('session', []));
require_admin();

$slug = sanitize_text((string) ($_GET['slug'] ?? ''), 100);
if (!preg_match('/^[a-z0-9-]{1,100}$/', $slug)) {
    http_response_code(404);
    exit;
}

$files = [
    'png' => base_path('templates/' . $slug . '/preview.png'),
    'jpg' => base_path('templates/' . $slug . '/preview.jpg'),
    'webp' => base_path('templates/' . $slug . '/preview.webp'),
];
$mime = ['png' => 'image/png', 'jpg' => 'image/jpeg', 'webp' => 'image/webp'];

foreach ($files as $type => $file) {
    if (is_file($file)) {
        header('Content-Type: ' . $mime[$type]);
        header('Cache-Control: private, max-age=3600');
        readfile($file);
        exit;
    }
}

http_response_code(404);
