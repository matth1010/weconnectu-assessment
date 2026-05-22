<?php

declare(strict_types=1);

return [
    'db' => [
        'driver' => getenv('DB_DRIVER') ?: 'sqlite',
        'host' => getenv('DB_HOST') ?: '127.0.0.1',
        'port' => getenv('DB_PORT') ?: '3306',
        'name' => getenv('DB_NAME') ?: 'sa_contact_form',
        'user' => getenv('DB_USER') ?: 'root',
        'pass' => getenv('DB_PASS') ?: '',
        'charset' => 'utf8mb4',
        'sqlite_path' => getenv('SQLITE_PATH') ?: dirname(__DIR__) . '/database/weconnectu.sqlite',
    ],
];
