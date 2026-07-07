<?php

// Base path detection (set by Caddy for /payments/ subpath routing)
$basePath = $_SERVER['HTTP_X_BASE_PATH'] ?? '/';

// Read APP_URL from .env file (same approach as Mail helper)
$envUrl = getenv('APP_URL') ?: ($_ENV['APP_URL'] ?? '');
if (!$envUrl) {
    $envFile = __DIR__ . '/../.env';
    if (file_exists($envFile)) {
        $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '' || $line[0] === '#') continue;
            if (strpos($line, '=') !== false) {
                list($key, $value) = explode('=', $line, 2);
                if (trim($key) === 'APP_URL') {
                    $envUrl = trim($value);
                    break;
                }
            }
        }
    }
}

// Full base URL for email links and absolute redirects
if ($envUrl) {
    $baseUrl = rtrim($envUrl, '/');
} else {
    $protocol = $_SERVER['HTTP_X_FORWARDED_PROTO'] ?? ((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http');
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $baseUrl = $protocol . '://' . $host . rtrim($basePath, '/');
}

return [
    'host' => 'localhost',
    'dbname' => 'savings_db',
    'username' => 'root',
    'password' => '',
    'base_path' => rtrim($basePath, '/'),
    'base_url' => $baseUrl,

    // App version and build info
    'app_version' => getenv('APP_VERSION') ?: '1.4.3',
    'app_build_date' => getenv('APP_BUILD_DATE') ?: '2026-07-06',

    // Payment system defaults
    'default_fixed_amount' => getenv('DEFAULT_FIXED_AMOUNT') ?: 50000,
];
