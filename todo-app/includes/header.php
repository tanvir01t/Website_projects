<?php
/**
 * includes/header.php
 * Shared page <head> + opening markup for every protected page.
 * Requires config/functions.php to already be loaded by the
 * including page (which should also call requireLogin()).
 *
 * Usage in a page:
 *   require_once __DIR__ . '/../config/database.php';
 *   require_once __DIR__ . '/../includes/functions.php';
 *   requireLogin();
 *   $pageTitle = 'Dashboard';
 *   $activeNav = 'dashboard';
 *   require __DIR__ . '/../includes/header.php';
 */

$pageTitle = $pageTitle ?? 'TaskFlow';
?>
<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($pageTitle) ?> · TaskFlow</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css">
    <?php if (!empty($extraStylesheet)): ?>
        <link rel="stylesheet" href="<?= e($extraStylesheet) ?>">
    <?php endif; ?>

    <script>
        // Base URL for AJAX/fetch calls in app.js / tasks.js — lets
        // the app run from any subfolder (e.g. /todo-app) unmodified.
        window.BASE_URL = '<?= BASE_URL ?>';

        // Apply saved theme immediately (before paint) to avoid a
        // light-mode flash when the user has dark mode on.
        (function () {
            const saved = localStorage.getItem('theme');
            if (saved === 'dark') {
                document.documentElement.setAttribute('data-theme', 'dark');
            }
        })();
    </script>
</head>
<body>
<div id="page-loader" class="page-loader"><div class="loader-ring"></div></div>

<div class="app-shell">
