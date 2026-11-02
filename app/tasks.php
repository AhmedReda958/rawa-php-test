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
