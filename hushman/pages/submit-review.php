<?php
// ============================================================
// pages/submit-review.php — Review Submission Handler
// ============================================================
require_once __DIR__ . '/../includes/functions.php';
requireLogin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . SITE_URL);
    exit;
}

$productId = (int)($_POST['product_id'] ?? 0);
$rating    = (int)($_POST['rating']     ?? 0);
$comment   = trim($_POST['comment']     ?? '');

if (!$productId || $rating < 1 || $rating > 5 || empty($comment)) {
    setFlash('error', 'Please fill in all review fields.');
    header('Location: ' . $_SERVER['HTTP_REFERER'] ?? SITE_URL);
    exit;
}

// Check if user already reviewed this product
$db   = getDB();
$chk  = $db->prepare("SELECT id FROM reviews WHERE product_id = ? AND user_id = ?");
$chk->execute([$productId, $_SESSION['user_id']]);
if ($chk->fetch()) {
    setFlash('error', 'You have already reviewed this product.');
    header('Location: ' . $_SERVER['HTTP_REFERER'] ?? SITE_URL);
    exit;
}

$stmt = $db->prepare("INSERT INTO reviews (product_id, user_id, rating, comment) VALUES (?, ?, ?, ?)");
$stmt->execute([$productId, $_SESSION['user_id'], $rating, $comment]);

setFlash('success', 'Your review has been submitted successfully!');
header('Location: ' . $_SERVER['HTTP_REFERER'] ?? SITE_URL);
exit;
