<?php
/**
 * includes/nav.php
 * Gedeelde bo-navbar vir ingetekende bladsye.
 * Verwag $currentUser (array) en opsioneel $activePage (string) om deur die
 * bladsy wat dit insluit gestel te word voordat hierdie lêer gerequire word.
 */
$activePage = $activePage ?? '';
$isMatched  = !empty($currentUser['is_matched']) && !empty($currentUser['current_match_id']);
?>
<nav class="topbar">
    <a href="<?= e(base_url('frontend/pages/home.php')) ?>" class="logostuff">
        <img src="<?= e(base_url('frontend/assets/images/logo.svg')) ?>" alt="What's Left logo">
        What&rsquo;s Left
    </a>

    <div class="navstuff">
        <a href="<?= e(base_url('frontend/pages/home.php')) ?>" class="homebutton <?= $activePage === 'home' ? 'active' : '' ?>">Home</a>

        <?php if ($isMatched): ?>
            <a href="<?= e(base_url('frontend/pages/match_space.php')) ?>" class="<?= $activePage === 'match' ? 'active' : '' ?>">Match</a>
        <?php else: ?>
            <a href="<?= e(base_url('frontend/pages/discover.php')) ?>" class="<?= $activePage === 'discover' ? 'active' : '' ?>">Discover/Match</a>
        <?php endif; ?>

        <a href="<?= e(base_url('frontend/pages/public_reasons.php')) ?>" class="<?= $activePage === 'public_reasons' ? 'active' : '' ?>">Public reasons</a>

        <!-- george = die klein rondte ikoon met die gebruiker se eerste letter -->
        <a href="<?= e(base_url('frontend/pages/edit_profile.php')) ?>" class="george" title="Edit profile">
            <?= e(strtoupper(substr($currentUser['first_name'] ?? '?', 0, 1))) ?>
        </a>

        <!-- vinnige uitteken-skakel sodat dit maklik is om tussen toets-rekeninge te spring wanneer jy wys hoe dit werk -->
        <a href="<?= e(base_url('frontend/pages/logout.php')) ?>" class="logoutbtn" title="Log out">Log out</a>
    </div>
</nav>
