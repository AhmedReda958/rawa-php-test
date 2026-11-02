<?php

require_once __DIR__ . '/../../app/auth.php';
require_once __DIR__ . '/../../app/http.php';
require_once __DIR__ . '/../../app/tasks.php';
require_once __DIR__ . '/../../app/task-validation.php';

if (!isLoggedIn()) {
    jsonResponse(['message' => 'Please log in again to add tasks.'], 401);
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Allow: POST');
    jsonResponse(['message' => 'Method not allowed.'], 405);
}

if (!validCsrfToken($_POST['csrf_token'] ?? null)) {
    jsonResponse(['message' => 'Unable to verify this form. Reload the page and try again.'], 403);
}

$validation = validateTask($_POST);

if ($validation['errors'] !== []) {
    jsonResponse(['message' => 'Please correct the highlighted fields.', 'errors' => $validation['errors']], 422);
}

try {
    // Ownership always comes from the session, regardless of any submitted user_id.
    $task = createTask((int) $_SESSION['user_id'], $validation['values']);
    jsonResponse(['task' => $task], 201);
} catch (RuntimeException $exception) {
    error_log('Create task database error: ' . $exception->getMessage());
    jsonResponse(['message' => 'Unable to save your task right now. Please try again later.'], 503);
}
