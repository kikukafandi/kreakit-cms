<?php

declare(strict_types=1);

/**
 * Contoh konfigurasi KreaKit CMS.
 * Salin file ini menjadi config.php lalu isi sesuai hosting.
 * Jangan commit config.php produksi karena berisi kredensial database.
 */
return [
    'app' => [
        'name' => 'KreaKit CMS',
        'env' => 'local',
        'debug' => false,
        'base_url' => '',
        'timezone' => 'Asia/Jakarta',
    ],
    'database' => [
        'enabled' => false,
        'host' => 'localhost',
        'port' => 3306,
        'name' => 'kreakit_cms',
        'user' => 'kreakit_user',
        'password' => 'change-this-password',
        'charset' => 'utf8mb4',
    ],
    'session' => [
        'name' => 'KREAKITCMSSESSID',
        'lifetime' => 0,
        'idle_timeout' => 7200,
        'same_site' => 'Lax',
        'save_path' => __DIR__ . '/../storage/sessions',
    ],
    'security' => [
        'csrf_key' => '_csrf_token',
    ],
];
