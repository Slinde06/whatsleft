<?php
require_once __DIR__ . '/../includes/auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_response(['success' => false, 'error' => 'Invalid request method.'], 405);
}

if ($currentUser['undo_used']) {
    json_response(['success' => false, 'error' => 'You have already used your one undo.'], 422);
}
if ($currentUser['is_matched']) {
    json_response(['success' => false, 'error' => 'You\'re already in an active match - end it before undoing an older one.'], 422);
}

$stmt = $pdo->prepare(
    "SELECT * FROM matches WHERE (user1_id = ? OR user2_id = ?) AND status = 'ended' ORDER BY ended_at DESC LIMIT 1"
);
$stmt->execute([$currentUser['id'], $currentUser['id']]);
$match = $stmt->fetch();

if (!$match) {
    json_response(['success' => false, 'error' => 'There is nothing to undo.'], 422);
}

$otherUserId = $match['user1_id'] == $currentUser['id'] ? $match['user2_id'] : $match['user1_id'];

// Die ander persoon moet ook vry wees om weer in hierdie match te kom.
$otherUser = get_user($pdo, $otherUserId);
if ($otherUser && $otherUser['is_matched']) {
    json_response(['success' => false, 'error' => 'That person is already in a different match, so this can\'t be restored.'], 422);
}

$pdo->beginTransaction();
try {
    $pdo->prepare(
        "UPDATE matches SET status = 'active', ended_at = NULL, ended_by = NULL, undo_of_match = 1 WHERE id = ?"
    )->execute([$match['id']]);

    $pdo->prepare('UPDATE users SET is_matched = 1, current_match_id = ?, undo_used = 1 WHERE id = ?')
        ->execute([$match['id'], $currentUser['id']]);

    $pdo->prepare('UPDATE users SET is_matched = 1, current_match_id = ? WHERE id = ?')
        ->execute([$match['id'], $otherUserId]);

    $pdo->commit();
} catch (Exception $e) {
    $pdo->rollBack();
    json_response(['success' => false, 'error' => 'Could not undo. Please try again.'], 500);
}

json_response(['success' => true, 'match_id' => $match['id']]);
