<?php

return [
    'host' => 'localhost',
    'dbname' => 'savings_db',
    'username' => 'root',
    'password' => '',
    
    // App version and build info
    'app_version' => getenv('APP_VERSION') ?: '1.1.0',
    'app_build_date' => getenv('APP_BUILD_DATE') ?: '2026-07-03 10:15:00',
    'app_commit_hash' => '02f962b',
];
