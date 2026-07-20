<?php
/**
 * dashboard/index.php
 * Main dashboard: stats, completion chart, today's tasks,
 * and recent activity feed — all scoped to the logged-in user.
 */
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
requireLogin();

$pdo    = getDB();
$userId = currentUserId();

// ---- Fetch the user's avatar for the navbar ----
$stmt = $pdo->prepare('SELECT full_name, avatar FROM users WHERE id = ?');
$stmt->execute([$userId]);
$userRow    = $stmt->fetch();
$avatarPath = $userRow['avatar'] ?? null;

// ---- Stat counts (all scoped with user_id = ? — never trust a global query) ----
$stmt = $pdo->prepare('SELECT COUNT(*) FROM tasks WHERE user_id = ?');
$stmt->execute([$userId]);
$totalTasks = (int)$stmt->fetchColumn();

$stmt = $pdo->prepare("SELECT COUNT(*) FROM tasks WHERE user_id = ? AND status = 'completed'");
$stmt->execute([$userId]);
$completedTasks = (int)$stmt->fetchColumn();

$pendingTasks = $totalTasks - $completedTasks;
$completionPct = $totalTasks > 0 ? round(($completedTasks / $totalTasks) * 100) : 0;

// ---- Today's tasks ----
$stmt = $pdo->prepare(
    "SELECT id, title, priority, status, due_date
     FROM tasks
     WHERE user_id = ? AND due_date = CURDATE()
     ORDER BY FIELD(priority, 'high', 'medium', 'low'), status ASC
     LIMIT 8"
);
$stmt->execute([$userId]);
$todayTasks = $stmt->fetchAll();

// ---- Recent activity (last 6 events) ----
$stmt = $pdo->prepare(
    'SELECT action, description, created_at
     FROM activity_log
     WHERE user_id = ?
     ORDER BY created_at DESC
     LIMIT 6'
);
$stmt->execute([$userId]);
$recentActivity = $stmt->fetchAll();

$activityIcons = [
    'account_created' => 'fa-user-plus',
    'login'            => 'fa-right-to-bracket',
    'logout'           => 'fa-right-from-bracket',
    'task_created'     => 'fa-circle-plus',
    'task_completed'   => 'fa-circle-check',
    'task_updated'     => 'fa-pen',
    'task_deleted'     => 'fa-trash',
];

$pageTitle = 'Dashboard';
$activeNav = 'dashboard';
require __DIR__ . '/../includes/header.php';
require __DIR__ . '/../includes/sidebar.php';
?>
<main class="main-content">
    <?php require __DIR__ . '/../includes/navbar.php'; ?>
    <div class="page-content">

        <div class="page-header">
            <div>
                <h1>Welcome back, <?= e(explode(' ', $userRow['full_name'])[0]) ?> 👋</h1>
                <p>Here's what's happening with your tasks today.</p>
            </div>
            <a href="<?= BASE_URL ?>/tasks/index.php" class="btn btn-primary">
                <i class="fa-solid fa-plus"></i> Add New Task
            </a>
        </div>

        <!-- Stat Cards -->
        <div class="stats-grid">
            <div class="card stat-card">
                <div class="stat-icon total"><i class="fa-solid fa-list-check"></i></div>
                <div>
                    <div class="stat-value"><?= $totalTasks ?></div>
                    <div class="stat-label">Total Tasks</div>
                </div>
            </div>
            <div class="card stat-card">
                <div class="stat-icon completed"><i class="fa-solid fa-circle-check"></i></div>
                <div>
                    <div class="stat-value"><?= $completedTasks ?></div>
                    <div class="stat-label">Completed</div>
                </div>
            </div>
            <div class="card stat-card">
                <div class="stat-icon pending"><i class="fa-solid fa-hourglass-half"></i></div>
                <div>
                    <div class="stat-value"><?= $pendingTasks ?></div>
                    <div class="stat-label">Pending</div>
                </div>
            </div>
            <div class="card stat-card">
                <div class="stat-icon percent"><i class="fa-solid fa-chart-pie"></i></div>
                <div style="width:100%;">
                    <div class="stat-value"><?= $completionPct ?>%</div>
                    <div class="stat-label">Completion Rate</div>
                </div>
            </div>
        </div>

        <div style="display:grid; grid-template-columns: 1.3fr 1fr; gap:20px;" class="dashboard-grid">

            <!-- Left column -->
            <div style="display:flex; flex-direction:column; gap:20px;">

                <!-- Chart -->
                <div class="card dash-panel">
                    <div class="dash-panel-head">
                        <h3>Task Overview</h3>
                        <?php if ($totalTasks > 0): ?>
                            <span class="dash-panel-meta"><?= $completionPct ?>% complete</span>
                        <?php endif; ?>
                    </div>
                    <?php if ($totalTasks > 0): ?>
                        <div class="chart-container">
                            <canvas id="statusChart"></canvas>
                        </div>
                    <?php else: ?>
                        <div class="empty-state" style="padding:30px;">
                            <i class="fa-solid fa-chart-pie"></i>
                            <h3>No data yet</h3>
                            <p>Add your first task to see the chart come alive.</p>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Today's Tasks -->
                <div class="card dash-panel">
                    <div class="dash-panel-head">
                        <h3>Today's Tasks</h3>
                        <?php if (!empty($todayTasks)): ?>
                            <a href="<?= BASE_URL ?>/tasks/index.php?filter=today" class="dash-panel-link">View all <i class="fa-solid fa-arrow-right"></i></a>
                        <?php endif; ?>
                    </div>

                    <?php if (empty($todayTasks)): ?>
                        <div class="empty-state" style="padding:30px;">
                            <i class="fa-solid fa-mug-hot"></i>
                            <h3>Nothing due today</h3>
                            <p>Enjoy the calm, or get ahead on tomorrow's tasks.</p>
                        </div>
                    <?php else: ?>
                        <div class="dash-list">
                            <?php foreach ($todayTasks as $t): ?>
                                <div class="dash-list-row">
                                    <div class="dash-list-row-main">
                                        <i class="fa-solid <?= $t['status'] === 'completed' ? 'fa-circle-check' : 'fa-circle' ?>"
                                           style="color: <?= $t['status'] === 'completed' ? 'var(--color-success)' : 'var(--text-muted)' ?>;"></i>
                                        <span class="dash-list-title" style="<?= $t['status'] === 'completed' ? 'text-decoration:line-through; color:var(--text-muted);' : '' ?>">
                                            <?= e($t['title']) ?>
                                        </span>
                                    </div>
                                    <span class="badge badge-<?= e($t['priority']) ?>"><?= ucfirst(e($t['priority'])) ?></span>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Right column: Recent Activity -->
            <div class="card dash-panel" style="height:fit-content;">
                <div class="dash-panel-head">
                    <h3>Recent Activity</h3>
                </div>

                <?php if (empty($recentActivity)): ?>
                    <div class="empty-state" style="padding:30px;">
                        <i class="fa-solid fa-clock-rotate-left"></i>
                        <h3>No activity yet</h3>
                        <p>Your actions will show up here.</p>
                    </div>
                <?php else: ?>
                    <div class="dash-activity-list">
                        <?php foreach ($recentActivity as $act): ?>
                            <div class="dash-activity-row">
                                <div class="dash-activity-icon">
                                    <i class="fa-solid <?= e($activityIcons[$act['action']] ?? 'fa-circle-info') ?>"></i>
                                </div>
                                <div class="dash-activity-body">
                                    <div class="dash-activity-desc"><?= e($act['description']) ?></div>
                                    <div class="dash-activity-time">
                                        <?= e(date('M j, g:i A', strtotime($act['created_at']))) ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</main>

<style>
    @media (max-width: 1000px) {
        .dashboard-grid { grid-template-columns: 1fr !important; }
    }
</style>

<?php if ($totalTasks > 0): ?>
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.umd.min.js"></script>
<script>
    const ctx = document.getElementById('statusChart');
    new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: ['Completed', 'Pending'],
            datasets: [{
                data: [<?= $completedTasks ?>, <?= $pendingTasks ?>],
                backgroundColor: ['#00B894', '#6C5CE7'],
                borderWidth: 0,
                hoverOffset: 6,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: '68%',
            plugins: {
                legend: { position: 'bottom', labels: { color: getComputedStyle(document.body).color, usePointStyle: true, padding: 16, boxWidth: 8 } }
            }
        }
    });
</script>
<?php endif; ?>

<?php require __DIR__ . '/../includes/footer.php'; ?>
