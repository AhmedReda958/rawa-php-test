<?php

require_once __DIR__ . '/../app/auth.php';

if (isLoggedIn()) {
    redirect('dashboard.php');
}  

$email = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = is_string($_POST['email'] ?? null) ? trim($_POST['email']) : '';
    $password = is_string($_POST['password'] ?? null) ? $_POST['password'] : '';

    if (!validCsrfToken($_POST['csrf_token'] ?? null)) {
        http_response_code(403);
        $error = 'Unable to verify this form. Reload the page and try again.';
    } elseif (strlen($email) > 254 || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } elseif ($password === '') {
        $error = 'Please enter your password.';
    } else {
        try {
            $query = getDatabase()->prepare('SELECT id, name, password FROM users WHERE email = ?');
            $query->execute([$email]);
            $user = $query->fetch();

            if ($user && password_verify($password, $user['password'])) {
                // Replace the anonymous session ID before storing the user's identity.
                session_regenerate_id(true);
                $_SESSION = [
                    'user_id' => (int) $user['id'],
                    'user_name' => $user['name'],
                ];
                redirect('dashboard.php');
            }

            $error = 'Email or password is incorrect.';
        } catch (PDOException $exception) {
            error_log('Login database error: ' . $exception->getMessage());
            http_response_code(503);
            $error = 'Unable to log in right now. Please try again later.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | Task Manager</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/login.css">
    <script src="assets/js/login.js" defer></script>
</head>
<body>
    <main class="login-container">
        <section class="card" aria-labelledby="login-heading">
            <p class="app-name">Task Manager</p>
            <h1 id="login-heading">Log in</h1>
            <p class="intro">Sign in to manage your tasks.</p>

            <?php if ($error !== ''): ?>
                <p class="error" role="alert"><?= escape($error) ?></p>
            <?php endif; ?>

            <form id="login-form" action="login.php" method="post">
                <input type="hidden" name="csrf_token" value="<?= escape(csrfToken()) ?>">

                <div class="form-field">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" value="<?= escape($email) ?>" maxlength="254" autocomplete="username" required>
                </div>

                <div class="form-field">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" autocomplete="current-password" required>
                </div>

                <button type="submit">Log in</button>
            </form>
        </section>
    </main>
</body>
</html>
