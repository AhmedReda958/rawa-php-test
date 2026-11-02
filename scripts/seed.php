<?php

declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

require_once __DIR__ . '/../app/database.php';

$database = getDatabase();
$today = new DateTimeImmutable('today');
$tasks = [
    ['Buy groceries', 'Pick up vegetables, rice, eggs, and milk for the week.', 'high', 'pending', '+2 days'],
    ['Book a dentist appointment', 'Call the clinic and ask for an afternoon appointment.', 'medium', 'pending', 'today'],
    ['Organize the bookshelf', '', 'low', 'pending', null],
    ['Pay the electricity bill', 'Check the latest bill and pay it before the next billing cycle.', 'high', 'pending', '-2 days'],
    ['Plan the weekend trip', 'Compare train times and choose a place to stay for two nights.', 'high', 'in_progress', '+3 days'],
    ['Prepare a family dinner', "Choose a menu for six people.\nCheck for food allergies before buying ingredients.", 'medium', 'in_progress', '+5 days'],
    ['Finish reading a book', 'Read the remaining chapters of the current novel.', 'low', 'in_progress', null],
    ['Renew car insurance', 'Compare renewal offers and keep a copy of the updated policy.', 'high', 'completed', '-5 days'],
    ['Collect the laundry', 'Pick up the clothes from the laundry shop on the way home.', 'medium', 'completed', '-3 days'],
    ['Water the plants', 'Water the balcony plants and remove any dry leaves.', 'low', 'completed', '-1 day'],
];

$database->beginTransaction();

try {
    $userQuery = $database->prepare('SELECT id FROM users WHERE email = ? FOR UPDATE');
    $userQuery->execute(['john@example.com']);
    $userId = $userQuery->fetchColumn();

    if ($userId === false) {
        $createUser = $database->prepare('INSERT INTO users (name, email, password) VALUES (?, ?, ?)');
        $createUser->execute(['Ahmed', 'john@example.com', password_hash('TaskDemo123!', PASSWORD_DEFAULT)]);
        $userId = (int) $database->lastInsertId();
    }

    // Re-running the seed leaves existing tasks and any edits intact.
    $exists = $database->prepare('SELECT id FROM tasks WHERE user_id = ? AND title = ?');
    $insert = $database->prepare(
        'INSERT INTO tasks (user_id, title, description, priority, status, due_date)
         VALUES (?, ?, ?, ?, ?, ?)'
    );
    $added = 0;

    foreach ($tasks as [$title, $description, $priority, $status, $relativeDate]) {
        $exists->execute([$userId, $title]);

        if ($exists->fetchColumn() !== false) {
            continue;
        }

        $dueDate = $relativeDate === null ? null : $today->modify($relativeDate)->format('Y-m-d');
        $insert->execute([$userId, $title, $description, $priority, $status, $dueDate]);
        $added++;
    }

    $database->commit();
    echo "Added $added sample tasks for Ahmed. Existing tasks were kept.\n";
} catch (Throwable $exception) {
    $database->rollBack();
    fwrite(STDERR, $exception->getMessage() . PHP_EOL);
    exit(1);
}
