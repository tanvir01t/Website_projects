<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($adminTitle) ? htmlspecialchars($adminTitle) . ' — Admin' : 'Admin Panel' ?> | HushMan</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@600;700&family=DM+Sans:wght@400;500;600&display=swap" rel="stylesheet">
    <link href="../assets/css/style.css" rel="stylesheet">

    <style>
    @media(max-width:991px){
      .admin-sidebar{position:fixed;z-index:200;transition:transform .25s ease}
      .admin-mobile-overlay{position:fixed;inset:0;background:rgba(0,0,0,.5);z-index:199;display:none}
      .admin-mobile-overlay.show{display:block}
      .admin-main{margin-left:0!important}
      .admin-topbar{padding:12px 16px}
      .admin-content{padding:16px}
      .stat-card{padding:14px}
      .admin-table{overflow-x:auto;-webkit-overflow-scrolling:touch}
      .admin-table table{min-width:600px}
    }
    </style>
</head>
<body>

<!-- Admin Sidebar -->
<div class="admin-sidebar" id="adminSidebar">
    <a href="dashboard.php" class="admin-sidebar-brand">HUSH<span>MAN</span> <small style="font-size:10px;opacity:.5;font-family:'DM Sans',sans-serif;font-weight:400;letter-spacing:.05em">ADMIN</small></a>

    <div class="mt-2">
        <div style="padding:8px 24px;font-size:10px;letter-spacing:.2em;text-transform:uppercase;color:rgba(255,255,255,.25);font-weight:600">Main</div>
        <a href="dashboard.php" class="admin-nav-link <?= basename($_SERVER['PHP_SELF']) === 'dashboard.php' ? 'active' : '' ?>">
            <i class="bi bi-speedometer2"></i> Dashboard
        </a>
        <a href="orders.php" class="admin-nav-link <?= basename($_SERVER['PHP_SELF']) === 'orders.php' ? 'active' : '' ?>">
            <i class="bi bi-bag-check"></i> Orders
            <?php
            $db = getDB();
            $pending = $db->query("SELECT COUNT(*) FROM orders WHERE order_status = 'pending'")->fetchColumn();
            if($pending > 0): ?>
            <span class="ms-auto badge" style="background:var(--gold);font-size:10px"><?= $pending ?></span>
            <?php endif; ?>
        </a>
        <a href="products.php" class="admin-nav-link <?= basename($_SERVER['PHP_SELF']) === 'products.php' ? 'active' : '' ?>">
            <i class="bi bi-box-seam"></i> Products
        </a>
        <a href="add-product.php" class="admin-nav-link <?= basename($_SERVER['PHP_SELF']) === 'add-product.php' ? 'active' : '' ?>">
            <i class="bi bi-plus-square"></i> Add Product
        </a>
        <a href="categories.php" class="admin-nav-link <?= basename($_SERVER['PHP_SELF']) === 'categories.php' ? 'active' : '' ?>">
            <i class="bi bi-grid"></i> Categories
        </a>

        <div style="padding:8px 24px;margin-top:8px;font-size:10px;letter-spacing:.2em;text-transform:uppercase;color:rgba(255,255,255,.25);font-weight:600">Users</div>
        <a href="users.php" class="admin-nav-link <?= basename($_SERVER['PHP_SELF']) === 'users.php' ? 'active' : '' ?>">
            <i class="bi bi-people"></i> Manage Users
        </a>
        <a href="reviews.php" class="admin-nav-link <?= basename($_SERVER['PHP_SELF']) === 'reviews.php' ? 'active' : '' ?>">
            <i class="bi bi-star"></i> Reviews
        </a>
        <a href="coupons.php" class="admin-nav-link <?= basename($_SERVER['PHP_SELF']) === 'coupons.php' ? 'active' : '' ?>">
            <i class="bi bi-tag"></i> Coupons
        </a>

        <div style="padding:8px 24px;margin-top:8px;font-size:10px;letter-spacing:.2em;text-transform:uppercase;color:rgba(255,255,255,.25);font-weight:600">Account</div>
        <a href="<?= SITE_URL ?>" target="_blank" class="admin-nav-link">
            <i class="bi bi-shop"></i> View Store
        </a>
        <a href="logout.php" class="admin-nav-link">
            <i class="bi bi-box-arrow-right"></i> Logout
        </a>
    </div>
</div>

<!-- Main Content -->
<div class="admin-main">
    <!-- Top Bar -->
    <div class="admin-topbar">
        <div class="d-flex align-items-center gap-3">
            <button class="btn btn-sm btn-outline-secondary d-lg-none" id="sidebarToggle"><i class="bi bi-list fs-5"></i></button>
            <h1 class="admin-page-title"><?= isset($adminTitle) ? htmlspecialchars($adminTitle) : 'Dashboard' ?></h1>
        </div>
        <div class="d-flex align-items-center gap-3">
            <?php $flash = getFlash(); if($flash): ?>
            <div class="alert alert-<?= $flash['type'] === 'error' ? 'danger' : $flash['type'] ?> py-1 px-3 mb-0 small">
                <?= htmlspecialchars($flash['message']) ?>
            </div>
            <?php endif; ?>
            <div class="d-flex align-items-center gap-2">
                <div style="width:34px;height:34px;background:var(--gold);border-radius:50%;display:flex;align-items:center;justify-content:center;color:#fff;font-weight:700;font-size:14px">
                    <?= strtoupper(substr($_SESSION['admin_name'] ?? 'A', 0, 1)) ?>
                </div>
                <div>
                    <div style="font-size:13px;font-weight:600"><?= htmlspecialchars($_SESSION['admin_name'] ?? 'Admin') ?></div>
                    <div style="font-size:11px;color:var(--muted);text-transform:capitalize"><?= htmlspecialchars($_SESSION['admin_role'] ?? 'admin') ?></div>
                </div>
            </div>
        </div>
    </div>
    <div class="admin-content">
