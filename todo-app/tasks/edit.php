<?php
/**
 * tasks/edit.php
 * AJAX endpoint (POST, JSON response) to update an existing task.
 * Always scoped to WHERE id = ? AND user_id = ? so a user can
 * never edit another user's task, even by guessing IDs.
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
$taskId      = (int)($_POST['task_id'] ?? 0);
$title       = clean($_POST['title'] ?? '');
$description = clean($_POST['description'] ?? '');
$category    = clean($_POST['category'] ?? '') ?: 'General';
$tags        = clean($_POST['tags'] ?? '');
$priority    = $_POST['priority'] ?? 'medium';
$dueDate     = clean($_POST['due_date'] ?? '');

$errors = [];
if ($taskId <= 0) {
    $errors[] = 'Invalid task.';
}
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

// Ownership check baked directly into the UPDATE's WHERE clause
$stmt = $pdo->prepare(
    'UPDATE tasks
     SET title = ?, description = ?, category = ?, tags = ?, priority = ?, due_date = ?
     WHERE id = ? AND user_id = ?'
);
$stmt->execute([
    $title,
    $description ?: null,
    $category,
    $tags ?: null,
    $priority,
    $dueDate ?: null,
    $taskId,
    $userId,
]);

if ($stmt->rowCount() === 0) {
    // Either the task doesn't exist, doesn't belong to this user,
    // or nothing actually changed.
    $check = $pdo->prepare('SELECT id FROM tasks WHERE id = ? AND user_id = ?');
    $check->execute([$taskId, $userId]);
    if (!$check->fetch()) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Task not found.']);
        exit;
    }
}

logActivity($userId, $taskId, 'task_updated', 'Updated task "' . $title . '".');

echo json_encode(['success' => true, 'message' => 'Task updated successfully!']);
