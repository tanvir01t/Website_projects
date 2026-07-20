<?php
/**
 * tasks/_task_item.php
 * Renders a single task row. Included both by tasks/index.php
 * (initial page load) and reusable if you later want AJAX-inserted
 * rows to share the exact same markup.
 * Expects $task (assoc array) in scope.
 */
$isCompleted = $task['status'] === 'completed';
$dueLabel    = $task['due_date'] ? date('M j', strtotime($task['due_date'])) : null;
$isOverdue   = $task['due_date'] && !$isCompleted && strtotime($task['due_date']) < strtotime('today');
?>
<div class="task-item" data-task-id="<?= (int)$task['id'] ?>"
     style="display:flex; align-items:flex-start; gap:12px; padding:14px; border-radius:var(--radius-md); border:1px solid var(--border-color); transition:background 0.2s ease;">

    <button class="task-check-btn" onclick="toggleTaskComplete(<?= (int)$task['id'] ?>, this)"
            style="width:22px; height:22px; border-radius:50%; border:2px solid <?= $isCompleted ? 'var(--color-success)' : 'var(--border-color)' ?>; background:<?= $isCompleted ? 'var(--color-success)' : 'transparent' ?>; display:flex; align-items:center; justify-content:center; cursor:pointer; flex-shrink:0; margin-top:2px;">
        <i class="fa-solid fa-check" style="font-size:0.65rem; color:#fff; <?= $isCompleted ? '' : 'display:none;' ?>"></i>
    </button>

    <div style="flex:1; min-width:0;">
        <div style="display:flex; align-items:center; gap:10px; flex-wrap:wrap;">
            <span class="task-title" style="font-weight:500; <?= $isCompleted ? 'text-decoration:line-through; color:var(--text-muted);' : '' ?>">
                <?= e($task['title']) ?>
            </span>
            <span class="badge badge-<?= e($task['priority']) ?>"><?= ucfirst(e($task['priority'])) ?></span>
            <?php if (!empty($task['category']) && $task['category'] !== 'General'): ?>
                <span class="badge" style="background:var(--bg-body); color:var(--text-muted);"><?= e($task['category']) ?></span>
            <?php endif; ?>
        </div>

        <?php if (!empty($task['description'])): ?>
            <p style="font-size:0.82rem; color:var(--text-muted); margin-top:4px;"><?= e(mb_strimwidth($task['description'], 0, 120, '…')) ?></p>
        <?php endif; ?>

        <div style="display:flex; align-items:center; gap:14px; margin-top:8px; font-size:0.76rem; color:var(--text-muted);">
            <?php if ($dueLabel): ?>
                <span style="<?= $isOverdue ? 'color:var(--color-error); font-weight:600;' : '' ?>">
                    <i class="fa-regular fa-calendar"></i> <?= e($dueLabel) ?> <?= $isOverdue ? '(Overdue)' : '' ?>
                </span>
            <?php endif; ?>
            <?php if (!empty($task['tags'])): ?>
                <span><i class="fa-solid fa-tags"></i> <?= e($task['tags']) ?></span>
            <?php endif; ?>
        </div>
    </div>

    <div style="display:flex; gap:6px; flex-shrink:0;">
        <button class="icon-btn" style="width:32px; height:32px;"
                onclick='openTaskModal(<?= json_encode($task, JSON_UNESCAPED_UNICODE) ?>)' title="Edit">
            <i class="fa-solid fa-pen" style="font-size:0.75rem;"></i>
        </button>
        <button class="icon-btn" style="width:32px; height:32px;"
                onclick="openDeleteModal(<?= (int)$task['id'] ?>)" title="Delete">
            <i class="fa-solid fa-trash" style="font-size:0.75rem;"></i>
        </button>
    </div>
</div>
