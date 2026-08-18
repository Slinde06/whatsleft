<?php
$activePage = 'home';
require_once __DIR__ . '/../../includes/auth.php';

$isMatched = !empty($currentUser['is_matched']) && !empty($currentUser['current_match_id']);

// Can this user undo their most recent ended match?
$canUndo = false;
$undoMatch = null;
if (!$currentUser['undo_used'] && !$isMatched) {
    $stmt = $pdo->prepare(
        "SELECT m.*, u.first_name AS other_first_name
         FROM matches m
         JOIN users u ON u.id = IF(m.user1_id = ?, m.user2_id, m.user1_id)
         WHERE (m.user1_id = ? OR m.user2_id = ?) AND m.status = 'ended'
         ORDER BY m.ended_at DESC LIMIT 1"
    );
    $stmt->execute([$currentUser['id'], $currentUser['id'], $currentUser['id']]);
    $undoMatch = $stmt->fetch();
    $canUndo = (bool) $undoMatch;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Home - What's Left</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="<?= e(base_url('frontend/assets/css/style.css')) ?>" rel="stylesheet">
</head>
<body>

<?php require __DIR__ . '/../../includes/nav.php'; ?>

<div class="wl-page">
    <div class="wl-home-flex">
        <div class="wl-home-side-img" style="background-image:url('<?= e(base_url('frontend/assets/images/evan-tang-IIxQMoT2F7A-unsplash.jpg')) ?>');"></div>

        <div class="wl-home-content">

            <?php if ($canUndo): ?>
                <div class="wl-panel mb-4 d-flex justify-content-between align-items-center flex-wrap gap-3">
                    <div>
                        <strong>You still have one undo left.</strong>
                        <div style="font-size:.9rem;">Your last match with <?= e($undoMatch['other_first_name']) ?> ended &mdash; you could restore it. This can only be done once, ever.</div>
                    </div>
                    <button class="btn btn-wl-outline" id="undo-btn">Undo last unmatch</button>
                </div>
                <div id="undo-message"></div>
            <?php endif; ?>

            <div class="text-center mb-5">
                <div class="wl-auth-eyebrow">A dating site with a memory</div>
                <h1 style="color:var(--peach); font-size:2.4rem;">The right match <span style="color:var(--white)">changes everything,</span></h1>
                <p style="color:#d9d3e8;">Every choice you make here is permanent and public. No ghosting, no deleting, no do-overs &mdash; just one honest shot at the right match.</p>
            </div>

            <div class="row g-4 text-center mb-5" style="color:#e9e4f2;">
                <div class="col-md-4">
                    <h3 class="h5" style="color:var(--white)">One match changes your whole site</h3>
                    <p style="font-size:.92rem;">The moment you match, your entire experience moves to a new, private space built for the two of you. There&rsquo;s no going back to browsing.</p>
                </div>
                <div class="col-md-4">
                    <h3 class="h5" style="color:var(--white)">One undo. Ever.</h3>
                    <p style="font-size:.92rem;">Across your entire time on What&rsquo;s Left, you get exactly one undo. Spend it carefully &mdash; it doesn&rsquo;t come back.</p>
                </div>
                <div class="col-md-4">
                    <h3 class="h5" style="color:var(--white)">Nothing is ever deleted</h3>
                    <p style="font-size:.92rem;">Every match, unmatch, and reason you give stays on the record &mdash; visible to others &mdash; for as long as you&rsquo;re a member.</p>
                </div>
            </div>

            <div class="wl-auth-eyebrow text-center">How it works</div>
            <h2 class="text-center mb-4" style="color:var(--white)">Four steps. No undo.</h2>
            <div class="row g-4 mb-5" style="color:#e9e4f2;">
                <div class="col-md-6">
                    <h3 class="h6" style="color:var(--white)">Build your profile</h3>
                    <p style="font-size:.9rem;">Sign up and tell the truth. Your profile is the only pitch you get.</p>
                </div>
                <div class="col-md-6">
                    <h3 class="h6" style="color:var(--white)">Meet one person at a time</h3>
                    <p style="font-size:.9rem;">You&rsquo;re shown one profile at a time. You can move to the next one, but never back.</p>
                </div>
                <div class="col-md-6">
                    <h3 class="h6" style="color:var(--white)">Decide</h3>
                    <p style="font-size:.9rem;">Pass and explain why in public, or match and mean it.</p>
                </div>
                <div class="col-md-6">
                    <h3 class="h6" style="color:var(--white)">Live in the new space</h3>
                    <p style="font-size:.9rem;">Matching transforms your account into a shared space with them.</p>
                </div>
            </div>

        </div>

        <div class="wl-home-side-img" style="background-image:url('<?= e(base_url('frontend/assets/images/yianni-mathioudakis-jKaznAY9iAo-unsplash.jpg')) ?>');"></div>
    </div>
</div>

<div style="background:#0f1015; padding: 3.5rem 1.5rem; text-align:center;">
    <?php if ($isMatched): ?>
        <h2 style="color:var(--white)">You&rsquo;re in a match.</h2>
        <p style="color:#c9c4d4;">Head into your shared space.</p>
        <a href="<?= e(base_url('frontend/pages/match_space.php')) ?>" class="btn btn-wl-primary btn-lg">Go to Match</a>
    <?php else: ?>
        <h2 style="color:var(--white)">Ready to make a decision that matters?</h2>
        <p style="color:#c9c4d4;">Go discover what&rsquo;s left.</p>
        <a href="<?= e(base_url('frontend/pages/discover.php')) ?>" class="btn btn-wl-primary btn-lg">Discover</a>
    <?php endif; ?>
</div>

<?php require __DIR__ . '/../../includes/footer.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<?php if ($canUndo): ?>
<script>
    document.getElementById('undo-btn').addEventListener('click', function () {
        if (!confirm('This is your one and only undo, ever. Are you sure?')) return;
        fetch(<?= json_encode(base_url('api/undo_action.php')) ?>, { method: 'POST' })
            .then(r => r.json())
            .then(data => {
                const box = document.getElementById('undo-message');
                if (data.success) {
                    window.location.href = <?= json_encode(base_url('frontend/pages/match_space.php')) ?>;
                } else {
                    box.innerHTML = '<div class="wl-alert-error">' + (data.error || 'Could not undo.') + '</div>';
                }
            });
    });
</script>
<?php endif; ?>
</body>
</html>
