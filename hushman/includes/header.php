<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($pageTitle) ? sanitize($pageTitle) . ' — ' . SITE_NAME : SITE_NAME . ' | Men\'s Fashion' ?></title>
    <meta name="description" content="<?= isset($pageDesc) ? sanitize($pageDesc) : 'HushMan — Premium Men\'s Fashion. Shop T-Shirts, Shirts, Pants & more.' ?>">
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@400;500;600;700&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
    <!-- Main CSS -->
    <link href="<?= SITE_URL ?>/assets/css/style.css" rel="stylesheet">
    <link href="<?= SITE_URL ?>/assets/css/hero-new-styles.css" rel="stylesheet">
    <!-- Critical mobile fix -->
    <style>
      @media(max-width:767px){
        body{overflow-x:hidden}
        .container{padding-left:16px;padding-right:16px}
      }
    </style>
</head>
<body>

<!-- Top Bar -->
<div class="top-bar">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-md-6 d-none d-md-block">
                <span class="top-bar-text"><i class="bi bi-truck me-1"></i> Free delivery on orders over ৳2000</span>
            </div>
            <div class="col-md-6 text-end">
                <?php if(isLoggedIn()): ?>
                    <a href="<?= SITE_URL ?>/pages/profile.php" class="top-bar-link">
                        <i class="bi bi-person-circle me-1"></i><?= sanitize($_SESSION['user_name'] ?? 'My Account') ?>
                    </a>
                    <span class="mx-2 text-muted">|</span>
                    <a href="<?= SITE_URL ?>/pages/logout.php" class="top-bar-link">Logout</a>
                <?php else: ?>
                    <a href="<?= SITE_URL ?>/pages/login.php" class="top-bar-link"><i class="bi bi-person me-1"></i>Login</a>
                    <span class="mx-2">|</span>
                    <a href="<?= SITE_URL ?>/pages/register.php" class="top-bar-link">Register</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Main Navbar -->
<nav class="navbar navbar-expand-lg navbar-light sticky-top main-nav" id="mainNav">
    <div class="container">
        <!-- Logo -->
        <a class="navbar-brand" href="<?= SITE_URL ?>/index.php">
            <span class="brand-text">HUSH<span class="brand-accent">MAN</span></span>
        </a>

        <!-- Mobile Cart + Toggle -->
        <div class="d-flex align-items-center d-lg-none gap-2">
            <a href="<?= SITE_URL ?>/pages/cart.php" class="cart-icon-link">
                <i class="bi bi-bag"></i>
                <span class="cart-badge"><?= getCartCount() ?></span>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navMenu">
                <span class="navbar-toggler-icon"></span>
            </button>
        </div>

        <!-- Nav Links -->
        <div class="collapse navbar-collapse" id="navMenu">
            <ul class="navbar-nav mx-auto mb-2 mb-lg-0">
                <li class="nav-item"><a class="nav-link" href="<?= SITE_URL ?>/index.php">Home</a></li>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">Shop</a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="<?= SITE_URL ?>/pages/shop.php">All Products</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <?php foreach(getCategories() as $cat): ?>
                        <li><a class="dropdown-item" href="<?= SITE_URL ?>/pages/shop.php?category=<?= $cat['slug'] ?>"><?= sanitize($cat['name']) ?></a></li>
                        <?php endforeach; ?>
                    </ul>
                </li>
                <li class="nav-item"><a class="nav-link" href="<?= SITE_URL ?>/pages/shop.php?sale=1">Sale</a></li>
                <li class="nav-item"><a class="nav-link" href="<?= SITE_URL ?>/index.php#contact">Contact</a></li>
            </ul>
            <!-- Desktop Search + Cart -->
            <div class="d-flex align-items-center gap-3 d-none d-lg-flex">
                <form action="<?= SITE_URL ?>/pages/shop.php" method="GET" class="search-form">
                    <input type="text" name="search" class="search-input" placeholder="Search products..." value="<?= isset($_GET['search']) ? sanitize($_GET['search']) : '' ?>">
                    <button type="submit" class="search-btn"><i class="bi bi-search"></i></button>
                </form>
                <a href="<?= SITE_URL ?>/pages/cart.php" class="cart-icon-link">
                    <i class="bi bi-bag fs-5"></i>
                    <span class="cart-badge"><?= getCartCount() ?></span>
                </a>
            </div>
        </div>
    </div>
</nav>

<!-- Flash Message -->
<?php $flash = getFlash(); if($flash): ?>
<div class="container mt-3">
    <div class="alert alert-<?= $flash['type'] === 'error' ? 'danger' : $flash['type'] ?> alert-dismissible fade show" role="alert">
        <i class="bi bi-<?= $flash['type'] === 'success' ? 'check-circle' : 'exclamation-circle' ?> me-2"></i>
        <?= sanitize($flash['message']) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
</div>
<?php endif; ?>
