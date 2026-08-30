<?php

declare(strict_types=1);

return [
    'name' => env('APP_NAME', 'Staffing Platform'),
    'env' => env('APP_ENV', 'production'),
    'debug' => env('APP_DEBUG', false),
    'url' => rtrim((string) env('APP_URL', 'http://localhost'), '/'),
    'key' => env('APP_KEY', ''),
    'timezone' => 'UTC',

    'session' => [
        'name' => env('SESSION_NAME', 'app_session'),
        'lifetime' => (int) env('SESSION_LIFETIME', 7200),
        'secure' => (bool) env('SESSION_SECURE', false),
    ],

    'uploads' => [
        'resume_max_bytes' => (int) env('UPLOAD_MAX_RESUME_MB', 8) * 1024 * 1024,
        'avatar_max_bytes' => (int) env('UPLOAD_MAX_AVATAR_MB', 3) * 1024 * 1024,
        'resume_path' => STORAGE_PATH . '/uploads/resumes',
        'avatar_path' => STORAGE_PATH . '/uploads/avatars',
        'logo_path' => STORAGE_PATH . '/uploads/logos',
        'resume_mimes' => ['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'],
        'resume_extensions' => ['pdf', 'doc', 'docx'],
        'avatar_mimes' => ['image/jpeg', 'image/png', 'image/webp'],
        'avatar_extensions' => ['jpg', 'jpeg', 'png', 'webp'],
    ],

    'mail' => [
        'driver' => env('MAIL_DRIVER', 'log'),
        'from_address' => env('MAIL_FROM_ADDRESS', 'noreply@example.com'),
        'from_name' => env('MAIL_FROM_NAME', 'Staffing Platform'),
        'smtp' => [
            'host' => env('MAIL_HOST', ''),
            'port' => (int) env('MAIL_PORT', 587),
            'encryption' => env('MAIL_ENCRYPTION', 'tls'),
            'username' => env('MAIL_USERNAME', ''),
            'password' => env('MAIL_PASSWORD', ''),
            'timeout' => (int) env('MAIL_TIMEOUT', 10),
        ],
    ],
];
