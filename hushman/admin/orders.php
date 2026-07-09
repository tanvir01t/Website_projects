<?php
// ============================================================
// admin/orders.php — Orders Management
// ============================================================
require_once __DIR__ . '/../includes/functions.php';
requireAdminLogin();

$db = getDB();

// Handle status update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $orderId        = (int)$_POST['order_id'];
    $newStatus      = $_POST['order_status'];
    $paymentStatus  = $_POST['payment_status'] ?? null;

    $validStatuses = ['pending','processing','shipped','delivered','cancelled'];
    if (in_array($newStatus, $validStatuses)) {
        $db->prepare("UPDATE orders SET order_status = ?, updated_at = NOW() WHERE id = ?")->execute([$newStatus, $orderId]);
    }
    if ($paymentStatus && in_array($paymentStatus, ['pending','paid','failed'])) {
        $db->prepare("UPDATE orders SET payment_status = ? WHERE id = ?")->execute([$paymentStatus, $orderId]);
    }
    setFlash('success', 'Order status updated.');
    header('Location: orders.php'); exit;
}

// Filter
$statusFilter = trim($_GET['status'] ?? '');
$searchFilter = trim($_GET['search'] ?? '');
$page         = max(1, (int)($_GET['page'] ?? 1));
$perPage      = 15;
$offset       = ($page - 1) * $perPage;

// Single order detail view
$detailId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($detailId) {
    $order = getOrderDetails($detailId);
    if (!$order) { setFlash('error', 'Order not found.'); header('Location: orders.php'); exit; }
    $adminTitle = 'Order ' . $order['order_number'];
    require_once __DIR__ . '/header.php';
    ?>
    <div class="d-flex justify-content-between align-items-center mb-4">
        <a href="orders.php" class="btn btn-outline-dark btn-sm"><i class="bi bi-arrow-left me-2"></i>All Orders</a>
        <span class="status-badge status-<?= $order['order_status'] ?>"><?= ucfirst($order['order_status']) ?></span>
    </div>

    <div class="row g-4">
        <div class="col-lg-8">
            <!-- Order Items -->
            <div class="card-plain p-4 mb-4">
                <h6 class="fw-700 mb-3">Order Items</h6>
                <?php foreach($order['items'] as $item): ?>
                <div class="d-flex gap-3 align-items-center mb-3 pb-3" style="border-bottom:1px solid var(--border)">
                    <img src="<?= SITE_URL ?>/assets/images/products/<?= htmlspecialchars($item['product_image']) ?>" style="width:54px;height:66px;object-fit:cover;border-radius:4px" onerror="this.src='<?= SITE_URL ?>/assets/images/placeholder.jpg'">
                    <div class="flex-grow-1">
                        <div class="fw-600"><?= htmlspecialchars($item['product_name']) ?></div>
                        <div class="text-muted small"><?= $item['size'] ? 'Size: '.$item['size'].' · ' : '' ?><?= $item['color'] ? 'Color: '.$item['color'] : '' ?></div>
                        <div class="text-muted small">Qty: <?= $item['quantity'] ?> × <?= formatPrice($item['unit_price']) ?></div>
                    </div>
                    <div class="fw-700"><?= formatPrice($item['subtotal']) ?></div>
                </div>
                <?php endforeach; ?>
                <div class="row g-1">
                    <div class="col-6 text-muted">Subtotal:</div><div class="col-6 text-end fw-600"><?= formatPrice($order['total_amount']) ?></div>
                    <?php if($order['discount_amount'] > 0): ?>
                    <div class="col-6 text-muted">Discount:</div><div class="col-6 text-end text-danger">-<?= formatPrice($order['discount_amount']) ?></div>
                    <?php endif; ?>
                    <div class="col-6 text-muted">Shipping:</div><div class="col-6 text-end"><?= $order['shipping_cost'] == 0 ? '<span class="text-success">Free</span>' : formatPrice($order['shipping_cost']) ?></div>
                    <div class="col-6 fw-700 pt-2" style="border-top:1px solid var(--border)">Total:</div><div class="col-6 text-end fw-700 pt-2" style="border-top:1px solid var(--border)"><?= formatPrice($order['final_amount']) ?></div>
                </div>
            </div>
            <!-- Customer Info -->
            <div class="card-plain p-4">
                <h6 class="fw-700 mb-3">Delivery Details</h6>
                <div class="row g-2" style="font-size:14px">
                    <div class="col-4 text-muted">Name</div><div class="col-8"><?= htmlspecialchars($order['full_name']) ?></div>
                    <div class="col-4 text-muted">Email</div><div class="col-8"><?= htmlspecialchars($order['email']) ?></div>
                    <div class="col-4 text-muted">Phone</div><div class="col-8"><?= htmlspecialchars($order['phone']) ?></div>
                    <div class="col-4 text-muted">Address</div><div class="col-8"><?= htmlspecialchars($order['address']) ?>, <?= htmlspecialchars($order['city']) ?><?= $order['postal_code'] ? ' - '.$order['postal_code'] : '' ?></div>
                    <?php if($order['notes']): ?><div class="col-4 text-muted">Notes</div><div class="col-8"><?= htmlspecialchars($order['notes']) ?></div><?php endif; ?>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <!-- Status Update -->
            <div class="card-plain p-4 mb-4">
                <h6 class="fw-700 mb-3">Update Status</h6>
                <form method="POST">
                    <input type="hidden" name="order_id" value="<?= $order['id'] ?>">
                    <div class="mb-3">
                        <label class="form-label">Order Status</label>
                        <select name="order_status" class="form-select">
                            <?php foreach(['pending','processing','shipped','delivered','cancelled'] as $s): ?>
                            <option value="<?= $s ?>" <?= $order['order_status'] === $s ? 'selected' : '' ?>><?= ucfirst($s) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Payment Status</label>
                        <select name="payment_status" class="form-select">
                            <?php foreach(['pending','paid','failed'] as $s): ?>
                            <option value="<?= $s ?>" <?= $order['payment_status'] === $s ? 'selected' : '' ?>><?= ucfirst($s) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <button type="submit" name="update_status" class="btn btn-primary w-100">Update Order</button>
                </form>
            </div>
            <div class="card-plain p-4">
                <h6 class="fw-700 mb-3">Order Info</h6>
                <div style="font-size:13px">
                    <div class="mb-2"><span class="text-muted">Order #:</span> <strong><?= htmlspecialchars($order['order_number']) ?></strong></div>
                    <div class="mb-2"><span class="text-muted">Date:</span> <?= formatDate($order['created_at']) ?></div>
                    <div class="mb-2"><span class="text-muted">Payment:</span> <span class="text-capitalize"><?= $order['payment_method'] ?></span></div>
                    <?php if($order['coupon_code']): ?><div class="mb-2"><span class="text-muted">Coupon:</span> <?= htmlspecialchars($order['coupon_code']) ?></div><?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    <?php
    require_once __DIR__ . '/footer.php';
    exit;
}

// Orders list
$where  = "WHERE 1=1";
$params = [];
if ($statusFilter) { $where .= " AND o.order_status = ?"; $params[] = $statusFilter; }
if ($searchFilter) { $where .= " AND (o.order_number LIKE ? OR o.full_name LIKE ? OR o.email LIKE ?)"; $params = array_merge($params, ["%$searchFilter%","%$searchFilter%","%$searchFilter%"]); }

$total      = $db->prepare("SELECT COUNT(*) FROM orders o $where"); $total->execute($params);
$totalCount = $total->fetchColumn(); $totalPages = ceil($totalCount / $perPage);
$params[]   = $perPage; $params[] = $offset;

$orders = $db->prepare("SELECT o.*, u.name as user_name FROM orders o JOIN users u ON o.user_id = u.id $where ORDER BY o.created_at DESC LIMIT ? OFFSET ?");
$orders->execute($params); $orders = $orders->fetchAll();

$adminTitle = 'Orders';
require_once __DIR__ . '/header.php';
?>

<!-- Status tabs -->
<div class="d-flex gap-2 mb-4 flex-wrap">
    <?php
    $statuses = [''=>'All','pending'=>'Pending','processing'=>'Processing','shipped'=>'Shipped','delivered'=>'Delivered','cancelled'=>'Cancelled'];
    foreach($statuses as $val => $label):
        $count = $db->prepare("SELECT COUNT(*) FROM orders" . ($val ? " WHERE order_status = ?" : ""));
        $count->execute($val ? [$val] : []);
    ?>
    <a href="orders.php<?= $val ? '?status='.$val : '' ?>" class="btn btn-sm <?= $statusFilter === $val ? 'btn-primary' : 'btn-outline-secondary' ?>">
        <?= $label ?> <span class="badge bg-light text-dark ms-1"><?= $count->fetchColumn() ?></span>
    </a>
    <?php endforeach; ?>
    <!-- Search -->
    <form method="GET" class="ms-auto d-flex gap-2">
        <?php if($statusFilter): ?><input type="hidden" name="status" value="<?= htmlspecialchars($statusFilter) ?>"><?php endif; ?>
        <input type="text" name="search" class="form-control form-control-sm" placeholder="Search order/name/email..." value="<?= htmlspecialchars($searchFilter) ?>" style="width:220px">
        <button class="btn btn-sm btn-primary"><i class="bi bi-search"></i></button>
    </form>
</div>

<div class="admin-table">
    <table class="table table-hover mb-0">
        <thead>
            <tr><th>Order #</th><th>Customer</th><th>Date</th><th>Amount</th><th>Payment</th><th>Status</th><th>Action</th></tr>
        </thead>
        <tbody>
            <?php if(empty($orders)): ?>
            <tr><td colspan="7" class="text-center py-5 text-muted">No orders found.</td></tr>
            <?php else: ?>
            <?php foreach($orders as $order): ?>
            <tr>
                <td><strong style="font-size:13px"><?= htmlspecialchars($order['order_number']) ?></strong></td>
                <td><?= htmlspecialchars($order['full_name']) ?><br><small class="text-muted"><?= htmlspecialchars($order['email']) ?></small></td>
                <td><small><?= date('d M Y', strtotime($order['created_at'])) ?><br><?= date('h:i A', strtotime($order['created_at'])) ?></small></td>
                <td class="fw-700"><?= formatPrice($order['final_amount']) ?></td>
                <td><span class="badge bg-light text-dark text-capitalize border"><?= $order['payment_method'] ?></span></td>
                <td><span class="status-badge status-<?= $order['order_status'] ?>"><?= ucfirst($order['order_status']) ?></span></td>
                <td><a href="orders.php?id=<?= $order['id'] ?>" class="btn btn-sm btn-outline-primary">Manage</a></td>
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
        <li class="page-item <?= $i===$page?'active':'' ?>">
            <a class="page-link" href="?page=<?=$i?>&status=<?=urlencode($statusFilter)?>&search=<?=urlencode($searchFilter)?>"><?=$i?></a>
        </li>
        <?php endfor; ?>
    </ul>
</nav>
<?php endif; ?>
<div class="text-muted small mt-2">Total: <?= $totalCount ?> orders</div>

<?php require_once __DIR__ . '/footer.php'; ?>
