<?php
/**
 * config/database.php
 * Establish a PDO connection to the MySQL database.
 */

define('DB_HOST', 'localhost');
define('DB_NAME', 'tunehub');
define('DB_USER', 'root');
define('DB_PASS', '');

try {
    // Connect to the database
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4", DB_USER, DB_PASS);

    // Set robust error mode
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Security: disable emulated prepared statements
    $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);

    // Default fetch to associative array
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    // In production, log this error instead of echoing it
    die("Database Connection failed: " . $e->getMessage());
}
?>
