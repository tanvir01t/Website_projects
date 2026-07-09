<?php
// ============================================================
// pages/apply-coupon.php — Coupon AJAX Handler
// ============================================================
require_once __DIR__ . '/../includes/functions.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
    exit;
}

$code  = strtoupper(trim($_POST['coupon'] ?? ''));
$total = getCartTotal();

if (empty($code)) {
    echo json_encode(['success' => false, 'message' => 'Please enter a coupon code']);
    exit;
}

$result = applyCoupon($code, $total);

if (isset($result['error'])) {
    echo json_encode(['success' => false, 'message' => $result['error']]);
    exit;
}

// Store in session
$_SESSION['coupon_discount'] = $result['discount'];
$_SESSION['coupon_code']     = $code;

echo json_encode([
    'success'  => true,
    'message'  => 'Coupon applied successfully!',
    'discount' => formatPrice($result['discount']),
]);
exit;
