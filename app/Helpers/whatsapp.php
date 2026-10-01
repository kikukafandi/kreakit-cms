<?php

declare(strict_types=1);

function whatsapp_url(?string $number, string $message = ''): string
{
    $number = normalize_whatsapp($number);
    if ($number === '') {
        return '#';
    }

    $url = 'https://wa.me/' . rawurlencode($number);
    if ($message !== '') {
        $url .= '?text=' . rawurlencode($message);
    }
    return $url;
}
