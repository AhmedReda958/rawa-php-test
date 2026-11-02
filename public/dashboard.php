<?php

require_once __DIR__ . '/../app/auth.php';
requireLogin();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard | Task Manager</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <main>
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
        <section class="card" aria-labelledby="dashboard-heading">
            <h2 id="dashboard-heading">Dashboard</h2>
            <p>You are logged in.</p>
        </section>
    </main>
</body>
</html>
