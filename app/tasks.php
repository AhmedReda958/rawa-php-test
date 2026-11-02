<?php

declare(strict_types=1);

require_once __DIR__ . '/database.php';

function getUserTasks(int $userId): array
{
    // The caller supplies the authenticated user's ID, never an ID from the request.
    $query = getDatabase()->prepare(
        'SELECT id, title, description, priority, status, due_date, created_at
         FROM tasks
         WHERE user_id = ?
         ORDER BY created_at DESC, id DESC'
    );
    $query->execute([$userId]);

    return $query->fetchAll();
}

function createTask(int $userId, array $values): array
{
    $database = getDatabase();
    $query = $database->prepare(
        'INSERT INTO tasks (user_id, title, description, priority, status, due_date)
         VALUES (?, ?, ?, ?, ?, ?)'
    );
    $query->execute([
        $userId, $values['title'], $values['description'],
        $values['priority'], $values['status'], $values['due_date'],
    ]);

    $query = $database->prepare(
        'SELECT id, title, description, priority, status, due_date, created_at
         FROM tasks WHERE id = ? AND user_id = ?'
    );
    $query->execute([$database->lastInsertId(), $userId]);

    return $query->fetch();
}

function updateTask(int $userId, int $taskId, array $values): ?array
{
    $database = getDatabase();
    $query = $database->prepare(
        'UPDATE tasks SET title = ?, description = ?, priority = ?, status = ?, due_date = ?
         WHERE id = ? AND user_id = ?'
    );
    $query->execute([
        $values['title'], $values['description'], $values['priority'],
        $values['status'], $values['due_date'], $taskId, $userId,
    ]);

    // A zero row count also occurs when the submitted values are unchanged.
    return getUserTask($userId, $taskId);
}

function updateTaskStatus(int $userId, int $taskId, string $status): ?array
{
    $query = getDatabase()->prepare('UPDATE tasks SET status = ? WHERE id = ? AND user_id = ?');
    $query->execute([$status, $taskId, $userId]);

    return getUserTask($userId, $taskId);
}

function getUserTask(int $userId, int $taskId): ?array
{
    $query = getDatabase()->prepare(
        'SELECT id, title, description, priority, status, due_date, created_at
         FROM tasks WHERE id = ? AND user_id = ?'
    );
    $query->execute([$taskId, $userId]);

    return $query->fetch() ?: null;
}
