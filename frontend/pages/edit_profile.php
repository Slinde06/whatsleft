<?php
$activePage = 'profile';
require_once __DIR__ . '/../../includes/auth.php';

$photos      = get_user_photos($pdo, $currentUser['id']);
$hobbies     = get_user_hobbies($pdo, $currentUser['id']);
$personality = get_user_personality($pdo, $currentUser['id']);

$welcome = isset($_GET['welcome']);

// Tel gepubliseerde (geëindigde) matches se redes vir die "PERMANENT ACCOUNT RULES" blokkie.
$reasonsStmt = $pdo->prepare(
    "SELECT COUNT(*) AS c FROM matches WHERE (user1_id = ? OR user2_id = ?) AND status = 'ended' AND ended_by = ?"
);
$reasonsStmt->execute([$currentUser['id'], $currentUser['id'], $currentUser['id']]);
$publishedReasons = (int) $reasonsStmt->fetch()['c'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Customize your profile - What's Left</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="<?= e(base_url('frontend/assets/css/style.css')) ?>" rel="stylesheet">
</head>
<body>

<?php require __DIR__ . '/../../includes/nav.php'; ?>

<div class="profile-pagebox-main">

    <?php if ($welcome): ?>
        <div class="yaybox">Account created! Add a few photos and tell people about yourself before you head to Home.</div>
    <?php endif; ?>
    <div id="save-message"></div>

    <div class="row g-4">
        <div class="col-lg-5">
            <h1 style="color:var(--white)"><?= e($currentUser['first_name']) ?></h1>

            <div class="profile-photoframe-left mb-3">
                <div class="profile-photo-box-primary mb-3" id="primary-photo-box">
                    <img id="primary-photo-img" src="<?= e(photo_url($photos[0]['photo_path'] ?? null)) ?>" alt="Primary photo">
                </div>
                <label class="text-white">Description of you:</label>
                <textarea class="form-control profile-input-description" id="description" rows="5" placeholder="Trail runs on Saturdays, terrible at board games..."><?= e($currentUser['description']) ?></textarea>
            </div>

            <label class="text-white">Location:</label>
            <input type="text" class="form-control profile-input-location mb-3" id="location" placeholder="e.g. Pretoria" value="<?= e($currentUser['location']) ?>">

            <label class="text-white">Hobbies/Likes: <span class="opacity-75" style="font-size:.8rem;">(press Enter to add)</span></label>
            <input type="text" class="form-control profile-input-hobby mb-2" id="hobby-input" placeholder="Type a hobby and press Enter">
            <div id="hobby-tags" class="mb-3">
                <?php foreach ($hobbies as $hobby): ?>
                    <span class="appletag" data-hobby="<?= e($hobby) ?>"><?= e($hobby) ?> <a href="#" class="remove-hobby text-danger text-decoration-none">&times;</a></span>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="col-lg-7">
            <div class="profile-bigbox-right">
                <h2>Customize your profile:</h2>
                <p>This is your only pitch - make it count. Changes save when you press &ldquo;Save changes&rdquo;.</p>

                <h3 class="h5">Photos:</h3>
                <p style="font-size:.9rem;">Add up to 6 photos. Your first photo is shown first in Discover.</p>
                <div class="row g-2 mb-4" id="photo-grid">
                    <?php foreach ($photos as $p): ?>
                        <div class="col-3 photo-thumb-wrap" data-photo-id="<?= (int) $p['id'] ?>">
                            <img class="photothumb" src="<?= e(photo_url($p['photo_path'])) ?>" alt="Profile photo">
                            <a href="#" class="remove-photo small text-white d-block text-center">Remove</a>
                        </div>
                    <?php endforeach; ?>
                    <?php if (count($photos) < 6): ?>
                        <div class="col-3" id="add-photo-slot">
                            <label for="photo-upload" class="photothumb d-flex align-items-center justify-content-center" style="cursor:pointer; border:2px dashed var(--white); background:transparent;">
                                + ADD
                            </label>
                            <input type="file" id="photo-upload" accept="image/*" class="d-none">
                        </div>
                    <?php endif; ?>
                </div>

                <hr style="border-color: rgba(255,255,255,.3)">

                <h3 class="h5">Info:</h3>
                <div class="row g-3">
                    <div class="col-md-6">
                        <label>First Name:</label>
                        <input type="text" class="form-control profile-input-first-name" id="first_name" value="<?= e($currentUser['first_name']) ?>">
                    </div>
                    <div class="col-md-6">
                        <label>Last Name:</label>
                        <input type="text" class="form-control profile-input-last-name" id="last_name" value="<?= e($currentUser['last_name']) ?>">
                    </div>
                    <div class="col-12">
                        <label>Email:</label>
                        <input type="email" class="form-control profile-input-email" id="email" value="<?= e($currentUser['email']) ?>">
                    </div>
                    <div class="col-md-6">
                        <label>Date of birth (can&rsquo;t edit):</label>
                        <input type="text" class="form-control profile-input-dob" value="<?= e($currentUser['dob']) ?>" disabled>
                    </div>
                    <div class="col-md-6">
                        <label>Looking for:</label>
                        <select class="form-control profile-input-looking-for" id="looking_for">
                            <?php foreach (['Male', 'Female', 'Everyone'] as $opt): ?>
                                <option value="<?= $opt ?>" <?= $currentUser['looking_for'] === $opt ? 'selected' : '' ?>><?= $opt ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <h3 class="h5 mt-4">Personality / Behaviors</h3>
                <p class="profile-slidertext2-personality">Drag each slider to where you actually fall between the two words - not the middle by default. This is what other members will see on your card, so it&rsquo;s worth getting right.</p>
                <div class="profile-sliderbox-personality">
                    <div class="profile-slider-introvert-extrovert">
                        <div class="profile-slider-introvert-extrovert-labels"><span>Introvert</span><span>Extrovert</span></div>
                        <input type="range" min="0" max="100" id="slider-ie" value="<?= (int) $personality['introvert_extrovert'] ?>">
                    </div>
                    <div class="profile-slider-analytical-creative">
                        <div class="profile-slider-analytical-creative-labels"><span>Analytical</span><span>Creative</span></div>
                        <input type="range" min="0" max="100" id="slider-ac" value="<?= (int) $personality['analytical_creative'] ?>">
                    </div>
                    <div class="profile-slider-tech-savvy">
                        <div class="profile-slider-tech-savvy-labels"><span>Tech Savvy</span><span>Not Technical</span></div>
                        <input type="range" min="0" max="100" id="slider-ts" value="<?= (int) $personality['tech_savvy'] ?>">
                    </div>
                    <div class="profile-slider-sporty mb-0">
                        <div class="profile-slider-sporty-labels"><span>Sporty</span><span>Un-sporty</span></div>
                        <input type="range" min="0" max="100" id="slider-sp" value="<?= (int) $personality['sporty'] ?>">
                    </div>
                </div>

                <div class="profile-rulesbox-bottom mt-4">
                    <div class="profile-rulesbox-title">PERMANENT ACCOUNT RULES</div>
                    <ul>
                        <li>Skips available: <?= $currentUser['skip_used'] ? '0' : '1' ?> of 1 remaining</li>
                        <li>Undos available: <?= $currentUser['undo_used'] ? '0' : '1' ?> of 1 remaining</li>
                        <li>Public reasons on record: <?= $publishedReasons ?> published</li>
                        <li>Profile deletion: Not permitted</li>
                    </ul>
                </div>

                <div class="text-end mt-4">
                    <button class="btn profile-button-save" id="save-changes-btn">Save changes</button>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require __DIR__ . '/../../includes/footer.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    window.WL = {
        baseUrl: <?= json_encode(base_url('')) ?>,
        welcome: <?= json_encode($welcome) ?>
    };
</script>
<script src="<?= e(base_url('frontend/assets/js/profile.js')) ?>"></script>
</body>
</html>
