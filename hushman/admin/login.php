<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login — HushMan</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@600;700&family=DM+Sans:wght@400;500;600&display=swap" rel="stylesheet">
    <link href="../assets/css/style.css" rel="stylesheet">
    <style>
        body { background: #0d0d0d; min-height: 100vh; display: flex; align-items: center; justify-content: center; }
        .admin-login-card {
            background: #1a1a1a;
            border: 1px solid #2a2a2a;
            border-radius: 12px;
            padding: 48px 40px;
            width: 100%;
            max-width: 420px;
            box-shadow: 0 20px 60px rgba(0,0,0,.5);
        }
        .admin-login-card .form-control {
            background: #0d0d0d;
            border-color: #333;
            color: #fff;
        }
        .admin-login-card .form-control:focus {
            background: #0d0d0d;
            border-color: var(--gold);
            color: #fff;
        }
        .admin-login-card .form-label { color: rgba(255,255,255,.6); }
        .admin-login-card .form-control::placeholder { color: #555; }
    </style>
</head>
<body>
<?php
require_once __DIR__ . '/../includes/functions.php';
if (isAdminLoggedIn()) { header('Location: dashboard.php'); exit; }

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($email && $password) {
        $db   = getDB();
        $stmt = $db->prepare("SELECT * FROM admin WHERE email = ?");
        $stmt->execute([$email]);
        $admin = $stmt->fetch();

        if ($admin && password_verify($password, $admin['password'])) {
            $_SESSION['admin_id']   = $admin['id'];
            $_SESSION['admin_name'] = $admin['name'];
            $_SESSION['admin_role'] = $admin['role'];
            header('Location: dashboard.php');
            exit;
        } else {
            $error = 'Invalid admin credentials.';
        }
    } else {
        $error = 'Please fill in all fields.';
    }
}
?>
<div class="admin-login-card">
    <div class="text-center mb-4">
        <div style="font-family:'Cormorant Garamond',serif;font-size:26px;font-weight:700;letter-spacing:.12em;color:#fff">
            HUSH<span style="color:var(--gold)">MAN</span>
        </div>
        <div class="text-muted mt-1" style="font-size:13px">Admin Panel</div>
    </div>
    <h5 class="text-white mb-4" style="font-family:'Cormorant Garamond',serif;font-size:22px">Sign In</h5>

    <?php if($error): ?>
    <div class="alert alert-danger" style="font-size:13px"><i class="bi bi-exclamation-circle me-2"></i><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="POST">
        <div class="mb-3">
            <label class="form-label">Email Address</label>
            <input type="email" name="email" class="form-control" placeholder="enter admin email" value="<?= isset($_POST['email']) ? htmlspecialchars($_POST['email']) : '' ?>" required>
        </div>
        <div class="mb-4">
            <label class="form-label">Password</label>
            <input type="password" name="password" class="form-control" placeholder="Your password" required>
        </div>
        <button type="submit" class="btn btn-gold w-100">Sign In to Admin</button>
        <div class="text-center mt-3">
            <a href="<?= SITE_URL ?>" style="font-size:12px ; color : white;" ><i class="bi bi-arrow-left me-1"></i>Back to Store</a>
        </div>
    </form>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
