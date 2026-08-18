<?php
$activePage = 'public_reasons';
require_once __DIR__ . '/../../includes/auth.php';

// Pulls straight from the leaderboard_view SQL view (see database/whats_left.sql),
// which itself uses correlated subqueries over swipes/matches per user.
$stmt = $pdo->query(
    'SELECT * FROM leaderboard_view ORDER BY number_of_unmatches DESC, number_of_likes DESC'
);
$rows = $stmt->fetchAll();

// Photo per row (small join done here rather than in the view, kept simple).
$photoStmt = $pdo->prepare('SELECT photo_path FROM user_photos WHERE user_id = ? ORDER BY sort_order ASC LIMIT 1');
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Public reasons - What's Left</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="<?= e(base_url('frontend/assets/css/style.css')) ?>" rel="stylesheet">
</head>
<body>

<?php require __DIR__ . '/../../includes/nav.php'; ?>

<div class="wl-page wl-leaderboard">
    <h1 class="text-white mb-4">Leaderboard:</h1>

    <div class="table-responsive">
        <table class="table mb-0">
            <thead>
                <tr>
                    <th>Img:</th>
                    <th>Name:</th>
                    <th>Surname:</th>
                    <th>Number of unmatches:</th>
                    <th>Number of rejections:</th>
                    <th>Number of likes:</th>
                    <th>Number of dislikes:</th>
                    <th>Reason for most recent unmatch:</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($rows as $row): ?>
                    <?php
                        $photoStmt->execute([$row['id']]);
                        $photo = $photoStmt->fetch();
                    ?>
                    <tr>
                        <td><img class="avatar" src="<?= e(photo_url($photo['photo_path'] ?? null)) ?>" alt=""></td>
                        <td><?= e($row['first_name']) ?></td>
                        <td><?= e($row['last_name']) ?></td>
                        <td><?= (int) $row['number_of_unmatches'] ?></td>
                        <td><?= (int) $row['number_of_rejections'] ?></td>
                        <td><?= (int) $row['number_of_likes'] ?></td>
                        <td><?= (int) $row['number_of_dislikes'] ?></td>
                        <td><?= $row['reason_for_most_recent_unmatch'] ? e($row['reason_for_most_recent_unmatch']) : '<span class="opacity-50">&mdash;</span>' ?></td>
                    </tr>
                <?php endforeach; ?>
                <?php if (empty($rows)): ?>
                    <tr><td colspan="8" class="text-center text-white py-4">No members yet.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require __DIR__ . '/../../includes/footer.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
