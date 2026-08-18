<?php
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../includes/functions.php';

$_SESSION = [];
session_destroy();
redirect(base_url('frontend/pages/login.php'));
