<?php
/**
 * index.php
 * Die app maak altyd hier eerste oop. Stuur die besoeker plek toe:
 *   - vroeer was dit: nie ingeteken nie -> login bladsy, ingeteken -> tuisblad
 */
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/functions.php';

// LET WEL: dit het gebruik om ingetekende mense reguit na home.php te stuur,
// maar vir demo doeleindes wil ons altyd op die login skerm land sodat dit
// vinnig is om uit te teken en 'n ander toets-rekening te gebruik. Ou gedrag
// hieronder gehou (kommentaar) ingeval ons dit weer terug wil kry.
// if (!empty($_SESSION['user_id'])) {
//     redirect(base_url('frontend/pages/home.php'));
// } else {
//     redirect(base_url('frontend/pages/login.php'));
// }

redirect(base_url('frontend/pages/login.php'));
