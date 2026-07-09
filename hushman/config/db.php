<?php
// ============================================================
// config/db.php — Database Connection
// ============================================================

define('DB_HOST', 'localhost');
define('DB_USER', 'root');         // Change to your MySQL username
define('DB_PASS', '');             // Change to your MySQL password
define('DB_NAME', 'hushman');
define('SITE_URL', 'http://localhost/hushman');
define('SITE_NAME', 'HushMan');
define('CURRENCY', '৳');
define('SHIPPING_COST', 60);

// Create PDO connection
function getDB() {
    static $pdo = null;
    if ($pdo === null) {
        try {
            $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4";
            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ];
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            die('<div style="font-family:sans-serif;padding:20px;background:#fee;border:1px solid red;margin:20px">
                <h3>Database Connection Failed</h3>
                <p>' . htmlspecialchars($e->getMessage()) . '</p>
                <p>Please check your database settings in <code>config/db.php</code></p>
            </div>');
        }
    }
    return $pdo;
}
?>
