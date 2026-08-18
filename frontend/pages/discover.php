<?php
$activePage = 'discover';
require_once __DIR__ . '/../../includes/auth.php';

// Already matched? There's nothing to discover - send them to their MatchSpace.
if (!empty($currentUser['is_matched']) && !empty($currentUser['current_match_id'])) {
    redirect(base_url('frontend/pages/match_space.php'));
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Discover - What's Left</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="<?= e(base_url('frontend/assets/css/style.css')) ?>" rel="stylesheet">
</head>
<body>

<?php require __DIR__ . '/../../includes/nav.php'; ?>

<div class="wl-page" id="discover-app">

    <div id="skip-badge" class="text-center mb-3 fw-bold" style="color:var(--peach); display:none;">
        1 SKIP REMAINING
    </div>

    <div id="loading-state" class="text-center text-white py-5">Loading the next profile&hellip;</div>

    <div id="no-more-state" class="wl-panel text-center py-5" style="display:none;">
        <h2>That&rsquo;s everyone.</h2>
        <p>There&rsquo;s no one left to discover right now &mdash; check back later.</p>
        <a href="<?= e(base_url('frontend/pages/home.php')) ?>" class="btn btn-wl-outline">Back to Home</a>
    </div>

    <div class="row g-4" id="candidate-state" style="display:none;">
        <div class="col-lg-4">
            <div class="wl-photo-frame">
                <div class="photo-box mb-3">
                    <img id="c-photo" src="" alt="Candidate photo">
                </div>
                <div class="d-flex gap-2">
                    <button class="btn btn-wl-secondary flex-fill" id="dislike-btn">Dislike</button>
                    <button class="btn btn-wl-primary flex-fill" id="like-btn">Like</button>
                </div>
            </div>
        </div>

        <div class="col-lg-5">
            <div class="wl-panel">
                <h1 id="c-name" class="h2"></h1>
                <p id="c-location" class="mb-3"></p>
                <p id="c-description"></p>
                <div id="c-hobbies" class="mb-3"></div>

                <div class="wl-slider-card">
                    <h3 class="h6 mb-3">Personality / Behaviors</h3>
                    <div class="wl-slider">
                        <div class="labels"><span>Introvert</span><span>Extrovert</span></div>
                        <input type="range" min="0" max="100" id="p-ie" disabled>
                    </div>
                    <div class="wl-slider">
                        <div class="labels"><span>Analytical</span><span>Creative</span></div>
                        <input type="range" min="0" max="100" id="p-ac" disabled>
                    </div>
                    <div class="wl-slider">
                        <div class="labels"><span>Tech Savvy</span><span>Not Technical</span></div>
                        <input type="range" min="0" max="100" id="p-ts" disabled>
                    </div>
                    <div class="wl-slider mb-0">
                        <div class="labels"><span>Sporty</span><span>Un-sporty</span></div>
                        <input type="range" min="0" max="100" id="p-sp" disabled>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-3">
            <div class="wl-rules-box mb-3">
                <div class="wl-rules-title">BEFORE YOU DECIDE</div>
                <ul>
                    <li>You may skip exactly one profile, ever.</li>
                    <li>Passing (Dislike) requires a public, detailed reason.</li>
                    <li>Matching moves you to a shared space &mdash; you can&rsquo;t browse again unless it ends.</li>
                </ul>
            </div>
            <button class="btn btn-wl-outline w-100" id="skip-btn">Next (use 1-time skip)</button>
            <div id="action-message" class="mt-3"></div>
        </div>
    </div>
</div>

<?php require __DIR__ . '/../../includes/footer.php'; ?>

<!-- Dislike reason modal -->
<div class="modal fade" id="dislikeModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Tell the record why.</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <p class="text-muted">This reason becomes part of the public record, permanently.</p>
        <div class="mb-3">
            <label>Short description:</label>
            <input type="text" class="form-control" id="reason-short" maxlength="150">
        </div>
        <div class="mb-3">
            <label>Long description (as much detail as possible):</label>
            <textarea class="form-control" id="reason-long" rows="4"></textarea>
        </div>
        <div class="wl-alert-error" id="reason-error" style="display:none;"></div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-wl-secondary" data-bs-dismiss="modal">Back</button>
        <button type="button" class="btn btn-wl-primary" id="confirm-dislike-btn">Save and pass</button>
      </div>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    window.WL = { baseUrl: <?= json_encode(base_url('')) ?> };
</script>
<script src="<?= e(base_url('frontend/assets/js/discover.js')) ?>"></script>
</body>
</html>
