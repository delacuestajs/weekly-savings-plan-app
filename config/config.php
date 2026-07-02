<?php

return [
    'host' => 'localhost',
    'dbname' => 'savings_db',
    'username' => 'root',
    'password' => '',
    
    // App version and build info
    'app_version' => getenv('APP_VERSION') ?: '1.0.1',
    'app_build_date' => getenv('APP_BUILD_DATE') ?: '2026-07-02 12:27:00',
    'app_commit_hash' => '74c3d56',
];
