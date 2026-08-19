<?php
$activePage = 'home';
require_once __DIR__ . '/../../includes/auth.php';

$isMatched = !empty($currentUser['is_matched']) && !empty($currentUser['current_match_id']);

// Kan hierdie gebruiker hulle mees onlangse geëindigde match ongedaan maak?
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

<?php if ($canUndo): ?>
    <div class="home-pagebox-undo pb-0">
        <div class="home-bigbox-undo mb-4 d-flex justify-content-between align-items-center flex-wrap gap-3">
            <div>
                <strong>You still have one undo left.</strong>
                <div style="font-size:.9rem;">Your last match with <?= e($undoMatch['other_first_name']) ?> ended - you could restore it. This can only be done once, ever.</div>
            </div>
            <button class="btn home-button-undo-top" id="undo-btn">Undo last unmatch</button>
        </div>
        <div id="undo-message"></div>
    </div>
<?php endif; ?>

<!-- Die hero het vroeer net op die plein navy agtergrond gesit - het nou 'n
     regte prent-agtergrond met 'n gradient bo-oor gegee sodat dit soos 'n
     regte landing bladsy lyk, nie 'n mock-up nie. -->
<div class="home-herosection-top">
    <div class="home-pagebox-hero">
        <div class="row align-items-center g-4 mb-5">
            <div class="col-lg-2 d-none d-lg-block">
                <div class="home-sidepic-left">
                    <img src="<?= e(base_url('frontend/assets/images/brooke-cagle-oTweoxMKdkA-unsplash.jpg')) ?>" alt="What's Left member">
                </div>
            </div>
            <div class="col-lg-8 text-center">
                <div class="home-tinytext-hero">A dating site with a memory</div>
                <h1 style="color:var(--peach); font-size:2.6rem;">The right match <span style="color:var(--white)">changes everything,</span></h1>
                <p style="color:#d9d3e8; font-size:1.05rem;">Every choice you make here is permanent and public. No ghosting, no deleting, no do-overs - just one honest shot at the right match.</p>
            </div>
            <div class="col-lg-2 d-none d-lg-block">
                <div class="home-sidepic-right">
                    <img src="<?= e(base_url('frontend/assets/images/toa-heftiba-O3ymvT7Wf9U-unsplash.jpg')) ?>" alt="What's Left member">
                </div>
            </div>
        </div>
    </div>
</div>

<div class="home-pagebox-main pt-5">

    <div class="row g-4 text-center mb-5">
        <div class="col-md-4">
            <div class="home-featurecard-one-match">
                <div class="home-featureicon-one-match">01</div>
                <h3 class="h5" style="color:var(--white)">One match changes your whole site</h3>
                <p style="font-size:.92rem; color:#e9e4f2;">The moment you match, your entire experience moves to a new, private space built for the two of you. There&rsquo;s no going back to browsing.</p>
            </div>
        </div>
        <div class="col-md-4">
            <div class="home-featurecard-one-undo">
                <div class="home-featureicon-one-undo">02</div>
                <h3 class="h5" style="color:var(--white)">One undo. Ever.</h3>
                <p style="font-size:.92rem; color:#e9e4f2;">Across your entire time on What&rsquo;s Left, you get exactly one undo. Spend it carefully - it doesn&rsquo;t come back.</p>
            </div>
        </div>
        <div class="col-md-4">
            <div class="home-featurecard-nothing-deleted">
                <div class="home-featureicon-nothing-deleted">03</div>
                <h3 class="h5" style="color:var(--white)">Nothing is ever deleted</h3>
                <p style="font-size:.92rem; color:#e9e4f2;">Every match, unmatch, and reason you give stays on the record - visible to others - for as long as you&rsquo;re a member.</p>
            </div>
        </div>
    </div>

    <div class="home-tinytext-steps text-center">How it works</div>
    <h2 class="text-center mb-4" style="color:var(--white)">Four steps. No undo.</h2>
    <div class="row g-4 mb-5" style="color:#e9e4f2;">
        <div class="col-md-6">
            <div class="home-stepcard-1-build-profile">
                <span class="home-stepnum-1-build-profile">1</span>
                <div>
                    <h3 class="h6 mb-1" style="color:var(--white)">Build your profile</h3>
                    <p style="font-size:.9rem; margin:0;">Sign up and tell the truth. Your profile is the only pitch you get.</p>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="home-stepcard-2-meet-one">
                <span class="home-stepnum-2-meet-one">2</span>
                <div>
                    <h3 class="h6 mb-1" style="color:var(--white)">Meet one person at a time</h3>
                    <p style="font-size:.9rem; margin:0;">You&rsquo;re shown one profile at a time. You can move to the next one, but never back.</p>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="home-stepcard-3-decide">
                <span class="home-stepnum-3-decide">3</span>
                <div>
                    <h3 class="h6 mb-1" style="color:var(--white)">Decide</h3>
                    <p style="font-size:.9rem; margin:0;">Pass and explain why in public, or match and mean it.</p>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="home-stepcard-4-live-in-space">
                <span class="home-stepnum-4-live-in-space">4</span>
                <div>
                    <h3 class="h6 mb-1" style="color:var(--white)">Live in the new space</h3>
                    <p style="font-size:.9rem; margin:0;">Matching transforms your account into a shared space with them.</p>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="home-ctaband-bottom">
    <?php if ($isMatched): ?>
        <h2 style="color:var(--white)">You&rsquo;re in a match.</h2>
        <p style="color:#c9c4d4;">Head into your shared space.</p>
        <a href="<?= e(base_url('frontend/pages/match_space.php')) ?>" class="btn home-button-cta-bottom btn-lg">Go to Match</a>
    <?php else: ?>
        <h2 style="color:var(--white)">Ready to make a decision that matters?</h2>
        <p style="color:#c9c4d4;">Go discover what&rsquo;s left.</p>
        <a href="<?= e(base_url('frontend/pages/discover.php')) ?>" class="btn home-button-cta-bottom btn-lg">Discover</a>
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
                    box.innerHTML = '<div class="uhohbox">' + (data.error || 'Could not undo.') + '</div>';
                }
            });
    });
</script>
<?php endif; ?>
</body>
</html>
