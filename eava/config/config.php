<?php
// Application Configuration

// Database settings
define('DB_HOST', 'localhost');
define('DB_NAME', 'eava_db');
define('DB_USER', 'root');
define('DB_PASS', 'password');

// Site settings
define('SITE_NAME', 'EAVA');
define('SITE_URL', 'http://localhost/eava');

// Mail settings
define('MAIL_FROM', 'noreply@eava.com');
define('MAIL_FROM_NAME', 'EAVA Support');
define('MAIL_REPLY_TO', 'support@eava.com');

// CSRF token name
define('CSRF_TOKEN_NAME', 'csrf_token');

// Other constants
define('UPLOAD_DIR', __DIR__ . '/../uploads/media/');
define('MAX_UPLOAD_SIZE', 10 * 1024 * 1024); // 10MB

// Log settings
define('LOG_PATH', __DIR__ . '/../logs/');
define('LOG_LEVEL', 'debug'); // Set log level

// Timezone
date_default_timezone_set('UTC');