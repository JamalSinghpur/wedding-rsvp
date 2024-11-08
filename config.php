<?php
$host = 'localhost'; // Your database host
$port = 3307; // Your database port
$dbName = 'rsvp'; // Your database name
$user = 'root'; // Your database username
$password = ''; // Your database password

// DSN STRING - data source name
$dsn = "mysql:host={$host};port={$port};dbname={$dbName};charset=utf8";

// CREATE PDO INSTANCE
try {
    $pdo = new PDO($dsn, $user, $password);
    // Set to throw exceptions on error
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    // Log the error instead of displaying it
    error_log('Database Connection Error: ' . $e->getMessage());
    die('An error occurred while connecting to the database. Please try again later.');
}
?>
