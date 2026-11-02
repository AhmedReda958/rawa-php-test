# Task Manager

A task management project built with plain PHP, HTML, CSS, and vanilla JavaScript.
Currently, it includes login, session authentication, logout, a protected dashboard
placeholder, and the database setup. Task management will be added next.

## Requirements

- PHP 8.2 or newer
- PHP's PDO MySQL extension (`pdo_mysql`)
- MySQL 8.0 or newer

## Database setup

Connect using your local MySQL administrator account:

```sh
mysql -u root -p
```

Run the following SQL, replacing the example password with a strong local password:

```sql
CREATE DATABASE task_manager CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'task_manager_app'@'localhost' IDENTIFIED BY 'replace_with_your_local_password';
GRANT SELECT, INSERT, UPDATE, DELETE ON task_manager.* TO 'task_manager_app'@'localhost';
```

Exit MySQL and import the schema using the administrator account:

```sh
mysql -u root -p task_manager < database.sql
cp config/database.example.php config/database.php
```

Set the database password in `config/database.php` to match the password you chose.
This file is ignored by Git and sits outside the web root. The application account
can read and change records only in this database; it cannot create or drop tables.

Import `database.sql` once into an empty database. It creates the tables and a test
user, since the app does not include signup. Task due dates are optional. Stored priority
values are `low`, `medium`, and `high`; statuses are `pending`, `in_progress`, and
`completed`.

## Test login

- Email: `john@example.com`
- Password: `TaskDemo123!`

The test user's password is stored as a hash in the database. These credentials
are for local development and reviewing the project.

## Run locally

From the project directory, run:

```sh
php -S 127.0.0.1:8000 -t public
```

Open http://127.0.0.1:8000 in your browser. Press `Ctrl+C` to stop the server.

## Project structure

```text
app/
  auth.php              Session setup, authentication guard, and CSRF helpers
  database.php          Shared PDO connection
config/
  database.example.php  Example database settings
  database.php          Private local settings (ignored by Git)
public/
  index.php             Redirects to login or dashboard
  login.php             Login form and credential verification
  dashboard.php         Protected dashboard placeholder
  logout.php            POST-only logout
  assets/
    css/style.css       Styles
    js/app.js           JavaScript entry point
database.sql            Table definitions and test user
```

The `public` directory is the web root.
