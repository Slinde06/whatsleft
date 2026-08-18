<?php
/**
 * api/quick_unmatch_action.php
 * Net-vir-demo kortpad, sit langs die regte unmatch_action.php.
 * Eindig die huidige match onmiddellik sonder om 'n publieke rede nodig te
 * hê, sodat dit vinnig is om die match te herstel terwyl jy vir iemand wys
 * hoe die matchmaking werk.
 * Raak NIE undo_used aan nie - dit gedra soos 'n gewone unmatch, dis net
 * sonder die rede-teks, so die regte een-keer-ooit undo bly ongeskonde.
 */
require_once __DIR__ . '/../includes/auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_response(['success' => false, 'error' => 'Invalid request method.'], 405);
}
if (empty($currentUser['is_matched']) || empty($currentUser['current_match_id'])) {
    json_response(['success' => false, 'error' => 'You are not currently in a match.'], 422);
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
    )->execute([$currentUser['id'], 'Quick demo unmatch', 'i dont care', $matchId]);

    $pdo->prepare('UPDATE users SET is_matched = 0, current_match_id = NULL WHERE id IN (?, ?)')
        ->execute([$currentUser['id'], $partnerId]);

    $pdo->commit();
} catch (Exception $e) {
    $pdo->rollBack();
    json_response(['success' => false, 'error' => 'Could not unmatch. Please try again.'], 500);
}

json_response(['success' => true]);
