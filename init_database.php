<?php
// init_database.php - Run this file ONCE to set up all database tables

define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'duck_gallery');

echo "<h1>Duck Gallery Database Initialization</h1>";
echo "<pre>";

try {
    // Step 1: Connect to MySQL server
    echo "Step 1: Connecting to MySQL server...\n";
    $pdo = new PDO("mysql:host=" . DB_HOST, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "✓ Connected to MySQL server\n\n";
    
    // Step 2: Create database if not exists
    echo "Step 2: Creating database 'duck_gallery'...\n";
    $pdo->exec("CREATE DATABASE IF NOT EXISTS " . DB_NAME . " CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    echo "✓ Database created/verified\n\n";
    
    // Step 3: Select database
    echo "Step 3: Selecting database...\n";
    $pdo->exec("USE " . DB_NAME);
    echo "✓ Database selected\n\n";
    
    // Step 4: Create users table
    echo "Step 4: Creating 'users' table...\n";
    $pdo->exec("CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(50) UNIQUE NOT NULL,
        email VARCHAR(100) UNIQUE NOT NULL,
        password_hash VARCHAR(255) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    echo "✓ Users table created\n\n";
    
    // Step 5: Create favorites table
    echo "Step 5: Creating 'favorites' table...\n";
    $pdo->exec("CREATE TABLE IF NOT EXISTS favorites (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        duck_url VARCHAR(500) NOT NULL,
        duck_name VARCHAR(255),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        UNIQUE KEY unique_favorite (user_id, duck_url),
        INDEX idx_user_id (user_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    echo "✓ Favorites table created\n\n";
    
    // Step 6: Create ratings table
    echo "Step 6: Creating 'ratings' table...\n";
    $pdo->exec("CREATE TABLE IF NOT EXISTS ratings (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        duck_url VARCHAR(500) NOT NULL,
        duck_name VARCHAR(255),
        rating INT NOT NULL CHECK (rating >= 1 AND rating <= 5),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        UNIQUE KEY unique_rating (user_id, duck_url),
        INDEX idx_user_id (user_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    echo "✓ Ratings table created\n\n";
    
    // Step 7: Create comments table
    echo "Step 7: Creating 'comments' table...\n";
    $pdo->exec("CREATE TABLE IF NOT EXISTS comments (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        duck_url VARCHAR(500) NOT NULL,
        duck_name VARCHAR(255),
        comment TEXT NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_user_id (user_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    echo "✓ Comments table created\n\n";
    
    // Step 8: Verify all tables
    echo "Step 8: Verifying all tables exist...\n";
    $stmt = $pdo->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    $requiredTables = ['users', 'favorites', 'ratings', 'comments'];
    $missingTables = array_diff($requiredTables, $tables);
    
    if (empty($missingTables)) {
        echo "✓ All required tables exist:\n";
        foreach ($tables as $table) {
            // Get row count
            $countStmt = $pdo->query("SELECT COUNT(*) FROM $table");
            $count = $countStmt->fetchColumn();
            echo "  - $table ($count rows)\n";
        }
    } else {
        echo "✗ Missing tables: " . implode(', ', $missingTables) . "\n";
    }
    
    echo "\n";
    echo "=" . str_repeat("=", 50) . "\n";
    echo "✓✓✓ DATABASE SETUP COMPLETE! ✓✓✓\n";
    echo "=" . str_repeat("=", 50) . "\n";
    echo "\nYou can now use the Duck Gallery application!\n";
    echo "Next steps:\n";
    echo "1. Go to signup.php to create an account\n";
    echo "2. Login and start using the app\n";
    echo "3. You can delete this init_database.php file for security\n";
    
} catch (PDOException $e) {
    echo "\n✗✗✗ ERROR ✗✗✗\n";
    echo "Database setup failed: " . $e->getMessage() . "\n";
    echo "\nPlease check:\n";
    echo "1. XAMPP MySQL is running\n";
    echo "2. Database credentials are correct in this file\n";
    echo "3. MySQL user has CREATE DATABASE privileges\n";
}

echo "</pre>";

echo "<hr>";
echo "<h2>Quick Actions:</h2>";
echo "<ul>";
echo "<li><a href='signup.php'>Go to Sign Up Page</a></li>";
echo "<li><a href='login.php'>Go to Login Page</a></li>";
echo "<li><a href='index.php'>Go to Main App</a></li>";
echo "</ul>";
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Database Initialization</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            max-width: 800px;
            margin: 50px auto;
            padding: 20px;
            background: #f5f5f5;
        }
        pre {
            background: #1e1e1e;
            color: #00ff00;
            padding: 20px;
            border-radius: 10px;
            overflow-x: auto;
            font-size: 14px;
            line-height: 1.6;
        }
        h1 {
            color: #FF8C42;
            text-align: center;
        }
        h2 {
            color: #4A90E2;
        }
        a {
            color: #4A90E2;
            text-decoration: none;
            font-weight: bold;
        }
        a:hover {
            text-decoration: underline;
        }
        ul {
            list-style: none;
            padding: 0;
        }
        li {
            margin: 10px 0;
            padding: 10px;
            background: white;
            border-radius: 5px;
            border-left: 4px solid #FF8C42;
        }
    </style>
</head>
<body>
</body>
</html>