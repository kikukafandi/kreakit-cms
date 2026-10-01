<?php

declare(strict_types=1);

namespace KreaKit\Core;

final class Session
{
    public static function start(array $config = []): void
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            return;
        }

        $isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
            || (($_SERVER['SERVER_PORT'] ?? null) === '443');

        $savePath = $config['save_path'] ?? null;
        if (is_string($savePath) && $savePath !== '') {
            if (!is_dir($savePath)) {
                @mkdir($savePath, 0775, true);
            }
            if (is_dir($savePath) && is_writable($savePath)) {
                session_save_path($savePath);
            }
        }

        session_name($config['name'] ?? 'KREAKITCMSSESSID');
        session_set_cookie_params([
            'lifetime' => (int) ($config['lifetime'] ?? 0),
            'path' => '/',
            'domain' => '',
            'secure' => $isHttps,
            'httponly' => true,
            'samesite' => $config['same_site'] ?? 'Lax',
        ]);
        session_start();

        self::enforceIdleTimeout((int) ($config['idle_timeout'] ?? 7200));
    }

    public static function regenerate(): void
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_regenerate_id(true);
        }
    }

    public static function flash(string $key, ?string $message = null): ?string
    {
        if ($message !== null) {
            $_SESSION['_flash'][$key] = $message;
            return null;
        }

        $value = $_SESSION['_flash'][$key] ?? null;
        unset($_SESSION['_flash'][$key]);
        return $value;
    }

    public static function destroy(): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            return;
        }

        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
        }
        session_destroy();
    }

    private static function enforceIdleTimeout(int $seconds): void
    {
        if ($seconds <= 0) {
            return;
        }

        $now = time();
        if (isset($_SESSION['_last_activity']) && ($now - (int) $_SESSION['_last_activity']) > $seconds) {
            self::destroy();
            session_start();
        }
        $_SESSION['_last_activity'] = $now;
    }
}
