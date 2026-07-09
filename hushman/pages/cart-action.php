<?php
// ============================================================
// pages/cart-action.php — Cart AJAX Handler
// ============================================================
require_once __DIR__ . '/../includes/functions.php';

$isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) || isset($_POST['action']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'add') {
        $productId = (int)($_POST['product_id'] ?? 0);
        $size      = trim($_POST['size'] ?? '');
        $color     = trim($_POST['color'] ?? '');
        $qty       = max(1, (int)($_POST['quantity'] ?? 1));

        $success = addToCart($productId, $size, $color, $qty);
        header('Content-Type: application/json');
        echo json_encode(['success' => $success, 'cart_count' => getCartCount(), 'message' => $success ? 'Added to cart' : 'Product not found']);
        exit;
    }

    if ($action === 'update') {
        $key = $_POST['key'] ?? '';
        $qty = (int)($_POST['quantity'] ?? 1);
        updateCartQuantity($key, $qty);
        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'cart_count' => getCartCount()]);
        exit;
    }

    if ($action === 'remove') {
        $key = $_POST['key'] ?? '';
        removeFromCart($key);
        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'cart_count' => getCartCount()]);
        exit;
    }
}

// GET actions (for direct links)
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $action = $_GET['action'] ?? '';
    if ($action === 'clear') {
        clearCart();
        unset($_SESSION['coupon_discount'], $_SESSION['coupon_code']);
        setFlash('info', 'Cart has been cleared.');
    }
}

header('Location: ' . SITE_URL . '/pages/cart.php');
exit;
?>
