<?php

require_once __DIR__ . '/../app/auth.php';

redirect(isLoggedIn() ? 'dashboard.php' : 'login.php');
