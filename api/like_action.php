<?php
require_once __DIR__ . '/../includes/auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_response(['success' => false, 'error' => 'Invalid request method.'], 405);
}
if (!empty($currentUser['is_matched'])) {
    json_response(['success' => false, 'error' => 'You are already in a match.'], 422);
}

$input = json_decode(file_get_contents('php://input'), true) ?? $_POST;
$targetId = (int) ($input['target_id'] ?? 0);

if ($targetId <= 0 || $targetId === (int) $currentUser['id']) {
    json_response(['success' => false, 'error' => 'Invalid profile.'], 422);
}

$target = get_user($pdo, $targetId);
if (!$target) {
    json_response(['success' => false, 'error' => 'That profile no longer exists.'], 404);
}
if ($target['is_matched']) {
    json_response(['success' => false, 'error' => 'That person just matched with someone else.'], 409);
}

// Al klaar op hierdie persoon geswipe? Moenie dubbel-swipe toelaat nie.
$dupe = $pdo->prepare('SELECT id FROM swipes WHERE swiper_id = ? AND target_id = ?');
$dupe->execute([$currentUser['id'], $targetId]);
if ($dupe->fetch()) {
    json_response(['success' => false, 'error' => 'You already made a decision on this profile.'], 409);
}

$pdo->beginTransaction();
try {
    $pdo->prepare("INSERT INTO swipes (swiper_id, target_id, action) VALUES (?, ?, 'like')")
        ->execute([$currentUser['id'], $targetId]);

    $pdo->prepare('INSERT INTO matches (user1_id, user2_id) VALUES (?, ?)')
        ->execute([$currentUser['id'], $targetId]);
    $matchId = (int) $pdo->lastInsertId();

    $pdo->prepare('UPDATE users SET is_matched = 1, current_match_id = ? WHERE id IN (?, ?)')
        ->execute([$matchId, $currentUser['id'], $targetId]);

    $pdo->commit();
} catch (Exception $e) {
    $pdo->rollBack();
    json_response(['success' => false, 'error' => 'Could not process that like. Please try again.'], 500);
}

json_response(['success' => true, 'matched' => true, 'match_id' => $matchId]);
