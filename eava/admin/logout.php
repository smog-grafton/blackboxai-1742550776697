<?php
require_once '../config/config.php';
require_once '../classes/User.php';

session_start();

$user = new User();
$user->logout();

header('Location: index.php');
exit;