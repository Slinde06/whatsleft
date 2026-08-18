<?php
require_once __DIR__ . '/../includes/auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_response(['success' => false, 'error' => 'Invalid request method.'], 405);
}

$input = json_decode(file_get_contents('php://input'), true);
if (!is_array($input)) {
    $input = $_POST;
}

$firstName   = trim($input['first_name'] ?? '');
$lastName    = trim($input['last_name'] ?? '');
$email       = trim($input['email'] ?? '');
$lookingFor  = trim($input['looking_for'] ?? '');
$location    = trim($input['location'] ?? '');
$description = trim($input['description'] ?? '');
$hobbies     = $input['hobbies'] ?? [];
$personality = $input['personality'] ?? [];

if (!is_array($hobbies)) {
    $hobbies = array_filter(array_map('trim', explode(',', (string) $hobbies)));
}
$hobbies = array_slice(array_values(array_unique(array_filter($hobbies))), 0, 12);

if ($firstName === '' || $lastName === '' || $email === '' || $lookingFor === '') {
    json_response(['success' => false, 'error' => 'First name, last name, email and looking-for are required.'], 422);
}
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    json_response(['success' => false, 'error' => 'Please enter a valid email address.'], 422);
}

$check = $pdo->prepare('SELECT id FROM users WHERE email = ? AND id != ?');
$check->execute([$email, $currentUser['id']]);
if ($check->fetch()) {
    json_response(['success' => false, 'error' => 'That email is already in use by another account.'], 422);
}

$pdo->beginTransaction();
try {
    $stmt = $pdo->prepare(
        'UPDATE users SET first_name = ?, last_name = ?, email = ?, looking_for = ?, location = ?, description = ? WHERE id = ?'
    );
    $stmt->execute([$firstName, $lastName, $email, $lookingFor, $location, $description, $currentUser['id']]);


    $pdo->prepare('DELETE FROM user_hobbies WHERE user_id = ?')->execute([$currentUser['id']]);
    if (!empty($hobbies)) {
        $hobbyStmt = $pdo->prepare('INSERT INTO user_hobbies (user_id, hobby) VALUES (?, ?)');
        foreach ($hobbies as $hobby) {
            $hobby = trim(substr($hobby, 0, 50));
            if ($hobby !== '') {
                $hobbyStmt->execute([$currentUser['id'], $hobby]);
            }
        }
    }


    $ie = max(0, min(100, (int) ($personality['introvert_extrovert'] ?? 50)));
    $ac = max(0, min(100, (int) ($personality['analytical_creative'] ?? 50)));
    $ts = max(0, min(100, (int) ($personality['tech_savvy'] ?? 50)));
    $sp = max(0, min(100, (int) ($personality['sporty'] ?? 50)));

    $pdo->prepare(
        'INSERT INTO user_personality (user_id, introvert_extrovert, analytical_creative, tech_savvy, sporty)
         VALUES (?, ?, ?, ?, ?)
         ON DUPLICATE KEY UPDATE introvert_extrovert = VALUES(introvert_extrovert),
                                  analytical_creative = VALUES(analytical_creative),
                                  tech_savvy = VALUES(tech_savvy),
                                  sporty = VALUES(sporty)'
    )->execute([$currentUser['id'], $ie, $ac, $ts, $sp]);

    $pdo->commit();
} catch (Exception $e) {
    $pdo->rollBack();
    json_response(['success' => false, 'error' => 'Could not save changes. Please try again.'], 500);
}

json_response(['success' => true]);
