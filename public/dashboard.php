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
    <link rel="stylesheet" href="assets/css/dashboard.css">
    <link rel="stylesheet" href="assets/css/task-form.css">
    <script type="module" src="assets/js/dashboard.js"></script>
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
                <div class="task-section-header">
                    <h2 id="tasks-heading">Your tasks</h2>
                    <button type="button" id="open-task-form" hidden>Add a task</button>
                </div>

                <dialog class="task-dialog" id="task-dialog" aria-labelledby="task-form-heading">
                    <div class="task-dialog-header">
                        <h2 id="task-form-heading">Add a task</h2>
                    </div>
                    <form id="task-form" action="api/tasks.php" method="post">
                        <input type="hidden" name="csrf_token" value="<?= escape(csrfToken()) ?>">
                        <input type="hidden" name="task_id" value="">
                        <p class="error" id="task-form-error" role="alert" hidden></p>

                        <div class="form-field">
                            <label for="task-title">Title</label>
                            <input id="task-title" name="title" required maxlength="255" aria-describedby="title-error" autofocus>
                            <small class="field-error" id="title-error"></small>
                        </div>
                        <div class="form-field">
                            <label for="task-description">Description (optional)</label>
                            <textarea id="task-description" name="description" rows="3" maxlength="5000" aria-describedby="description-error"></textarea>
                            <small class="field-error" id="description-error"></small>
                        </div>
                        <div class="task-form-options">
                            <div class="form-field">
                                <label for="task-priority">Priority</label>
                                <select id="task-priority" name="priority" required aria-describedby="priority-error">
                                    <option value="low">Low</option>
                                    <option value="medium" selected>Medium</option>
                                    <option value="high">High</option>
                                </select>
                                <small class="field-error" id="priority-error"></small>
                            </div>
                            <div class="form-field">
                                <label for="task-status">Status</label>
                                <select id="task-status" name="status" required aria-describedby="status-error">
                                    <option value="pending">Pending</option>
                                    <option value="in_progress">In Progress</option>
                                    <option value="completed">Completed</option>
                                </select>
                                <small class="field-error" id="status-error"></small>
                            </div>
                            <div class="form-field">
                                <label for="task-due-date">Due date (optional)</label>
                                <input type="date" id="task-due-date" name="due_date" min="1000-01-01" max="9999-12-31" aria-describedby="due_date-error">
                                <small class="field-error" id="due_date-error"></small>
                            </div>
                        </div>
                        <div class="task-form-actions">
                            <button type="submit">Save task</button>
                            <button type="button" class="button-secondary" id="close-task-form">Cancel</button>
                        </div>
                    </form>
                </dialog>
                <dialog class="task-dialog delete-dialog" id="delete-task-dialog" aria-labelledby="delete-task-heading" aria-describedby="delete-task-description">
                    <h2 id="delete-task-heading">Delete task?</h2>
                    <p id="delete-task-description">Delete <strong id="delete-task-title"></strong>? This cannot be undone.</p>
                    <p class="error" id="delete-task-error" role="alert" hidden></p>
                    <form id="delete-task-form" action="api/tasks.php" method="post">
                        <div class="task-form-actions">
                            <button type="submit" class="button-danger" id="confirm-delete-task">Delete</button>
                            <button type="button" class="button-secondary" id="cancel-delete-task" autofocus>Cancel</button>
                        </div>
                    </form>
                </dialog>
                <p class="success" id="task-notice" role="status" hidden></p>

                <div class="task-stats" role="group" aria-label="Filter tasks by status" hidden>
                    <?php foreach ($filters as $status => $filter): ?>
                        <button type="button" class="status-filter status-<?= escape($status) ?>" data-status="<?= escape($status) ?>" data-label="<?= escape($filter['label']) ?>" aria-pressed="<?= $status === 'all' ? 'true' : 'false' ?>">
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
                <noscript><p>Enable JavaScript to add, search, filter, and sort tasks. Your existing tasks are listed below.</p></noscript>

                <ul class="task-list" id="task-list">
                    <?php foreach ($tasks as $task): ?>
                        <li class="task-item status-<?= escape($task['status']) ?>" data-title="<?= escape($task['title']) ?>" data-description="<?= escape($task['description']) ?>" data-status="<?= escape($task['status']) ?>" data-priority="<?= escape($task['priority']) ?>" data-due="<?= escape($task['due_date'] ?? '') ?>" data-created="<?= escape($task['created_at']) ?>" data-id="<?= (int) $task['id'] ?>">
                            <div class="task-content">
                                <div class="task-heading">
                                    <h3 class="task-title"><?= escape($task['title']) ?></h3>
                                    <details class="task-menu" hidden>
                                        <summary class="task-menu-toggle" aria-label="Actions for <?= escape($task['title']) ?>" title="Task actions">⋯</summary>
                                        <div class="task-menu-options" role="group" aria-label="Task actions">
                                            <button type="button" class="edit-task">Edit</button>
                                            <button type="button" class="delete-task">Delete</button>
                                        </div>
                                    </details>
                                </div>
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
                            <div class="task-actions">
                                <span class="badge task-status"><?= escape($statusLabels[$task['status']]) ?></span>
                                <?php if ($task['status'] !== 'completed'): ?>
                                    <?php $nextStatus = $task['status'] === 'pending' ? 'in_progress' : 'completed'; ?>
                                    <button type="button" class="button-secondary change-status" data-status="<?= escape($nextStatus) ?>" hidden>Mark as <?= escape($statusLabels[$nextStatus]) ?></button>
                                <?php endif; ?>
                            </div>
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
