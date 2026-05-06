<?php
// cms/config.php — Datenbank-Konfiguration

return [
    'db' => [
        'host'     => '127.0.0.1',
        'port'     => 3306,
        'name'     => 'friedrichdb',
        'user'     => 'friedrichdbuser',
        'password' => 'fjWVT9Iy7j5ecItEClfi',
        'charset'  => 'utf8mb4',
    ],
    'session' => [
        'cookie_name' => 'fs_admin_sess',
        'lifetime'    => 60 * 60 * 8, // 8 Stunden
    ],
    'paths' => [
        'uploads' => __DIR__ . '/../uploads',
    ],
];
