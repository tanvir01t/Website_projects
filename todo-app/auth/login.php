<?php
/**
 * auth/login.php
 * Handles both displaying the login form (GET) and processing
 * the login submission (POST), including "Remember Me".
 */
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

if (isLoggedIn()) {
    header('Location: ' . BASE_URL . '/dashboard/index.php');
    exit;
}

$errors = [];
$old = ['email' => ''];

// ---- Auto-login via "Remember Me" cookie, if present ----
if (!isLoggedIn() && !empty($_COOKIE['remember_token'])) {
    $pdo = getDB();
    $stmt = $pdo->prepare(
        'SELECT id, full_name FROM users
         WHERE remember_token = ? AND remember_expires > NOW() LIMIT 1'
    );
    $stmt->execute([$_COOKIE['remember_token']]);
    $user = $stmt->fetch();

    if ($user) {
        session_regenerate_id(true);
        $_SESSION['user_id']   = $user['id'];
        $_SESSION['user_name'] = $user['full_name'];
        header('Location: ' . BASE_URL . '/dashboard/index.php');
        exit;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_protect();

    $email        = strtolower(clean($_POST['email'] ?? ''));
    $password     = (string)($_POST['password'] ?? '');
    $rememberMe   = isset($_POST['remember_me']);

    $old['email'] = $email;

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Please enter a valid email address.';
    }
    if ($password === '') {
        $errors['password'] = 'Please enter your password.';
    }

    if (empty($errors)) {
        $pdo = getDB();
        $stmt = $pdo->prepare('SELECT id, full_name, password_hash FROM users WHERE email = ? LIMIT 1');
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        // Generic error message on purpose — don't reveal whether
        // the email exists or the password was wrong (prevents
        // account enumeration).
        if (!$user || !password_verify($password, $user['password_hash'])) {
            $errors['general'] = 'Incorrect email or password.';
        } else {
            session_regenerate_id(true); // prevent session fixation
            $_SESSION['user_id']   = $user['id'];
            $_SESSION['user_name'] = $user['full_name'];

            if ($rememberMe) {
                $token   = bin2hex(random_bytes(32));
                $expires = date('Y-m-d H:i:s', strtotime('+30 days'));

                $upd = $pdo->prepare(
                    'UPDATE users SET remember_token = ?, remember_expires = ? WHERE id = ?'
                );
                $upd->execute([$token, $expires, $user['id']]);

                setcookie('remember_token', $token, [
                    'expires'  => time() + (30 * 24 * 60 * 60),
                    'path'     => '/',
                    'httponly' => true,
                    'samesite' => 'Lax',
                ]);
            }

            logActivity($user['id'], null, 'login', 'Logged in.');

            flash('success', 'Welcome back, ' . $user['full_name'] . '!');
            header('Location: ' . BASE_URL . '/dashboard/index.php');
            exit;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Log In · TaskFlow</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/auth.css">
</head>
<body class="auth-body">

    <div class="auth-card">
        <div class="auth-logo"><i class="fa-solid fa-list-check"></i> TaskFlow</div>
        <p class="auth-subtitle">Welcome back! Log in to continue</p>

        <?php if (isset($errors['general'])): ?>
            <div class="field-error show" style="text-align:center; margin-bottom:16px;">
                <i class="fa-solid fa-circle-exclamation"></i> <?= e($errors['general']) ?>
            </div>
        <?php endif; ?>

        <form id="loginForm" method="POST" action="login.php" novalidate>
            <?= csrf_field() ?>

            <div class="form-group">
                <label for="email">Email Address</label>
                <div class="input-wrap">
                    <i class="fa-solid fa-envelope field-icon"></i>
                    <input type="email" id="email" name="email" placeholder="you@example.com"
                           value="<?= e($old['email']) ?>" autocomplete="email" required>
                </div>
                <?php if (isset($errors['email'])): ?>
                    <div class="field-error show"><?= e($errors['email']) ?></div>
                <?php else: ?>
                    <div class="field-error"></div>
                <?php endif; ?>
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <div class="input-wrap">
                    <i class="fa-solid fa-lock field-icon"></i>
                    <input type="password" id="password" name="password" placeholder="Your password"
                           autocomplete="current-password" required>
                    <button type="button" class="toggle-password" onclick="togglePasswordField('password', this)">
                        <i class="fa-solid fa-eye"></i>
                    </button>
                </div>
                <?php if (isset($errors['password'])): ?>
                    <div class="field-error show"><?= e($errors['password']) ?></div>
                <?php else: ?>
                    <div class="field-error"></div>
                <?php endif; ?>
            </div>

            <div class="form-row-between">
                <label class="remember-check">
                    <input type="checkbox" name="remember_me" id="remember_me">
                    Remember me
                </label>
                <a href="forgot-password.php">Forgot password?</a>
            </div>

            <button type="submit" class="btn-auth">
                <span class="btn-label" data-original="Log In">Log In</span>
                <span class="spinner"></span>
            </button>
        </form>

        <p class="auth-switch">Don't have an account? <a href="register.php" style='color: white;'>Sign up</a></p>
    </div>

    <script src="../assets/js/auth.js"></script>
</body>
</html>
