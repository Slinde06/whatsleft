<?php
/**
 * includes/functions.php
 * Klein herbruikbare helper funksies wat oor bladsye en api endpoints gebruik word.
 */

function e($value) {
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}

function calculate_age($dob) {
    $birth = new DateTime($dob);
    $today = new DateTime('today');
    return $birth->diff($today)->y;
}

function get_user(PDO $pdo, int $id) {
    $stmt = $pdo->prepare('SELECT * FROM users WHERE id = ?');
    $stmt->execute([$id]);
    return $stmt->fetch();
}

function get_user_photos(PDO $pdo, int $userId) {
    $stmt = $pdo->prepare('SELECT * FROM user_photos WHERE user_id = ? ORDER BY sort_order ASC, id ASC');
    $stmt->execute([$userId]);
    return $stmt->fetchAll();
}

function get_primary_photo(PDO $pdo, int $userId) {
    $photos = get_user_photos($pdo, $userId);
    return $photos[0]['photo_path'] ?? null;
}

function get_user_hobbies(PDO $pdo, int $userId) {
    $stmt = $pdo->prepare('SELECT hobby FROM user_hobbies WHERE user_id = ? ORDER BY id ASC');
    $stmt->execute([$userId]);
    return array_column($stmt->fetchAll(), 'hobby');
}

function get_user_personality(PDO $pdo, int $userId) {
    $stmt = $pdo->prepare('SELECT * FROM user_personality WHERE user_id = ?');
    $stmt->execute([$userId]);
    $row = $stmt->fetch();
    if (!$row) {
        return ['introvert_extrovert' => 50, 'analytical_creative' => 50, 'tech_savvy' => 50, 'sporty' => 50];
    }
    return $row;
}

/**
 * Die id van die gebruiker se lewende match-vennoot, of null as hulle nie tans gematch is nie.
 */
function get_match_partner_id(PDO $pdo, int $userId, int $matchId) {
    $stmt = $pdo->prepare('SELECT user1_id, user2_id FROM matches WHERE id = ?');
    $stmt->execute([$matchId]);
    $row = $stmt->fetch();
    if (!$row) return null;
    return $row['user1_id'] == $userId ? $row['user2_id'] : $row['user1_id'];
}

function json_response($data, int $status = 200) {
    http_response_code($status);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}

function redirect(string $path) {
    header('Location: ' . $path);
    exit;
}

/**
 * Bou 'n URL vanaf die projek se root af, bv. base_url('frontend/pages/home.php').
 * Werk maak nie saak onder watter folder naam hierdie projek in htdocs/ sit nie.
 */
function base_url(string $path = '') {
    return APP_URL_BASE . '/' . ltrim($path, '/');
}

/**
 * Pad na 'n gebruiker se foto vir gebruik in <img src="">, val terug op 'n placeholder.
 */
function photo_url($photoPath) {
    if (empty($photoPath)) {
        return base_url('frontend/assets/images/placeholder-photo.svg');
    }
    return base_url('uploads/profile_photos/' . $photoPath);
}
