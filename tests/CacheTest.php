<?php

require_once __DIR__ . '/TestRunner.php';
require_once __DIR__ . '/../includes/cache.php';

class CacheTest
{
    private QueryCache $cache;

    public function setUp(): void
    {
        $this->cache = new QueryCache(2);
        $this->cache->flush();
    }

    public function testRememberStoresValue(): void
    {
        $result = $this->cache->remember('test_key', 60, function () {
            return ['foo' => 'bar'];
        });
        TestRunner::assertEquals('bar', $result['foo']);
    }

    public function testRememberReturnsCachedValue(): void
    {
        $callCount = 0;
        $this->cache->remember('count_test', 60, function () use (&$callCount) {
            $callCount++;
            return ['count' => $callCount];
        });
        $second = $this->cache->remember('count_test', 60, function () use (&$callCount) {
            $callCount++;
            return ['count' => $callCount];
        });
        TestRunner::assertEquals(1, $second['count'], 'Should return cached value without calling callback again');
    }

    public function testForgetInvalidatesKey(): void
    {
        $this->cache->put('forget_me', 'original');
        $this->cache->forget('forget_me');
        $result = $this->cache->remember('forget_me', 60, function () {
            return 'fresh';
        });
        TestRunner::assertEquals('fresh', $result);
    }

    public function testFlushClearsAll(): void
    {
        $this->cache->put('key1', 'a');
        $this->cache->put('key2', 'b');
        $this->cache->flush();
        $r1 = $this->cache->remember('key1', 60, fn() => 'x');
        $r2 = $this->cache->remember('key2', 60, fn() => 'y');
        TestRunner::assertEquals('x', $r1);
        TestRunner::assertEquals('y', $r2);
    }

    public function testTtlExpiration(): void
    {
        $this->cache->remember('ttl_test', 1, fn() => 'first');
        sleep(2);
        $result = $this->cache->remember('ttl_test', 1, fn() => 'second');
        TestRunner::assertEquals('second', $result, 'Should expire after TTL');
    }
}
