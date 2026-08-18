<?php

$activePage = $activePage ?? '';
$isMatched  = !empty($currentUser['is_matched']) && !empty($currentUser['current_match_id']);
?>
<nav class="wl-navbar">
    <a href="<?= e(base_url('frontend/pages/home.php')) ?>" class="wl-brand">
        <img src="<?= e(base_url('frontend/assets/images/logo.svg')) ?>" alt="What's Left logo">
        What&rsquo;s Left
    </a>

    <div class="wl-nav-links">
        <a href="<?= e(base_url('frontend/pages/home.php')) ?>" class="<?= $activePage === 'home' ? 'active' : '' ?>">Home</a>

        <?php if ($isMatched): ?>
            <a href="<?= e(base_url('frontend/pages/match_space.php')) ?>" class="<?= $activePage === 'match' ? 'active' : '' ?>">Match</a>
        <?php else: ?>
            <a href="<?= e(base_url('frontend/pages/discover.php')) ?>" class="<?= $activePage === 'discover' ? 'active' : '' ?>">Discover/Match</a>
        <?php endif; ?>

        <a href="<?= e(base_url('frontend/pages/public_reasons.php')) ?>" class="<?= $activePage === 'public_reasons' ? 'active' : '' ?>">Public reasons</a>

        <a href="<?= e(base_url('frontend/pages/edit_profile.php')) ?>" class="wl-profile-icon" title="Edit profile">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <circle cx="12" cy="8" r="4"/>
                <path d="M4 20c0-4.4 3.6-8 8-8s8 3.6 8 8"/>
            </svg>
        </a>
    </div>
</nav>
