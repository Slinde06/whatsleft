<?php
require_once __DIR__ . '/../includes/auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_response(['success' => false, 'error' => 'Invalid request method.'], 405);
}
if (empty($currentUser['is_matched']) || empty($currentUser['current_match_id'])) {
    json_response(['success' => false, 'error' => 'You are not currently in a match.'], 422);
}

$input = json_decode(file_get_contents('php://input'), true) ?? $_POST;
$message = trim($input['message'] ?? '');

if ($message === '') {
    json_response(['success' => false, 'error' => 'Message cannot be empty.'], 422);
}
if (strlen($message) > 2000) {
    $message = substr($message, 0, 2000);
}

$matchId = (int) $currentUser['current_match_id'];

// Maak seker hierdie match is regtig aktief en behoort aan die huidige gebruiker.
$check = $pdo->prepare("SELECT id FROM matches WHERE id = ? AND status = 'active' AND (user1_id = ? OR user2_id = ?)");
$check->execute([$matchId, $currentUser['id'], $currentUser['id']]);
if (!$check->fetch()) {
    json_response(['success' => false, 'error' => 'This match is no longer active.'], 422);
}

$stmt = $pdo->prepare('INSERT INTO messages (match_id, sender_id, message) VALUES (?, ?, ?)');
$stmt->execute([$matchId, $currentUser['id'], $message]);

json_response([
    'success' => true,
    'message' => [
        'id'        => (int) $pdo->lastInsertId(),
        'sender_id' => (int) $currentUser['id'],
        'message'   => $message,
    ],
]);
