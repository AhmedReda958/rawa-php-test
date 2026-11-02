# Task Manager

A task management project built with plain PHP, HTML, CSS, and vanilla JavaScript.
Currently, it includes login, session authentication, logout, and a dashboard with
task statistics and the logged-in user's task list. Adding, editing, and deleting
tasks will be implemented next.

The dashboard supports title search, status and priority filters, and sorting by
creation date, due date, priority, or title. Search, filtering, and sorting use
vanilla JavaScript and can be combined. Status counts always reflect all of the
user's tasks; Reset clears the filters and restores newest-first sorting.

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

- Name: Ahmed
- Email: `john@example.com`
- Password: `TaskDemo123!`

The test user's password is stored as a hash in the database. These credentials
are for local development and reviewing the project.

## Sample tasks

After configuring the database, run:

```sh
php scripts/seed.php
```

This renames the test user to Ahmed and adds 10 everyday sample tasks: 4 Pending, 3 In
Progress, and 3 Completed. The examples cover every status/priority combination,
past, current, and future due dates, missing due dates, and empty or multiline
descriptions. Re-running the command skips existing sample titles for this user
and does not overwrite task records. Log out and back in after renaming the user
to refresh the welcome message.

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
  tasks.php             Queries scoped to the authenticated user
config/
  database.example.php  Example database settings
  database.php          Private local settings (ignored by Git)
public/
  index.php             Redirects to login or dashboard
  login.php             Login form and credential verification
  dashboard.php         Protected task statistics and task list
  logout.php            POST-only logout
  assets/
    css/
      style.css         Loads the shared stylesheets
      variables.css     Shared colors, spacing, fonts, and sizes
      base.css          Element defaults and page layout
      components.css    Forms, buttons, cards, badges, and messages
      login.css         Login page layout
      dashboard.css     Dashboard and task list layout
    js/
      login.js          Login form validation
      dashboard.js      Dashboard controls and task list updates
      task-utils.js     Task comparison and filter helpers
database.sql            Table definitions and test user
scripts/
  seed.php              Optional local sample tasks
```

The `public` directory is the web root.

Both pages load `style.css` and their own page stylesheet. Change shared design
values in `variables.css`, reusable controls in `components.css`, and page layouts
in their respective stylesheets. The dashboard uses native JavaScript modules;
no build step or frontend dependencies are needed.
