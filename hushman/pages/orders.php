<?php
// ============================================================
// pages/orders.php — Order History
// ============================================================
require_once __DIR__ . '/../includes/functions.php';
requireLogin();

$orders = getUserOrders($_SESSION['user_id']);

// Single order detail view
$detailId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$detail   = $detailId ? getOrderDetails($detailId, $_SESSION['user_id']) : null;

$pageTitle = $detail ? 'Order Details' : 'My Orders';
?>
<?php require_once __DIR__ . '/../includes/header.php'; ?>

<div class="page-breadcrumb">
  <div class="container">
    <nav><ol class="breadcrumb">
      <li class="breadcrumb-item"><a href="<?= SITE_URL ?>">Home</a></li>
      <li class="breadcrumb-item"><a href="<?= SITE_URL ?>/pages/profile.php">My Account</a></li>
      <li class="breadcrumb-item active"><?= $detail ? 'Order Details' : 'My Orders' ?></li>
    </ol></nav>
  </div>
</div>

<div class="container my-5">
  <?php if($detail): ?>
  <!-- Order Detail View -->
  <div class="d-flex justify-content-between align-items-center mb-4">
    <h1 style="font-family:var(--ff-display);font-size:30px;margin:0">Order Details</h1>
    <a href="<?= SITE_URL ?>/pages/orders.php" class="btn btn-outline-dark btn-sm"><i class="bi bi-arrow-left me-2"></i>Back to Orders</a>
  </div>
  <div class="row g-4">
    <div class="col-lg-8">
      <div class="card-plain p-4 mb-4">
        <div class="d-flex justify-content-between align-items-start mb-3">
          <div>
            <div class="order-number"><?= sanitize($detail['order_number']) ?></div>
            <div class="text-muted small"><?= formatDate($detail['created_at']) ?></div>
          </div>
          <span class="status-badge status-<?= $detail['order_status'] ?>"><?= ucfirst($detail['order_status']) ?></span>
        </div>
        <hr>
        <?php foreach($detail['items'] as $item): ?>
        <div class="d-flex gap-3 align-items-center mb-3 pb-3" style="border-bottom:1px solid var(--border)">
          <img src="<?= productImageUrl($item['product_image']) ?>" style="width:60px;height:74px;object-fit:cover;border-radius:4px">
          <div class="flex-grow-1">
            <div class="fw-600"><?= sanitize($item['product_name']) ?></div>
            <div class="text-muted small"><?= $item['size'] ? 'Size: '.$item['size'] : '' ?> <?= $item['color'] ? '· Color: '.$item['color'] : '' ?></div>
            <div class="text-muted small">Qty: <?= $item['quantity'] ?> × <?= formatPrice($item['unit_price']) ?></div>
          </div>
          <div class="fw-700"><?= formatPrice($item['subtotal']) ?></div>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
    <div class="col-lg-4">
      <div class="cart-summary">
        <h6 class="fw-700 mb-3">Order Summary</h6>
        <div class="cart-summary-row"><span>Subtotal</span><span><?= formatPrice($detail['total_amount']) ?></span></div>
        <?php if($detail['discount_amount'] > 0): ?>
        <div class="cart-summary-row" style="color:var(--danger)"><span>Discount</span><span>-<?= formatPrice($detail['discount_amount']) ?></span></div>
        <?php endif; ?>
        <div class="cart-summary-row"><span>Shipping</span><span><?= $detail['shipping_cost'] == 0 ? '<span class="text-success">Free</span>' : formatPrice($detail['shipping_cost']) ?></span></div>
        <div class="cart-summary-row total"><span>Total</span><span><?= formatPrice($detail['final_amount']) ?></span></div>
        <hr>
        <div class="mb-2"><strong class="small">Payment Method:</strong><span class="text-muted small ms-2 text-capitalize"><?= $detail['payment_method'] ?></span></div>
        <div class="mb-2"><strong class="small">Delivery To:</strong></div>
        <div class="text-muted small"><?= sanitize($detail['full_name']) ?><br><?= sanitize($detail['address']) ?>, <?= sanitize($detail['city']) ?></div>
      </div>
    </div>
  </div>

  <?php else: ?>
  <!-- Orders List -->
  <h1 style="font-family:var(--ff-display);font-size:clamp(22px,5vw,34px);font-weight:700;margin-bottom:32px">My Orders</h1>
  <?php if(empty($orders)): ?>
  <div class="text-center py-5">
    <i class="bi bi-bag-x" style="font-size:56px;color:var(--border)"></i>
    <h4 class="mt-4" style="font-family:var(--ff-display)">No orders yet</h4>
    <p class="text-muted">You haven't placed any orders yet.</p>
    <a href="<?= SITE_URL ?>/pages/shop.php" class="btn btn-primary">Start Shopping</a>
  </div>
  <?php else: ?>
  <?php foreach($orders as $order): ?>
  <div class="order-card">
    <div class="row align-items-center g-3">
      <div class="col-md-3">
        <div class="order-number"><?= sanitize($order['order_number']) ?></div>
        <div class="text-muted small"><?= formatDate($order['created_at']) ?></div>
      </div>
      <div class="col-md-2">
        <span class="status-badge status-<?= $order['order_status'] ?>"><?= ucfirst($order['order_status']) ?></span>
      </div>
      <div class="col-md-2">
        <div class="text-muted small">Total</div>
        <div class="fw-700"><?= formatPrice($order['final_amount']) ?></div>
      </div>
      <div class="col-md-2">
        <div class="text-muted small">Payment</div>
        <div class="text-capitalize"><?= $order['payment_method'] ?></div>
      </div>
      <div class="col-md-3 text-md-end">
        <a href="<?= SITE_URL ?>/pages/orders.php?id=<?= $order['id'] ?>" class="btn btn-outline-dark btn-sm">View Details</a>
      </div>
    </div>
  </div>
  <?php endforeach; ?>
  <?php endif; ?>
  <?php endif; ?>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
