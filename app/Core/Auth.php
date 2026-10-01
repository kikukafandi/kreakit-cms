<?php

declare(strict_types=1);

namespace KreaKit\Core;

final class Auth
{
    public static function admin(): ?array
    {
        return $_SESSION['admin'] ?? null;
    }

    public static function check(): bool
    {
        return self::admin() !== null;
    }

    public static function requireAdmin(): void
    {
        if (!self::check()) {
            \redirect(\url('/admin/login.php'));
        }
    }
}
