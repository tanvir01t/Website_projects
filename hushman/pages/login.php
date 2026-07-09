<?php
// ============================================================
// pages/login.php — User Login
// ============================================================
require_once __DIR__ . '/../includes/functions.php';
if (isLoggedIn()) { header('Location: ' . SITE_URL); exit; }

$error    = '';
$redirect = isset($_GET['redirect']) ? $_GET['redirect'] : SITE_URL;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        $error = 'Please fill in all fields.';
    } else {
        $db   = getDB();
        $stmt = $db->prepare("SELECT * FROM users WHERE email = ? AND is_active = 1");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id']   = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['user_email']= $user['email'];
            setFlash('success', 'Welcome back, ' . $user['name'] . '!');
            header('Location: ' . $redirect);
            exit;
        } else {
            $error = 'Invalid email or password.';
        }
    }
}
$pageTitle = 'Login';
?>
<?php require_once __DIR__ . '/../includes/header.php'; ?>
<div class="auth-wrap">
  <div class="container">
    <div class="auth-card mx-auto">
      <div class="text-center mb-4">
        <a href="<?= SITE_URL ?>" style="font-family:var(--ff-display);font-size:22px;font-weight:700;letter-spacing:.1em">HUSH<span style="color:var(--gold)">MAN</span></a>
      </div>
      <h2 class="auth-title text-center">Welcome Back</h2>
      <p class="auth-subtitle text-center">Sign in to your account to continue</p>

      <?php if($error): ?>
      <div class="alert alert-danger"><i class="bi bi-exclamation-circle me-2"></i><?= htmlspecialchars($error) ?></div>
      <?php endif; ?>

      <form method="POST">
        <div class="mb-3">
          <label class="form-label">Email Address</label>
          <input type="email" name="email" class="form-control" placeholder="you@example.com" value="<?= isset($_POST['email']) ? sanitize($_POST['email']) : '' ?>" required>
        </div>
        <div class="mb-3">
          <label class="form-label">Password</label>
          <div class="input-group">
            <input type="password" name="password" class="form-control" id="passwordField" placeholder="Your password" required>
            <button class="btn btn-outline-secondary" type="button" onclick="togglePass()"><i class="bi bi-eye" id="eyeIcon"></i></button>
          </div>
        </div>
        <div class="mb-4 d-flex justify-content-between align-items-center">
          <div class="form-check">
            <input type="checkbox" class="form-check-input" id="remember">
            <label class="form-check-label small" for="remember">Remember me</label>
          </div>
          <a href="#" class="text-muted small">Forgot password?</a>
        </div>
        <input type="hidden" name="redirect" value="<?= htmlspecialchars($redirect) ?>">
        <button type="submit" class="btn btn-primary w-100 btn-lg mb-3">Sign In</button>
        <p class="text-center text-muted small">Don't have an account? <a href="<?= SITE_URL ?>/pages/register.php" class="text-gold fw-500">Create one</a></p>
      </form>

      
    </div>
  </div>
</div>
<script>
function togglePass() {
  const f = document.getElementById('passwordField');
  const i = document.getElementById('eyeIcon');
  f.type = f.type === 'password' ? 'text' : 'password';
  i.className = f.type === 'password' ? 'bi bi-eye' : 'bi bi-eye-slash';
}
</script>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
