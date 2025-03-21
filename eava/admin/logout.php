<?php
require_once __DIR__ . '/../classes/Session.php';
require_once __DIR__ . '/../classes/Logger.php';

$session = Session::getInstance();
$logger = new Logger();

// Log the logout action if user was logged in
if ($session->isLoggedIn()) {
    $user = $session->getUser();
    $logger->info('Admin logout', [
        'user_id' => $user['id'],
        'username' => $user['username'],
        'ip' => $_SERVER['REMOTE_ADDR']
    ]);
}

// Clear remember token cookie if exists
if (isset($_COOKIE['remember_token'])) {
    setcookie('remember_token', '', time() - 3600, '/', '', true, true);
}

// Destroy session
$session->logout();

// Redirect to login page
header('Location: /admin/login.php');
exit;