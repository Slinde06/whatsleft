<?php
require_once __DIR__ . '/../includes/auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_response(['success' => false, 'error' => 'Invalid request method.'], 405);
}

$userId = (int) $currentUser['id'];

// Hoeveel foto's het hierdie gebruiker al?
$countStmt = $pdo->prepare('SELECT COUNT(*) AS c FROM user_photos WHERE user_id = ?');
$countStmt->execute([$userId]);
$existingCount = (int) $countStmt->fetch()['c'];

if ($existingCount >= 6) {
    json_response(['success' => false, 'error' => 'You already have 6 photos - remove one first.'], 422);
}

if (empty($_FILES['photo']) || $_FILES['photo']['error'] !== UPLOAD_ERR_OK) {
    json_response(['success' => false, 'error' => 'No photo was received.'], 422);
}

$file = $_FILES['photo'];

$allowed = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp', 'image/gif' => 'gif'];
$finfo = finfo_open(FILEINFO_MIME_TYPE);
$mime = finfo_file($finfo, $file['tmp_name']);
finfo_close($finfo);

if (!isset($allowed[$mime])) {
    json_response(['success' => false, 'error' => 'Only JPG, PNG, WEBP or GIF images are allowed.'], 422);
}

if ($file['size'] > 5 * 1024 * 1024) {
    json_response(['success' => false, 'error' => 'Photo must be smaller than 5MB.'], 422);
}

$ext = $allowed[$mime];
$filename = 'user' . $userId . '_' . bin2hex(random_bytes(6)) . '.' . $ext;
$destDir = __DIR__ . '/../uploads/profile_photos/';
$destPath = $destDir . $filename;

if (!is_dir($destDir)) {
    mkdir($destDir, 0755, true);
}

if (!move_uploaded_file($file['tmp_name'], $destPath)) {
    json_response(['success' => false, 'error' => 'Could not save the uploaded photo.'], 500);
}

$stmt = $pdo->prepare('INSERT INTO user_photos (user_id, photo_path, sort_order) VALUES (?, ?, ?)');
$stmt->execute([$userId, $filename, $existingCount]);

json_response([
    'success'    => true,
    'photo_id'   => (int) $pdo->lastInsertId(),
    'photo_url'  => photo_url($filename),
]);
