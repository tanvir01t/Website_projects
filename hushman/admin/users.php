<?php
// ============================================================
// admin/users.php — Users Management
// ============================================================
require_once __DIR__ . '/../includes/functions.php';
requireAdminLogin();

$db = getDB();

// Toggle active
if (isset($_GET['toggle']) && is_numeric($_GET['toggle'])) {
    $db->prepare("UPDATE users SET is_active = NOT is_active WHERE id = ?")->execute([(int)$_GET['toggle']]);
    setFlash('success', 'User status updated.');
    header('Location: users.php'); exit;
}

$search = trim($_GET['search'] ?? '');
$page   = max(1, (int)($_GET['page'] ?? 1));
$perPage = 15; $offset = ($page-1) * $perPage;

$where  = $search ? "WHERE name LIKE ? OR email LIKE ? OR phone LIKE ?" : "";
$params = $search ? ["%$search%","%$search%","%$search%"] : [];

$total      = $db->prepare("SELECT COUNT(*) FROM users $where"); $total->execute($params);
$totalCount = $total->fetchColumn(); $totalPages = ceil($totalCount / $perPage);
$params[]   = $perPage; $params[] = $offset;

$users = $db->prepare("SELECT u.*, (SELECT COUNT(*) FROM orders WHERE user_id=u.id) as order_count, (SELECT COALESCE(SUM(final_amount),0) FROM orders WHERE user_id=u.id AND order_status='delivered') as total_spent FROM users u $where ORDER BY u.created_at DESC LIMIT ? OFFSET ?");
$users->execute($params); $users = $users->fetchAll();

$adminTitle = 'Users';
require_once __DIR__ . '/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <form method="GET" class="d-flex gap-2">
        <input type="text" name="search" class="form-control form-control-sm" placeholder="Search by name, email, phone..." value="<?= htmlspecialchars($search) ?>" style="width:260px">
        <button type="submit" class="btn btn-sm btn-primary"><i class="bi bi-search"></i></button>
        <?php if($search): ?><a href="users.php" class="btn btn-sm btn-outline-secondary">Clear</a><?php endif; ?>
    </form>
    <span class="text-muted small">Total: <?= $totalCount ?> users</span>
</div>

<div class="admin-table">
    <table class="table table-hover mb-0">
        <thead>
            <tr><th>User</th><th>Phone</th><th>Orders</th><th>Total Spent</th><th>Joined</th><th>Status</th><th>Actions</th></tr>
        </thead>
        <tbody>
            <?php if(empty($users)): ?>
            <tr><td colspan="7" class="text-center py-5 text-muted">No users found.</td></tr>
            <?php else: ?>
            <?php foreach($users as $user): ?>
            <tr>
                <td>
                    <div class="d-flex align-items-center gap-2">
                        <div style="width:36px;height:36px;border-radius:50%;background:var(--gold);display:flex;align-items:center;justify-content:center;color:#fff;font-weight:700;font-size:14px;flex-shrink:0">
                            <?= strtoupper(substr($user['name'],0,1)) ?>
                        </div>
                        <div>
                            <div class="fw-600" style="font-size:13.5px"><?= htmlspecialchars($user['name']) ?></div>
                            <div class="text-muted" style="font-size:11.5px"><?= htmlspecialchars($user['email']) ?></div>
                        </div>
                    </div>
                </td>
                <td><?= htmlspecialchars($user['phone'] ?? '—') ?></td>
                <td><span class="badge bg-light text-dark border"><?= $user['order_count'] ?> orders</span></td>
                <td class="fw-600"><?= formatPrice($user['total_spent']) ?></td>
                <td><small><?= date('d M Y', strtotime($user['created_at'])) ?></small></td>
                <td>
                    <span class="badge <?= $user['is_active'] ? 'bg-success' : 'bg-danger' ?>">
                        <?= $user['is_active'] ? 'Active' : 'Blocked' ?>
                    </span>
                </td>
                <td>
                    <a href="users.php?toggle=<?= $user['id'] ?>" class="btn btn-sm btn-outline-<?= $user['is_active'] ? 'danger' : 'success' ?>" title="<?= $user['is_active'] ? 'Block' : 'Activate' ?>">
                        <i class="bi bi-<?= $user['is_active'] ? 'person-x' : 'person-check' ?>"></i>
                    </a>
                </td>
            </tr>
            <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php if($totalPages > 1): ?>
<nav class="mt-4 d-flex justify-content-center">
    <ul class="pagination pagination-sm">
        <?php for($i=1;$i<=$totalPages;$i++): ?>
        <li class="page-item <?= $i===$page?'active':'' ?>"><a class="page-link" href="?page=<?=$i?>&search=<?=urlencode($search)?>"><?=$i?></a></li>
        <?php endfor; ?>
    </ul>
</nav>
<?php endif; ?>
<?php require_once __DIR__ . '/footer.php'; ?>
