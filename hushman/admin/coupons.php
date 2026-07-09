<?php
// ============================================================
// admin/coupons.php — Coupons Management
// ============================================================
require_once __DIR__ . '/../includes/functions.php';
requireAdminLogin();
$db = getDB();
$errors = [];

// Add coupon
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_coupon'])) {
    $code     = strtoupper(trim($_POST['code'] ?? ''));
    $type     = $_POST['discount_type'] ?? 'percent';
    $value    = (float)($_POST['discount_value'] ?? 0);
    $minOrder = (float)($_POST['min_order'] ?? 0);
    $maxUses  = (int)($_POST['max_uses'] ?? 100);
    $expires  = $_POST['expires_at'] ?? null;

    if (empty($code))  $errors[] = 'Coupon code is required.';
    if ($value <= 0)   $errors[] = 'Discount value must be > 0.';

    if (empty($errors)) {
        try {
            $db->prepare("INSERT INTO coupons (code, discount_type, discount_value, min_order, max_uses, expires_at) VALUES (?,?,?,?,?,?)")
               ->execute([$code, $type, $value, $minOrder, $maxUses, $expires ?: null]);
            setFlash('success', 'Coupon created!');
            header('Location: coupons.php'); exit;
        } catch (Exception $e) {
            $errors[] = 'Coupon code already exists.';
        }
    }
}

// Toggle / Delete
if (isset($_GET['toggle']) && is_numeric($_GET['toggle'])) {
    $db->prepare("UPDATE coupons SET is_active = NOT is_active WHERE id = ?")->execute([(int)$_GET['toggle']]);
    header('Location: coupons.php'); exit;
}
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $db->prepare("DELETE FROM coupons WHERE id = ?")->execute([(int)$_GET['delete']]);
    setFlash('success', 'Coupon deleted.');
    header('Location: coupons.php'); exit;
}

$coupons = $db->query("SELECT * FROM coupons ORDER BY created_at DESC")->fetchAll();
$adminTitle = 'Coupons';
require_once __DIR__ . '/header.php';
?>

<div class="row g-4">
    <div class="col-lg-8">
        <div class="admin-table">
            <table class="table table-hover mb-0">
                <thead><tr><th>Code</th><th>Type</th><th>Value</th><th>Min Order</th><th>Used</th><th>Expires</th><th>Status</th><th>Actions</th></tr></thead>
                <tbody>
                <?php if(empty($coupons)): ?>
                <tr><td colspan="8" class="text-center py-5 text-muted">No coupons yet.</td></tr>
                <?php else: ?>
                <?php foreach($coupons as $c): ?>
                <tr>
                    <td><strong class="text-primary" style="font-family:monospace"><?= htmlspecialchars($c['code']) ?></strong></td>
                    <td><span class="badge <?= $c['discount_type']==='percent'?'bg-info text-dark':'bg-warning text-dark' ?>"><?= $c['discount_type'] ?></span></td>
                    <td class="fw-700"><?= $c['discount_type']==='percent' ? $c['discount_value'].'%' : formatPrice($c['discount_value']) ?></td>
                    <td><?= formatPrice($c['min_order']) ?></td>
                    <td><?= $c['used_count'] ?>/<?= $c['max_uses'] ?></td>
                    <td><small><?= $c['expires_at'] ? date('d M Y', strtotime($c['expires_at'])) : '—' ?></small></td>
                    <td><span class="badge <?= $c['is_active'] ? 'bg-success' : 'bg-secondary' ?>"><?= $c['is_active'] ? 'Active' : 'Inactive' ?></span></td>
                    <td>
                        <a href="coupons.php?toggle=<?= $c['id'] ?>" class="btn btn-sm btn-outline-<?= $c['is_active'] ? 'warning' : 'success' ?>"><i class="bi bi-toggle-<?= $c['is_active'] ? 'on' : 'off' ?>"></i></a>
                        <a href="coupons.php?delete=<?= $c['id'] ?>" class="btn btn-sm btn-outline-danger confirm-delete"><i class="bi bi-trash3"></i></a>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card-plain p-4">
            <h6 class="fw-700 mb-3">Create Coupon</h6>
            <?php if(!empty($errors)): ?><div class="alert alert-danger mb-3"><?php foreach($errors as $e) echo htmlspecialchars($e).'<br>'; ?></div><?php endif; ?>
            <form method="POST">
                <div class="mb-3"><label class="form-label">Coupon Code *</label><input type="text" name="code" class="form-control text-uppercase" required placeholder="e.g. SAVE20" style="letter-spacing:.1em"></div>
                <div class="mb-3">
                    <label class="form-label">Discount Type</label>
                    <select name="discount_type" class="form-select">
                        <option value="percent">Percentage (%)</option>
                        <option value="fixed">Fixed Amount (৳)</option>
                    </select>
                </div>
                <div class="mb-3"><label class="form-label">Discount Value *</label><input type="number" name="discount_value" class="form-control" step="0.01" min="0" required placeholder="e.g. 20"></div>
                <div class="mb-3"><label class="form-label">Minimum Order (৳)</label><input type="number" name="min_order" class="form-control" step="0.01" min="0" value="0"></div>
                <div class="mb-3"><label class="form-label">Max Uses</label><input type="number" name="max_uses" class="form-control" min="1" value="100"></div>
                <div class="mb-3"><label class="form-label">Expiry Date</label><input type="date" name="expires_at" class="form-control"></div>
                <button type="submit" name="add_coupon" class="btn btn-primary w-100">Create Coupon</button>
            </form>
        </div>
    </div>
</div>
<?php require_once __DIR__ . '/footer.php'; ?>
