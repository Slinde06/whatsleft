<?php
require_once __DIR__ . '/../includes/auth.php';

if (!empty($currentUser['is_matched'])) {
    json_response(['success' => false, 'error' => 'You are already in a match.'], 422);
}

// The "go back" button re-opens one specific profile instead of the next one in line.
$requestedId = (int) ($_GET['target_id'] ?? 0);

if ($requestedId > 0) {
    $candidate = get_user($pdo, $requestedId);
    if (!$candidate || $candidate['id'] == $currentUser['id']) {
        json_response(['success' => false, 'error' => 'That profile is not available.'], 404);
    }
} else {
    // Almal behalwe ek, en almal wat nie tans gematch is nie - in id volgorde.
    // Loop vir ewig deur die lys (nooit "done" nie): 'n sessie-teller skuif elke keer een plek aan en wikkel om by die einde.
    $totalStmt = $pdo->prepare('SELECT COUNT(*) FROM users WHERE id != ? AND is_matched = 0');
    $totalStmt->execute([$currentUser['id']]);
    $total = (int) $totalStmt->fetchColumn();

    if ($total === 0) {
        json_response(['success' => true, 'done' => true]);
    }

    if (!isset($_SESSION['discover_cursor'])) {
        $_SESSION['discover_cursor'] = 0;
    }
    $offset = $_SESSION['discover_cursor'] % $total;
    $_SESSION['discover_cursor']++;

    $stmt = $pdo->prepare(
        "SELECT * FROM users
         WHERE id != ?
           AND is_matched = 0
         ORDER BY id ASC
         LIMIT 1 OFFSET {$offset}"
    );
    $stmt->execute([$currentUser['id']]);
    $candidate = $stmt->fetch();

    if (!$candidate) {
        json_response(['success' => true, 'done' => true]);
    }
}

$dupe = $pdo->prepare('SELECT action FROM swipes WHERE swiper_id = ? AND target_id = ? AND undone = 0');
$dupe->execute([$currentUser['id'], $candidate['id']]);
$dupeRow = $dupe->fetch();
$alreadyDecided = (bool) $dupeRow;
$previousAction = $dupeRow['action'] ?? null;

$photos = get_user_photos($pdo, $candidate['id']);
$hobbies = get_user_hobbies($pdo, $candidate['id']);
$personality = get_user_personality($pdo, $candidate['id']);

json_response([
    'success' => true,
    'done'    => false,
    'candidate' => [
        'id'          => (int) $candidate['id'],
        'first_name'  => $candidate['first_name'],
        'age'         => calculate_age($candidate['dob']),
        'location'    => $candidate['location'],
        'description' => $candidate['description'],
        'hobbies'     => $hobbies,
        'photo_url'   => photo_url($photos[0]['photo_path'] ?? null),
        'personality' => [
            'introvert_extrovert' => (int) $personality['introvert_extrovert'],
            'analytical_creative' => (int) $personality['analytical_creative'],
            'tech_savvy'          => (int) $personality['tech_savvy'],
            'sporty'              => (int) $personality['sporty'],
        ],
        'already_decided' => $alreadyDecided,
        'previous_action'  => $previousAction,
    ],
    'skip_used' => (bool) $currentUser['skip_used'],
    'dislike_undo_used' => (bool) $currentUser['dislike_undo_used'],
]);
