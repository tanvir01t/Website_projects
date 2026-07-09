<?php
// ============================================================
// includes/functions.php — Helper Functions
// ============================================================

require_once __DIR__ . '/../config/db.php';

// Start session safely
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ── Authentication ──────────────────────────────────────────

function isLoggedIn() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

function isAdminLoggedIn() {
    return isset($_SESSION['admin_id']) && !empty($_SESSION['admin_id']);
}

function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: ' . SITE_URL . '/pages/login.php?redirect=' . urlencode($_SERVER['REQUEST_URI']));
        exit;
    }
}

function requireAdminLogin() {
    if (!isAdminLoggedIn()) {
        header('Location: ' . SITE_URL . '/admin/login.php');
        exit;
    }
}

function getCurrentUser() {
    if (!isLoggedIn()) return null;
    $db = getDB();
    $stmt = $db->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    return $stmt->fetch();
}

// ── Products ────────────────────────────────────────────────

function getFeaturedProducts($limit = 8) {
    $db = getDB();
    $stmt = $db->prepare("
        SELECT p.*, c.name as category_name,
               (p.price - (p.price * p.discount_percent / 100)) as sale_price
        FROM products p
        JOIN categories c ON p.category_id = c.id
        WHERE p.is_featured = 1 AND p.is_active = 1
        ORDER BY p.created_at DESC
        LIMIT ?
    ");
    $stmt->execute([$limit]);
    return $stmt->fetchAll();
}

function getProductBySlug($slug) {
    $db = getDB();
    $stmt = $db->prepare("
        SELECT p.*, c.name as category_name, c.slug as category_slug,
               (p.price - (p.price * p.discount_percent / 100)) as sale_price
        FROM products p
        JOIN categories c ON p.category_id = c.id
        WHERE p.slug = ? AND p.is_active = 1
    ");
    $stmt->execute([$slug]);
    return $stmt->fetch();
}

function getProductById($id) {
    $db = getDB();
    $stmt = $db->prepare("
        SELECT p.*, c.name as category_name,
               (p.price - (p.price * p.discount_percent / 100)) as sale_price
        FROM products p
        JOIN categories c ON p.category_id = c.id
        WHERE p.id = ? AND p.is_active = 1
    ");
    $stmt->execute([$id]);
    return $stmt->fetch();
}

function getProducts($categorySlug = null, $search = null, $page = 1, $perPage = 12) {
    $db = getDB();
    $offset = ($page - 1) * $perPage;
    $params = [];
    $where = "WHERE p.is_active = 1";

    if ($categorySlug) {
        $where .= " AND c.slug = ?";
        $params[] = $categorySlug;
    }
    if ($search) {
        $where .= " AND (p.name LIKE ? OR p.description LIKE ?)";
        $params[] = "%$search%";
        $params[] = "%$search%";
    }

    $countStmt = $db->prepare("SELECT COUNT(*) FROM products p JOIN categories c ON p.category_id = c.id $where");
    $countStmt->execute($params);
    $total = $countStmt->fetchColumn();

    $params[] = $perPage;
    $params[] = $offset;
    $stmt = $db->prepare("
        SELECT p.*, c.name as category_name, c.slug as category_slug,
               (p.price - (p.price * p.discount_percent / 100)) as sale_price
        FROM products p
        JOIN categories c ON p.category_id = c.id
        $where
        ORDER BY p.created_at DESC
        LIMIT ? OFFSET ?
    ");
    $stmt->execute($params);

    return ['products' => $stmt->fetchAll(), 'total' => $total, 'pages' => ceil($total / $perPage)];
}

function getCategories() {
    $db = getDB();
    $stmt = $db->query("SELECT c.*, COUNT(p.id) as product_count
        FROM categories c LEFT JOIN products p ON c.id = p.category_id AND p.is_active = 1
        WHERE c.is_active = 1 GROUP BY c.id ORDER BY c.name");
    return $stmt->fetchAll();
}

function getProductReviews($productId) {
    $db = getDB();
    $stmt = $db->prepare("
        SELECT r.*, u.name as user_name
        FROM reviews r JOIN users u ON r.user_id = u.id
        WHERE r.product_id = ? AND r.is_approved = 1
        ORDER BY r.created_at DESC
    ");
    $stmt->execute([$productId]);
    return $stmt->fetchAll();
}

function getAverageRating($productId) {
    $db = getDB();
    $stmt = $db->prepare("SELECT AVG(rating) as avg, COUNT(*) as count FROM reviews WHERE product_id = ? AND is_approved = 1");
    $stmt->execute([$productId]);
    return $stmt->fetch();
}

// ── Cart ────────────────────────────────────────────────────

function getCartItems() {
    return $_SESSION['cart'] ?? [];
}

function getCartCount() {
    $cart = getCartItems();
    return array_sum(array_column($cart, 'quantity'));
}

function getCartTotal() {
    $cart = getCartItems();
    $total = 0;
    foreach ($cart as $item) {
        $total += $item['sale_price'] * $item['quantity'];
    }
    return $total;
}

function addToCart($productId, $size, $color, $quantity = 1) {
    $product = getProductById($productId);
    if (!$product) return false;

    $key = $productId . '_' . $size . '_' . $color;
    if (isset($_SESSION['cart'][$key])) {
        $_SESSION['cart'][$key]['quantity'] += $quantity;
    } else {
        $_SESSION['cart'][$key] = [
            'product_id' => $productId,
            'name'       => $product['name'],
            'image'      => $product['image'],
            'price'      => $product['price'],
            'sale_price' => $product['sale_price'],
            'size'       => $size,
            'color'      => $color,
            'quantity'   => $quantity,
        ];
    }
    return true;
}

function removeFromCart($key) {
    unset($_SESSION['cart'][$key]);
}

function updateCartQuantity($key, $quantity) {
    if ($quantity <= 0) {
        removeFromCart($key);
    } else {
        $_SESSION['cart'][$key]['quantity'] = $quantity;
    }
}

function clearCart() {
    $_SESSION['cart'] = [];
}

// ── Orders ──────────────────────────────────────────────────

function generateOrderNumber() {
    return 'MS-' . date('Y') . '-' . str_pad(mt_rand(1, 99999), 5, '0', STR_PAD_LEFT);
}

function getUserOrders($userId) {
    $db = getDB();
    $stmt = $db->prepare("SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC");
    $stmt->execute([$userId]);
    return $stmt->fetchAll();
}

function getOrderDetails($orderId, $userId = null) {
    $db = getDB();
    $sql = "SELECT * FROM orders WHERE id = ?";
    $params = [$orderId];
    if ($userId) { $sql .= " AND user_id = ?"; $params[] = $userId; }
    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    $order = $stmt->fetch();
    if (!$order) return null;

    $stmt2 = $db->prepare("SELECT * FROM order_items WHERE order_id = ?");
    $stmt2->execute([$orderId]);
    $order['items'] = $stmt2->fetchAll();
    return $order;
}

function applyCoupon($code, $total) {
    $db = getDB();
    $stmt = $db->prepare("SELECT * FROM coupons WHERE code = ? AND is_active = 1 AND (expires_at IS NULL OR expires_at >= CURDATE()) AND used_count < max_uses");
    $stmt->execute([$code]);
    $coupon = $stmt->fetch();
    if (!$coupon) return ['error' => 'Invalid or expired coupon code'];
    if ($total < $coupon['min_order']) return ['error' => 'Minimum order amount is ' . CURRENCY . $coupon['min_order']];

    $discount = ($coupon['discount_type'] === 'percent')
        ? ($total * $coupon['discount_value'] / 100)
        : $coupon['discount_value'];
    return ['success' => true, 'discount' => $discount, 'coupon' => $coupon];
}

// ── Formatting ──────────────────────────────────────────────

function formatPrice($price) {
    return CURRENCY . number_format($price, 2);
}

function formatDate($date) {
    return date('d M Y, h:i A', strtotime($date));
}

function sanitize($input) {
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

function getStatusBadge($status) {
    $badges = [
        'pending'    => 'warning',
        'processing' => 'info',
        'shipped'    => 'primary',
        'delivered'  => 'success',
        'cancelled'  => 'danger',
        'paid'       => 'success',
        'failed'     => 'danger',
    ];
    return $badges[$status] ?? 'secondary';
}

function productImageUrl($image) {
    if ($image && file_exists(__DIR__ . '/../assets/images/products/' . $image)) {
        return SITE_URL . '/assets/images/products/' . $image;
    }
    return SITE_URL . '/assets/images/placeholder.jpg';
}

function setFlash($type, $message) {
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

function getFlash() {
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}

// ── Admin Stats ─────────────────────────────────────────────

function getAdminStats() {
    $db = getDB();
    $stats = [];
    $stats['total_orders']   = $db->query("SELECT COUNT(*) FROM orders")->fetchColumn();
    $stats['total_users']    = $db->query("SELECT COUNT(*) FROM users")->fetchColumn();
    $stats['total_products'] = $db->query("SELECT COUNT(*) FROM products WHERE is_active = 1")->fetchColumn();
    $stats['total_revenue']  = $db->query("SELECT COALESCE(SUM(final_amount),0) FROM orders WHERE order_status = 'delivered'")->fetchColumn();
    $stats['pending_orders'] = $db->query("SELECT COUNT(*) FROM orders WHERE order_status = 'pending'")->fetchColumn();
    $stats['low_stock']      = $db->query("SELECT COUNT(*) FROM products WHERE stock <= 5 AND is_active = 1")->fetchColumn();
    return $stats;
}
?>
