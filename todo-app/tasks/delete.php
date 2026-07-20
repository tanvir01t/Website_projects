<?php
/**
 * tasks/delete.php
 * AJAX endpoint (POST, JSON response) to delete a task.
 * Ownership enforced via WHERE id = ? AND user_id = ?.
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

// Grab the title first (for the activity log) while confirming ownership
$stmt = $pdo->prepare('SELECT title FROM tasks WHERE id = ? AND user_id = ?');
$stmt->execute([$taskId, $userId]);
$task = $stmt->fetch();

if (!$task) {
    http_response_code(404);
    echo json_encode(['success' => false, 'message' => 'Task not found.']);
    exit;
}

$del = $pdo->prepare('DELETE FROM tasks WHERE id = ? AND user_id = ?');
$del->execute([$taskId, $userId]);

logActivity($userId, null, 'task_deleted', 'Deleted task "' . $task['title'] . '".');

echo json_encode(['success' => true, 'message' => 'Task deleted.']);
