<?php

declare(strict_types=1);

namespace KreaKit\Core;

use PDO;
use PDOException;
use RuntimeException;
use Throwable;

final class Database
{
    private ?PDO $pdo = null;

    public function __construct(private readonly array $config)
    {
    }

    public function pdo(): PDO
    {
        if ($this->pdo instanceof PDO) {
            return $this->pdo;
        }

        if (($this->config['enabled'] ?? true) === false) {
            throw new RuntimeException('Database belum dikonfigurasi.');
        }

        $charset = $this->config['charset'] ?? 'utf8mb4';
        $dsn = sprintf(
            'mysql:host=%s;port=%d;dbname=%s;charset=%s',
            $this->config['host'] ?? 'localhost',
            (int) ($this->config['port'] ?? 3306),
            $this->config['name'] ?? '',
            $charset
        );

        try {
            $this->pdo = new PDO($dsn, $this->config['user'] ?? '', $this->config['password'] ?? '', [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]);
        } catch (PDOException $exception) {
            $this->logError($exception);
            throw new RuntimeException('Koneksi database gagal. Periksa konfigurasi.');
        }

        return $this->pdo;
    }

    /** @return array<int, array<string, mixed>> */
    public function select(string $sql, array $params = []): array
    {
        $statement = $this->pdo()->prepare($sql);
        $statement->execute($params);
        return $statement->fetchAll();
    }

    /** @return array<string, mixed>|null */
    public function selectOne(string $sql, array $params = []): ?array
    {
        $statement = $this->pdo()->prepare($sql);
        $statement->execute($params);
        $row = $statement->fetch();
        return $row === false ? null : $row;
    }

    public function execute(string $sql, array $params = []): int
    {
        $statement = $this->pdo()->prepare($sql);
        $statement->execute($params);
        return $statement->rowCount();
    }

    public function lastInsertId(): string
    {
        return $this->pdo()->lastInsertId();
    }

    public function transaction(callable $callback): mixed
    {
        $pdo = $this->pdo();
        $pdo->beginTransaction();

        try {
            $result = $callback($this);
            $pdo->commit();
            return $result;
        } catch (Throwable $throwable) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            $this->logError($throwable);
            throw $throwable;
        }
    }

    private function logError(Throwable $throwable): void
    {
        $logDir = \function_exists('storage_path') ? \storage_path('logs') : dirname(__DIR__, 2) . '/storage/logs';
        if (!is_dir($logDir)) {
            @mkdir($logDir, 0775, true);
        }

        $message = sprintf('[%s] %s: %s in %s:%d%s', date('c'), $throwable::class, $throwable->getMessage(), $throwable->getFile(), $throwable->getLine(), PHP_EOL);
        @error_log($message, 3, $logDir . '/app.log');
    }
}
