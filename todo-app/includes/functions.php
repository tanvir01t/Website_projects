<?php
/**
 * includes/functions.php
 * -----------------------------------------------------------
 * Shared helper functions used across the whole application:
 *   - Session bootstrap
 *   - CSRF token generation/validation
 *   - Input sanitization (XSS protection)
 *   - Auth guard helpers
 *   - Flash message / toast helpers
 * -----------------------------------------------------------
 */

// Start the session once, with hardened cookie settings
if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => '/',
        'httponly' => true,     // JS cannot read the session cookie
        'samesite' => 'Lax',    // CSRF mitigation at the cookie level
        // 'secure' => true,     // uncomment when serving over HTTPS
    ]);
    session_start();
}

/* =============================================================
   CSRF PROTECTION
   ============================================================= */

/**
 * Generates (or reuses) a CSRF token for the current session
 * and returns it, ready to embed in a hidden form field.
 */
function csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Outputs a ready-to-use hidden <input> for forms.
 */
function csrf_field(): string
{
    return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') . '">';
}

/**
 * Validates a submitted CSRF token against the session token.
 * Uses hash_equals() to prevent timing attacks.
 */
function csrf_verify(?string $submittedToken): bool
{
    if (empty($_SESSION['csrf_token']) || empty($submittedToken)) {
        return false;
    }
    return hash_equals($_SESSION['csrf_token'], $submittedToken);
}

/**
 * Call at the top of every POST handler. Kills the request with
 * a 403 if the CSRF token is missing or invalid.
 */
function csrf_protect(): void
{
    $token = $_POST['csrf_token'] ?? '';
    if (!csrf_verify($token)) {
        http_response_code(403);
        die('Invalid or expired security token. Please refresh the page and try again.');
    }
}

/* =============================================================
   INPUT SANITIZATION (XSS PROTECTION)
   ============================================================= */

/**
 * Escapes a string for safe HTML output. Use this EVERY time
 * user-supplied data is echoed into HTML.
 */
function e(?string $value): string
{
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}

/**
 * Trims and strips tags from raw user input before it touches
 * business logic or the database. (Prepared statements handle
 * SQL injection separately — this is about normalizing input
 * and stripping stray HTML/script tags.)
 */
function clean(?string $value): string
{
    return trim(strip_tags($value ?? ''));
}

/* =============================================================
   AUTH GUARDS
   ============================================================= */

function isLoggedIn(): bool
{
    return !empty($_SESSION['user_id']);
}

/**
 * Redirects to the login page if the user isn't authenticated.
 * Call this at the top of every protected page.
 */
function requireLogin(): void
{
    if (!isLoggedIn()) {
        header('Location: ' . BASE_URL . '/auth/login.php');
        exit;
    }
}

function currentUserId(): ?int
{
    return $_SESSION['user_id'] ?? null;
}

/* =============================================================
   FLASH MESSAGES / TOASTS
   ============================================================= */

/**
 * Queues a one-time flash message (shown as a toast on next page
 * load, then cleared automatically).
 * $type: 'success' | 'error' | 'info' | 'warning'
 */
function flash(string $type, string $message): void
{
    $_SESSION['flash'][] = ['type' => $type, 'message' => $message];
}

/**
 * Retrieves and clears all queued flash messages.
 * Returns an array of ['type' => ..., 'message' => ...]
 */
function getFlashes(): array
{
    $flashes = $_SESSION['flash'] ?? [];
    unset($_SESSION['flash']);
    return $flashes;
}

/* =============================================================
   MISC HELPERS
   ============================================================= */

/**
 * Simple activity logger used by the "Recent Activity" widget.
 */
function logActivity(int $userId, ?int $taskId, string $action, string $description): void
{
    $pdo = getDB();
    $stmt = $pdo->prepare(
        'INSERT INTO activity_log (user_id, task_id, action, description) VALUES (?, ?, ?, ?)'
    );
    $stmt->execute([$userId, $taskId, $action, $description]);
}
