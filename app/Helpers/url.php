<?php

declare(strict_types=1);

function url(string $path = ''): string
{
    $baseUrl = rtrim((string) app_config('app.base_url', ''), '/');
    $path = '/' . ltrim($path, '/');
    return $baseUrl . ($path === '/' ? '/' : $path);
}

function redirect(string $to, int $status = 302): never
{
    header('Location: ' . $to, true, $status);
    exit;
}
