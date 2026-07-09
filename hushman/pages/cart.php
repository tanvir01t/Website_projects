<?php
// ============================================================
// pages/cart.php — Shopping Cart
// ============================================================
require_once __DIR__ . '/../includes/functions.php';

$cartItems = getCartItems();
$subtotal  = getCartTotal();
$discount  = $_SESSION['coupon_discount'] ?? 0;
$shipping  = ($subtotal - $discount) >= 2000 ? 0 : SHIPPING_COST;
$total     = max(0, $subtotal - $discount) + $shipping;
$pageTitle = 'Shopping Cart';
?>
<?php require_once __DIR__ . '/../includes/header.php'; ?>

<!-- Breadcrumb -->
<div class="page-breadcrumb">
  <div class="container">
    <nav><ol class="breadcrumb">
      <li class="breadcrumb-item"><a href="<?= SITE_URL ?>">Home</a></li>
      <li class="breadcrumb-item active">Shopping Cart</li>
    </ol></nav>
  </div>
</div>

<div class="container my-5">
  <h1 style="font-family:var(--ff-display);font-size:clamp(22px,6vw,34px);font-weight:700;margin-bottom:24px">
    Shopping Cart <span class="text-muted fs-5 fw-normal">(<?= getCartCount() ?> items)</span>
  </h1>

  <?php if(empty($cartItems)): ?>
  <div class="text-center py-5">
    <i class="bi bi-bag" style="font-size:64px;color:var(--border)"></i>
    <h3 class="mt-4" style="font-family:var(--ff-display)">Your cart is empty</h3>
    <p class="text-muted mb-4">Looks like you haven't added anything to your cart yet.</p>
    <a href="<?= SITE_URL ?>/pages/shop.php" class="btn btn-primary btn-lg">Start Shopping</a>
  </div>
  <?php else: ?>

  <div class="row g-4">
    <!-- Cart Items -->
    <div class="col-lg-8">
      <div class="card-plain p-4">
        <?php foreach($cartItems as $key => $item): ?>
        <div class="cart-item d-flex gap-3 align-items-start flex-wrap flex-sm-nowrap">
          <img src="<?= productImageUrl($item['image']) ?>" class="cart-item-img" alt="<?= sanitize($item['name']) ?>">
          <div class="flex-grow-1">
            <h6 class="mb-1" style="font-family:var(--ff-display);font-size:17px"><?= sanitize($item['name']) ?></h6>
            <div class="text-muted small mb-2">
              <?php if($item['size']): ?><span class="me-3">Size: <strong><?= sanitize($item['size']) ?></strong></span><?php endif; ?>
              <?php if($item['color']): ?><span>Color: <strong><?= sanitize($item['color']) ?></strong></span><?php endif; ?>
            </div>
            <div class="d-flex align-items-center gap-3 flex-wrap">
              <div class="qty-group">
                <button class="qty-btn" onclick="updateQty('<?= htmlspecialchars($key, ENT_QUOTES) ?>', -1)">−</button>
                <input type="number" class="qty-val cart-qty-input" value="<?= $item['quantity'] ?>" data-key="<?= htmlspecialchars($key, ENT_QUOTES) ?>" min="1">
                <button class="qty-btn" onclick="updateQty('<?= htmlspecialchars($key, ENT_QUOTES) ?>', 1)">+</button>
              </div>
              <span class="fw-700" style="font-family:var(--ff-display);font-size:18px"><?= formatPrice($item['sale_price'] * $item['quantity']) ?></span>
              <?php if($item['price'] > $item['sale_price']): ?>
              <span class="text-muted text-decoration-line-through small"><?= formatPrice($item['price'] * $item['quantity']) ?></span>
              <?php endif; ?>
            </div>
          </div>
          <button class="btn btn-sm text-danger remove-cart-item" data-key="<?= htmlspecialchars($key, ENT_QUOTES) ?>" title="Remove">
            <i class="bi bi-trash3"></i>
          </button>
        </div>
        <?php endforeach; ?>
        <div class="d-flex justify-content-between align-items-center mt-3 pt-3" style="border-top:1px solid var(--border)">
          <a href="<?= SITE_URL ?>/pages/shop.php" class="btn btn-outline-dark btn-sm"><i class="bi bi-arrow-left me-2"></i>Continue Shopping</a>
          <a href="<?= SITE_URL ?>/pages/cart-action.php?action=clear" class="btn btn-outline-danger btn-sm"><i class="bi bi-trash me-1"></i>Clear Cart</a>
        </div>
      </div>
    </div>

    <!-- Summary -->
    <div class="col-lg-4">
      <div class="cart-summary">
        <h5 style="font-family:var(--ff-display);font-size:22px;margin-bottom:20px">Order Summary</h5>
        <div class="cart-summary-row"><span>Subtotal (<?= getCartCount() ?> items)</span><span><?= formatPrice($subtotal) ?></span></div>
        <?php if($discount > 0): ?>
        <div class="cart-summary-row" style="color:var(--danger)"><span>Discount (<?= htmlspecialchars($_SESSION['coupon_code'] ?? '') ?>)</span><span>-<?= formatPrice($discount) ?></span></div>
        <?php endif; ?>
        <div class="cart-summary-row">
          <span>Shipping</span>
          <span><?= $shipping === 0 ? '<span class="text-success">Free</span>' : formatPrice($shipping) ?></span>
        </div>
        <?php if($shipping > 0): ?>
        <div class="text-muted" style="font-size:12px;margin-top:-6px;margin-bottom:8px">Add <?= formatPrice(2000 - ($subtotal - $discount)) ?> more for free shipping</div>
        <?php endif; ?>
        <div class="cart-summary-row total"><span>Total</span><span><?= formatPrice($total) ?></span></div>

        <!-- Coupon -->
        <div class="mt-3 mb-3">
          <div class="input-group input-group-sm">
            <input type="text" id="couponCode" class="form-control" placeholder="Coupon code" value="<?= htmlspecialchars($_SESSION['coupon_code'] ?? '') ?>">
            <button class="btn btn-outline-dark" id="applyCoupon">Apply</button>
          </div>
          <div id="couponResult" class="mt-1">
            <?php if($discount > 0): ?><span class="text-success small"><i class="bi bi-check-circle me-1"></i>Coupon applied! Saved <?= formatPrice($discount) ?></span><?php endif; ?>
          </div>
          <div class="mt-1 text-muted" style="font-size:11px">Try: WELCOME20 / FLAT200 / STYLE10</div>
        </div>

        <a href="<?= SITE_URL ?>/pages/checkout.php" class="btn btn-primary w-100 btn-lg">
          Proceed to Checkout <i class="bi bi-arrow-right ms-2"></i>
        </a>
        <div class="text-center mt-3">
          <span class="text-muted" style="font-size:12px"><i class="bi bi-lock-fill me-1"></i>Secure & encrypted checkout</span>
        </div>
      </div>
    </div>
  </div>
  <?php endif; ?>
</div>

<script>
const siteUrl = '<?= SITE_URL ?>';
function updateQty(key, delta) {
  const input = document.querySelector(`.cart-qty-input[data-key="${key}"]`);
  if (input) {
    let newVal = Math.max(1, parseInt(input.value) + delta);
    input.value = newVal;
    input.dispatchEvent(new Event('change'));
  }
}
</script>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
