<?php
/**
 * tasks/add.php
 * AJAX endpoint (POST, JSON response) to create a new task.
 */
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Please log in again.']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed.']);
    exit;
}

if (!csrf_verify($_POST['csrf_token'] ?? '')) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Invalid security token. Please refresh and try again.']);
    exit;
}

$userId      = currentUserId();
$title       = clean($_POST['title'] ?? '');
$description = clean($_POST['description'] ?? '');
$category    = clean($_POST['category'] ?? '') ?: 'General';
$tags        = clean($_POST['tags'] ?? '');
$priority    = $_POST['priority'] ?? 'medium';
$dueDate     = clean($_POST['due_date'] ?? '');

// ---- Validation ----
$errors = [];
if (mb_strlen($title) < 1 || mb_strlen($title) > 200) {
    $errors[] = 'Title is required and must be under 200 characters.';
}
if (!in_array($priority, ['low', 'medium', 'high'], true)) {
    $priority = 'medium';
}
if ($dueDate !== '' && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $dueDate)) {
    $errors[] = 'Invalid due date format.';
}

if (!empty($errors)) {
    http_response_code(422);
    echo json_encode(['success' => false, 'message' => implode(' ', $errors)]);
    exit;
}

$pdo = getDB();
$stmt = $pdo->prepare(
    'INSERT INTO tasks (user_id, title, description, category, tags, priority, due_date)
     VALUES (?, ?, ?, ?, ?, ?, ?)'
);
$stmt->execute([
    $userId,
    $title,
    $description ?: null,
    $category,
    $tags ?: null,
    $priority,
    $dueDate ?: null,
]);

$taskId = (int)$pdo->lastInsertId();
logActivity($userId, $taskId, 'task_created', 'Created task "' . $title . '".');

echo json_encode(['success' => true, 'message' => 'Task added successfully!', 'task_id' => $taskId]);
