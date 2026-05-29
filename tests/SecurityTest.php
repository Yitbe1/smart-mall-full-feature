<?php

require_once __DIR__ . '/TestRunner.php';

class SecurityTest
{
    public function testCsrfTokenGeneration(): void
    {
        if (!isset($_SESSION)) {
            @session_start();
        }
        $token = bin2hex(random_bytes(32));
        $_SESSION['csrf_token'] = $token;
        TestRunner::assertTrue(isset($_SESSION['csrf_token']), 'CSRF token should be set in session');
        TestRunner::assertEquals(64, strlen($_SESSION['csrf_token']), 'CSRF token should be 64 hex chars');
    }

    public function testCsrfTokenValidation(): void
    {
        if (!isset($_SESSION)) {
            @session_start();
        }
        $token = bin2hex(random_bytes(32));
        $_SESSION['csrf_token'] = $token;
        TestRunner::assertTrue(hash_equals($_SESSION['csrf_token'], $token), 'hash_equals should validate matching token');
        TestRunner::assertFalse(hash_equals($_SESSION['csrf_token'], 'invalid'), 'hash_equals should reject wrong token');
    }

    public function testPasswordHashing(): void
    {
        $password = 'SecurePass123!';
        $hash = password_hash($password, PASSWORD_BCRYPT);
        TestRunner::assertTrue(password_verify($password, $hash), 'password_verify should validate correct password');
        TestRunner::assertFalse(password_verify('wrong', $hash), 'password_verify should reject wrong password');
    }

    public function testEmailValidation(): void
    {
        $valid = 'user@example.com';
        $invalid = 'not-an-email';
        TestRunner::assertTrue(filter_var($valid, FILTER_VALIDATE_EMAIL) !== false, 'Should validate correct email');
        TestRunner::assertFalse(filter_var($invalid, FILTER_VALIDATE_EMAIL) !== false, 'Should reject invalid email');
    }

    public function testRandomBytesLength(): void
    {
        $token = bin2hex(random_bytes(32));
        TestRunner::assertEquals(64, strlen($token), '32 random bytes should produce 64 hex chars');
    }
}
