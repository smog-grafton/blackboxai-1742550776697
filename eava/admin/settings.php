<?php
require_once '../config/config.php';
require_once '../classes/Database.php';
require_once '../classes/User.php';
require_once '../classes/Settings.php';

session_start();

$user = new User();
$settings = Settings::getInstance();

// Check if user is logged in and is admin
if (!$user->isLoggedIn() || !$user->isAdmin()) {
    header('Location: index.php');
    exit;
}

$currentUser = $user->getCurrentUser();
$message = '';
$error = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST[CSRF_TOKEN_NAME]) && $user->validateCSRFToken($_POST[CSRF_TOKEN_NAME])) {
    try {
        // General Settings
        $generalSettings = [
            'general' => [
                'site_name' => $_POST['site_name'] ?? '',
                'site_description' => $_POST['site_description'] ?? '',
                'footer_text' => $_POST['footer_text'] ?? '',
                'contact_email' => $_POST['contact_email'] ?? '',
                'contact_phone' => $_POST['contact_phone'] ?? '',
                'contact_address' => $_POST['contact_address'] ?? ''
            ]
        ];

        // Theme Settings
        $themeSettings = [
            'theme' => [
                'primary_color' => $_POST['primary_color'] ?? '',
                'secondary_color' => $_POST['secondary_color'] ?? '',
                'font_family' => $_POST['font_family'] ?? '',
                'header_style' => $_POST['header_style'] ?? '',
                'footer_style' => $_POST['footer_style'] ?? ''
            ]
        ];

        // Social Media Settings
        $socialSettings = [
            'social_media' => [
                'facebook_url' => $_POST['facebook_url'] ?? '',
                'twitter_url' => $_POST['twitter_url'] ?? '',
                'youtube_url' => $_POST['youtube_url'] ?? '',
                'instagram_url' => $_POST['instagram_url'] ?? '',
                'facebook_api_key' => $_POST['facebook_api_key'] ?? '',
                'twitter_api_key' => $_POST['twitter_api_key'] ?? '',
                'youtube_api_key' => $_POST['youtube_api_key'] ?? ''
            ]
        ];

        // Payment Gateway Settings
        $paymentSettings = [
            'payment' => [
                'flutterwave_public_key' => $_POST['flutterwave_public_key'] ?? '',
                'flutterwave_secret_key' => $_POST['flutterwave_secret_key'] ?? '',
                'paypal_client_id' => $_POST['paypal_client_id'] ?? '',
                'paypal_secret' => $_POST['paypal_secret'] ?? '',
                'stripe_public_key' => $_POST['stripe_public_key'] ?? '',
                'stripe_secret_key' => $_POST['stripe_secret_key'] ?? '',
                'currency' => $_POST['currency'] ?? 'USD'
            ]
        ];

        // Update all settings
        $settings->setMultiple($generalSettings);
        $settings->setMultiple($themeSettings);
        $settings->setMultiple($socialSettings);
        $settings->setMultiple($paymentSettings);

        // Handle logo upload
        if (isset($_FILES['site_logo']) && $_FILES['site_logo']['error'] === UPLOAD_ERR_OK) {
            $logoFile = $_FILES['site_logo'];
            $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
            
            if (in_array($logoFile['type'], $allowedTypes)) {
                $uploadDir = '../uploads/media/';
                $fileName = 'logo_' . time() . '_' . basename($logoFile['name']);
                $uploadPath = $uploadDir . $fileName;
                
                if (move_uploaded_file($logoFile['tmp_name'], $uploadPath)) {
                    $settings->set('site_logo', $fileName, 'general');
                }
            }
        }

        $message = 'Settings updated successfully!';
        
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}

// Get current settings
$generalSettings = $settings->getGroup('general');
$themeSettings = $settings->getGroup('theme');
$socialSettings = $settings->getGroup('social_media');
$paymentSettings = $settings->getGroup('payment');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settings - <?php echo SITE_NAME; ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        body {
            font-family: 'Inter', sans-serif;
        }
    </style>
</head>
<body class="bg-gray-100">
    <div class="min-h-screen">
        <!-- Top Navigation -->
        <?php include 'includes/header.php'; ?>

        <div class="flex">
            <!-- Sidebar -->
            <?php include 'includes/sidebar.php'; ?>

            <!-- Main Content -->
            <div class="flex-1 p-8">
                <div class="max-w-7xl mx-auto">
                    <div class="bg-white shadow-sm rounded-lg p-6">
                        <h1 class="text-2xl font-bold mb-6">Site Settings</h1>

                        <?php if ($message): ?>
                        <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6">
                            <?php echo htmlspecialchars($message); ?>
                        </div>
                        <?php endif; ?>

                        <?php if ($error): ?>
                        <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6">
                            <?php echo htmlspecialchars($error); ?>
                        </div>
                        <?php endif; ?>

                        <form method="post" enctype="multipart/form-data">
                            <input type="hidden" name="<?php echo CSRF_TOKEN_NAME; ?>" 
                                   value="<?php echo $user->generateCSRFToken(); ?>">

                            <!-- General Settings -->
                            <div class="mb-8">
                                <h2 class="text-lg font-semibold mb-4">General Settings</h2>
                                <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Site Name</label>
                                        <input type="text" name="site_name" 
                                               value="<?php echo htmlspecialchars($generalSettings['site_name'] ?? ''); ?>"
                                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Site Logo</label>
                                        <input type="file" name="site_logo" 
                                               class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
                                    </div>
                                    <div class="md:col-span-2">
                                        <label class="block text-sm font-medium text-gray-700">Site Description</label>
                                        <textarea name="site_description" rows="3" 
                                                  class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"><?php echo htmlspecialchars($generalSettings['site_description'] ?? ''); ?></textarea>
                                    </div>
                                </div>
                            </div>

                            <!-- Theme Settings -->
                            <div class="mb-8">
                                <h2 class="text-lg font-semibold mb-4">Theme Settings</h2>
                                <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Primary Color</label>
                                        <input type="color" name="primary_color" 
                                               value="<?php echo htmlspecialchars($themeSettings['primary_color'] ?? '#000000'); ?>"
                                               class="mt-1 block w-full h-10 rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Secondary Color</label>
                                        <input type="color" name="secondary_color" 
                                               value="<?php echo htmlspecialchars($themeSettings['secondary_color'] ?? '#000000'); ?>"
                                               class="mt-1 block w-full h-10 rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    </div>
                                </div>
                            </div>

                            <!-- Social Media Settings -->
                            <div class="mb-8">
                                <h2 class="text-lg font-semibold mb-4">Social Media Settings</h2>
                                <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Facebook URL</label>
                                        <input type="url" name="facebook_url" 
                                               value="<?php echo htmlspecialchars($socialSettings['facebook_url'] ?? ''); ?>"
                                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Twitter URL</label>
                                        <input type="url" name="twitter_url" 
                                               value="<?php echo htmlspecialchars($socialSettings['twitter_url'] ?? ''); ?>"
                                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">YouTube URL</label>
                                        <input type="url" name="youtube_url" 
                                               value="<?php echo htmlspecialchars($socialSettings['youtube_url'] ?? ''); ?>"
                                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Instagram URL</label>
                                        <input type="url" name="instagram_url" 
                                               value="<?php echo htmlspecialchars($socialSettings['instagram_url'] ?? ''); ?>"
                                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    </div>
                                </div>
                            </div>

                            <!-- Payment Settings -->
                            <div class="mb-8">
                                <h2 class="text-lg font-semibold mb-4">Payment Gateway Settings</h2>
                                <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Flutterwave Public Key</label>
                                        <input type="password" name="flutterwave_public_key" 
                                               value="<?php echo htmlspecialchars($paymentSettings['flutterwave_public_key'] ?? ''); ?>"
                                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Flutterwave Secret Key</label>
                                        <input type="password" name="flutterwave_secret_key" 
                                               value="<?php echo htmlspecialchars($paymentSettings['flutterwave_secret_key'] ?? ''); ?>"
                                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">PayPal Client ID</label>
                                        <input type="password" name="paypal_client_id" 
                                               value="<?php echo htmlspecialchars($paymentSettings['paypal_client_id'] ?? ''); ?>"
                                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">PayPal Secret</label>
                                        <input type="password" name="paypal_secret" 
                                               value="<?php echo htmlspecialchars($paymentSettings['paypal_secret'] ?? ''); ?>"
                                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Stripe Public Key</label>
                                        <input type="password" name="stripe_public_key" 
                                               value="<?php echo htmlspecialchars($paymentSettings['stripe_public_key'] ?? ''); ?>"
                                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Stripe Secret Key</label>
                                        <input type="password" name="stripe_secret_key" 
                                               value="<?php echo htmlspecialchars($paymentSettings['stripe_secret_key'] ?? ''); ?>"
                                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    </div>
                                </div>
                            </div>

                            <div class="flex justify-end">
                                <button type="submit" 
                                        class="bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                                    Save Settings
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Toggle password visibility
        document.querySelectorAll('input[type="password"]').forEach(input => {
            const toggleBtn = document.createElement('button');
            toggleBtn.type = 'button';
            toggleBtn.className = 'absolute right-2 top-1/2 transform -translate-y-1/2 text-gray-500';
            toggleBtn.innerHTML = '<i class="fas fa-eye"></i>';
            
            toggleBtn.addEventListener('click', () => {
                const type = input.type === 'password' ? 'text' : 'password';
                input.type = type;
                toggleBtn.innerHTML = type === 'password' ? 
                    '<i class="fas fa-eye"></i>' : 
                    '<i class="fas fa-eye-slash"></i>';
            });

            input.parentElement.style.position = 'relative';
            input.parentElement.appendChild(toggleBtn);
        });
    </script>
</body>
</html>