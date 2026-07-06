<?php

// Base path detection (set by Caddy for /payments/ subpath routing)
$basePath = $_SERVER['HTTP_X_BASE_PATH'] ?? '/';

return [
    'host' => 'localhost',
    'dbname' => 'savings_db',
    'username' => 'root',
    'password' => '',
    'base_path' => rtrim($basePath, '/'),

    // App version and build info
    'app_version' => getenv('APP_VERSION') ?: '1.3.0',
    'app_build_date' => getenv('APP_BUILD_DATE') ?: '2026-07-05 12:21:42',

    // Payment system defaults
    'default_fixed_amount' => getenv('DEFAULT_FIXED_AMOUNT') ?: 50000,
];
