<?php
require_once __DIR__ . '/../includes/auth.php';

if (empty($currentUser['is_matched']) || empty($currentUser['current_match_id'])) {
    json_response(['success' => false, 'error' => 'You are not currently in a match.'], 422);
}

$matchId = (int) $currentUser['current_match_id'];
$sinceId = (int) ($_GET['since_id'] ?? 0);

$stmt = $pdo->prepare('SELECT * FROM messages WHERE match_id = ? AND id > ? ORDER BY id ASC');
$stmt->execute([$matchId, $sinceId]);
$rows = $stmt->fetchAll();

$messages = array_map(function ($m) {
    return [
        'id'        => (int) $m['id'],
        'sender_id' => (int) $m['sender_id'],
        'message'   => $m['message'],
    ];
}, $rows);

// As die match geëindig het (bv. die ander persoon het unmatch gedoen), sê vir die client sodat hy kan redirect.
$statusStmt = $pdo->prepare('SELECT status FROM matches WHERE id = ?');
$statusStmt->execute([$matchId]);
$status = $statusStmt->fetch()['status'] ?? 'ended';

json_response(['success' => true, 'messages' => $messages, 'match_status' => $status]);
