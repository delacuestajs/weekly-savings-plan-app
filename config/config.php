<?php

return [
    'host' => 'localhost',
    'dbname' => 'savings_db',
    'username' => 'root',
    'password' => '',
    
    // App version and build info
    'app_version' => getenv('APP_VERSION') ?: '1.1.1',
    'app_build_date' => getenv('APP_BUILD_DATE') ?: '2026-07-03 12:33:10',
    'app_commit_hash' => '0ab67a6',
];
