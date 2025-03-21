<?php
require_once __DIR__ . '/../classes/Session.php';
require_once __DIR__ . '/../classes/Settings.php';

$session = Session::getInstance();

// Check if user is logged in and is an admin
if (!$session->isLoggedIn()) {
    header('Location: /admin/login.php');
    exit;
}

$user = $session->getUser();
if ($user['role'] !== 'admin') {
    $session->setFlash('error', 'You do not have permission to access the admin area.');
    header('Location: /');
    exit;
}

// Redirect to dashboard
header('Location: /admin/dashboard.php');
exit;