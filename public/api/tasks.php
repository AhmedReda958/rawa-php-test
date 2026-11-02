<?php

require_once __DIR__ . '/../../app/auth.php';
require_once __DIR__ . '/../../app/http.php';
require_once __DIR__ . '/../../app/tasks.php';
require_once __DIR__ . '/../../app/task-validation.php';

if (!isLoggedIn()) {
    jsonResponse(['message' => 'Please log in again to save tasks.'], 401);
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Allow: POST');
    jsonResponse(['message' => 'Method not allowed.'], 405);
}

if (!validCsrfToken($_POST['csrf_token'] ?? null)) {
    jsonResponse(['message' => 'Unable to verify this form. Reload the page and try again.'], 403);
}

$taskId = null;
$submittedId = $_POST['task_id'] ?? '';

if ($submittedId !== '') {
    $taskId = is_string($submittedId)
        ? filter_var($submittedId, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]])
        : false;

    if ($taskId === false) {
        jsonResponse(['message' => 'Invalid task ID.'], 400);
    }
}

$action = $_POST['action'] ?? 'save';

if (!in_array($action, ['save', 'status'], true)) {
    jsonResponse(['message' => 'Invalid task action.'], 400);
}

if ($action === 'status') {
    if ($taskId === null) {
        jsonResponse(['message' => 'A task ID is required.'], 400);
    }

    if (!isValidTaskStatus($_POST['status'] ?? null)) {
        jsonResponse(['message' => 'Choose a valid status.'], 422);
    }

    try {
        $task = updateTaskStatus((int) $_SESSION['user_id'], $taskId, $_POST['status']);

        if ($task === null) {
            jsonResponse(['message' => 'Task not found or no longer available.'], 404);
        }

        jsonResponse(['task' => $task]);
    } catch (RuntimeException $exception) {
        error_log('Task status database error: ' . $exception->getMessage());
        jsonResponse(['message' => 'Unable to change the status right now. Please try again later.'], 503);
    }
}

$validation = validateTask($_POST);

if ($validation['errors'] !== []) {
    jsonResponse(['message' => 'Please correct the highlighted fields.', 'errors' => $validation['errors']], 422);
}

try {
    // Ownership always comes from the session, regardless of any submitted user_id.
    $userId = (int) $_SESSION['user_id'];

    if ($taskId !== null) {
        $task = updateTask($userId, $taskId, $validation['values']);

        if ($task === null) {
            jsonResponse(['message' => 'Task not found or no longer available.'], 404);
        }

        jsonResponse(['task' => $task]);
    }

    jsonResponse(['task' => createTask($userId, $validation['values'])], 201);
} catch (RuntimeException $exception) {
    error_log('Save task database error: ' . $exception->getMessage());
    jsonResponse(['message' => 'Unable to save your task right now. Please try again later.'], 503);
}
