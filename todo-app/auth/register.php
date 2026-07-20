<?php
/**
 * auth/register.php
 * Handles both displaying the registration form (GET) and
 * processing the registration submission (POST).
 */
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

// Already logged in? Send to dashboard.
if (isLoggedIn()) {
    header('Location: ' . BASE_URL . '/dashboard/index.php');
    exit;
}

$errors = [];
$old = ['full_name' => '', 'email' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_protect();

    $fullName        = clean($_POST['full_name'] ?? '');
    $email           = strtolower(clean($_POST['email'] ?? ''));
    $password        = (string)($_POST['password'] ?? '');
    $confirmPassword = (string)($_POST['confirm_password'] ?? '');

    $old['full_name'] = $fullName;
    $old['email']     = $email;

    // ---- Server-side validation (never trust the client) ----
    if (mb_strlen($fullName) < 2) {
        $errors['full_name'] = 'Please enter your full name.';
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Please enter a valid email address.';
    }
    if (mb_strlen($password) < 8) {
        $errors['password'] = 'Password must be at least 8 characters.';
    }
    if ($password !== $confirmPassword) {
        $errors['confirm_password'] = 'Passwords do not match.';
    }

    // ---- Check for duplicate email (prepared statement) ----
    if (empty($errors)) {
        $pdo = getDB();
        $stmt = $pdo->prepare('SELECT id FROM users WHERE email = ? LIMIT 1');
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $errors['email'] = 'An account with this email already exists.';
        }
    }

    // ---- Create the account ----
    if (empty($errors)) {
        $passwordHash = password_hash($password, PASSWORD_BCRYPT);

        $stmt = $pdo->prepare(
            'INSERT INTO users (full_name, email, password_hash) VALUES (?, ?, ?)'
        );
        $stmt->execute([$fullName, $email, $passwordHash]);

        $userId = (int)$pdo->lastInsertId();

        // Log the user straight in after registering
        session_regenerate_id(true); // prevent session fixation
        $_SESSION['user_id']   = $userId;
        $_SESSION['user_name'] = $fullName;

        logActivity($userId, null, 'account_created', 'Welcome to the app! Your account was created.');

        flash('success', 'Account created successfully. Welcome aboard!');
        header('Location: ' . BASE_URL . '/dashboard/index.php');
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Account · TaskFlow</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/auth.css">
</head>
<body class="auth-body">

    <div class="auth-card">
        <div class="auth-logo"><i class="fa-solid fa-list-check"></i> TaskFlow</div>
        <p class="auth-subtitle">Create your account to get organized</p>

        <form id="registerForm" method="POST" action="register.php" novalidate>
            <?= csrf_field() ?>

            <div class="form-group">
                <label for="full_name">Full Name</label>
                <div class="input-wrap">
                    <i class="fa-solid fa-user field-icon"></i>
                    <input type="text" id="full_name" name="full_name" placeholder="Jane Doe"
                           value="<?= e($old['full_name']) ?>" autocomplete="name" required>
                </div>
                <?php if (isset($errors['full_name'])): ?>
                    <div class="field-error show"><?= e($errors['full_name']) ?></div>
                <?php else: ?>
                    <div class="field-error"></div>
                <?php endif; ?>
            </div>

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
                    <input type="password" id="password" name="password" placeholder="At least 8 characters"
                           autocomplete="new-password" required>
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

            <div class="form-group">
                <label for="confirm_password">Confirm Password</label>
                <div class="input-wrap">
                    <i class="fa-solid fa-lock field-icon"></i>
                    <input type="password" id="confirm_password" name="confirm_password" placeholder="Re-enter your password"
                           autocomplete="new-password" required>
                    <button type="button" class="toggle-password" onclick="togglePasswordField('confirm_password', this)">
                        <i class="fa-solid fa-eye"></i>
                    </button>
                </div>
                <?php if (isset($errors['confirm_password'])): ?>
                    <div class="field-error show"><?= e($errors['confirm_password']) ?></div>
                <?php else: ?>
                    <div class="field-error"></div>
                <?php endif; ?>
            </div>

            <button type="submit" class="btn-auth">
                <span class="btn-label" data-original="Create Account">Create Account</span>
                <span class="spinner"></span>
            </button>
        </form>

        <p class="auth-switch">Already have an account? <a href="login.php" style='color: white;'>Log in</a></p>
    </div>

    <script src="../assets/js/auth.js"></script>
</body>
</html>
