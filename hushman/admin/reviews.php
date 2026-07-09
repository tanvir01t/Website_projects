<?php
// ============================================================
// admin/reviews.php — Reviews Management
// ============================================================
require_once __DIR__ . '/../includes/functions.php';
requireAdminLogin();
$db = getDB();

// Approve / Delete
if (isset($_GET['approve']) && is_numeric($_GET['approve'])) {
    $db->prepare("UPDATE reviews SET is_approved = NOT is_approved WHERE id = ?")->execute([(int)$_GET['approve']]);
    header('Location: reviews.php'); exit;
}
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $db->prepare("DELETE FROM reviews WHERE id = ?")->execute([(int)$_GET['delete']]);
    setFlash('success', 'Review deleted.');
    header('Location: reviews.php'); exit;
}

$reviews = $db->query("
    SELECT r.*, u.name as user_name, p.name as product_name, p.slug as product_slug
    FROM reviews r JOIN users u ON r.user_id = u.id JOIN products p ON r.product_id = p.id
    ORDER BY r.created_at DESC
")->fetchAll();

$adminTitle = 'Reviews';
require_once __DIR__ . '/header.php';
?>

<div class="admin-table">
    <table class="table table-hover mb-0">
        <thead><tr><th>User</th><th>Product</th><th>Rating</th><th>Comment</th><th>Date</th><th>Status</th><th>Actions</th></tr></thead>
        <tbody>
        <?php if(empty($reviews)): ?>
        <tr><td colspan="7" class="text-center py-5 text-muted">No reviews yet.</td></tr>
        <?php else: ?>
        <?php foreach($reviews as $r): ?>
        <tr>
            <td class="fw-600" style="font-size:13px"><?= htmlspecialchars($r['user_name']) ?></td>
            <td style="font-size:12px"><a href="<?= SITE_URL ?>/pages/product.php?slug=<?= $r['product_slug'] ?>" target="_blank"><?= htmlspecialchars(substr($r['product_name'],0,30)) ?>...</a></td>
            <td><span style="color:var(--gold)"><?= str_repeat('★', $r['rating']) ?><?= str_repeat('☆', 5-$r['rating']) ?></span></td>
            <td style="font-size:12.5px;max-width:200px"><?= htmlspecialchars(substr($r['comment'],0,80)) ?>...</td>
            <td><small><?= date('d M Y', strtotime($r['created_at'])) ?></small></td>
            <td><span class="badge <?= $r['is_approved'] ? 'bg-success' : 'bg-warning text-dark' ?>"><?= $r['is_approved'] ? 'Approved' : 'Pending' ?></span></td>
            <td>
                <a href="reviews.php?approve=<?= $r['id'] ?>" class="btn btn-sm btn-outline-<?= $r['is_approved'] ? 'warning' : 'success' ?>" title="<?= $r['is_approved'] ? 'Unapprove' : 'Approve' ?>">
                    <i class="bi bi-<?= $r['is_approved'] ? 'eye-slash' : 'check' ?>"></i>
                </a>
                <a href="reviews.php?delete=<?= $r['id'] ?>" class="btn btn-sm btn-outline-danger confirm-delete"><i class="bi bi-trash3"></i></a>
            </td>
        </tr>
        <?php endforeach; ?>
        <?php endif; ?>
        </tbody>
    </table>
</div>
<?php require_once __DIR__ . '/footer.php'; ?>
