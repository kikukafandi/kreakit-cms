<?php

declare(strict_types=1);

function installer_lock_path(): string
{
    return base_path('storage/installed.lock');
}

function installer_is_locked(): bool
{
    return is_file(installer_lock_path());
}

/** @return array<string, array{ok: bool, label: string, detail: string}> */
function installer_requirements(): array
{
    $configDir = base_path('config');
    $storageDir = storage_path();
    $sessionsDir = storage_path('sessions');

    return [
        'php' => [
            'ok' => version_compare(PHP_VERSION, '8.1.0', '>='),
            'label' => 'PHP 8.1+',
            'detail' => 'Versi saat ini: ' . PHP_VERSION,
        ],
        'pdo_mysql' => [
            'ok' => extension_loaded('pdo_mysql'),
            'label' => 'PDO MySQL extension',
            'detail' => extension_loaded('pdo_mysql') ? 'Tersedia' : 'Aktifkan ekstensi pdo_mysql di hosting.',
        ],
        'config_writable' => [
            'ok' => is_dir($configDir) && is_writable($configDir),
            'label' => 'Folder config bisa ditulis',
            'detail' => is_writable($configDir) ? 'OK' : 'Pastikan folder config writable sementara saat install.',
        ],
        'storage_writable' => [
            'ok' => installer_ensure_dir($storageDir) && is_writable($storageDir),
            'label' => 'Folder storage bisa ditulis',
            'detail' => is_writable($storageDir) ? 'OK' : 'Pastikan folder storage writable.',
        ],
        'sessions_writable' => [
            'ok' => installer_ensure_dir($sessionsDir) && is_writable($sessionsDir),
            'label' => 'Folder storage/sessions bisa ditulis',
            'detail' => is_writable($sessionsDir) ? 'OK' : 'Pastikan folder storage/sessions writable.',
        ],
        'schema_file' => [
            'ok' => is_readable(base_path('database/schema.sql')),
            'label' => 'database/schema.sql tersedia',
            'detail' => is_readable(base_path('database/schema.sql')) ? 'OK' : 'File schema tidak ditemukan/terbaca.',
        ],
        'seed_file' => [
            'ok' => is_readable(base_path('database/seed.sql')),
            'label' => 'database/seed.sql tersedia',
            'detail' => is_readable(base_path('database/seed.sql')) ? 'OK' : 'File seed tidak ditemukan/terbaca.',
        ],
    ];
}

function installer_requirements_ok(): bool
{
    foreach (installer_requirements() as $requirement) {
        if (!$requirement['ok']) {
            return false;
        }
    }

    return true;
}

function installer_ensure_dir(string $path): bool
{
    return is_dir($path) || @mkdir($path, 0775, true);
}

/** @param array<string, string|int|bool> $input */
function installer_connect(array $input, bool $withoutDatabase = false): PDO
{
    $host = trim((string) ($input['db_host'] ?? 'localhost'));
    $port = (int) ($input['db_port'] ?? 3306);
    $name = trim((string) ($input['db_name'] ?? ''));
    $user = trim((string) ($input['db_user'] ?? ''));
    $password = (string) ($input['db_password'] ?? '');
    $charset = trim((string) ($input['db_charset'] ?? 'utf8mb4')) ?: 'utf8mb4';

    $dsn = sprintf('mysql:host=%s;port=%d;charset=%s%s', $host, $port, $charset, $withoutDatabase ? '' : ';dbname=' . $name);

    return new PDO($dsn, $user, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);
}

/** @param array<string, string|int|bool> $input @return array{success: bool, message: string} */
function installer_test_connection(array $input, bool $attemptCreateDatabase): array
{
    try {
        if ($attemptCreateDatabase) {
            $pdo = installer_connect($input, true);
            $databaseName = trim((string) ($input['db_name'] ?? ''));
            if ($databaseName === '' || !preg_match('/^[A-Za-z0-9_\-]+$/', $databaseName)) {
                return ['success' => false, 'message' => 'Nama database tidak valid. Gunakan huruf, angka, underscore, atau dash.'];
            }
            $pdo->exec('CREATE DATABASE IF NOT EXISTS `' . str_replace('`', '``', $databaseName) . '` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');
        }

        $pdo = installer_connect($input);
        $pdo->query('SELECT 1');

        return ['success' => true, 'message' => $attemptCreateDatabase ? 'Database siap digunakan.' : 'Koneksi database berhasil.'];
    } catch (PDOException) {
        $message = $attemptCreateDatabase
            ? 'Koneksi atau pembuatan database gagal. Jika hosting tidak memberi privilege CREATE DATABASE, buat database dan user dari cPanel/MySQL Wizard lalu hilangkan centang advanced create database.'
            : 'Koneksi database gagal. Periksa host, port, nama database, username, password, dan pastikan database sudah dibuat di hosting.';
        return ['success' => false, 'message' => $message];
    }
}

/** @param array<string, string|int|bool> $input */
function installer_write_config(array $input): void
{
    $configFile = base_path('config/config.php');
    $baseUrl = rtrim(trim((string) ($input['base_url'] ?? '')), '/');
    $timezone = trim((string) ($input['timezone'] ?? 'Asia/Jakarta')) ?: 'Asia/Jakarta';
    $host = trim((string) ($input['db_host'] ?? 'localhost'));
    $port = (int) ($input['db_port'] ?? 3306);
    $name = trim((string) ($input['db_name'] ?? ''));
    $user = trim((string) ($input['db_user'] ?? ''));
    $password = (string) ($input['db_password'] ?? '');
    $charset = trim((string) ($input['db_charset'] ?? 'utf8mb4')) ?: 'utf8mb4';

    $content = "<?php\n\ndeclare(strict_types=1);\n\nreturn [\n"
        . "    'app' => [\n"
        . "        'name' => 'KreaKit CMS',\n"
        . "        'env' => 'production',\n"
        . "        'debug' => false,\n"
        . "        'base_url' => " . var_export($baseUrl, true) . ",\n"
        . "        'timezone' => " . var_export($timezone, true) . ",\n"
        . "    ],\n"
        . "    'database' => [\n"
        . "        'enabled' => true,\n"
        . "        'host' => " . var_export($host, true) . ",\n"
        . "        'port' => " . var_export($port, true) . ",\n"
        . "        'name' => " . var_export($name, true) . ",\n"
        . "        'user' => " . var_export($user, true) . ",\n"
        . "        'password' => " . var_export($password, true) . ",\n"
        . "        'charset' => " . var_export($charset, true) . ",\n"
        . "    ],\n"
        . "    'session' => [\n"
        . "        'name' => 'KREAKITCMSSESSID',\n"
        . "        'lifetime' => 0,\n"
        . "        'idle_timeout' => 7200,\n"
        . "        'same_site' => 'Lax',\n"
        . "        'save_path' => __DIR__ . '/../storage/sessions',\n"
        . "    ],\n"
        . "    'security' => [\n"
        . "        'csrf_key' => '_csrf_token',\n"
        . "    ],\n"
        . "];\n";

    if (@file_put_contents($configFile, $content, LOCK_EX) === false) {
        throw new RuntimeException('Config tidak dapat ditulis. Pastikan folder config writable lalu coba lagi.');
    }

    @chmod($configFile, 0600);
    if (is_readable($configFile) === false) {
        @chmod($configFile, 0640);
    }
}

function installer_run_sql_file(PDO $pdo, string $file): void
{
    $sql = @file_get_contents($file);
    if ($sql === false) {
        throw new RuntimeException('File SQL tidak dapat dibaca.');
    }

    foreach (installer_split_sql($sql) as $statement) {
        $trimmed = trim($statement);
        if ($trimmed === '') {
            continue;
        }
        $pdo->exec($trimmed);
    }
}

/** @return list<string> */
function installer_split_sql(string $sql): array
{
    $statements = [];
    $buffer = '';
    $length = strlen($sql);
    $quote = null;
    $escaped = false;
    $lineComment = false;
    $blockComment = false;

    for ($i = 0; $i < $length; $i++) {
        $char = $sql[$i];
        $next = $i + 1 < $length ? $sql[$i + 1] : '';

        if ($lineComment) {
            if ($char === "\n") {
                $lineComment = false;
                $buffer .= $char;
            }
            continue;
        }

        if ($blockComment) {
            if ($char === '*' && $next === '/') {
                $blockComment = false;
                $i++;
            }
            continue;
        }

        if ($quote === null && $char === '-' && $next === '-') {
            $prev = $i === 0 ? "\n" : $sql[$i - 1];
            $afterNext = $i + 2 < $length ? $sql[$i + 2] : '';
            if (($prev === "\n" || $prev === "\r") && ($afterNext === ' ' || $afterNext === "\t" || $afterNext === "\r" || $afterNext === "\n")) {
                $lineComment = true;
                $i++;
                continue;
            }
        }

        if ($quote === null && $char === '#') {
            $lineComment = true;
            continue;
        }

        if ($quote === null && $char === '/' && $next === '*') {
            $blockComment = true;
            $i++;
            continue;
        }

        if (($char === "'" || $char === '"') && !$escaped) {
            if ($quote === null) {
                $quote = $char;
            } elseif ($quote === $char) {
                $quote = null;
            }
        }

        if ($char === ';' && $quote === null) {
            $statements[] = $buffer;
            $buffer = '';
            $escaped = false;
            continue;
        }

        $buffer .= $char;
        $escaped = ($char === '\\' && !$escaped);
        if ($char !== '\\') {
            $escaped = false;
        }
    }

    if (trim($buffer) !== '') {
        $statements[] = $buffer;
    }

    return $statements;
}

function installer_create_first_admin(PDO $pdo, string $name, string $email, string $password): void
{
    $hash = password_hash($password, PASSWORD_DEFAULT);
    if ($hash === false) {
        throw new RuntimeException('Password admin tidak dapat diproses.');
    }

    $existing = $pdo->query('SELECT id FROM admins ORDER BY id ASC LIMIT 1')->fetch();
    if (is_array($existing) && isset($existing['id'])) {
        $statement = $pdo->prepare('UPDATE admins SET name = :name, email = :email, password_hash = :password_hash, is_active = 1 WHERE id = :id');
        $statement->execute([
            'name' => $name,
            'email' => $email,
            'password_hash' => $hash,
            'id' => (int) $existing['id'],
        ]);
        return;
    }

    $statement = $pdo->prepare('INSERT INTO admins (name, email, password_hash, is_active) VALUES (:name, :email, :password_hash, 1)');
    $statement->execute([
        'name' => $name,
        'email' => $email,
        'password_hash' => $hash,
    ]);
}

function installer_create_lock(): void
{
    $lockDir = dirname(installer_lock_path());
    installer_ensure_dir($lockDir);
    $content = 'KreaKit CMS installed at ' . date('c') . PHP_EOL;
    if (@file_put_contents(installer_lock_path(), $content, LOCK_EX) === false) {
        throw new RuntimeException('Installer selesai, tetapi lock file tidak dapat dibuat. Buat manual file storage/installed.lock sebelum online.');
    }
    @chmod(installer_lock_path(), 0640);
}

/** @param array<string, string|int|bool> $input @return array{success: bool, message: string} */
function installer_install(array $input, bool $attemptCreateDatabase): array
{
    if (!installer_requirements_ok()) {
        return ['success' => false, 'message' => 'Requirement belum terpenuhi. Perbaiki item yang gagal sebelum install.'];
    }

    try {
        $test = installer_test_connection($input, $attemptCreateDatabase);
        if (!$test['success']) {
            return $test;
        }

        installer_write_config($input);
        $pdo = installer_connect($input);
        $pdo->beginTransaction();
        try {
            installer_run_sql_file($pdo, base_path('database/schema.sql'));
            installer_run_sql_file($pdo, base_path('database/seed.sql'));
            installer_create_first_admin(
                $pdo,
                trim((string) ($input['admin_name'] ?? 'Admin')),
                strtolower(trim((string) ($input['admin_email'] ?? ''))),
                (string) ($input['admin_password'] ?? '')
            );
            // MySQL implicitly commits DDL statements, so a transaction opened
            // before schema.sql may already be closed by this point.
            if ($pdo->inTransaction()) {
                $pdo->commit();
            }
        } catch (Throwable $throwable) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            throw $throwable;
        }
        installer_create_lock();

        return ['success' => true, 'message' => 'Instalasi selesai. Installer sekarang terkunci.'];
    } catch (PDOException) {
        return ['success' => false, 'message' => 'Instalasi database gagal. Periksa privilege user database dan pastikan schema belum rusak. Detail rahasia tidak ditampilkan.'];
    } catch (Throwable $throwable) {
        return ['success' => false, 'message' => $throwable->getMessage()];
    }
}
