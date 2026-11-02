<?php

require_once __DIR__ . '/../app/auth.php';
requireLogin();

require_once __DIR__ . '/../app/tasks.php';

$tasks = [];
$error = '';
$counts = ['pending' => 0, 'in_progress' => 0, 'completed' => 0];
$statusLabels = ['pending' => 'Pending', 'in_progress' => 'In Progress', 'completed' => 'Completed'];

try {
    $tasks = getUserTasks((int) $_SESSION['user_id']);

    foreach ($tasks as $task) {
        $counts[$task['status']]++;
    }
} catch (RuntimeException $exception) {
    error_log('Dashboard database error: ' . $exception->getMessage());
    http_response_code(503);
    $error = 'Unable to load your tasks right now. Please try again later.';
}

$filters = [
    'all' => ['label' => 'All tasks', 'count' => count($tasks)],
    'pending' => ['label' => 'Pending', 'count' => $counts['pending']],
    'in_progress' => ['label' => 'In Progress', 'count' => $counts['in_progress']],
    'completed' => ['label' => 'Completed', 'count' => $counts['completed']],
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard | Task Manager</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <script src="assets/js/app.js" defer></script>
</head>
<body>
    <main class="dashboard-container" id="task-dashboard">
        <header class="dashboard-header">
            <div>
                <p class="app-name">Task Manager</p>
                <h1>Welcome, <?= escape($_SESSION['user_name']) ?></h1>
            </div>
            <form action="logout.php" method="post">
                <input type="hidden" name="csrf_token" value="<?= escape(csrfToken()) ?>">
                <button class="button-secondary" type="submit">Log out</button>
            </form>
        </header>
        <?php if ($error !== ''): ?>
            <p class="error" role="alert"><?= escape($error) ?></p>
        <?php else: ?>
            <section class="card" aria-labelledby="tasks-heading">
                <h2 id="tasks-heading">Your tasks</h2>

                <div class="task-stats" role="group" aria-label="Filter tasks by status" hidden>
                    <?php foreach ($filters as $status => $filter): ?>
                        <button type="button" class="status-filter status-<?= escape($status) ?>" data-status="<?= escape($status) ?>" aria-pressed="<?= $status === 'all' ? 'true' : 'false' ?>">
                            <?= escape($filter['label']) ?> (<?= $filter['count'] ?>)
                        </button>
                    <?php endforeach; ?>
                </div>

                <div class="task-toolbar" hidden>
                    <div class="search-field">
                        <label for="task-search">Search by title</label>
                        <input type="search" id="task-search" placeholder="Search tasks…" autocomplete="off">
                    </div>
                    <div>
                        <label for="priority-filter">Priority</label>
                        <select id="priority-filter">
                            <option value="all">All priorities</option>
                            <option value="low">Low</option>
                            <option value="medium">Medium</option>
                            <option value="high">High</option>
                        </select>
                    </div>
                    <div>
                        <label for="task-sort">Sort by</label>
                        <select id="task-sort">
                            <option value="newest">Newest first</option>
                            <option value="oldest">Oldest first</option>
                            <option value="due">Due date: nearest first</option>
                            <option value="priority">Priority: high to low</option>
                            <option value="title">Title: A–Z</option>
                        </select>
                    </div>
                    <button type="button" class="button-secondary clear-filters" id="clear-filters">Reset</button>
                </div>

                <p id="task-results" class="task-results" role="status" aria-live="polite"><?= count($tasks) ?> tasks</p>
                <noscript><p>Enable JavaScript to search, filter, and sort. All your tasks are listed below.</p></noscript>

                <ul class="task-list" id="task-list">
                    <?php foreach ($tasks as $task): ?>
                        <li class="task-item status-<?= escape($task['status']) ?>" data-title="<?= escape($task['title']) ?>" data-status="<?= escape($task['status']) ?>" data-priority="<?= escape($task['priority']) ?>" data-due="<?= escape($task['due_date'] ?? '') ?>" data-created="<?= escape($task['created_at']) ?>" data-id="<?= (int) $task['id'] ?>">
                            <div class="task-content">
                                <h3 class="task-title"><?= escape($task['title']) ?></h3>
                                <p class="task-description"><?= $task['description'] !== '' ? escape($task['description']) : 'No description' ?></p>
                                <div class="task-meta">
                                    <span class="badge priority-<?= escape($task['priority']) ?>"><?= escape(ucfirst($task['priority'])) ?> priority</span>
                                    <span class="task-date">
                                        <?php if ($task['due_date'] !== null): ?>
                                            Due: <time datetime="<?= escape($task['due_date']) ?>"><?= escape((new DateTimeImmutable($task['due_date']))->format('M j, Y')) ?></time>
                                        <?php else: ?>
                                            No due date
                                        <?php endif; ?>
                                    </span>
                                </div>
                            </div>
                            <span class="badge task-status">
                                <?= escape($statusLabels[$task['status']]) ?>
                            </span>
                        </li>
                    <?php endforeach; ?>
                </ul>
                <div class="empty-state" id="task-empty" <?= $tasks !== [] ? 'hidden' : '' ?>>
                    <p><strong id="empty-heading">No tasks yet</strong></p>
                    <p id="empty-description">Your tasks will appear here.</p>
                </div>
            </section>
        <?php endif; ?>
    </main>
</body>
</html>
