<?php

declare(strict_types=1);

namespace KreaKit\Core;

use RuntimeException;

final class Auth
{
    private const LOGIN_ATTEMPTS_KEY = '_admin_login_attempts';
    private const MAX_LOGIN_ATTEMPTS = 5;
    private const LOCK_SECONDS = 300;

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
            Session::flash('error', 'Silakan login untuk mengakses halaman admin.');
            \redirect(\url('/admin/login.php'));
        }
    }

    /** @return array{success: bool, message: string} */
    public static function attempt(Database $database, string $email, string $password): array
    {
        if (self::isLoginLocked()) {
            return [
                'success' => false,
                'message' => 'Login gagal. Periksa email dan password Anda.',
            ];
        }

        $email = strtolower(trim($email));

        try {
            $admin = $database->selectOne(
                'SELECT id, name, email, password_hash FROM admins WHERE email = :email AND is_active = 1 LIMIT 1',
                ['email' => $email]
            );
        } catch (RuntimeException) {
            self::recordFailedLogin();
            return [
                'success' => false,
                'message' => 'Login gagal. Periksa email dan password Anda.',
            ];
        }

        if ($admin === null || !password_verify($password, (string) $admin['password_hash'])) {
            self::recordFailedLogin();
            return [
                'success' => false,
                'message' => 'Login gagal. Periksa email dan password Anda.',
            ];
        }

        self::clearFailedLogins();
        Session::regenerate();

        $_SESSION['admin'] = [
            'id' => (int) $admin['id'],
            'name' => (string) $admin['name'],
            'email' => (string) $admin['email'],
            'logged_in_at' => time(),
        ];

        try {
            $database->execute('UPDATE admins SET last_login_at = NOW() WHERE id = :id', ['id' => (int) $admin['id']]);
        } catch (RuntimeException) {
            // Login should remain successful even if last_login_at cannot be recorded.
        }

        return [
            'success' => true,
            'message' => 'Login berhasil.',
        ];
    }

    public static function logout(): void
    {
        Session::destroy();
    }

    /** @return array{success: bool, message: string} */
    public static function updatePassword(Database $database, string $currentPassword, string $newPassword): array
    {
        $admin = self::admin();
        if ($admin === null) {
            return [
                'success' => false,
                'message' => 'Sesi admin tidak valid. Silakan login ulang.',
            ];
        }

        try {
            $row = $database->selectOne(
                'SELECT id, password_hash FROM admins WHERE id = :id AND is_active = 1 LIMIT 1',
                ['id' => (int) $admin['id']]
            );
        } catch (RuntimeException) {
            return [
                'success' => false,
                'message' => 'Password tidak dapat diperbarui saat ini.',
            ];
        }

        if ($row === null || !password_verify($currentPassword, (string) $row['password_hash'])) {
            return [
                'success' => false,
                'message' => 'Password tidak dapat diperbarui. Periksa input Anda.',
            ];
        }

        $hash = password_hash($newPassword, PASSWORD_DEFAULT);
        if ($hash === false) {
            return [
                'success' => false,
                'message' => 'Password tidak dapat diperbarui saat ini.',
            ];
        }

        try {
            $database->execute(
                'UPDATE admins SET password_hash = :password_hash WHERE id = :id',
                [
                    'password_hash' => $hash,
                    'id' => (int) $admin['id'],
                ]
            );
        } catch (RuntimeException) {
            return [
                'success' => false,
                'message' => 'Password tidak dapat diperbarui saat ini.',
            ];
        }

        Session::regenerate();
        return [
            'success' => true,
            'message' => 'Password berhasil diperbarui.',
        ];
    }

    public static function isLoginLocked(): bool
    {
        $sessionLock = self::sessionLockedUntil();
        $ipLock = (int) (self::loadIpAttempts()['locked_until'] ?? 0);
        $lockedUntil = max($sessionLock, $ipLock);

        if ($lockedUntil > time()) {
            return true;
        }

        if ($sessionLock > 0 || $ipLock > 0) {
            self::clearFailedLogins();
        }

        return false;
    }

    public static function remainingLockSeconds(): int
    {
        $lockedUntil = max(
            self::sessionLockedUntil(),
            (int) (self::loadIpAttempts()['locked_until'] ?? 0)
        );

        return max(0, $lockedUntil - time());
    }

    private static function recordFailedLogin(): void
    {
        $attempts = self::incrementAttempts($_SESSION[self::LOGIN_ATTEMPTS_KEY] ?? []);
        $_SESSION[self::LOGIN_ATTEMPTS_KEY] = $attempts;

        $ipAttempts = self::incrementAttempts(self::loadIpAttempts());
        self::saveIpAttempts($ipAttempts);
    }

    private static function clearFailedLogins(): void
    {
        unset($_SESSION[self::LOGIN_ATTEMPTS_KEY]);

        $path = self::ipRateLimitPath();
        if ($path !== null && is_file($path)) {
            @unlink($path);
        }
    }

    /** @param array<string, mixed> $attempts @return array<string, int> */
    private static function incrementAttempts(array $attempts): array
    {
        $attempts = [
            'count' => ((int) ($attempts['count'] ?? 0)) + 1,
            'first_attempt_at' => (int) ($attempts['first_attempt_at'] ?? time()),
            'locked_until' => (int) ($attempts['locked_until'] ?? 0),
        ];

        if ($attempts['count'] >= self::MAX_LOGIN_ATTEMPTS) {
            $attempts['locked_until'] = time() + self::LOCK_SECONDS;
        }

        return $attempts;
    }

    private static function sessionLockedUntil(): int
    {
        $attempts = $_SESSION[self::LOGIN_ATTEMPTS_KEY] ?? [];
        return (int) ($attempts['locked_until'] ?? 0);
    }

    /** @return array<string, int> */
    private static function loadIpAttempts(): array
    {
        $path = self::ipRateLimitPath();
        if ($path === null || !is_file($path)) {
            return [];
        }

        $data = json_decode((string) @file_get_contents($path), true);
        return is_array($data) ? $data : [];
    }

    /** @param array<string, int> $attempts */
    private static function saveIpAttempts(array $attempts): void
    {
        $path = self::ipRateLimitPath();
        if ($path === null) {
            return;
        }

        $dir = dirname($path);
        if (!is_dir($dir)) {
            @mkdir($dir, 0775, true);
        }

        @file_put_contents($path, json_encode($attempts, JSON_THROW_ON_ERROR), LOCK_EX);
    }

    private static function ipRateLimitPath(): ?string
    {
        if (!function_exists('storage_path')) {
            return null;
        }

        $ip = (string) ($_SERVER['REMOTE_ADDR'] ?? 'cli');
        return \storage_path('cache/login-rate-' . hash('sha256', $ip) . '.json');
    }
}
