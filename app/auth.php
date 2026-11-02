<?php

declare(strict_types=1);

require_once __DIR__ . '/database.php';

// Cookies work over local HTTP and become Secure when served over HTTPS.
ini_set('session.use_strict_mode', '1');
ini_set('session.use_only_cookies', '1');
session_name('auth_session');
session_set_cookie_params([
    'lifetime' => 0,
    'path' => '/',
    'secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
    'httponly' => true,
    'samesite' => 'Lax',
]);
session_start();

// Avoid serving protected pages from the browser's cache after logout.
header('Cache-Control: no-store');

function redirect(string $path): never
{
    header('Location: ' . $path, true, 303);
    exit;
}

function isLoggedIn(): bool
{
    return isset($_SESSION['user_id']);
}

function requireLogin(): void
{
    if (!isLoggedIn()) {
        redirect('login.php');
    }
}

function escape(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function csrfToken(): string
{
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['csrf_token'];
}

function validCsrfToken(mixed $token): bool
{
    return is_string($token) && hash_equals(csrfToken(), $token);
}
