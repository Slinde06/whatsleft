<?php
require_once __DIR__ . '/../includes/auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_response(['success' => false, 'error' => 'Invalid request method.'], 405);
}

// One "go back" per account, ever - once dislike_undo_used flips to 1 it never resets.
if ($currentUser['dislike_undo_used']) {
    json_response(['success' => false, 'error' => 'You have already used your one go-back.'], 422);
}
if (!empty($currentUser['is_matched'])) {
    json_response(['success' => false, 'error' => 'You are already in a match.'], 422);
}

$stmt = $pdo->prepare(
    "SELECT * FROM swipes WHERE swiper_id = ? AND action = 'dislike' AND undone = 0 ORDER BY id DESC LIMIT 1"
);
$stmt->execute([$currentUser['id']]);
$lastDislike = $stmt->fetch();

if (!$lastDislike) {
    json_response(['success' => false, 'error' => 'There is no dislike to go back to.'], 422);
}

$target = get_user($pdo, (int) $lastDislike['target_id']);
if (!$target) {
    json_response(['success' => false, 'error' => 'That profile no longer exists.'], 404);
}
if ($target['is_matched']) {
    json_response(['success' => false, 'error' => 'That person has since matched with someone else.'], 409);
}

$pdo->beginTransaction();
try {
    // Mark it "undone" rather than deleting it - the reason stays on the public
    // record permanently (leaderboard_view still counts it), it just no longer
    // blocks a fresh decision on this one profile.
    $pdo->prepare('UPDATE swipes SET undone = 1 WHERE id = ?')->execute([$lastDislike['id']]);
    $pdo->prepare('UPDATE users SET dislike_undo_used = 1 WHERE id = ?')->execute([$currentUser['id']]);
    $pdo->commit();
} catch (Exception $e) {
    $pdo->rollBack();
    json_response(['success' => false, 'error' => 'Could not go back. Please try again.'], 500);
}

json_response(['success' => true, 'target_id' => (int) $target['id']]);
