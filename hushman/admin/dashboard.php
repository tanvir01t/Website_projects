<?php
// ============================================================
// admin/dashboard.php — Admin Dashboard
// ============================================================
require_once __DIR__ . '/../includes/functions.php';
requireAdminLogin();

$stats = getAdminStats();
$db    = getDB();

// Recent orders
$recentOrders = $db->query("
    SELECT o.*, u.name as user_name
    FROM orders o JOIN users u ON o.user_id = u.id
    ORDER BY o.created_at DESC LIMIT 8
")->fetchAll();

// Monthly revenue (last 6 months)
$monthlyRevenue = $db->query("
    SELECT DATE_FORMAT(created_at,'%b') as month,
           COALESCE(SUM(final_amount),0) as revenue,
           COUNT(*) as orders
    FROM orders
    WHERE created_at >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
    GROUP BY YEAR(created_at), MONTH(created_at)
    ORDER BY created_at ASC
")->fetchAll();

// Low stock products
$lowStock = $db->query("
    SELECT p.*, c.name as category_name FROM products p
    JOIN categories c ON p.category_id = c.id
    WHERE p.stock <= 5 AND p.is_active = 1
    ORDER BY p.stock ASC LIMIT 5
")->fetchAll();

$adminTitle = 'Dashboard';
require_once __DIR__ . '/header.php';
?>

<!-- Stats Cards -->
<div class="row g-3 mb-4">
    <div class="col-6 col-xl-3">
        <div class="stat-card">
            <div class="stat-icon gold"><i class="bi bi-bag-check"></i></div>
            <div>
                <div class="stat-number"><?= number_format($stats['total_orders']) ?></div>
                <div class="stat-label">Total Orders</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="stat-card">
            <div class="stat-icon green"><i class="bi bi-currency-dollar"></i></div>
            <div>
                <div class="stat-number" style="font-size:22px">৳<?= number_format($stats['total_revenue']) ?></div>
                <div class="stat-label">Total Revenue</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="stat-card">
            <div class="stat-icon blue"><i class="bi bi-people"></i></div>
            <div>
                <div class="stat-number"><?= number_format($stats['total_users']) ?></div>
                <div class="stat-label">Customers</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="stat-card">
            <div class="stat-icon red"><i class="bi bi-clock-history"></i></div>
            <div>
                <div class="stat-number"><?= number_format($stats['pending_orders']) ?></div>
                <div class="stat-label">Pending Orders</div>
            </div>
        </div>
    </div>
</div>

<!-- Secondary Stats -->
<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="card-plain p-3 text-center">
            <div style="font-size:clamp(16px,3.5vw,28px);font-weight:700;font-family:'Cormorant Garamond',serif"><?= $stats['total_products'] ?></div>
            <div style="font-size:12px;color:var(--muted);text-transform:uppercase;letter-spacing:.1em">Active Products</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card-plain p-3 text-center">
            <div style="font-size:clamp(16px,3.5vw,28px);font-weight:700;font-family:'Cormorant Garamond',serif;color:var(--danger)"><?= $stats['low_stock'] ?></div>
            <div style="font-size:12px;color:var(--muted);text-transform:uppercase;letter-spacing:.1em">Low Stock Items</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card-plain p-3 text-center">
            <div style="font-size:clamp(16px,3.5vw,28px);font-weight:700;font-family:'Cormorant Garamond',serif;color:#198754">
                <?= $db->query("SELECT COUNT(*) FROM orders WHERE order_status='delivered'")->fetchColumn() ?>
            </div>
            <div style="font-size:12px;color:var(--muted);text-transform:uppercase;letter-spacing:.1em">Delivered</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card-plain p-3 text-center">
            <div style="font-size:clamp(16px,3.5vw,28px);font-weight:700;font-family:'Cormorant Garamond',serif;color:#0d6efd">
                <?= $db->query("SELECT COUNT(*) FROM orders WHERE order_status='shipped'")->fetchColumn() ?>
            </div>
            <div style="font-size:12px;color:var(--muted);text-transform:uppercase;letter-spacing:.1em">Shipped</div>
        </div>
    </div>
</div>

<div class="row g-4">
    <!-- Recent Orders -->
    <div class="col-lg-8">
        <div class="admin-table">
            <div class="p-3 d-flex justify-content-between align-items-center" style="border-bottom:1px solid var(--border)">
                <h6 class="mb-0 fw-700">Recent Orders</h6>
                <a href="orders.php" class="btn btn-sm btn-outline-dark">View All</a>
            </div>
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>Order #</th>
                        <th>Customer</th>
                        <th>Amount</th>
                        <th>Payment</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(empty($recentOrders)): ?>
                    <tr><td colspan="6" class="text-center text-muted py-4">No orders yet</td></tr>
                    <?php else: ?>
                    <?php foreach($recentOrders as $order): ?>
                    <tr>
                        <td><strong style="font-size:13px"><?= htmlspecialchars($order['order_number']) ?></strong><br><small class="text-muted"><?= date('d M Y', strtotime($order['created_at'])) ?></small></td>
                        <td><?= htmlspecialchars($order['user_name']) ?></td>
                        <td class="fw-700"><?= formatPrice($order['final_amount']) ?></td>
                        <td class="text-capitalize"><span class="badge bg-secondary"><?= $order['payment_method'] ?></span></td>
                        <td><span class="status-badge status-<?= $order['order_status'] ?>"><?= ucfirst($order['order_status']) ?></span></td>
                        <td><a href="orders.php?id=<?= $order['id'] ?>" class="btn btn-sm btn-outline-dark">Manage</a></td>
                    </tr>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Low Stock Alert -->
    <div class="col-lg-4">
        <div class="admin-table">
            <div class="p-3 d-flex justify-content-between align-items-center" style="border-bottom:1px solid var(--border)">
                <h6 class="mb-0 fw-700"><i class="bi bi-exclamation-triangle text-warning me-2"></i>Low Stock</h6>
                <a href="products.php" class="btn btn-sm btn-outline-dark">Manage</a>
            </div>
            <?php if(empty($lowStock)): ?>
            <div class="p-4 text-center text-muted">All products well stocked!</div>
            <?php else: ?>
            <?php foreach($lowStock as $p): ?>
            <div class="d-flex align-items-center gap-3 p-3" style="border-bottom:1px solid var(--border)">
                <div style="width:40px;height:48px;border-radius:4px;overflow:hidden;flex-shrink:0;background:var(--ivory)">
                    <img src="<?= SITE_URL ?>/assets/images/products/<?= htmlspecialchars($p['image']) ?>" style="width:100%;height:100%;object-fit:cover" onerror="this.src='<?= SITE_URL ?>/assets/images/placeholder.jpg'">
                </div>
                <div class="flex-grow-1">
                    <div style="font-size:13px;font-weight:600"><?= htmlspecialchars(substr($p['name'],0,28)) ?>...</div>
                    <div class="text-muted" style="font-size:11px"><?= htmlspecialchars($p['category_name']) ?></div>
                </div>
                <span class="badge <?= $p['stock'] == 0 ? 'bg-danger' : 'bg-warning text-dark' ?>">
                    <?= $p['stock'] == 0 ? 'Out' : $p['stock'].' left' ?>
                </span>
            </div>
            <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <!-- Quick Links -->
        <div class="card-plain p-3 mt-4">
            <h6 class="fw-700 mb-3">Quick Actions</h6>
            <div class="d-grid gap-2">
                <a href="add-product.php" class="btn btn-primary btn-sm"><i class="bi bi-plus-circle me-2"></i>Add New Product</a>
                <a href="orders.php?status=pending" class="btn btn-outline-warning btn-sm"><i class="bi bi-clock me-2"></i>View Pending Orders</a>
                <a href="<?= SITE_URL ?>" target="_blank" class="btn btn-outline-dark btn-sm"><i class="bi bi-shop me-2"></i>View Store Front</a>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/footer.php'; ?>
