# Task Manager

A simple task management app with login, task creation, editing, deletion, search,
and status and priority filters.

## Requirements

- PHP 8.2 or newer
- MySQL 8.0 or newer

## Setup

1. Clone the repository and open its directory:

   ```sh
   git clone https://github.com/AhmedReda958/rawa-php-test.git
   cd rawa-php-test
   ```

2. Start MySQL and connect with your administrator account:

   ```sh
   mysql -u root -p
   ```

3. Create the database and application user. Replace the example password:

   ```sql
   CREATE DATABASE task_manager CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
   CREATE USER 'task_manager_app'@'localhost' IDENTIFIED BY 'your_local_password';
   GRANT SELECT, INSERT, UPDATE, DELETE ON task_manager.* TO 'task_manager_app'@'localhost';
   EXIT;
   ```

4. From the project directory, import the tables and copy the configuration:

   ```sh
   mysql -u root -p task_manager < database.sql
   cp config/database.example.php config/database.php
   ```

5. Update `config/database.php` with your database credentials.
6. Create the test user and sample tasks:

   ```sh
   php scripts/seed.php
   ```

7. Start the app:

   ```sh
   php -S 127.0.0.1:8000 -t public
   ```

Open [http://127.0.0.1:8000](http://127.0.0.1:8000). Press `Ctrl+C` to stop the server.

Import `database.sql` once into an empty database. The seed adds 10 sample tasks
and can be rerun without duplicating unchanged samples. There is no signup page.
Local database credentials are ignored by Git.

## Test login

- Name: Ahmed
- Email: `ahmed@example.com`
- Password: `TaskDemo123!`

These credentials are for local testing and reviewing the project.
