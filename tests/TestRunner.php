<?php

class TestRunner
{
    public int $passed = 0;
    public int $failed = 0;
    private array $failures = [];
    private float $start;

    public function run(string $class): void
    {
        $this->start = microtime(true);
        $ref = new ReflectionClass($class);
        $instance = $ref->newInstance();

        echo "\n  " . $class . "\n  " . str_repeat('-', strlen($class)) . "\n";

        foreach ($ref->getMethods() as $method) {
            if (str_starts_with($method->name, 'test')) {
                try {
                    if ($ref->hasMethod('setUp')) {
                        $instance->setUp();
                    }
                    $instance->{$method->name}();
                    $this->passed++;
                    echo "  \033[32m✓\033[0m {$method->name}\n";
                } catch (Throwable $e) {
                    $this->failed++;
                    $this->failures[] = "  {$method->name}: {$e->getMessage()}";
                    echo "  \033[31m✗\033[0m {$method->name}\n";
                }
            }
        }
    }

    public static function assertTrue(mixed $actual, string $msg = ''): void
    {
        if ($actual !== true) {
            throw new RuntimeException($msg ?: "Expected true, got " . var_export($actual, true));
        }
    }

    public static function assertFalse(mixed $actual, string $msg = ''): void
    {
        if ($actual !== false) {
            throw new RuntimeException($msg ?: "Expected false, got " . var_export($actual, true));
        }
    }

    public static function assertEquals(mixed $expected, mixed $actual, string $msg = ''): void
    {
        if ($expected !== $actual) {
            throw new RuntimeException($msg ?: "Expected " . var_export($expected, true) . ", got " . var_export($actual, true));
        }
    }

    public static function assertNotNull(mixed $actual, string $msg = ''): void
    {
        if ($actual === null) {
            throw new RuntimeException($msg ?: "Expected non-null, got null");
        }
    }

    public static function assertCount(int $expected, array $actual, string $msg = ''): void
    {
        if (count($actual) !== $expected) {
            throw new RuntimeException($msg ?: "Expected count $expected, got " . count($actual));
        }
    }

    public function summary(): void
    {
        $elapsed = round((microtime(true) - $this->start) * 1000);
        $total = $this->passed + $this->failed;
        echo "\n  \033[1mResults:\033[0m {$this->passed} passed, {$this->failed} failed, $total total ({$elapsed}ms)\n";

        if ($this->failures) {
            echo "\n  \033[31mFailures:\033[0m\n";
            foreach ($this->failures as $f) {
                echo "  ✗ $f\n";
            }
        }

        echo "\n";
    }
}
