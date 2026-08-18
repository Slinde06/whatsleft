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
    redirect(base_url('frontend/pages/home.php'));
}

$photos = get_user_photos($pdo, $partner['id']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Before you can match again - What's Left</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="<?= e(base_url('frontend/assets/css/style.css')) ?>" rel="stylesheet">
</head>
<body>

<?php require __DIR__ . '/../../includes/nav.php'; ?>

<div class="wl-page">
    <div class="row g-4">
        <div class="col-lg-5">
            <h1 class="text-white h2">Before you can match again</h1>
            <p class="text-white-50">You&rsquo;ve chosen to unmatch. On What&rsquo;s Left, this decision and the reason behind it becomes part of your public profile and theirs, permanently.</p>

            <div class="wl-photo-frame mt-3">
                <div class="photo-box mb-3">
                    <img src="<?= e(photo_url($photos[0]['photo_path'] ?? null)) ?>" alt="<?= e($partner['first_name']) ?>">
                </div>
                <h2 class="h4 text-white mb-0"><?= e($partner['first_name']) ?></h2>
                <p class="text-white-50 mb-0"><?= e($partner['location']) ?></p>
                <p class="text-white"><?= nl2br(e($partner['description'])) ?></p>
            </div>
        </div>

        <div class="col-lg-7">
            <h2 style="color:var(--coral)">Tell the record why.</h2>

            <div id="form-error"></div>

            <div class="mb-3">
                <label class="text-white">Reason:</label>
                <label class="text-white-50 d-block" style="font-size:.85rem;">short description:</label>
                <input type="text" class="form-control wl-input" id="reason-short" maxlength="150">
            </div>
            <div class="mb-3">
                <label class="text-white-50" style="font-size:.85rem;">Long description (as much detail as possible):</label>
                <textarea class="form-control wl-input" id="reason-long" rows="6"></textarea>
            </div>

            <div class="wl-rules-box">
                <div class="wl-rules-title">This will be public and permanent</div>
                <ul>
                    <li>Once published, this reason cannot be edited or removed.</li>
                    <li>You have one undo across your entire account &mdash; using it here means you can&rsquo;t use it again later.</li>
                </ul>
            </div>

            <div class="d-flex justify-content-between mt-4">
                <a href="<?= e(base_url('frontend/pages/match_space.php')) ?>" class="btn btn-wl-secondary">Back</a>
                <button class="btn btn-wl-primary" id="save-unmatch-btn">Save and unmatch</button>
            </div>
        </div>
    </div>
</div>

<?php require __DIR__ . '/../../includes/footer.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    document.getElementById('save-unmatch-btn').addEventListener('click', function () {
        const reasonShort = document.getElementById('reason-short').value.trim();
        const reasonLong = document.getElementById('reason-long').value.trim();
        const errorBox = document.getElementById('form-error');
        errorBox.innerHTML = '';

        if (!reasonShort || !reasonLong) {
            errorBox.innerHTML = '<div class="wl-alert-error">Both a short and a detailed reason are required.</div>';
            return;
        }

        if (!confirm('This cannot be edited or undone unless you spend your one-time undo. Continue?')) return;

        fetch(<?= json_encode(base_url('api/unmatch_action.php')) ?>, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ reason_short: reasonShort, reason_long: reasonLong })
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                window.location.href = <?= json_encode(base_url('frontend/pages/home.php')) ?>;
            } else {
                errorBox.innerHTML = '<div class="wl-alert-error">' + (data.error || 'Could not unmatch.') + '</div>';
            }
        });
    });
</script>
</body>
</html>
