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
    // Bewaarmiddel vir data-teenstrydigheid - herstel en stuur terug na discover toe.
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

<div class="match-pagebox-main">
    <div class="row g-4">
        <div class="col-lg-4">
            <div class="match-photoframe-left mb-3">
                <div class="match-photo-box-left mb-3">
                    <img src="<?= e(photo_url($photos[0]['photo_path'] ?? null)) ?>" alt="<?= e($partner['first_name']) ?>">
                </div>
            </div>
            <h1 class="h2 text-white"><?= e($partner['first_name']) ?></h1>
            <p class="text-white-50"><?= e($partner['location']) ?> &middot; <?= (int) calculate_age($partner['dob']) ?> years old</p>
            <p class="text-white"><?= nl2br(e($partner['description'])) ?></p>
            <div>
                <?php foreach ($hobbies as $hobby): ?>
                    <span class="appletag"><?= e($hobby) ?></span>
                <?php endforeach; ?>
            </div>

            <div class="match-sliderbox-personality mt-3">
                <h3 class="h6 mb-3">Personality / Behaviors</h3>
                <p class="match-slidertext-personality">Where <?= e($partner['first_name']) ?> placed themselves on each scale - the closer to an end, the stronger that trait.</p>
                <div class="match-slider-introvert-extrovert">
                    <div class="match-slider-introvert-extrovert-labels"><span>Introvert</span><span>Extrovert</span></div>
                    <input type="range" min="0" max="100" value="<?= (int) $personality['introvert_extrovert'] ?>" disabled>
                </div>
                <div class="match-slider-analytical-creative">
                    <div class="match-slider-analytical-creative-labels"><span>Analytical</span><span>Creative</span></div>
                    <input type="range" min="0" max="100" value="<?= (int) $personality['analytical_creative'] ?>" disabled>
                </div>
                <div class="match-slider-tech-savvy">
                    <div class="match-slider-tech-savvy-labels"><span>Tech Savvy</span><span>Not Technical</span></div>
                    <input type="range" min="0" max="100" value="<?= (int) $personality['tech_savvy'] ?>" disabled>
                </div>
                <div class="match-slider-sporty mb-0">
                    <div class="match-slider-sporty-labels"><span>Sporty</span><span>Un-sporty</span></div>
                    <input type="range" min="0" max="100" value="<?= (int) $personality['sporty'] ?>" disabled>
                </div>
            </div>
        </div>

        <div class="col-lg-8">
            <div class="match-bigbox-right">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h2 class="h4 mb-0">Your shared space with <?= e($partner['first_name']) ?></h2>
                    <div class="d-flex gap-2 align-items-center">
                        <!-- klein demo-net kortpad: eindig die match onmiddellik sonder 'n
                             publieke rede, net sodat dit vinnig is om te herstel terwyl jy
                             vir mense wys hoe die werf werk. die regte "Unmatch" vloei
                             (met die publieke rede) bly dieselfde. -->
                        <button class="btn match-button-quick-unmatch btn-sm" id="quick-unmatch-btn" title="Demo only - instantly unmatch, no reason needed">Quick unmatch (demo)</button>
                        <a href="<?= e(base_url('frontend/pages/unmatch_reason.php')) ?>" class="btn match-button-unmatch btn-sm">Unmatch</a>
                    </div>
                </div>
                <div id="quick-unmatch-message"></div>

                <div class="match-chatbox-window" id="chat-window">
                    <?php if (empty($messages)): ?>
                        <p class="text-muted text-center mt-3" id="chat-empty-msg">No messages yet - say hi to <?= e($partner['first_name']) ?>!</p>
                    <?php endif; ?>
                    <?php foreach ($messages as $m): ?>
                        <div class="bubble1 <?= $m['sender_id'] == $currentUser['id'] ? 'mine' : 'theirs' ?>">
                            <?= nl2br(e($m['message'])) ?>
                        </div>
                    <?php endforeach; ?>
                </div>

                <form id="chat-form" class="d-flex gap-2 mt-3">
                    <input type="text" id="chat-input" class="form-control match-input-chat" placeholder="Say something..." autocomplete="off" required>
                    <button type="submit" class="btn match-button-send">Send</button>
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
<script>
    // Net vir demo doeleindes - quick unmatch knoppie, spring die publieke rede stap heeltemal oor.
    document.getElementById('quick-unmatch-btn').addEventListener('click', function () {
        if (!confirm('Demo unmatch: this instantly ends the match with no reason needed. Continue?')) return;
        fetch(<?= json_encode(base_url('api/quick_unmatch_action.php')) ?>, { method: 'POST' })
            .then(r => r.json())
            .then(data => {
                const box = document.getElementById('quick-unmatch-message');
                if (data.success) {
                    window.location.href = <?= json_encode(base_url('frontend/pages/home.php')) ?>;
                } else {
                    box.innerHTML = '<div class="uhohbox">' + (data.error || 'Could not unmatch.') + '</div>';
                }
            });
    });
</script>
</body>
</html>
