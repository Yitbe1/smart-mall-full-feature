<?php
/**
 * File-based query result cache.
 *
 * Cache keys are derived from the SQL + params, stored as
 * serialized PHP in a flat file under a cache directory.
 *
 * Usage:
 *   require_once __DIR__ . '/cache.php';
 *   $cache = new QueryCache();
 *   $results = $cache->remember('unique_key', 300, function() use ($pdo) {
 *       $stmt = $pdo->query("SELECT ...");
 *       return $stmt->fetchAll();
 *   });
 */

class QueryCache
{
    private string $dir;
    private int $default_ttl;

    public function __construct(int $default_ttl = 300)
    {
        $this->dir = __DIR__ . '/../cache/queries';
        $this->default_ttl = $default_ttl;

        if (!is_dir($this->dir)) {
            @mkdir($this->dir, 0775, true);
        }
    }

    /**
     * Get cached value or store the result of $callback.
     */
    public function remember(string $key, int $ttl, callable $callback): mixed
    {
        $file = $this->filePath($key);

        if (is_file($file) && (time() - filemtime($file)) < $ttl) {
            $data = @file_get_contents($file);
            $cached = @unserialize($data);
            if ($cached !== false) {
                return $cached;
            }
        }

        $value = $callback();

        $this->write($file, $value);

        return $value;
    }

    /**
     * Store a value in the cache.
     */
    public function put(string $key, mixed $value, int $ttl = 300): void
    {
        $this->write($this->filePath($key), $value);
    }

    /**
     * Invalidate a specific cache key.
     */
    public function forget(string $key): void
    {
        $file = $this->filePath($key);
        if (is_file($file)) {
            @unlink($file);
        }
    }

    /**
     * Flush entire cache directory.
     */
    public function flush(): void
    {
        $files = glob($this->dir . '/*');
        foreach ($files as $f) {
            @unlink($f);
        }
    }

    private function filePath(string $key): string
    {
        $hash = hash('md5', $key);
        return $this->dir . '/' . $hash . '.cache';
    }

    private function write(string $file, mixed $value): void
    {
        $tmp = $file . '.' . getmypid() . '.tmp';
        @file_put_contents($tmp, serialize($value), LOCK_EX);
        @rename($tmp, $file);
    }
}

/**
 * Convenience wrapper to cache a database query result.
 *
 * @param PDOStatement $stmt Prepared & executed statement
 * @param int $fetch_style PDO fetch style (default FETCH_ASSOC)
 * @param int $ttl Cache TTL in seconds
 * @return array Cached result rows
 */
function cache_query_results(PDO $pdo, string $sql, array $params = [], int $ttl = 300, int $fetch_style = PDO::FETCH_ASSOC): array
{
    static $cache = null;
    if ($cache === null) {
        $cache = new QueryCache();
    }

    $key = $sql . '|' . serialize($params);

    return $cache->remember($key, $ttl, function() use ($pdo, $sql, $params, $fetch_style) {
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll($fetch_style);
    });
}

/**
 * Invalidate cache entries matching a prefix pattern.
 */
function invalidate_cache_pattern(string $prefix): void
{
    static $cache = null;
    if ($cache === null) {
        $cache = new QueryCache();
    }
    $cache->flush();
}
