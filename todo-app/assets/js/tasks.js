/**
 * assets/js/tasks.js
 * Powers tasks/index.php: Add/Edit modal, Delete confirmation modal,
 * AJAX calls to add.php / edit.php / delete.php / complete.php,
 * and debounced live search.
 */

const csrfToken = () => document.querySelector('input[name="csrf_token"]').value;

/* ---------------- Add / Edit modal ---------------- */

function openTaskModal(task = null) {
    const overlay = document.getElementById('taskModalOverlay');
    const form = document.getElementById('taskForm');
    form.reset();
    document.querySelectorAll('#taskForm .field-error').forEach(el => el.remove());

    if (task) {
        document.getElementById('taskModalTitle').textContent = 'Edit Task';
        document.getElementById('task_id').value = task.id;
        document.getElementById('taskTitle').value = task.title;
        document.getElementById('taskDescription').value = task.description || '';
        document.getElementById('taskPriority').value = task.priority;
        document.getElementById('taskDueDate').value = task.due_date || '';
        document.getElementById('taskCategory').value = task.category || '';
        document.getElementById('taskTags').value = task.tags || '';
    } else {
        document.getElementById('taskModalTitle').textContent = 'Add New Task';
        document.getElementById('task_id').value = '';
    }

    overlay.classList.add('show');
    setTimeout(() => document.getElementById('taskTitle').focus(), 50);
}

function closeTaskModal() {
    document.getElementById('taskModalOverlay').classList.remove('show');
}

document.getElementById('taskModalOverlay')?.addEventListener('click', (e) => {
    if (e.target.id === 'taskModalOverlay') closeTaskModal();
});

document.getElementById('taskForm')?.addEventListener('submit', async (e) => {
    e.preventDefault();

    const titleInput = document.getElementById('taskTitle');
    if (titleInput.value.trim().length === 0) {
        titleInput.style.borderColor = 'var(--color-error)';
        showToast('error', 'Task title is required.');
        return;
    }

    const taskId = document.getElementById('task_id').value;
    const endpoint = (window.BASE_URL || '') + (taskId ? '/tasks/edit.php' : '/tasks/add.php');

    const formData = new FormData(e.target);
    const submitBtn = document.getElementById('taskSubmitBtn');
    submitBtn.disabled = true;
    submitBtn.textContent = 'Saving...';

    try {
        const res = await fetch(endpoint, { method: 'POST', body: formData });
        const data = await res.json();

        if (data.success) {
            showToast('success', data.message);
            closeTaskModal();
            setTimeout(() => window.location.reload(), 500);
        } else {
            showToast('error', data.message || 'Something went wrong.');
        }
    } catch (err) {
        showToast('error', 'Network error. Please try again.');
    } finally {
        submitBtn.disabled = false;
        submitBtn.textContent = 'Save Task';
    }
});

/* ---------------- Delete modal ---------------- */

function openDeleteModal(taskId) {
    document.getElementById('deleteTaskId').value = taskId;
    document.getElementById('deleteModalOverlay').classList.add('show');
}

function closeDeleteModal() {
    document.getElementById('deleteModalOverlay').classList.remove('show');
}

document.getElementById('deleteModalOverlay')?.addEventListener('click', (e) => {
    if (e.target.id === 'deleteModalOverlay') closeDeleteModal();
});

async function confirmDeleteTask() {
    const taskId = document.getElementById('deleteTaskId').value;
    const formData = new FormData();
    formData.append('task_id', taskId);
    formData.append('csrf_token', csrfToken());

    try {
        const res = await fetch((window.BASE_URL || '') + '/tasks/delete.php', { method: 'POST', body: formData });
        const data = await res.json();

        if (data.success) {
            showToast('success', data.message);
            const row = document.querySelector(`.task-item[data-task-id="${taskId}"]`);
            row?.remove();
            closeDeleteModal();
        } else {
            showToast('error', data.message || 'Could not delete task.');
        }
    } catch (err) {
        showToast('error', 'Network error. Please try again.');
    }
}

/* ---------------- Toggle complete ---------------- */

async function toggleTaskComplete(taskId, btnEl) {
    const formData = new FormData();
    formData.append('task_id', taskId);
    formData.append('csrf_token', csrfToken());

    try {
        const res = await fetch((window.BASE_URL || '') + '/tasks/complete.php', { method: 'POST', body: formData });
        const data = await res.json();

        if (data.success) {
            const completed = data.status === 'completed';
            const row = btnEl.closest('.task-item');
            const titleEl = row.querySelector('.task-title');

            btnEl.style.background = completed ? 'var(--color-success)' : 'transparent';
            btnEl.style.borderColor = completed ? 'var(--color-success)' : 'var(--border-color)';
            btnEl.querySelector('i').style.display = completed ? 'block' : 'none';
            titleEl.style.textDecoration = completed ? 'line-through' : 'none';
            titleEl.style.color = completed ? 'var(--text-muted)' : '';

            showToast('success', completed ? 'Task completed! 🎉' : 'Marked as pending.');
        } else {
            showToast('error', data.message || 'Could not update task.');
        }
    } catch (err) {
        showToast('error', 'Network error. Please try again.');
    }
}

/* ---------------- Live search (debounced) ---------------- */

const taskSearchInput = document.getElementById('taskSearchInput');
if (taskSearchInput) {
    let debounceTimer;
    taskSearchInput.addEventListener('input', () => {
        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(() => {
            const params = new URLSearchParams(window.location.search);
            params.set('q', taskSearchInput.value.trim());
            params.delete('page');
            window.location.search = params.toString();
        }, 600);
    });
}
