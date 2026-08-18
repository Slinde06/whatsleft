<?php


require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/functions.php';

if (empty($_SESSION['user_id'])) {

    if (strpos($_SERVER['SCRIPT_NAME'], '/api/') !== false) {
        json_response(['success' => false, 'error' => 'Not authenticated.'], 401);
    }
    redirect(base_url('frontend/pages/login.php'));
}

$currentUser = get_user($pdo, (int) $_SESSION['user_id']);

if (!$currentUser) {

    session_destroy();
    redirect(base_url('frontend/pages/login.php'));
}
