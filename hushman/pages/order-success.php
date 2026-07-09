<?php
// ============================================================
// pages/order-success.php
// ============================================================
require_once __DIR__ . '/../includes/functions.php';
requireLogin();

$orderId = (int)($_GET['id'] ?? 0);
$order   = getOrderDetails($orderId, $_SESSION['user_id']);
if (!$order) { header('Location: ' . SITE_URL); exit; }
$pageTitle = 'Order Confirmed';
?>
<?php require_once __DIR__ . '/../includes/header.php'; ?>
<div class="container my-5 text-center" style="max-width:640px">
  <div class="p-5 card-plain">
    <div style="width:80px;height:80px;background:var(--gold);border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 24px;font-size:36px;color:white">
      <i class="bi bi-check-lg"></i>
    </div>
    <h2 style="font-family:var(--ff-display);font-size:clamp(22px,5vw,34px)">Order Confirmed!</h2>
    <p class="text-muted mb-3">Thank you for your order. We'll get it packed and shipped soon.</p>
    <div class="p-3 rounded mb-4" style="background:var(--ivory)">
      <div class="row g-2 text-start">
        <div class="col-6"><span class="text-muted small">Order Number</span><div class="fw-700"><?= sanitize($order['order_number']) ?></div></div>
        <div class="col-6"><span class="text-muted small">Total Paid</span><div class="fw-700"><?= formatPrice($order['final_amount']) ?></div></div>
        <div class="col-6"><span class="text-muted small">Payment</span><div class="fw-700 text-capitalize"><?= $order['payment_method'] ?></div></div>
        <div class="col-6"><span class="text-muted small">Status</span><div><span class="status-badge status-<?= $order['order_status'] ?>"><?= ucfirst($order['order_status']) ?></span></div></div>
      </div>
    </div>
    <div class="d-flex gap-3 justify-content-center flex-wrap">
      <a href="<?= SITE_URL ?>/pages/orders.php" class="btn btn-primary">View My Orders</a>
      <a href="<?= SITE_URL ?>/pages/shop.php" class="btn btn-outline-dark">Continue Shopping</a>
    </div>
  </div>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
