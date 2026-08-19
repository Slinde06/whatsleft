<?php
$activePage = 'discover';
require_once __DIR__ . '/../../includes/auth.php';

// Al gematch? Daar's niks meer om te ontdek nie - stuur hulle na hul MatchSpace toe.
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

<div class="discover-pagebox-main" id="discover-app">

    <div id="skip-badge" class="text-center mb-3 fw-bold" style="color:var(--peach); display:none;">
        1 SKIP REMAINING
    </div>

    <div id="loading-state" class="text-center text-white py-5">Loading the next profile&hellip;</div>

    <div id="no-more-state" class="discover-bigbox-nomore text-center py-5" style="display:none;">
        <h2>That&rsquo;s everyone.</h2>
        <p>There&rsquo;s no one left to discover right now - check back later.</p>
        <a href="<?= e(base_url('frontend/pages/home.php')) ?>" class="btn discover-button-back-home">Back to Home</a>
    </div>

    <div class="row g-4" id="candidate-state" style="display:none;">
        <div class="col-lg-4">
            <div class="discover-photoframe-left">
                <div class="discover-photo-box-left mb-3 position-relative">
                    <img id="c-photo" src="" alt="Candidate photo">
                    <button type="button" class="discover-photo-nav discover-photo-nav-prev" id="photo-prev-btn" style="display:none;" aria-label="Previous photo">&#10094;</button>
                    <button type="button" class="discover-photo-nav discover-photo-nav-next" id="photo-next-btn" style="display:none;" aria-label="Next photo">&#10095;</button>
                </div>
                <div class="d-flex gap-2">
                    <button class="btn discover-button-dislike flex-fill" id="dislike-btn">Dislike</button>
                    <button class="btn discover-button-like flex-fill" id="like-btn">Like</button>
                </div>
            </div>
        </div>

        <div class="col-lg-5">
            <div class="discover-bigbox-middle">
                <h1 id="c-name" class="h2"></h1>
                <p id="c-location" class="mb-3"></p>
                <p id="c-description"></p>
                <div id="c-hobbies" class="mb-3"></div>

                <div class="discover-sliderbox-personality">
                    <h3 class="h6 mb-3">Personality / Behaviors</h3>
                    <p class="discover-slidertext-personality">How this person placed themselves on each scale.</p>
                    <div class="discover-slider-introvert-extrovert">
                        <div class="discover-slider-introvert-extrovert-labels"><span>Introvert</span><span>Extrovert</span></div>
                        <input type="range" min="0" max="100" id="p-ie" disabled>
                    </div>
                    <div class="discover-slider-analytical-creative">
                        <div class="discover-slider-analytical-creative-labels"><span>Analytical</span><span>Creative</span></div>
                        <input type="range" min="0" max="100" id="p-ac" disabled>
                    </div>
                    <div class="discover-slider-tech-savvy">
                        <div class="discover-slider-tech-savvy-labels"><span>Tech Savvy</span><span>Not Technical</span></div>
                        <input type="range" min="0" max="100" id="p-ts" disabled>
                    </div>
                    <div class="discover-slider-sporty mb-0">
                        <div class="discover-slider-sporty-labels"><span>Sporty</span><span>Un-sporty</span></div>
                        <input type="range" min="0" max="100" id="p-sp" disabled>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-3">
            <div class="discover-rulesbox-right mb-3">
                <div class="discover-rulesbox-title">BEFORE YOU DECIDE</div>
                <ul>
                    <li>You may skip exactly one profile with no reason, ever - after that, Dislike itself becomes a quick skip.</li>
                    <li>Passing (Dislike) requires a public, detailed reason until your one skip is used.</li>
                    <li>Matching moves you to a shared space - you can&rsquo;t browse again unless it ends.</li>
                    <li>You get exactly one lifetime &ldquo;go back&rdquo; to revisit the last person you disliked.</li>
                </ul>
            </div>
            <!-- <button class="btn discover-button-skip w-100" id="skip-btn">Next (use 1-time skip)</button> -->
            <button class="btn discover-button-skip w-100" id="next-btn" style="display:none;">Next</button>

            <!-- One-time-per-account rewind: jumps back to the last person you disliked. -->
            <button class="btn discover-button-back w-100 mt-2" id="rewind-btn" style="display:none;">Go back to last dislike (1-time)</button>

            <div id="action-message" class="mt-3"></div>
        </div>
    </div>
</div>

<?php require __DIR__ . '/../../includes/footer.php'; ?>

<!-- Dislike-rede modal -->
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
        <div class="uhohbox" id="reason-error" style="display:none;"></div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn discover-modal-button-back" data-bs-dismiss="modal">Back</button>
        <button type="button" class="btn discover-modal-button-confirm" id="confirm-dislike-btn">Save and pass</button>
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
