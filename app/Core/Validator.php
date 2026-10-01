<?php

declare(strict_types=1);

namespace KreaKit\Core;

final class Validator
{
    /** @var array<string, list<string>> */
    private array $errors = [];

    public function required(string $field, mixed $value, string $label): self
    {
        if ($value === null || trim((string) $value) === '') {
            $this->errors[$field][] = $label . ' wajib diisi.';
        }
        return $this;
    }

    public function maxLength(string $field, mixed $value, int $max, string $label): self
    {
        if ($value !== null && mb_strlen((string) $value) > $max) {
            $this->errors[$field][] = $label . ' maksimal ' . $max . ' karakter.';
        }
        return $this;
    }

    public function email(string $field, mixed $value, string $label): self
    {
        $value = trim((string) $value);
        if ($value !== '' && filter_var($value, FILTER_VALIDATE_EMAIL) === false) {
            $this->errors[$field][] = $label . ' tidak valid.';
        }
        return $this;
    }

    public function url(string $field, mixed $value, string $label): self
    {
        $value = trim((string) $value);
        if ($value !== '' && filter_var($value, FILTER_VALIDATE_URL) === false) {
            $this->errors[$field][] = $label . ' tidak valid.';
        }
        if ($value !== '' && !in_array(parse_url($value, PHP_URL_SCHEME), ['http', 'https'], true)) {
            $this->errors[$field][] = $label . ' harus diawali http atau https.';
        }
        return $this;
    }

    public function passes(): bool
    {
        return $this->errors === [];
    }

    /** @return array<string, list<string>> */
    public function errors(): array
    {
        return $this->errors;
    }
}
