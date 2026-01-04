<?php
/**
 * Hassan IDE - Logout
 */
define('HASSAN_IDE', true);
require_once __DIR__ . '/api/auth.php';

$auth = new Auth();
$auth->logout();

header('Location: ' . SITE_URL . '/');
exit;
