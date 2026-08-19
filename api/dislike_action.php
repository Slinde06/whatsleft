<?php
require_once __DIR__ . '/../includes/auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_response(['success' => false, 'error' => 'Invalid request method.'], 405);
}

$input = json_decode(file_get_contents('php://input'), true) ?? $_POST;
$targetId = (int) ($input['target_id'] ?? 0);
$reasonShort = trim($input['reason_short'] ?? '');
$reasonLong  = trim($input['reason_long'] ?? '');

if ($targetId <= 0 || $targetId === (int) $currentUser['id']) {
    json_response(['success' => false, 'error' => 'Invalid profile.'], 422);
}
if ($reasonShort === '' || $reasonLong === '') {
    json_response(['success' => false, 'error' => 'A short and a detailed reason are both required - this will be public and permanent.'], 422);
}

$dupe = $pdo->prepare('SELECT id FROM swipes WHERE swiper_id = ? AND target_id = ? AND undone = 0');
$dupe->execute([$currentUser['id'], $targetId]);
if ($dupe->fetch()) {
    json_response(['success' => false, 'error' => 'You already made a decision on this profile.'], 409);
}

$stmt = $pdo->prepare(
    "INSERT INTO swipes (swiper_id, target_id, action, reason_short, reason_long) VALUES (?, ?, 'dislike', ?, ?)"
);
$stmt->execute([$currentUser['id'], $targetId, $reasonShort, $reasonLong]);

json_response(['success' => true]);
