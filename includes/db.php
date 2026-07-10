<?php
// Creates PDO connection to the database.

define("DB_HOST", "127.0.0.1");
define("DB_NAME", "art_gallery");
define("DB_USER", "root");
define("DB_PASS", "hello");
define("DB_CHARSET", "utf8mb4");

// Paths to uploaded images.
define("ASSETS", "assets/");
define("PAINTINGS", "gallery/paintings/");
define("PORTRAITS", "gallery/portraits/");

$dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;

$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, // throw on errors
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC, // return assoc arrays
    PDO::ATTR_EMULATE_PREPARES => false, // real prepared statements
];

try {
    $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
} catch (PDOException $e) {
    http_response_code(500);
    exit("Database connection failed: " . $e->getMessage());
}
