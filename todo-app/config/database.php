<?php
/**
 * config/database.php
 * -----------------------------------------------------------
 * Central database connection using PDO.
 * PDO with prepared statements is our primary defense against
 * SQL injection across the entire application.
 * -----------------------------------------------------------
 */

// ---- Database credentials -----------------------------------
// In a real production deploy, pull these from environment
// variables instead of hardcoding them.
define('DB_HOST', getenv('DB_HOST') ?: '127.0.0.1');
define('DB_NAME', getenv('DB_NAME') ?: 'todo_app');
define('DB_USER', getenv('DB_USER') ?: 'root');
define('DB_PASS', getenv('DB_PASS') ?: '');
define('DB_CHARSET', 'utf8mb4');

// ---- Base URL auto-detection -----------------------------------
// Works out how deep the project sits below the web server's
// document root (e.g. "" if placed directly in htdocs, or
// "/todo-app" if placed in htdocs/todo-app). This means every
// link, redirect, and asset path in the app works no matter what
// folder name you deploy it under — nothing to hand-edit.
if (!defined('BASE_URL')) {
    $projectRoot = realpath(__DIR__ . '/..');
    $docRoot     = realpath($_SERVER['DOCUMENT_ROOT'] ?? '');

    $baseUrl = '';
    if ($docRoot && $projectRoot && str_starts_with($projectRoot, $docRoot)) {
        $baseUrl = str_replace('\\', '/', substr($projectRoot, strlen($docRoot)));
    }
    define('BASE_URL', rtrim($baseUrl, '/'));
}

/**
 * getDB()
 * Returns a shared PDO instance (singleton pattern so we don't
 * open a new connection on every call).
 */
function getDB(): PDO
{
    static $pdo = null;

    if ($pdo === null) {
        $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=' . DB_CHARSET;

        $options = [
            // Throw exceptions on error instead of silent failure
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            // Return associative arrays by default
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            // Use REAL prepared statements (not emulated) — critical
            // for full SQL injection protection
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];

        try {
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            // Never leak raw DB errors (host, credentials) to the browser
            error_log('Database connection failed: ' . $e->getMessage());
            http_response_code(500);
            die('Something went wrong. Please try again later.');
        }
    }

    return $pdo;
}
