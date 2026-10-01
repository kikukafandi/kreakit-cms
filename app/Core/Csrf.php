<?php

declare(strict_types=1);

namespace KreaKit\Core;

final class Csrf
{
    public static function token(string $key = '_csrf_token'): string
    {
        if (empty($_SESSION[$key]) || !is_string($_SESSION[$key])) {
            $_SESSION[$key] = bin2hex(random_bytes(32));
        }

        return $_SESSION[$key];
    }

    public static function field(string $key = '_csrf_token'): string
    {
        $token = htmlspecialchars(self::token($key), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $name = htmlspecialchars($key, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        return '<input type="hidden" name="' . $name . '" value="' . $token . '">';
    }

    public static function verify(?string $token, string $key = '_csrf_token'): bool
    {
        if (!is_string($token) || empty($_SESSION[$key]) || !is_string($_SESSION[$key])) {
            return false;
        }

        return hash_equals($_SESSION[$key], $token);
    }

    public static function requireValid(?string $token, string $key = '_csrf_token'): void
    {
        if (!self::verify($token, $key)) {
            http_response_code(419);
            exit('Sesi formulir tidak valid. Silakan muat ulang halaman.');
        }
    }
}
