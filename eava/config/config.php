<?php
return [
    // Database Configuration
    'db_host' => 'localhost',
    'db_name' => 'eava_db',
    'db_user' => 'eava_user',
    'db_pass' => 'eava_password',

    // Application Settings
    'app_name' => 'EAVA',
    'app_url' => 'http://localhost:8000',
    'app_env' => 'development', // development, production
    'debug' => true,
    'timezone' => 'UTC',

    // Session Configuration
    'session_lifetime' => 7200, // 2 hours
    'session_secure' => false,
    'session_http_only' => true,

    // Mail Configuration
    'mail_driver' => 'smtp',
    'mail_host' => 'smtp.mailtrap.io',
    'mail_port' => 2525,
    'mail_username' => null,
    'mail_password' => null,
    'mail_encryption' => null,
    'mail_from_address' => 'noreply@eava.org',
    'mail_from_name' => 'EAVA',

    // File Upload Settings
    'upload_max_size' => 5242880, // 5MB
    'allowed_file_types' => [
        'image' => ['jpg', 'jpeg', 'png', 'gif'],
        'document' => ['pdf', 'doc', 'docx', 'xls', 'xlsx'],
        'video' => ['mp4', 'avi', 'mov']
    ],
    'upload_path' => __DIR__ . '/../uploads',

    // Payment Gateway Settings
    'stripe_key' => '',
    'stripe_secret' => '',
    'paypal_client_id' => '',
    'paypal_secret' => '',
    'currency' => 'USD',

    // Social Media API Keys
    'facebook_app_id' => '',
    'facebook_app_secret' => '',
    'twitter_api_key' => '',
    'twitter_api_secret' => '',
    'instagram_client_id' => '',
    'instagram_client_secret' => '',

    // Security Settings
    'jwt_secret' => 'your-jwt-secret-key',
    'password_min_length' => 8,
    'password_requires_special' => true,
    'password_requires_number' => true,
    'password_requires_uppercase' => true,
    'max_login_attempts' => 5,
    'lockout_time' => 900, // 15 minutes

    // Cache Settings
    'cache_driver' => 'file', // file, redis, memcached
    'cache_prefix' => 'eava_',
    'cache_lifetime' => 3600, // 1 hour

    // Logging Settings
    'log_channel' => 'daily', // single, daily, slack, syslog
    'log_level' => 'debug', // debug, info, notice, warning, error, critical, alert, emergency
    'log_max_files' => 30,

    // API Settings
    'api_debug' => true,
    'api_timeout' => 30,
    'api_pagination_limit' => 50,

    // Media Settings
    'media_library_path' => __DIR__ . '/../uploads/media',
    'media_library_url' => '/uploads/media',
    'image_thumbnail_sizes' => [
        'small' => [150, 150],
        'medium' => [300, 300],
        'large' => [600, 600]
    ],

    // Social Wall Settings
    'social_wall_refresh_interval' => 300, // 5 minutes
    'social_wall_cache_time' => 3600, // 1 hour
    'social_wall_items_per_network' => 5,

    // Festival Settings
    'festival_registration_open' => true,
    'festival_max_participants' => 1000,
    'festival_date_start' => '2024-06-01',
    'festival_date_end' => '2024-06-07',

    // Donation Settings
    'minimum_donation_amount' => 5,
    'featured_donation_amount' => 100,
    'donation_currency_symbol' => '$',

    // Grant Settings
    'grant_application_fee' => 25,
    'grant_review_period_days' => 30,
    'grant_max_file_size' => 10485760, // 10MB

    // Campaign Settings
    'campaign_min_goal' => 1000,
    'campaign_max_duration_days' => 90,
    'campaign_featured_cost' => 50,

    // Event Settings
    'event_registration_fee' => 0,
    'event_max_attendees' => 200,
    'event_reminder_days' => 2,

    // Project Settings
    'project_status_colors' => [
        'draft' => '#6B7280',
        'active' => '#10B981',
        'completed' => '#3B82F6',
        'cancelled' => '#EF4444'
    ],

    // Program Settings
    'program_application_deadline_days' => 14,
    'program_max_participants' => 50,
    'program_waitlist_limit' => 20,

    // Resource Settings
    'resource_download_requires_login' => true,
    'resource_max_downloads_per_day' => 50,
    'resource_featured_limit' => 10,

    // Blog Settings
    'blog_posts_per_page' => 10,
    'blog_allow_comments' => true,
    'blog_moderate_comments' => true,
    'blog_excerpt_length' => 200,

    // Admin Settings
    'admin_theme' => 'default',
    'admin_items_per_page' => 20,
    'admin_recent_activities' => 10,
    'admin_dashboard_stats_days' => 30
];