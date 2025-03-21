<?php
require_once __DIR__ . '/../classes/Session.php';
require_once __DIR__ . '/../classes/Settings.php';
require_once __DIR__ . '/../classes/Media.php';
require_once __DIR__ . '/../classes/Validator.php';

$session = Session::getInstance();
if (!$session->isLoggedIn() || $session->getUser()['role'] !== 'admin') {
    header('Location: /admin/login.php');
    exit;
}

$settings = new Settings();
$media = new Media();
$message = '';
$error = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Validate CSRF token
        if (!$session->validateCsrfToken($_POST['csrf_token'])) {
            throw new Exception('Invalid security token');
        }

        // Handle logo upload
        if (isset($_FILES['site_logo']) && $_FILES['site_logo']['error'] === UPLOAD_ERR_OK) {
            $logoUpload = $media->upload($_FILES['site_logo'], $session->getUser()['id']);
            if ($logoUpload) {
                $_POST['site_logo'] = $logoUpload['file_path'];
            }
        }

        // Handle header video upload
        if (isset($_FILES['header_video']) && $_FILES['header_video']['error'] === UPLOAD_ERR_OK) {
            $videoUpload = $media->upload($_FILES['header_video'], $session->getUser()['id']);
            if ($videoUpload) {
                $_POST['header_video'] = $videoUpload['file_path'];
            }
        }

        // Update settings
        $settingsToUpdate = [
            // General Settings
            'site_name' => $_POST['site_name'],
            'site_description' => $_POST['site_description'],
            'site_email' => $_POST['site_email'],
            'site_phone' => $_POST['site_phone'],
            'site_address' => $_POST['site_address'],
            'site_logo' => $_POST['site_logo'] ?? $settings->get('site_logo'),
            
            // Social Media
            'facebook_url' => $_POST['facebook_url'],
            'twitter_url' => $_POST['twitter_url'],
            'instagram_url' => $_POST['instagram_url'],
            'youtube_url' => $_POST['youtube_url'],
            'linkedin_url' => $_POST['linkedin_url'],
            
            // API Keys
            'facebook_api_key' => $_POST['facebook_api_key'],
            'facebook_api_secret' => $_POST['facebook_api_secret'],
            'twitter_api_key' => $_POST['twitter_api_key'],
            'twitter_api_secret' => $_POST['twitter_api_secret'],
            'google_analytics_id' => $_POST['google_analytics_id'],
            'recaptcha_site_key' => $_POST['recaptcha_site_key'],
            'recaptcha_secret_key' => $_POST['recaptcha_secret_key'],
            
            // Design Settings
            'primary_color' => $_POST['primary_color'],
            'secondary_color' => $_POST['secondary_color'],
            'accent_color' => $_POST['accent_color'],
            'font_family' => $_POST['font_family'],
            'header_video' => $_POST['header_video'] ?? $settings->get('header_video'),
            'header_overlay_opacity' => $_POST['header_overlay_opacity'],
            
            // Email Settings
            'smtp_host' => $_POST['smtp_host'],
            'smtp_port' => $_POST['smtp_port'],
            'smtp_username' => $_POST['smtp_username'],
            'smtp_password' => $_POST['smtp_password'],
            'smtp_encryption' => $_POST['smtp_encryption'],
            'mail_from_address' => $_POST['mail_from_address'],
            'mail_from_name' => $_POST['mail_from_name'],
            
            // Payment Settings
            'stripe_public_key' => $_POST['stripe_public_key'],
            'stripe_secret_key' => $_POST['stripe_secret_key'],
            'paypal_client_id' => $_POST['paypal_client_id'],
            'paypal_secret' => $_POST['paypal_secret'],
            'currency' => $_POST['currency'],
            
            // Cache Settings
            'cache_driver' => $_POST['cache_driver'],
            'cache_lifetime' => $_POST['cache_lifetime'],
            
            // Other Settings
            'timezone' => $_POST['timezone'],
            'date_format' => $_POST['date_format'],
            'time_format' => $_POST['time_format'],
            'items_per_page' => $_POST['items_per_page']
        ];

        $settings->setMany($settingsToUpdate);
        $message = 'Settings updated successfully';
        
        // Clear cache if requested
        if (isset($_POST['clear_cache'])) {
            $cache = Cache::getInstance();
            $cache->clear();
            $message .= ' and cache cleared';
        }
    } catch (Exception $e) {
        $error = 'Failed to update settings: ' . $e->getMessage();
    }
}

$pageTitle = 'Site Settings';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Site Settings - <?= htmlspecialchars($settings->get('site_name')) ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body class="bg-gray-100">
    <?php include 'includes/header.php'; ?>

    <div class="flex">
        <?php include 'includes/sidebar.php'; ?>

        <main class="flex-1 p-8">
            <div class="max-w-4xl mx-auto">
                <h1 class="text-3xl font-bold mb-8">Site Settings</h1>

                <?php if ($message): ?>
                    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                        <?= htmlspecialchars($message) ?>
                    </div>
                <?php endif; ?>

                <?php if ($error): ?>
                    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                        <?= htmlspecialchars($error) ?>
                    </div>
                <?php endif; ?>

                <form action="" method="POST" enctype="multipart/form-data" class="space-y-6">
                    <input type="hidden" name="csrf_token" value="<?= $session->getCsrfToken() ?>">

                    <!-- Settings Tabs -->
                    <div x-data="{ activeTab: 'general' }">
                        <!-- Tab Navigation -->
                        <div class="border-b border-gray-200 mb-6">
                            <nav class="-mb-px flex space-x-8">
                                <button type="button" 
                                        @click="activeTab = 'general'"
                                        :class="{ 'border-blue-500 text-blue-600': activeTab === 'general' }"
                                        class="py-4 px-1 border-b-2 font-medium text-sm">
                                    General
                                </button>
                                <button type="button" 
                                        @click="activeTab = 'social'"
                                        :class="{ 'border-blue-500 text-blue-600': activeTab === 'social' }"
                                        class="py-4 px-1 border-b-2 font-medium text-sm">
                                    Social Media
                                </button>
                                <button type="button" 
                                        @click="activeTab = 'api'"
                                        :class="{ 'border-blue-500 text-blue-600': activeTab === 'api' }"
                                        class="py-4 px-1 border-b-2 font-medium text-sm">
                                    API Keys
                                </button>
                                <button type="button" 
                                        @click="activeTab = 'design'"
                                        :class="{ 'border-blue-500 text-blue-600': activeTab === 'design' }"
                                        class="py-4 px-1 border-b-2 font-medium text-sm">
                                    Design
                                </button>
                                <button type="button" 
                                        @click="activeTab = 'email'"
                                        :class="{ 'border-blue-500 text-blue-600': activeTab === 'email' }"
                                        class="py-4 px-1 border-b-2 font-medium text-sm">
                                    Email
                                </button>
                                <button type="button" 
                                        @click="activeTab = 'payment'"
                                        :class="{ 'border-blue-500 text-blue-600': activeTab === 'payment' }"
                                        class="py-4 px-1 border-b-2 font-medium text-sm">
                                    Payment
                                </button>
                                <button type="button" 
                                        @click="activeTab = 'advanced'"
                                        :class="{ 'border-blue-500 text-blue-600': activeTab === 'advanced' }"
                                        class="py-4 px-1 border-b-2 font-medium text-sm">
                                    Advanced
                                </button>
                            </nav>
                        </div>

                        <!-- Tab Panels -->
                        <div class="bg-white rounded-lg shadow-lg p-6">
                            <!-- General Settings -->
                            <div x-show="activeTab === 'general'" class="space-y-6">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Site Name</label>
                                    <input type="text" 
                                           name="site_name" 
                                           value="<?= htmlspecialchars($settings->get('site_name')) ?>"
                                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Site Description</label>
                                    <textarea name="site_description" 
                                              rows="3"
                                              class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"><?= htmlspecialchars($settings->get('site_description')) ?></textarea>
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Site Email</label>
                                        <input type="email" 
                                               name="site_email" 
                                               value="<?= htmlspecialchars($settings->get('site_email')) ?>"
                                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Site Phone</label>
                                        <input type="tel" 
                                               name="site_phone" 
                                               value="<?= htmlspecialchars($settings->get('site_phone')) ?>"
                                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                    </div>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Site Address</label>
                                    <textarea name="site_address" 
                                              rows="3"
                                              class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"><?= htmlspecialchars($settings->get('site_address')) ?></textarea>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Site Logo</label>
                                    <?php if ($logo = $settings->get('site_logo')): ?>
                                        <div class="mt-2 mb-4">
                                            <img src="<?= htmlspecialchars($logo) ?>" 
                                                 alt="Current logo" 
                                                 class="h-12">
                                        </div>
                                    <?php endif; ?>
                                    <input type="file" 
                                           name="site_logo" 
                                           accept="image/*"
                                           class="mt-1 block w-full">
                                </div>
                            </div>

                            <!-- Social Media Settings -->
                            <div x-show="activeTab === 'social'" class="space-y-6">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Facebook URL</label>
                                    <input type="url" 
                                           name="facebook_url" 
                                           value="<?= htmlspecialchars($settings->get('facebook_url')) ?>"
                                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Twitter URL</label>
                                    <input type="url" 
                                           name="twitter_url" 
                                           value="<?= htmlspecialchars($settings->get('twitter_url')) ?>"
                                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Instagram URL</label>
                                    <input type="url" 
                                           name="instagram_url" 
                                           value="<?= htmlspecialchars($settings->get('instagram_url')) ?>"
                                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700">YouTube URL</label>
                                    <input type="url" 
                                           name="youtube_url" 
                                           value="<?= htmlspecialchars($settings->get('youtube_url')) ?>"
                                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700">LinkedIn URL</label>
                                    <input type="url" 
                                           name="linkedin_url" 
                                           value="<?= htmlspecialchars($settings->get('linkedin_url')) ?>"
                                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                </div>
                            </div>

                            <!-- API Keys Settings -->
                            <div x-show="activeTab === 'api'" class="space-y-6">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Facebook API Key</label>
                                        <input type="password" 
                                               name="facebook_api_key" 
                                               value="<?= htmlspecialchars($settings->get('facebook_api_key')) ?>"
                                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Facebook API Secret</label>
                                        <input type="password" 
                                               name="facebook_api_secret" 
                                               value="<?= htmlspecialchars($settings->get('facebook_api_secret')) ?>"
                                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                    </div>
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Twitter API Key</label>
                                        <input type="password" 
                                               name="twitter_api_key" 
                                               value="<?= htmlspecialchars($settings->get('twitter_api_key')) ?>"
                                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Twitter API Secret</label>
                                        <input type="password" 
                                               name="twitter_api_secret" 
                                               value="<?= htmlspecialchars($settings->get('twitter_api_secret')) ?>"
                                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                    </div>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Google Analytics ID</label>
                                    <input type="text" 
                                           name="google_analytics_id" 
                                           value="<?= htmlspecialchars($settings->get('google_analytics_id')) ?>"
                                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">reCAPTCHA Site Key</label>
                                        <input type="text" 
                                               name="recaptcha_site_key" 
                                               value="<?= htmlspecialchars($settings->get('recaptcha_site_key')) ?>"
                                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">reCAPTCHA Secret Key</label>
                                        <input type="password" 
                                               name="recaptcha_secret_key" 
                                               value="<?= htmlspecialchars($settings->get('recaptcha_secret_key')) ?>"
                                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                    </div>
                                </div>
                            </div>

                            <!-- Design Settings -->
                            <div x-show="activeTab === 'design'" class="space-y-6">
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Primary Color</label>
                                        <input type="color" 
                                               name="primary_color" 
                                               value="<?= htmlspecialchars($settings->get('primary_color', '#3B82F6')) ?>"
                                               class="mt-1 block w-full h-10">
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Secondary Color</label>
                                        <input type="color" 
                                               name="secondary_color" 
                                               value="<?= htmlspecialchars($settings->get('secondary_color', '#1F2937')) ?>"
                                               class="mt-1 block w-full h-10">
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Accent Color</label>
                                        <input type="color" 
                                               name="accent_color" 
                                               value="<?= htmlspecialchars($settings->get('accent_color', '#10B981')) ?>"
                                               class="mt-1 block w-full h-10">
                                    </div>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Font Family</label>
                                    <select name="font_family"
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                        <option value="Inter" <?= $settings->get('font_family') === 'Inter' ? 'selected' : '' ?>>Inter</option>
                                        <option value="Roboto" <?= $settings->get('font_family') === 'Roboto' ? 'selected' : '' ?>>Roboto</option>
                                        <option value="Open Sans" <?= $settings->get('font_family') === 'Open Sans' ? 'selected' : '' ?>>Open Sans</option>
                                        <option value="Poppins" <?= $settings->get('font_family') === 'Poppins' ? 'selected' : '' ?>>Poppins</option>
                                    </select>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Header Video</label>
                                    <?php if ($video = $settings->get('header_video')): ?>
                                        <div class="mt-2 mb-4">
                                            <video src="<?= htmlspecialchars($video) ?>" 
                                                   controls
                                                   class="w-full max-w-md"></video>
                                        </div>
                                    <?php endif; ?>
                                    <input type="file" 
                                           name="header_video" 
                                           accept="video/*"
                                           class="mt-1 block w-full">
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Header Overlay Opacity</label>
                                    <input type="range" 
                                           name="header_overlay_opacity" 
                                           min="0" 
                                           max="100" 
                                           step="5"
                                           value="<?= htmlspecialchars($settings->get('header_overlay_opacity', '50')) ?>"
                                           class="mt-1 block w-full">
                                </div>
                            </div>

                            <!-- Email Settings -->
                            <div x-show="activeTab === 'email'" class="space-y-6">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">SMTP Host</label>
                                        <input type="text" 
                                               name="smtp_host" 
                                               value="<?= htmlspecialchars($settings->get('smtp_host')) ?>"
                                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">SMTP Port</label>
                                        <input type="number" 
                                               name="smtp_port" 
                                               value="<?= htmlspecialchars($settings->get('smtp_port')) ?>"
                                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                    </div>
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">SMTP Username</label>
                                        <input type="text" 
                                               name="smtp_username" 
                                               value="<?= htmlspecialchars($settings->get('smtp_username')) ?>"
                                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">SMTP Password</label>
                                        <input type="password" 
                                               name="smtp_password" 
                                               value="<?= htmlspecialchars($settings->get('smtp_password')) ?>"
                                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                    </div>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700">SMTP Encryption</label>
                                    <select name="smtp_encryption"
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                        <option value="tls" <?= $settings->get('smtp_encryption') === 'tls' ? 'selected' : '' ?>>TLS</option>
                                        <option value="ssl" <?= $settings->get('smtp_encryption') === 'ssl' ? 'selected' : '' ?>>SSL</option>
                                        <option value="none" <?= $settings->get('smtp_encryption') === 'none' ? 'selected' : '' ?>>None</option>
                                    </select>
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Mail From Address</label>
                                        <input type="email" 
                                               name="mail_from_address" 
                                               value="<?= htmlspecialchars($settings->get('mail_from_address')) ?>"
                                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Mail From Name</label>
                                        <input type="text" 
                                               name="mail_from_name" 
                                               value="<?= htmlspecialchars($settings->get('mail_from_name')) ?>"
                                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                    </div>
                                </div>
                            </div>

                            <!-- Payment Settings -->
                            <div x-show="activeTab === 'payment'" class="space-y-6">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Stripe Public Key</label>
                                        <input type="text" 
                                               name="stripe_public_key" 
                                               value="<?= htmlspecialchars($settings->get('stripe_public_key')) ?>"
                                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Stripe Secret Key</label>
                                        <input type="password" 
                                               name="stripe_secret_key" 
                                               value="<?= htmlspecialchars($settings->get('stripe_secret_key')) ?>"
                                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                    </div>
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">PayPal Client ID</label>
                                        <input type="text" 
                                               name="paypal_client_id" 
                                               value="<?= htmlspecialchars($settings->get('paypal_client_id')) ?>"
                                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">PayPal Secret</label>
                                        <input type="password" 
                                               name="paypal_secret" 
                                               value="<?= htmlspecialchars($settings->get('paypal_secret')) ?>"
                                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                    </div>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Currency</label>
                                    <select name="currency"
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                        <option value="USD" <?= $settings->get('currency') === 'USD' ? 'selected' : '' ?>>USD</option>
                                        <option value="EUR" <?= $settings->get('currency') === 'EUR' ? 'selected' : '' ?>>EUR</option>
                                        <option value="GBP" <?= $settings->get('currency') === 'GBP' ? 'selected' : '' ?>>GBP</option>
                                        <option value="CAD" <?= $settings->get('currency') === 'CAD' ? 'selected' : '' ?>>