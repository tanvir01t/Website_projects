<?php
// ============================================================
// pages/profile.php — User Profile
// ============================================================
require_once __DIR__ . '/../includes/functions.php';
requireLogin();

$user   = getCurrentUser();
$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name    = trim($_POST['name'] ?? '');
    $phone   = trim($_POST['phone'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $city    = trim($_POST['city'] ?? '');
    $newPass = $_POST['new_password'] ?? '';
    $curPass = $_POST['current_password'] ?? '';

    if (strlen($name) < 3) $errors[] = 'Name must be at least 3 characters.';

    if (!empty($newPass)) {
        if (!password_verify($curPass, $user['password'])) $errors[] = 'Current password is incorrect.';
        elseif (strlen($newPass) < 8) $errors[] = 'New password must be at least 8 characters.';
    }

    if (empty($errors)) {
        $db = getDB();
        if (!empty($newPass)) {
            $hashed = password_hash($newPass, PASSWORD_DEFAULT);
            $db->prepare("UPDATE users SET name=?, phone=?, address=?, city=?, password=? WHERE id=?")->execute([$name, $phone, $address, $city, $hashed, $_SESSION['user_id']]);
        } else {
            $db->prepare("UPDATE users SET name=?, phone=?, address=?, city=? WHERE id=?")->execute([$name, $phone, $address, $city, $_SESSION['user_id']]);
        }
        $_SESSION['user_name'] = $name;
        $success = true;
        $user = getCurrentUser();
        setFlash('success', 'Profile updated successfully!');
    }
}

$orders = getUserOrders($_SESSION['user_id']);
$pageTitle = 'My Profile';
?>
<?php require_once __DIR__ . '/../includes/header.php'; ?>

<div class="page-breadcrumb">
  <div class="container">
    <nav><ol class="breadcrumb">
      <li class="breadcrumb-item"><a href="<?= SITE_URL ?>">Home</a></li>
      <li class="breadcrumb-item active">My Profile</li>
    </ol></nav>
  </div>
</div>

<div class="container my-5">
  <div class="row g-4">
    <!-- Sidebar -->
    <div class="col-lg-3">
      <div class="card-plain p-4 text-center mb-3">
        <div style="width:72px;height:72px;background:var(--gold);border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 12px;font-family:var(--ff-display);font-size:30px;color:white;font-weight:700">
          <?= strtoupper(substr($user['name'], 0, 1)) ?>
        </div>
        <h5 style="font-family:var(--ff-display)"><?= sanitize($user['name']) ?></h5>
        <p class="text-muted small mb-0"><?= sanitize($user['email']) ?></p>
      </div>
      <div class="card-plain">
        <a href="<?= SITE_URL ?>/pages/profile.php" class="admin-nav-link active" style="border-left-color:var(--gold)"><i class="bi bi-person"></i> My Profile</a>
        <a href="<?= SITE_URL ?>/pages/orders.php" class="admin-nav-link" style="color:var(--mid)"><i class="bi bi-bag"></i> My Orders (<?= count($orders) ?>)</a>
        <a href="<?= SITE_URL ?>/pages/logout.php" class="admin-nav-link" style="color:var(--mid)"><i class="bi bi-box-arrow-right"></i> Logout</a>
      </div>
    </div>

    <!-- Profile Form -->
    <div class="col-lg-9">
      <?php if($errors): ?>
      <div class="alert alert-danger mb-4"><?php foreach($errors as $e): ?><div><?= htmlspecialchars($e) ?></div><?php endforeach; ?></div>
      <?php endif; ?>
      <div class="card-plain p-4 mb-4">
        <h5 style="font-family:var(--ff-display);font-size:22px;margin-bottom:20px">Personal Information</h5>
        <form method="POST">
          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label">Full Name</label>
              <input type="text" name="name" class="form-control" value="<?= sanitize($user['name']) ?>" required>
            </div>
            <div class="col-md-6">
              <label class="form-label">Email Address</label>
              <input type="email" class="form-control" value="<?= sanitize($user['email']) ?>" disabled>
              <div class="form-text">Email cannot be changed</div>
            </div>
            <div class="col-md-6">
              <label class="form-label">Phone Number</label>
              <input type="tel" name="phone" class="form-control" value="<?= sanitize($user['phone'] ?? '') ?>">
            </div>
            <div class="col-md-6">
              <label class="form-label">City</label>
              <input type="text" name="city" class="form-control" value="<?= sanitize($user['city'] ?? '') ?>">
            </div>
            <div class="col-12">
              <label class="form-label">Delivery Address</label>
              <textarea name="address" class="form-control" rows="2"><?= sanitize($user['address'] ?? '') ?></textarea>
            </div>
          </div>
          <hr class="my-4">
          <h6 class="fw-700 mb-3">Change Password <small class="text-muted fw-normal">(leave blank to keep current)</small></h6>
          <div class="row g-3">
            <div class="col-md-4">
              <label class="form-label">Current Password</label>
              <input type="password" name="current_password" class="form-control">
            </div>
            <div class="col-md-4">
              <label class="form-label">New Password</label>
              <input type="password" name="new_password" class="form-control" minlength="8">
            </div>
          </div>
          <div class="mt-4">
            <button type="submit" class="btn btn-primary">Save Changes</button>
          </div>
        </form>
      </div>

      <!-- Recent Orders -->
      <div class="card-plain p-4">
        <div class="d-flex justify-content-between align-items-center mb-3">
          <h5 style="font-family:var(--ff-display);font-size:20px;margin:0">Recent Orders</h5>
          <a href="<?= SITE_URL ?>/pages/orders.php" class="btn btn-outline-dark btn-sm">View All</a>
        </div>
        <?php if(empty($orders)): ?>
        <p class="text-muted">No orders yet.</p>
        <?php else: ?>
        <?php foreach(array_slice($orders, 0, 3) as $ord): ?>
        <div class="d-flex justify-content-between align-items-center py-2" style="border-bottom:1px solid var(--border)">
          <div>
            <div class="fw-600" style="font-size:14px"><?= sanitize($ord['order_number']) ?></div>
            <div class="text-muted small"><?= formatDate($ord['created_at']) ?></div>
          </div>
          <span class="status-badge status-<?= $ord['order_status'] ?>"><?= ucfirst($ord['order_status']) ?></span>
          <div class="fw-700"><?= formatPrice($ord['final_amount']) ?></div>
          <a href="<?= SITE_URL ?>/pages/orders.php?id=<?= $ord['id'] ?>" class="btn btn-sm btn-outline-dark">View</a>
        </div>
        <?php endforeach; ?>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
