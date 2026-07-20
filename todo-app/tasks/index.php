<?php
/**
 * tasks/index.php
 * Core task management page: list with search/filter/sort/pagination,
 * plus Add/Edit modal and Delete confirmation modal.
 * Actual CUD operations happen via AJAX against add.php/edit.php/
 * delete.php/complete.php (see tasks.js).
 */
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
requireLogin();

$pdo    = getDB();
$userId = currentUserId();

$stmt = $pdo->prepare('SELECT full_name, avatar FROM users WHERE id = ?');
$stmt->execute([$userId]);
$userRow    = $stmt->fetch();
$avatarPath = $userRow['avatar'] ?? null;

// ---- Read query params (search / filter / sort / page) ----
$search   = clean($_GET['q'] ?? '');
$filter   = clean($_GET['filter'] ?? 'all');       // all | pending | completed | high
$sort     = clean($_GET['sort'] ?? 'date_desc');   // date_desc | date_asc | priority
$page     = max(1, (int)($_GET['page'] ?? 1));
$perPage  = 8;
$offset   = ($page - 1) * $perPage;

// ---- Build the WHERE clause dynamically but safely (prepared params) ----
$where  = ['user_id = ?'];
$params = [$userId];

if ($search !== '') {
    $where[]  = '(title LIKE ? OR description LIKE ?)';
    $params[] = '%' . $search . '%';
    $params[] = '%' . $search . '%';
}

switch ($filter) {
    case 'pending':
        $where[] = "status = 'pending'";
        break;
    case 'completed':
        $where[] = "status = 'completed'";
        break;
    case 'high':
        $where[] = "priority = 'high'";
        break;
    case 'today':
        $where[] = 'due_date = CURDATE()';
        break;
}

$orderBy = match ($sort) {
    'date_asc'  => 'due_date ASC',
    'priority'  => "FIELD(priority, 'high', 'medium', 'low') ASC",
    default     => 'created_at DESC', // date_desc
};

$whereSql = implode(' AND ', $where);

// ---- Total count (for pagination) ----
$countStmt = $pdo->prepare("SELECT COUNT(*) FROM tasks WHERE $whereSql");
$countStmt->execute($params);
$totalCount = (int)$countStmt->fetchColumn();
$totalPages = max(1, (int)ceil($totalCount / $perPage));

// ---- Fetch this page's tasks ----
$sql = "SELECT * FROM tasks WHERE $whereSql ORDER BY $orderBy LIMIT $perPage OFFSET $offset";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$tasks = $stmt->fetchAll();

$pageTitle = 'My Tasks';
$activeNav = 'tasks';
$extraScript = BASE_URL . '/assets/js/tasks.js';
require __DIR__ . '/../includes/header.php';
require __DIR__ . '/../includes/sidebar.php';
?>
<main class="main-content">
    <?php require __DIR__ . '/../includes/navbar.php'; ?>
    <div class="page-content">

        <div class="page-header">
            <div>
                <h1>My Tasks</h1>
                <p><?= $totalCount ?> task<?= $totalCount === 1 ? '' : 's' ?> found</p>
            </div>
            <button class="btn btn-primary" id="openAddTaskBtn" onclick="openTaskModal()">
                <i class="fa-solid fa-plus"></i> Add Task <span style="opacity:.7; font-size:.75rem;">(N)</span>
            </button>
        </div>

        <!-- Filter / Sort toolbar -->
        <div class="card" style="padding:16px 18px; margin-bottom:20px; display:flex; flex-wrap:wrap; gap:12px; align-items:center; justify-content:space-between;">
            <div style="display:flex; gap:8px; flex-wrap:wrap;" id="filterTabs">
                <?php
                $tabs = ['all' => 'All', 'pending' => 'Pending', 'completed' => 'Completed', 'high' => 'High Priority'];
                foreach ($tabs as $key => $label):
                    $isActive = $filter === $key;
                ?>
                    <a href="?filter=<?= $key ?>&sort=<?= e($sort) ?>&q=<?= e($search) ?>"
                       class="btn btn-sm <?= $isActive ? 'btn-primary' : 'btn-outline' ?>"><?= $label ?></a>
                <?php endforeach; ?>
            </div>

            <form method="GET" class="filter-toolbar-form">
                <input type="hidden" name="filter" value="<?= e($filter) ?>">
                <div class="input-wrap" style="position:relative;">
                    <input type="text" name="q" id="taskSearchInput" value="<?= e($search) ?>" placeholder="Search tasks..."
                           style="padding:9px 12px 9px 34px; border-radius:var(--radius-md); border:1px solid var(--border-color); background:var(--bg-body); color:var(--text-main); font-size:0.87rem;">
                    <i class="fa-solid fa-magnifying-glass" style="position:absolute; left:12px; top:50%; transform:translateY(-50%); color:var(--text-muted); font-size:0.8rem;"></i>
                </div>
                <select name="sort" onchange="this.form.submit()" style="padding:9px 12px; border-radius:var(--radius-md); border:1px solid var(--border-color); background:var(--bg-body); color:var(--text-main); font-size:0.85rem;">
                    <option value="date_desc" <?= $sort === 'date_desc' ? 'selected' : '' ?>>Newest First</option>
                    <option value="date_asc" <?= $sort === 'date_asc' ? 'selected' : '' ?>>Due Date ↑</option>
                    <option value="priority" <?= $sort === 'priority' ? 'selected' : '' ?>>Priority</option>
                </select>
            </form>
        </div>

        <!-- Task list -->
        <div class="card" style="padding:10px;">
            <?php if (empty($tasks)): ?>
                <div class="empty-state">
                    <i class="fa-solid fa-clipboard-list"></i>
                    <h3>No tasks found</h3>
                    <p><?= $search !== '' ? 'Try a different search term.' : 'Add your first task to get started!' ?></p>
                </div>
            <?php else: ?>
                <div id="taskList" style="display:flex; flex-direction:column; gap:8px; padding:8px;">
                    <?php foreach ($tasks as $task): ?>
                        <?php include __DIR__ . '/_task_item.php'; ?>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- Pagination -->
        <?php if ($totalPages > 1): ?>
            <div style="display:flex; justify-content:center; gap:6px; margin-top:20px;">
                <?php for ($p = 1; $p <= $totalPages; $p++): ?>
                    <a href="?page=<?= $p ?>&filter=<?= e($filter) ?>&sort=<?= e($sort) ?>&q=<?= e($search) ?>"
                       class="btn btn-sm <?= $p === $page ? 'btn-primary' : 'btn-outline' ?>"><?= $p ?></a>
                <?php endfor; ?>
            </div>
        <?php endif; ?>

    </div>
</main>

<!-- Add / Edit Task Modal -->
<div class="modal-overlay" id="taskModalOverlay">
    <div class="modal-box" style="max-width:460px; text-align:left;">
        <h3 id="taskModalTitle" style="margin-bottom:18px;">Add New Task</h3>
        <form id="taskForm">
            <?= csrf_field() ?>
            <input type="hidden" name="task_id" id="task_id">

            <div class="form-group" style="margin-bottom:14px;">
                <label style="font-size:0.85rem; color:var(--text-muted); display:block; margin-bottom:5px;">Title *</label>
                <input type="text" name="title" id="taskTitle" required maxlength="200"
                       style="width:100%; padding:10px 12px; border-radius:var(--radius-md); border:1px solid var(--border-color); background:var(--bg-body); color:var(--text-main);">
            </div>

            <div class="form-group" style="margin-bottom:14px;">
                <label style="font-size:0.85rem; color:var(--text-muted); display:block; margin-bottom:5px;">Description</label>
                <textarea name="description" id="taskDescription" rows="3"
                       style="width:100%; padding:10px 12px; border-radius:var(--radius-md); border:1px solid var(--border-color); background:var(--bg-body); color:var(--text-main); font-family:inherit; resize:vertical;"></textarea>
            </div>

            <div class="form-grid-2" style="margin-bottom:14px;">
                <div class="form-group">
                    <label style="font-size:0.85rem; color:var(--text-muted); display:block; margin-bottom:5px;">Priority</label>
                    <select name="priority" id="taskPriority" style="width:100%; padding:10px 12px; border-radius:var(--radius-md); border:1px solid var(--border-color); background:var(--bg-body); color:var(--text-main);">
                        <option value="low">Low</option>
                        <option value="medium" selected>Medium</option>
                        <option value="high">High</option>
                    </select>
                </div>
                <div class="form-group">
                    <label style="font-size:0.85rem; color:var(--text-muted); display:block; margin-bottom:5px;">Due Date</label>
                    <input type="date" name="due_date" id="taskDueDate"
                       style="width:100%; padding:10px 12px; border-radius:var(--radius-md); border:1px solid var(--border-color); background:var(--bg-body); color:var(--text-main);">
                </div>
            </div>

            <div class="form-grid-2" style="margin-bottom:20px;">
                <div class="form-group">
                    <label style="font-size:0.85rem; color:var(--text-muted); display:block; margin-bottom:5px;">Category</label>
                    <input type="text" name="category" id="taskCategory" placeholder="General"
                       style="width:100%; padding:10px 12px; border-radius:var(--radius-md); border:1px solid var(--border-color); background:var(--bg-body); color:var(--text-main);">
                </div>
                <div class="form-group">
                    <label style="font-size:0.85rem; color:var(--text-muted); display:block; margin-bottom:5px;">Tags (comma-separated)</label>
                    <input type="text" name="tags" id="taskTags" placeholder="urgent, work"
                       style="width:100%; padding:10px 12px; border-radius:var(--radius-md); border:1px solid var(--border-color); background:var(--bg-body); color:var(--text-main);">
                </div>
            </div>

            <div class="modal-actions">
                <button type="button" class="btn btn-outline" onclick="closeTaskModal()">Cancel</button>
                <button type="submit" class="btn btn-primary" id="taskSubmitBtn">Save Task</button>
            </div>
        </form>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal-overlay" id="deleteModalOverlay">
    <div class="modal-box">
        <i class="fa-solid fa-triangle-exclamation modal-icon"></i>
        <h3>Delete this task?</h3>
        <p style="color:var(--text-muted); font-size:0.88rem; margin-top:6px;">This action cannot be undone.</p>
        <input type="hidden" id="deleteTaskId">
        <div class="modal-actions">
            <button class="btn btn-outline" onclick="closeDeleteModal()">Cancel</button>
            <button class="btn btn-danger" onclick="confirmDeleteTask()">Delete</button>
        </div>
    </div>
</div>

<?php require __DIR__ . '/../includes/footer.php'; ?>
