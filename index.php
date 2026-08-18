<?php

require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/functions.php';

if (!empty($_SESSION['user_id'])) {
    redirect(base_url('frontend/pages/home.php'));
} else {
    redirect(base_url('frontend/pages/login.php'));
}
