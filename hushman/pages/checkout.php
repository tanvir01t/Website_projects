<?php
// ============================================================
// pages/checkout.php — Checkout
// ============================================================
require_once __DIR__ . '/../includes/functions.php';
requireLogin();

$cart = getCartItems();
if (empty($cart)) { header('Location: ' . SITE_URL . '/pages/cart.php'); exit; }

$user     = getCurrentUser();
$subtotal = getCartTotal();
$discount = $_SESSION['coupon_discount'] ?? 0;
$shipping = ($subtotal - $discount) >= 2000 ? 0 : SHIPPING_COST;
$total    = max(0, $subtotal - $discount) + $shipping;
$errors   = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullName   = trim($_POST['full_name'] ?? '');
    $email      = trim($_POST['email'] ?? '');
    $phone      = trim($_POST['phone'] ?? '');
    $address    = trim($_POST['address'] ?? '');
    $city       = trim($_POST['city'] ?? '');
    $postal     = trim($_POST['postal_code'] ?? '');
    $payment    = $_POST['payment_method'] ?? 'cod';
    $notes      = trim($_POST['notes'] ?? '');

    if (empty($fullName))  $errors[] = 'Full name is required.';
    if (empty($phone))     $errors[] = 'Phone number is required.';
    if (empty($address))   $errors[] = 'Delivery address is required.';
    if (empty($city))      $errors[] = 'City is required.';

    if (empty($errors)) {
        $db = getDB();
        $orderNo = generateOrderNumber();

        $stmt = $db->prepare("INSERT INTO orders (user_id, order_number, full_name, email, phone, address, city, postal_code, total_amount, discount_amount, shipping_cost, final_amount, payment_method, coupon_code, notes) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)");
        $stmt->execute([
            $_SESSION['user_id'], $orderNo, $fullName, $email, $phone, $address, $city, $postal,
            $subtotal, $discount, $shipping, $total, $payment,
            $_SESSION['coupon_code'] ?? null, $notes
        ]);
        $orderId = $db->lastInsertId();

        // Insert order items
        foreach ($cart as $item) {
            $istmt = $db->prepare("INSERT INTO order_items (order_id, product_id, product_name, product_image, size, color, quantity, unit_price, subtotal) VALUES (?,?,?,?,?,?,?,?,?)");
            $istmt->execute([
                $orderId, $item['product_id'], $item['name'], $item['image'],
                $item['size'], $item['color'], $item['quantity'],
                $item['sale_price'], $item['sale_price'] * $item['quantity']
            ]);
            // Reduce stock
            $db->prepare("UPDATE products SET stock = stock - ? WHERE id = ? AND stock > 0")->execute([$item['quantity'], $item['product_id']]);
        }

        // Update coupon usage
        if (!empty($_SESSION['coupon_code'])) {
            $db->prepare("UPDATE coupons SET used_count = used_count + 1 WHERE code = ?")->execute([$_SESSION['coupon_code']]);
        }

        clearCart();
        unset($_SESSION['coupon_discount'], $_SESSION['coupon_code']);
        setFlash('success', "Order placed successfully! Your order number is <strong>$orderNo</strong>.");
        header('Location: ' . SITE_URL . '/pages/order-success.php?id=' . $orderId);
        exit;
    }
}
$pageTitle = 'Checkout';
?>
<?php require_once __DIR__ . '/../includes/header.php'; ?>

<div class="page-breadcrumb">
  <div class="container">
    <nav><ol class="breadcrumb">
      <li class="breadcrumb-item"><a href="<?= SITE_URL ?>">Home</a></li>
      <li class="breadcrumb-item"><a href="<?= SITE_URL ?>/pages/cart.php">Cart</a></li>
      <li class="breadcrumb-item active">Checkout</li>
    </ol></nav>
  </div>
</div>

<div class="container my-5">
  <h1 style="font-family:var(--ff-display);font-size:clamp(22px,6vw,34px);font-weight:700;margin-bottom:24px">Checkout</h1>

  <?php if(!empty($errors)): ?>
  <div class="alert alert-danger mb-4">
    <?php foreach($errors as $e): ?><div><i class="bi bi-x-circle me-2"></i><?= htmlspecialchars($e) ?></div><?php endforeach; ?>
  </div>
  <?php endif; ?>

  <form method="POST">
  <div class="row g-4">
    <!-- Delivery Info -->
    <div class="col-lg-7">
      <div class="card-plain p-4 mb-4">
        <h5 style="font-family:var(--ff-display);font-size:22px;margin-bottom:20px">Delivery Information</h5>
        <div class="row g-3">
          <div class="col-md-6">
            <label class="form-label">Full Name *</label>
            <input type="text" name="full_name" class="form-control" value="<?= $_POST['full_name'] ?? sanitize($user['name']) ?>" required>
          </div>
          <div class="col-md-6">
            <label class="form-label">Email *</label>
            <input type="email" name="email" class="form-control" value="<?= $_POST['email'] ?? sanitize($user['email']) ?>" required>
          </div>
          <div class="col-md-6">
            <label class="form-label">Phone *</label>
            <input type="tel" name="phone" class="form-control" value="<?= $_POST['phone'] ?? sanitize($user['phone'] ?? '') ?>" required>
          </div>
          <div class="col-md-6">
            <label class="form-label">City *</label>
            <input type="text" name="city" class="form-control" value="<?= $_POST['city'] ?? sanitize($user['city'] ?? '') ?>" required>
          </div>
          <div class="col-12">
            <label class="form-label">Delivery Address *</label>
            <textarea name="address" class="form-control" rows="2" required><?= $_POST['address'] ?? sanitize($user['address'] ?? '') ?></textarea>
          </div>
          <div class="col-md-6">
            <label class="form-label">Postal Code</label>
            <input type="text" name="postal_code" class="form-control" value="<?= $_POST['postal_code'] ?? '' ?>">
          </div>
          <div class="col-12">
            <label class="form-label">Order Notes <small class="text-muted">(Optional)</small></label>
            <textarea name="notes" class="form-control" rows="2" placeholder="Any special instructions for delivery..."><?= $_POST['notes'] ?? '' ?></textarea>
          </div>
        </div>
      </div>

      <!-- Payment Method -->
      <div class="card-plain p-4">
        <h5 style="font-family:var(--ff-display);font-size:22px;margin-bottom:20px">Payment Method</h5>
        <div class="row g-3">
          <?php
          $payments = [
            'cod'   => ['Cash on Delivery', 'bi bi-wallet2', 'Pay when your order arrives'],
            'bkash' => ['bKash', 'bi bi-phone', 'Pay with bKash mobile banking'],
            'nagad' => ['Nagad', 'bi bi-phone-fill', 'Pay with Nagad mobile banking'],
            'card'  => ['Card Payment', 'bi bi-credit-card', 'Visa / MasterCard / DBBL'],
          ];
          foreach($payments as $val => [$label, $icon, $desc]):
          ?>
          <div class="col-6">
            <label class="d-block p-3 rounded cursor-pointer payment-option" style="border:2px solid var(--border);cursor:pointer;transition:all .2s" for="pay_<?= $val ?>">
              <input type="radio" name="payment_method" value="<?= $val ?>" id="pay_<?= $val ?>" class="d-none payment-radio" <?= $val === 'cod' ? 'checked' : '' ?>>
              <i class="<?= $icon ?> fs-4 d-block mb-1 text-gold"></i>
              <strong style="font-size:13px"><?= $label ?></strong>
              <div class="text-muted" style="font-size:11px"><?= $desc ?></div>
            </label>
          </div>
          <?php endforeach; ?>
        </div>
      </div>
    </div>

    <!-- Order Summary -->
    <div class="col-lg-5">
      <div class="cart-summary">
        <h5 style="font-family:var(--ff-display);font-size:22px;margin-bottom:20px">Your Order (<?= count($cart) ?> items)</h5>
        <?php foreach($cart as $item): ?>
        <div class="d-flex gap-3 align-items-center mb-3 pb-3" style="border-bottom:1px solid var(--border)">
          <img src="<?= productImageUrl($item['image']) ?>" style="width:52px;height:64px;object-fit:cover;border-radius:4px">
          <div class="flex-grow-1">
            <div style="font-size:13px;font-weight:600"><?= sanitize($item['name']) ?></div>
            <div class="text-muted" style="font-size:11px"><?= $item['size'] ? 'Size: '.$item['size'] : '' ?> <?= $item['color'] ? '· '.$item['color'] : '' ?></div>
            <div style="font-size:12px">Qty: <?= $item['quantity'] ?></div>
          </div>
          <div style="font-size:14px;font-weight:700"><?= formatPrice($item['sale_price'] * $item['quantity']) ?></div>
        </div>
        <?php endforeach; ?>
        <div class="cart-summary-row"><span>Subtotal</span><span><?= formatPrice($subtotal) ?></span></div>
        <?php if($discount > 0): ?>
        <div class="cart-summary-row" style="color:var(--danger)"><span>Discount</span><span>-<?= formatPrice($discount) ?></span></div>
        <?php endif; ?>
        <div class="cart-summary-row"><span>Shipping</span><span><?= $shipping === 0 ? '<span class="text-success">Free</span>' : formatPrice($shipping) ?></span></div>
        <div class="cart-summary-row total"><span>Total</span><span><?= formatPrice($total) ?></span></div>
        <button type="submit" class="btn btn-primary w-100 btn-lg mt-3">
          <i class="bi bi-bag-check me-2"></i>Place Order
        </button>
        <div class="text-center mt-2 text-muted" style="font-size:12px"><i class="bi bi-lock-fill me-1"></i>Secure checkout</div>
      </div>
    </div>
  </div>
  </form>
</div>

<script>
document.querySelectorAll('.payment-radio').forEach(radio => {
  radio.addEventListener('change', function() {
    document.querySelectorAll('.payment-option').forEach(l => l.style.borderColor = 'var(--border)');
    if(this.checked) this.closest('.payment-option').style.borderColor = 'var(--gold)';
  });
  if(radio.checked) radio.closest('.payment-option').style.borderColor = 'var(--gold)';
});
</script>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
