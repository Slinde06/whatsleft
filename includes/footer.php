<!-- Ou een-lyn footer, hou dit hier as kommentaar ingeval die client die eenvoudige weergawe terug wil hê:
<footer class="bottomstuff">
    What's Left - every choice is permanent and public.
</footer>
-->
<footer class="bottomstuff">
    <div class="bottomstuffinner">
        <div class="bottomlogo">
            <img src="<?= e(base_url('frontend/assets/images/logo.svg')) ?>" alt="What's Left logo">
            <span>What&rsquo;s Left</span>
        </div>
        <div class="bottomlinks">
            <a href="<?= e(base_url('frontend/pages/home.php')) ?>">Home</a>
            <a href="<?= e(base_url('frontend/pages/discover.php')) ?>">Discover</a>
            <a href="<?= e(base_url('frontend/pages/public_reasons.php')) ?>">Public reasons</a>
            <a href="<?= e(base_url('frontend/pages/edit_profile.php')) ?>">Profile</a>
        </div>
        <div class="bottomnote">
            Every choice you make here is permanent and public - no ghosting, no deleting.
        </div>
        <div class="bottomcopy">&copy; <?= date('Y') ?> What&rsquo;s Left. All rights reserved.</div>
    </div>
</footer>
