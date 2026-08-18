<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

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

if (!defined('APP_URL_BASE')) {
    $projectRoot = str_replace('\\', '/', dirname(__DIR__));               // .../htdocs/whats-left
    $documentRoot = str_replace('\\', '/', rtrim($_SERVER['DOCUMENT_ROOT'] ?? '', '/'));
    $base = $documentRoot !== '' ? str_replace($documentRoot, '', $projectRoot) : '';
    define('APP_URL_BASE', rtrim($base, '/'));
}
