<?php
require_once __DIR__ . '/../includes/auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_response(['success' => false, 'error' => 'Invalid request method.'], 405);
}

$input = json_decode(file_get_contents('php://input'), true) ?? $_POST;
$photoId = (int) ($input['photo_id'] ?? 0);

$stmt = $pdo->prepare('SELECT * FROM user_photos WHERE id = ? AND user_id = ?');
$stmt->execute([$photoId, $currentUser['id']]);
$photo = $stmt->fetch();

if (!$photo) {
    json_response(['success' => false, 'error' => 'Photo not found.'], 404);
}

$path = __DIR__ . '/../uploads/profile_photos/' . $photo['photo_path'];
if (is_file($path)) {
    @unlink($path);
}

$pdo->prepare('DELETE FROM user_photos WHERE id = ?')->execute([$photoId]);

json_response(['success' => true]);
