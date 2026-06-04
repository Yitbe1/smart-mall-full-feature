<?php

function cache_path(): string
{
    return rtrim(__DIR__, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'cache' . DIRECTORY_SEPARATOR . 'queries' . DIRECTORY_SEPARATOR;
}

function invalidate_cache_pattern(string $pattern): void
{
    $dir = cache_path();
    if (!is_dir($dir)) {
        return;
    }
    foreach (glob($dir . '*.cache') as $file) {
        if (is_file($file)) {
            @unlink($file);
        }
    }
}

function cache_get(string $key): ?array
{
    $path = cache_path() . md5($key) . '.cache';
    if (!is_readable($path)) {
        return null;
    }
    return json_decode((string)file_get_contents($path), true) ?: null;
}

function cache_set(string $key, array $data, int $ttl = 300): void
{
    $dir = cache_path();
    if (!is_dir($dir)) {
        @mkdir($dir, 0775, true);
    }
    $payload = json_encode([
        'data'       => $data,
        'expires_at' => time() + $ttl,
    ]);
    @file_put_contents($dir . md5($key) . '.cache', $payload, LOCK_EX);
}
