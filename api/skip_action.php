<?php
require_once __DIR__ . '/../includes/auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_response(['success' => false, 'error' => 'Invalid request method.'], 405);
}
if ($currentUser['skip_used']) {
    json_response(['success' => false, 'error' => 'You have already used your one skip, ever.'], 422);
}

$input = json_decode(file_get_contents('php://input'), true) ?? $_POST;
$targetId = (int) ($input['target_id'] ?? 0);

if ($targetId <= 0 || $targetId === (int) $currentUser['id']) {
    json_response(['success' => false, 'error' => 'Invalid profile.'], 422);
}

$dupe = $pdo->prepare('SELECT id FROM swipes WHERE swiper_id = ? AND target_id = ?');
$dupe->execute([$currentUser['id'], $targetId]);
if ($dupe->fetch()) {
    json_response(['success' => false, 'error' => 'You already made a decision on this profile.'], 409);
}

$pdo->beginTransaction();
try {
    $pdo->prepare("INSERT INTO swipes (swiper_id, target_id, action) VALUES (?, ?, 'skip')")
        ->execute([$currentUser['id'], $targetId]);
    $pdo->prepare('UPDATE users SET skip_used = 1 WHERE id = ?')->execute([$currentUser['id']]);
    $pdo->commit();
} catch (Exception $e) {
    $pdo->rollBack();
    json_response(['success' => false, 'error' => 'Could not skip. Please try again.'], 500);
}

json_response(['success' => true]);
