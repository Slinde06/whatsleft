<?php
/**
 * config/db.php
 * Begin die sessie en maak 'n PDO-verbinding met MySQL oop.
 * Word deur elke bladsy/api endpoint ingesluit via includes/auth.php of direk.
 * (het die sessie/login idee hier gekry: https://www.youtube.com/watch?v=-rMq6PVCd0k)
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ---- XAMPP / plaaslike MySQL verstek waardes ----
$DB_HOST = 'localhost';
$DB_NAME = 'whats_left';
$DB_USER = 'root';
$DB_PASS = '';
$DB_CHARSET = 'utf8mb4';

$dsn = "mysql:host={$DB_HOST};dbname={$DB_NAME};charset={$DB_CHARSET}";

$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $DB_USER, $DB_PASS, $options);
} catch (PDOException $e) {
    die('Database connection failed. Make sure MySQL is running in XAMPP and the "whats_left" database has been imported. (' . $e->getMessage() . ')');
}

// ---- Werk die web-root van die projek uit sodat skakels werk maak nie saak
//      watter folder in XAMPP se htdocs/ die projek in is nie (bv. /whats-left) ----
if (!defined('APP_URL_BASE')) {
    $projectRoot = str_replace('\\', '/', dirname(__DIR__));               // .../htdocs/whats-left
    $documentRoot = str_replace('\\', '/', rtrim($_SERVER['DOCUMENT_ROOT'] ?? '', '/'));
    $base = $documentRoot !== '' ? str_replace($documentRoot, '', $projectRoot) : '';
    define('APP_URL_BASE', rtrim($base, '/'));
}
