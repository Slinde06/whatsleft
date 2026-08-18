<?php
require_once __DIR__ . '/../includes/auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_response(['success' => false, 'error' => 'Invalid request method.'], 405);
}
if (empty($currentUser['is_matched']) || empty($currentUser['current_match_id'])) {
    json_response(['success' => false, 'error' => 'You are not currently in a match.'], 422);
}

$input = json_decode(file_get_contents('php://input'), true) ?? $_POST;
$reasonShort = trim($input['reason_short'] ?? '');
$reasonLong  = trim($input['reason_long'] ?? '');

if ($reasonShort === '' || $reasonLong === '') {
    json_response(['success' => false, 'error' => 'A short and a detailed reason are both required - this will be public and permanent.'], 422);
}

$matchId = (int) $currentUser['current_match_id'];

$stmt = $pdo->prepare('SELECT * FROM matches WHERE id = ? AND status = ?');
$stmt->execute([$matchId, 'active']);
$match = $stmt->fetch();

if (!$match) {
    json_response(['success' => false, 'error' => 'This match is no longer active.'], 422);
}

$partnerId = $match['user1_id'] == $currentUser['id'] ? $match['user2_id'] : $match['user1_id'];

$pdo->beginTransaction();
try {
    $pdo->prepare(
        "UPDATE matches SET status = 'ended', ended_at = NOW(), ended_by = ?, end_reason_short = ?, end_reason_long = ? WHERE id = ?"
    )->execute([$currentUser['id'], $reasonShort, $reasonLong, $matchId]);

    $pdo->prepare('UPDATE users SET is_matched = 0, current_match_id = NULL WHERE id IN (?, ?)')
        ->execute([$currentUser['id'], $partnerId]);

    $pdo->commit();
} catch (Exception $e) {
    $pdo->rollBack();
    json_response(['success' => false, 'error' => 'Could not unmatch. Please try again.'], 500);
}

json_response(['success' => true]);
