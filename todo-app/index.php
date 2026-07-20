<?php
/**
 * index.php (project root)
 * Entry point — sends logged-in users to the dashboard and
 * everyone else to the login page.
 */
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';

header('Location: ' . BASE_URL . (isLoggedIn() ? '/dashboard/index.php' : '/auth/login.php'));
exit;
