<?php

return [
    'host' => 'localhost',
    'dbname' => 'savings_db',
    'username' => 'root',
    'password' => '',
    
    // App version and build info
    'app_version' => getenv('APP_VERSION') ?: '1.1.2',
    'app_build_date' => getenv('APP_BUILD_DATE') ?: '2026-07-04 11:27:53',
    'app_commit_hash' => 'cf07ddc',
];
