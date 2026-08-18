<?php
require_once __DIR__ . '/../includes/auth.php';

if (!empty($currentUser['is_matched'])) {
    json_response(['success' => false, 'error' => 'You are already in a match.'], 422);
}

$stmt = $pdo->prepare(
    "SELECT * FROM users
     WHERE id != ?
       AND is_matched = 0
       AND id NOT IN (SELECT target_id FROM swipes WHERE swiper_id = ?)
     ORDER BY id ASC
     LIMIT 1"
);
$stmt->execute([$currentUser['id'], $currentUser['id']]);
$candidate = $stmt->fetch();

if (!$candidate) {
    json_response(['success' => true, 'done' => true]);
}

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
    ],
    'skip_used' => (bool) $currentUser['skip_used'],
]);
