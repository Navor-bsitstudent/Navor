<?php
// setup_database.php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');

try {
    // Connect without selecting database
    $pdo = new PDO("mysql:host=" . DB_HOST, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Create database
    $pdo->exec("CREATE DATABASE IF NOT EXISTS duck_gallery");
    $pdo->exec("USE duck_gallery");
    
    // Create users table
    $pdo->exec("CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(50) UNIQUE NOT NULL,
        email VARCHAR(100) UNIQUE NOT NULL,
        password_hash VARCHAR(255) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");
    
    echo "Database setup completed successfully!";
    
} catch (PDOException $e) {
    die("Database setup failed: " . $e->getMessage());
}
?>