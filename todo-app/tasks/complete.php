<?php
/**
 * tasks/complete.php
 * AJAX endpoint (POST, JSON response) to toggle a task between
 * 'pending' and 'completed'.
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

$userId = currentUserId();
$taskId = (int)($_POST['task_id'] ?? 0);

if ($taskId <= 0) {
    http_response_code(422);
    echo json_encode(['success' => false, 'message' => 'Invalid task.']);
    exit;
}

$pdo = getDB();

$stmt = $pdo->prepare('SELECT title, status FROM tasks WHERE id = ? AND user_id = ?');
$stmt->execute([$taskId, $userId]);
$task = $stmt->fetch();

if (!$task) {
    http_response_code(404);
    echo json_encode(['success' => false, 'message' => 'Task not found.']);
    exit;
}

$newStatus = $task['status'] === 'completed' ? 'pending' : 'completed';
$completedAt = $newStatus === 'completed' ? date('Y-m-d H:i:s') : null;

$upd = $pdo->prepare('UPDATE tasks SET status = ?, completed_at = ? WHERE id = ? AND user_id = ?');
$upd->execute([$newStatus, $completedAt, $taskId, $userId]);

if ($newStatus === 'completed') {
    logActivity($userId, $taskId, 'task_completed', 'Completed task "' . $task['title'] . '".');
}

echo json_encode(['success' => true, 'status' => $newStatus]);
