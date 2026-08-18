<?php
/**
 * includes/auth.php
 * Sit dit heel bo-aan enige bladsy/endpoint wat 'n ingetekende gebruiker nodig het.
 * Gee $pdo (db verbinding) en $currentUser (array van die ingetekende gebruiker se ry).
 */

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/functions.php';

if (empty($_SESSION['user_id'])) {
    // Kyk of ons van 'n /api/ endpoint af geroep word (verwag JSON) of 'n bladsy (verwag redirect).
    if (strpos($_SERVER['SCRIPT_NAME'], '/api/') !== false) {
        json_response(['success' => false, 'error' => 'Not authenticated.'], 401);
    }
    redirect(base_url('frontend/pages/login.php'));
}

$currentUser = get_user($pdo, (int) $_SESSION['user_id']);

if (!$currentUser) {
    // Sessie het na 'n gebruiker gewys wat nie meer bestaan nie - forseer uitteken.
    session_destroy();
    redirect(base_url('frontend/pages/login.php'));
}
