<?php

return [
    'database' => [
        'host' => $_ENV['DB_HOST'] ?? 'localhost',
        'port' => $_ENV['DB_PORT'] ?? '3306',
        'name' => $_ENV['DB_NAME'] ?? 'invoice_db',
        'username' => $_ENV['DB_USERNAME'] ?? 'root',
        'password' => $_ENV['DB_PASSWORD'] ?? '',
        'charset' => 'utf8mb4'
    ],
    'cors' => [
        'allowed_origins' => '*',
        'allowed_methods' => 'GET, POST, PUT, DELETE, OPTIONS',
        'allowed_headers' => 'Content-Type, Authorization'
    ]
];
