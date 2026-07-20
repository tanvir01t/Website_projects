<?php
/**
 * auth/forgot-password.php
 * "Dummy" forgot-password flow as requested — collects an email
 * and shows a generic confirmation message without actually
 * sending an email (no mail server wired up). In a real deploy,
 * this would generate users.reset_token, email a signed link,
 * and a separate reset-password.php would consume that token.
 */
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

$submitted = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_protect();
    $email = strtolower(clean($_POST['email'] ?? ''));
    // Intentionally same response whether or not the email exists —
    // never reveal account existence via a password-reset form.
    $submitted = true;
}
?>
<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password · TaskFlow</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/auth.css">
</head>
<body class="auth-body">
    <div class="auth-card">
        <div class="auth-logo"><i class="fa-solid fa-key"></i> TaskFlow</div>

        <?php if ($submitted): ?>
            <p class="auth-subtitle" style="margin-bottom:24px;">
                If an account exists for that email, a password reset link has been sent.
                (Demo mode — no email is actually sent.)
            </p>
            <a href="login.php" class="btn-auth" style="text-decoration:none; display:flex;">Back to Login</a>
        <?php else: ?>
            <p class="auth-subtitle">Enter your email and we'll send you a reset link</p>
            <form method="POST" action="forgot-password.php">
                <?= csrf_field() ?>
                <div class="form-group">
                    <label for="email">Email Address</label>
                    <div class="input-wrap">
                        <i class="fa-solid fa-envelope field-icon"></i>
                        <input type="email" id="email" name="email" placeholder="you@example.com" required autocomplete="email">
                    </div>
                    <div class="field-error"></div>
                </div>
                <button type="submit" class="btn-auth">
                    <span class="btn-label" data-original="Send Reset Link">Send Reset Link</span>
                </button>
            </form>
        <?php endif; ?>

        <p class="auth-switch"><a href="login.php" style='color: white'>← Back to Login</a></p>
    </div>
</body>
</html>
