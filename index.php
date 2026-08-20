<?php
/**
 * index.php
 * Die app maak altyd hier eerste oop. Stuur die besoeker plek toe:
 *   -                                                                               die maak oop as die website launch
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






//                                                                                  stuur jou na die login page
redirect(base_url('frontend/pages/login.php'));
