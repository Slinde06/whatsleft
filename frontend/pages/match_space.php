<?php
$activePage = 'match';
require_once __DIR__ . '/../../includes/auth.php';

if (empty($currentUser['is_matched']) || empty($currentUser['current_match_id'])) {
    redirect(base_url('frontend/pages/discover.php'));
}

$matchId = (int) $currentUser['current_match_id'];
$partnerId = get_match_partner_id($pdo, (int) $currentUser['id'], $matchId);
$partner = $partnerId ? get_user($pdo, $partnerId) : null;

if (!$partner) {
    // Data inconsistency safeguard - reset and send back to discover.
    redirect(base_url('frontend/pages/discover.php'));
}

$photos = get_user_photos($pdo, $partner['id']);
$hobbies = get_user_hobbies($pdo, $partner['id']);
$personality = get_user_personality($pdo, $partner['id']);

$msgStmt = $pdo->prepare('SELECT * FROM messages WHERE match_id = ? ORDER BY id ASC');
$msgStmt->execute([$matchId]);
$messages = $msgStmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Your match - What's Left</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="<?= e(base_url('frontend/assets/css/style.css')) ?>" rel="stylesheet">
</head>
<body>

<?php require __DIR__ . '/../../includes/nav.php'; ?>

<div class="wl-page">
    <div class="row g-4">
        <div class="col-lg-4">
            <div class="wl-photo-frame mb-3">
                <div class="photo-box mb-3">
                    <img src="<?= e(photo_url($photos[0]['photo_path'] ?? null)) ?>" alt="<?= e($partner['first_name']) ?>">
                </div>
            </div>
            <h1 class="h2 text-white"><?= e($partner['first_name']) ?></h1>
            <p class="text-white-50"><?= e($partner['location']) ?> &middot; <?= (int) calculate_age($partner['dob']) ?> years old</p>
            <p class="text-white"><?= nl2br(e($partner['description'])) ?></p>
            <div>
                <?php foreach ($hobbies as $hobby): ?>
                    <span class="wl-tag"><?= e($hobby) ?></span>
                <?php endforeach; ?>
            </div>

            <div class="wl-slider-card mt-3">
                <h3 class="h6 mb-3">Personality / Behaviors</h3>
                <div class="wl-slider">
                    <div class="labels"><span>Introvert</span><span>Extrovert</span></div>
                    <input type="range" min="0" max="100" value="<?= (int) $personality['introvert_extrovert'] ?>" disabled>
                </div>
                <div class="wl-slider">
                    <div class="labels"><span>Analytical</span><span>Creative</span></div>
                    <input type="range" min="0" max="100" value="<?= (int) $personality['analytical_creative'] ?>" disabled>
                </div>
                <div class="wl-slider">
                    <div class="labels"><span>Tech Savvy</span><span>Not Technical</span></div>
                    <input type="range" min="0" max="100" value="<?= (int) $personality['tech_savvy'] ?>" disabled>
                </div>
                <div class="wl-slider mb-0">
                    <div class="labels"><span>Sporty</span><span>Un-sporty</span></div>
                    <input type="range" min="0" max="100" value="<?= (int) $personality['sporty'] ?>" disabled>
                </div>
            </div>
        </div>

        <div class="col-lg-8">
            <div class="wl-panel">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h2 class="h4 mb-0">Your shared space with <?= e($partner['first_name']) ?></h2>
                    <a href="<?= e(base_url('frontend/pages/unmatch_reason.php')) ?>" class="btn btn-wl-secondary btn-sm">Unmatch</a>
                </div>

                <div class="wl-chat-window" id="chat-window">
                    <?php foreach ($messages as $m): ?>
                        <div class="wl-chat-bubble <?= $m['sender_id'] == $currentUser['id'] ? 'mine' : 'theirs' ?>">
                            <?= nl2br(e($m['message'])) ?>
                        </div>
                    <?php endforeach; ?>
                </div>

                <form id="chat-form" class="d-flex gap-2 mt-3">
                    <input type="text" id="chat-input" class="form-control wl-input" placeholder="Say something..." autocomplete="off" required>
                    <button type="submit" class="btn btn-wl-primary">Send</button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require __DIR__ . '/../../includes/footer.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    window.WL = {
        baseUrl: <?= json_encode(base_url('')) ?>,
        currentUserId: <?= (int) $currentUser['id'] ?>,
        lastMessageId: <?= (int) ($messages ? $messages[count($messages) - 1]['id'] : 0) ?>
    };
</script>
<script src="<?= e(base_url('frontend/assets/js/chat.js')) ?>"></script>
</body>
</html>
