<?php
/**
 * auth/logout.php
 * Destroys the session and clears any "Remember Me" token,
 * both server-side (DB) and client-side (cookie).
 */
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

if (isLoggedIn()) {
    // Clear the remember-me token in the database so the cookie
    // (if it leaked or persists somewhere) can no longer be used.
    if (!empty($_COOKIE['remember_token'])) {
        $pdo = getDB();
        $stmt = $pdo->prepare(
            'UPDATE users SET remember_token = NULL, remember_expires = NULL WHERE id = ?'
        );
        $stmt->execute([currentUserId()]);
    }

    logActivity(currentUserId(), null, 'logout', 'Logged out.');
}

// Clear the remember-me cookie
if (isset($_COOKIE['remember_token'])) {
    setcookie('remember_token', '', [
        'expires'  => time() - 3600,
        'path'     => '/',
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
}

// Fully destroy the session
$_SESSION = [];
if (ini_get('session.use_cookies')) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params['path'], $params['domain'], $params['secure'], $params['httponly']);
}
session_destroy();

session_start();
flash('info', 'You have been logged out.');
header('Location: ' . BASE_URL . '/auth/login.php');
exit;
