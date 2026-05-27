<?php
// Shared currency helpers. Stored prices are USD; ETB is display/payment conversion.

if (!defined('SMARTMALL_BASE_CURRENCY')) {
    define('SMARTMALL_BASE_CURRENCY', 'USD');
}

if (!defined('SMARTMALL_EXCHANGE_API_URL')) {
    define('SMARTMALL_EXCHANGE_API_URL', 'https://open.er-api.com/v6/latest/USD');
}

function smartmall_supported_currencies(): array
{
    return ['USD', 'ETB'];
}

function smartmall_selected_currency(): string
{
    if (session_status() !== PHP_SESSION_ACTIVE && !headers_sent()) {
        session_start();
    }

    $currency = strtoupper($_SESSION['currency'] ?? SMARTMALL_BASE_CURRENCY);
    return in_array($currency, smartmall_supported_currencies(), true) ? $currency : SMARTMALL_BASE_CURRENCY;
}

function smartmall_set_selected_currency(string $currency): void
{
    if (session_status() !== PHP_SESSION_ACTIVE && !headers_sent()) {
        session_start();
    }

    $currency = strtoupper($currency);
    $_SESSION['currency'] = in_array($currency, smartmall_supported_currencies(), true)
        ? $currency
        : SMARTMALL_BASE_CURRENCY;
}

function smartmall_exchange_cache_path(): string
{
    return rtrim(sys_get_temp_dir(), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'smartmall_exchange_usd.json';
}

function smartmall_read_exchange_cache(): ?array
{
    $path = smartmall_exchange_cache_path();
    if (!is_readable($path)) {
        return null;
    }

    $data = json_decode((string)file_get_contents($path), true);
    if (!is_array($data) || empty($data['rates']) || !is_array($data['rates'])) {
        return null;
    }

    return $data;
}

function smartmall_fetch_exchange_rates(): ?array
{
    $raw = false;

    if (function_exists('curl_init')) {
        $ch = curl_init(SMARTMALL_EXCHANGE_API_URL);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 8,
            CURLOPT_SSL_VERIFYPEER => true,
        ]);
        $raw = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode !== 200) {
            $raw = false;
        }
    } else {
        $context = stream_context_create([
            'http' => ['timeout' => 8],
        ]);
        $raw = @file_get_contents(SMARTMALL_EXCHANGE_API_URL, false, $context);
    }

    if (!$raw) {
        return null;
    }

    $payload = json_decode((string)$raw, true);
    if (
        !is_array($payload)
        || ($payload['result'] ?? '') !== 'success'
        || ($payload['base_code'] ?? '') !== SMARTMALL_BASE_CURRENCY
        || empty($payload['rates']['ETB'])
    ) {
        return null;
    }

    $data = [
        'base' => SMARTMALL_BASE_CURRENCY,
        'rates' => [
            'USD' => 1.0,
            'ETB' => (float)$payload['rates']['ETB'],
        ],
        'fetched_at' => time(),
        'expires_at' => (int)($payload['time_next_update_unix'] ?? (time() + 86400)),
        'provider' => $payload['provider'] ?? 'https://www.exchangerate-api.com',
    ];

    @file_put_contents(smartmall_exchange_cache_path(), json_encode($data), LOCK_EX);
    return $data;
}

function smartmall_exchange_data(): array
{
    $cache = smartmall_read_exchange_cache();
    if ($cache && (int)($cache['expires_at'] ?? 0) > time()) {
        return $cache + ['stale' => false];
    }

    $fresh = smartmall_fetch_exchange_rates();
    if ($fresh) {
        return $fresh + ['stale' => false];
    }

    if ($cache) {
        return $cache + ['stale' => true];
    }

    return [
        'base' => SMARTMALL_BASE_CURRENCY,
        'rates' => ['USD' => 1.0, 'ETB' => 0.0],
        'fetched_at' => 0,
        'expires_at' => 0,
        'provider' => 'https://www.exchangerate-api.com',
        'stale' => true,
    ];
}

function smartmall_exchange_rate(?string $currency = null): float
{
    $currency = strtoupper($currency ?? smartmall_selected_currency());
    if ($currency === SMARTMALL_BASE_CURRENCY) {
        return 1.0;
    }

    $data = smartmall_exchange_data();
    $rate = (float)($data['rates'][$currency] ?? 0);
    return $rate > 0 ? $rate : 0.0;
}

function smartmall_convert_money(float $amountUsd, ?string $currency = null): float
{
    $currency = strtoupper($currency ?? smartmall_selected_currency());
    if ($currency === SMARTMALL_BASE_CURRENCY) {
        return $amountUsd;
    }

    $rate = smartmall_exchange_rate($currency);
    return $rate > 0 ? $amountUsd * $rate : $amountUsd;
}

function smartmall_format_money($amountUsd, ?string $currency = null): string
{
    $currency = strtoupper($currency ?? smartmall_selected_currency());
    if (!in_array($currency, smartmall_supported_currencies(), true)) {
        $currency = SMARTMALL_BASE_CURRENCY;
    }

    if ($currency === 'ETB') {
        $rate = smartmall_exchange_rate('ETB');
        if ($rate > 0) {
            return 'ETB ' . number_format((float)$amountUsd * $rate, 2);
        }

        $currency = SMARTMALL_BASE_CURRENCY;
    }

    return '$' . number_format((float)$amountUsd, 2);
}

function smartmall_currency_is_converted(): bool
{
    $currency = smartmall_selected_currency();
    return $currency !== SMARTMALL_BASE_CURRENCY && smartmall_exchange_rate($currency) > 0;
}
