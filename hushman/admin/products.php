<?php
// ============================================================
// admin/products.php — Product Management
// ============================================================
require_once __DIR__ . '/../includes/functions.php';
requireAdminLogin();

$db = getDB();

// Handle delete
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $db->prepare("UPDATE products SET is_active = 0 WHERE id = ?")->execute([(int)$_GET['delete']]);
    setFlash('success', 'Product deleted successfully.');
    header('Location: products.php'); exit;
}

// Handle toggle featured
if (isset($_GET['toggle_featured']) && is_numeric($_GET['toggle_featured'])) {
    $db->prepare("UPDATE products SET is_featured = NOT is_featured WHERE id = ?")->execute([(int)$_GET['toggle_featured']]);
    header('Location: products.php'); exit;
}

// Filters
$catFilter    = isset($_GET['category']) ? (int)$_GET['category'] : 0;
$searchFilter = trim($_GET['search'] ?? '');
$page         = max(1, (int)($_GET['page'] ?? 1));
$perPage      = 15;
$offset       = ($page - 1) * $perPage;

$where  = "WHERE p.is_active = 1";
$params = [];
if ($catFilter) { $where .= " AND p.category_id = ?"; $params[] = $catFilter; }
if ($searchFilter) { $where .= " AND p.name LIKE ?"; $params[] = "%$searchFilter%"; }

$total      = $db->prepare("SELECT COUNT(*) FROM products p $where");
$total->execute($params);
$totalCount = $total->fetchColumn();
$totalPages = ceil($totalCount / $perPage);

$params[] = $perPage; $params[] = $offset;
$products  = $db->prepare("
    SELECT p.*, c.name as category_name,
           (p.price - (p.price * p.discount_percent / 100)) as sale_price
    FROM products p JOIN categories c ON p.category_id = c.id
    $where ORDER BY p.created_at DESC LIMIT ? OFFSET ?
");
$products->execute($params);
$products  = $products->fetchAll();
$categories = getCategories();

$adminTitle = 'Products';
require_once __DIR__ . '/header.php';
?>

<!-- Toolbar -->
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
    <form method="GET" class="d-flex gap-2 flex-wrap">
        <input type="text" name="search" class="form-control form-control-sm" placeholder="Search products..." value="<?= htmlspecialchars($searchFilter) ?>" style="width:200px">
        <select name="category" class="form-select form-select-sm" style="width:160px">
            <option value="">All Categories</option>
            <?php foreach($categories as $cat): ?>
            <option value="<?= $cat['id'] ?>" <?= $catFilter === $cat['id'] ? 'selected' : '' ?>><?= htmlspecialchars($cat['name']) ?></option>
            <?php endforeach; ?>
        </select>
        <button type="submit" class="btn btn-sm btn-primary"><i class="bi bi-search"></i></button>
        <?php if($searchFilter || $catFilter): ?><a href="products.php" class="btn btn-sm btn-outline-secondary">Clear</a><?php endif; ?>
    </form>
    <a href="add-product.php" class="btn btn-primary btn-sm"><i class="bi bi-plus-circle me-2"></i>Add Product</a>
</div>

<div class="admin-table">
    <table class="table table-hover mb-0">
        <thead>
            <tr>
                <th>Image</th>
                <th>Product</th>
                <th>Category</th>
                <th>Price</th>
                <th>Stock</th>
                <th>Featured</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if(empty($products)): ?>
            <tr><td colspan="7" class="text-center py-5 text-muted">No products found.</td></tr>
            <?php else: ?>
            <?php foreach($products as $p): ?>
            <tr>
                <td>
                    <img src="<?= SITE_URL ?>/assets/images/products/<?= htmlspecialchars($p['image']) ?>"
                         style="width:44px;height:54px;object-fit:cover;border-radius:4px;background:var(--ivory)"
                         onerror="this.src='<?= SITE_URL ?>/assets/images/placeholder.jpg'">
                </td>
                <td>
                    <div style="font-weight:600;font-size:13.5px"><?= htmlspecialchars($p['name']) ?></div>
                    <div class="text-muted" style="font-size:11px">ID: <?= $p['id'] ?></div>
                </td>
                <td><span class="badge bg-light text-dark border"><?= htmlspecialchars($p['category_name']) ?></span></td>
                <td>
                    <div class="fw-700"><?= formatPrice($p['sale_price']) ?></div>
                    <?php if($p['discount_percent'] > 0): ?><div class="text-muted text-decoration-line-through" style="font-size:11px"><?= formatPrice($p['price']) ?></div><?php endif; ?>
                </td>
                <td>
                    <span class="badge <?= $p['stock'] == 0 ? 'bg-danger' : ($p['stock'] <= 5 ? 'bg-warning text-dark' : 'bg-success') ?>">
                        <?= $p['stock'] ?> in stock
                    </span>
                </td>
                <td>
                    <a href="products.php?toggle_featured=<?= $p['id'] ?>" title="Toggle featured">
                        <i class="bi bi-star<?= $p['is_featured'] ? '-fill text-warning' : '' ?> fs-5"></i>
                    </a>
                </td>
                <td>
                    <div class="d-flex gap-1">
                        <a href="edit-product.php?id=<?= $p['id'] ?>" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil"></i></a>
                        <a href="products.php?delete=<?= $p['id'] ?>" class="btn btn-sm btn-outline-danger confirm-delete" title="Delete"><i class="bi bi-trash3"></i></a>
                        <a href="<?= SITE_URL ?>/pages/product.php?slug=<?= htmlspecialchars($p['slug']) ?>" target="_blank" class="btn btn-sm btn-outline-secondary" title="View"><i class="bi bi-eye"></i></a>
                    </div>
                </td>
            </tr>
            <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<!-- Pagination -->
<?php if($totalPages > 1): ?>
<nav class="mt-4 d-flex justify-content-center">
    <ul class="pagination pagination-sm">
        <?php for($i = 1; $i <= $totalPages; $i++): ?>
        <li class="page-item <?= $i === $page ? 'active' : '' ?>">
            <a class="page-link" href="?page=<?= $i ?>&search=<?= urlencode($searchFilter) ?>&category=<?= $catFilter ?>"><?= $i ?></a>
        </li>
        <?php endfor; ?>
    </ul>
</nav>
<?php endif; ?>

<div class="text-muted small mt-2">Total: <?= $totalCount ?> products</div>

<?php require_once __DIR__ . '/footer.php'; ?>
