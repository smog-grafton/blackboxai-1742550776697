<?php
require_once __DIR__ . '/../classes/Session.php';
require_once __DIR__ . '/../classes/User.php';
require_once __DIR__ . '/../classes/Settings.php';
require_once __DIR__ . '/../classes/Logger.php';

$session = Session::getInstance();
$settings = new Settings();
$logger = new Logger();

// If already logged in, redirect to dashboard
if ($session->isLoggedIn() && $session->getUser()['role'] === 'admin') {
    header('Location: /admin/dashboard.php');
    exit;
}

$error = '';

// Handle login form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Validate CSRF token
        if (!$session->validateCsrfToken($_POST['csrf_token'])) {
            throw new Exception('Invalid security token');
        }

        $username = $_POST['username'] ?? '';
        $password = $_POST['password'] ?? '';
        $remember = isset($_POST['remember']);

        // Validate input
        if (empty($username) || empty($password)) {
            throw new Exception('Please enter both username and password');
        }

        // Attempt authentication
        $user = new User();
        $result = $user->authenticate($username, $password);

        if ($result && $result['role'] === 'admin') {
            // Set session
            $session->login($result);

            // Set remember token if requested
            if ($remember) {
                $token = bin2hex(random_bytes(32));
                $user->setRememberToken($result['id'], $token);
                setcookie('remember_token', $token, time() + (86400 * 30), '/', '', true, true);
            }

            // Log successful login
            $logger->info('Admin login successful', [
                'user_id' => $result['id'],
                'username' => $result['username'],
                'ip' => $_SERVER['REMOTE_ADDR']
            ]);

            // Redirect to dashboard
            header('Location: /admin/dashboard.php');
            exit;
        } else {
            // Log failed login attempt
            $logger->warning('Failed admin login attempt', [
                'username' => $username,
                'ip' => $_SERVER['REMOTE_ADDR']
            ]);

            throw new Exception('Invalid username or password');
        }
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - <?= htmlspecialchars($settings->get('site_name')) ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center">
    <div class="max-w-md w-full mx-4">
        <!-- Logo -->
        <div class="text-center mb-8">
            <?php if ($logo = $settings->get('site_logo')): ?>
                <img src="<?= htmlspecialchars($logo) ?>" 
                     alt="<?= htmlspecialchars($settings->get('site_name')) ?>" 
                     class="h-16 mx-auto">
            <?php else: ?>
                <h1 class="text-2xl font-bold">
                    <?= htmlspecialchars($settings->get('site_name')) ?>
                </h1>
            <?php endif; ?>
        </div>

        <!-- Login Form -->
        <div class="bg-white rounded-lg shadow-lg p-8">
            <h2 class="text-2xl font-bold text-center mb-8">Admin Login</h2>

            <?php if ($error): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                    <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>

            <form method="POST" class="space-y-6">
                <input type="hidden" name="csrf_token" value="<?= $session->getCsrfToken() ?>">

                <div>
                    <label for="username" class="block text-sm font-medium text-gray-700">
                        Username or Email
                    </label>
                    <input type="text" 
                           id="username" 
                           name="username" 
                           required 
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                </div>

                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700">
                        Password
                    </label>
                    <div class="relative">
                        <input type="password" 
                               id="password" 
                               name="password" 
                               required 
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        <button type="button"
                                onclick="togglePassword()"
                                class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-500 hover:text-gray-700">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                </div>

                <div class="flex items-center">
                    <input type="checkbox" 
                           id="remember" 
                           name="remember" 
                           class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                    <label for="remember" class="ml-2 block text-sm text-gray-700">
                        Remember me
                    </label>
                </div>

                <button type="submit" 
                        class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    Sign in
                </button>

                <div class="text-center">
                    <a href="/forgot-password" class="text-sm text-blue-600 hover:text-blue-800">
                        Forgot your password?
                    </a>
                </div>
            </form>
        </div>

        <!-- Back to Site -->
        <div class="text-center mt-8">
            <a href="/" class="text-sm text-gray-600 hover:text-gray-800">
                <i class="fas fa-arrow-left mr-1"></i>
                Back to site
            </a>
        </div>
    </div>

    <script>
        // Focus username field on page load
        document.getElementById('username').focus();

        // Toggle password visibility
        function togglePassword() {
            const password = document.getElementById('password');
            const button = password.nextElementSibling;
            const icon = button.querySelector('i');

            if (password.type === 'password') {
                password.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                password.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        }
    </script>
</body>
</html>