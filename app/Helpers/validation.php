<?php

declare(strict_types=1);

function clean_string(?string $value): string
{
    return trim((string) $value);
}

function normalize_whatsapp(?string $number): string
{
    $number = preg_replace('/[^0-9+]/', '', (string) $number) ?? '';
    if (str_starts_with($number, '+')) {
        $number = substr($number, 1);
    }
    if (str_starts_with($number, '08')) {
        $number = '62' . substr($number, 1);
    }
    return $number;
}
