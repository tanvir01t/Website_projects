<?php
// ============================================================
// pages/register.php — User Registration
// ============================================================
require_once __DIR__ . '/../includes/functions.php';
if (isLoggedIn()) { header('Location: ' . SITE_URL); exit; }

$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name     = trim($_POST['name'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $phone    = trim($_POST['phone'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm  = $_POST['confirm_password'] ?? '';

    // Validation
    if (strlen($name) < 3)             $errors[] = 'Name must be at least 3 characters.';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Please enter a valid email address.';
    if (strlen($password) < 8)         $errors[] = 'Password must be at least 8 characters.';
    if ($password !== $confirm)        $errors[] = 'Passwords do not match.';
    if (empty($phone))                 $errors[] = 'Phone number is required.';

    if (empty($errors)) {
        $db   = getDB();
        $stmt = $db->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $errors[] = 'This email is already registered. Please login instead.';
        } else {
            $hashed = password_hash($password, PASSWORD_DEFAULT);
            $ins    = $db->prepare("INSERT INTO users (name, email, phone, password) VALUES (?, ?, ?, ?)");
            $ins->execute([$name, $email, $phone, $hashed]);
            $userId = $db->lastInsertId();

            $_SESSION['user_id']    = $userId;
            $_SESSION['user_name']  = $name;
            $_SESSION['user_email'] = $email;
            setFlash('success', 'Welcome to HushMan, ' . $name . '! Your account has been created.');
            header('Location: ' . SITE_URL);
            exit;
        }
    }
}
$pageTitle = 'Create Account';
?>
<?php require_once __DIR__ . '/../includes/header.php'; ?>
<div class="auth-wrap">
  <div class="container">
    <div class="auth-card mx-auto">
      <div class="text-center mb-4">
        <a href="<?= SITE_URL ?>" style="font-family:var(--ff-display);font-size:22px;font-weight:700;letter-spacing:.1em">HUSH<span style="color:var(--gold)">MAN</span></a>
      </div>
      <h2 class="auth-title text-center">Create Account</h2>
      <p class="auth-subtitle text-center">Join HushMan and enjoy exclusive member benefits</p>

      <?php if(!empty($errors)): ?>
      <div class="alert alert-danger">
        <?php foreach($errors as $e): ?><div><i class="bi bi-x-circle me-2"></i><?= htmlspecialchars($e) ?></div><?php endforeach; ?>
      </div>
      <?php endif; ?>

      <form method="POST" novalidate>
        <div class="mb-3">
          <label class="form-label">Full Name *</label>
          <input type="text" name="name" class="form-control" placeholder="Your full name" value="<?= isset($_POST['name']) ? sanitize($_POST['name']) : '' ?>" required>
        </div>
        <div class="mb-3">
          <label class="form-label">Email Address *</label>
          <input type="email" name="email" class="form-control" placeholder="you@example.com" value="<?= isset($_POST['email']) ? sanitize($_POST['email']) : '' ?>" required>
        </div>
        <div class="mb-3">
          <label class="form-label">Phone Number *</label>
          <input type="tel" name="phone" class="form-control" placeholder="e.g. 01711234567" value="<?= isset($_POST['phone']) ? sanitize($_POST['phone']) : '' ?>" required>
        </div>
        <div class="mb-3">
          <label class="form-label">Password * <small class="text-muted">(min 8 characters)</small></label>
          <div class="input-group">
            <input type="password" name="password" class="form-control" id="passField" placeholder="Create a strong password" required>
            <button class="btn btn-outline-secondary" type="button" onclick="togglePass()"><i class="bi bi-eye" id="eyeIcon"></i></button>
          </div>
        </div>
        <div class="mb-4">
          <label class="form-label">Confirm Password *</label>
          <input type="password" name="confirm_password" class="form-control" placeholder="Repeat your password" required>
        </div>
        <div class="mb-4">
          <div class="form-check">
            <input type="checkbox" class="form-check-input" id="terms" required>
            <label class="form-check-label small" for="terms">I agree to the <a href="#" class="text-gold">Terms of Service</a> and <a href="#" class="text-gold">Privacy Policy</a></label>
          </div>
        </div>
        <button type="submit" class="btn btn-primary w-100 btn-lg mb-3">Create Account</button>
        <p class="text-center text-muted small">Already have an account? <a href="<?= SITE_URL ?>/pages/login.php" class="text-gold fw-500">Sign in</a></p>
      </form>
    </div>
  </div>
</div>
<script>
function togglePass() {
  const f = document.getElementById('passField');
  const i = document.getElementById('eyeIcon');
  f.type = f.type === 'password' ? 'text' : 'password';
  i.className = f.type === 'password' ? 'bi bi-eye' : 'bi bi-eye-slash';
}
</script>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
