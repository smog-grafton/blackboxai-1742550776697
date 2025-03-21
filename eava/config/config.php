<?php
// Database Configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'eava_db');
define('DB_USER', 'root');
define('DB_PASS', '');

// Site Configuration
define('SITE_NAME', 'East Africa Visual Arts');
define('SITE_URL', 'http://localhost:8000');
define('ADMIN_URL', SITE_URL . '/admin');

// Default Admin Credentials
define('DEFAULT_ADMIN_EMAIL', 'smoggrafron@gmail.com');
define('DEFAULT_ADMIN_USERNAME', 'smogcoders');
define('DEFAULT_ADMIN_NAME', 'main admin');
// Note: Password will be hashed during installation: Akiibu@989898

// Error Reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/../logs/error.log');

// Session Configuration
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_secure', 1);

// Timezone
date_default_timezone_set('Africa/Nairobi');

// File Upload Configuration
define('MAX_FILE_SIZE', 10 * 1024 * 1024); // 10MB
define('ALLOWED_FILE_TYPES', [
    'image/jpeg',
    'image/png',
    'image/gif',
    'application/pdf',
    'video/mp4'
]);

// Social Media API Configuration
define('SOCIAL_MEDIA_CONFIG', [
    'facebook' => [
        'app_id' => '',
        'app_secret' => '',
        'default_graph_version' => 'v12.0',
    ],
    'twitter' => [
        'api_key' => '',
        'api_secret' => '',
        'bearer_token' => '',
    ],
    'youtube' => [
        'api_key' => '',
    ]
]);

// Payment Gateway Configuration
define('PAYMENT_GATEWAYS', [
    'flutterwave' => [
        'public_key' => '',
        'secret_key' => '',
        'encryption_key' => '',
    ],
    'paypal' => [
        'client_id' => '',
        'client_secret' => '',
        'mode' => 'sandbox', // or 'live'
    ],
    'stripe' => [
        'publishable_key' => '',
        'secret_key' => '',
    ]
]);

// Security Configuration
define('CSRF_TOKEN_NAME', 'eava_csrf_token');
define('PASSWORD_RESET_EXPIRY', 24 * 60 * 60); // 24 hours

// Cache Configuration
define('CACHE_ENABLED', true);
define('CACHE_DURATION', 3600); // 1 hour

// Multilingual Configuration
define('DEFAULT_LANGUAGE', 'en');
define('AVAILABLE_LANGUAGES', [
    'en' => 'English',
    'sw' => 'Swahili',
    'fr' => 'French'
]);