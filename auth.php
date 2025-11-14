<?php
session_start();

// XAMPP Database configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'duck_gallery'); // You'll need to create this database
define('DB_USER', 'root'); // Default XAMPP username
define('DB_PASS', ''); // Default XAMPP password (empty)

// Database connection
function getDBConnection() {
    try {
        $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $pdo;
    } catch (PDOException $e) {
        die('Database connection failed: ' . $e->getMessage());
    }
}

// Initialize database tables
function initializeDatabase() {
    $pdo = getDBConnection();
    
    // Create users table if it doesn't exist
    $sql = "CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(50) UNIQUE NOT NULL,
        email VARCHAR(100) UNIQUE NOT NULL,
        password_hash VARCHAR(255) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    
    $pdo->exec($sql);
}

// Check if user is logged in
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// Get current user
function getCurrentUser() {
    if (!isLoggedIn()) {
        return null;
    }
    
    $pdo = getDBConnection();
    $stmt = $pdo->prepare('SELECT id, username, email, created_at FROM users WHERE id = ?');
    $stmt->execute([$_SESSION['user_id']]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

// Register new user
function registerUser($username, $email, $password) {
    $pdo = getDBConnection();
    
    // Initialize database on first use
    initializeDatabase();
    
    // Validate input
    if (strlen($username) < 3 || strlen($username) > 50) {
        return ['success' => false, 'error' => 'Username must be 3-50 characters'];
    }
    
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return ['success' => false, 'error' => 'Invalid email address'];
    }
    
    if (strlen($password) < 6) {
        return ['success' => false, 'error' => 'Password must be at least 6 characters'];
    }
    
    // Check if username exists
    $stmt = $pdo->prepare('SELECT id FROM users WHERE username = ?');
    $stmt->execute([$username]);
    if ($stmt->fetch()) {
        return ['success' => false, 'error' => 'Username already exists'];
    }
    
    // Check if email exists
    $stmt = $pdo->prepare('SELECT id FROM users WHERE email = ?');
    $stmt->execute([$email]);
    if ($stmt->fetch()) {
        return ['success' => false, 'error' => 'Email already registered'];
    }
    
    // Hash password
    $passwordHash = password_hash($password, PASSWORD_BCRYPT);
    
    // Insert user
    try {
        $stmt = $pdo->prepare('INSERT INTO users (username, email, password_hash) VALUES (?, ?, ?)');
        $stmt->execute([$username, $email, $passwordHash]);
        
        return ['success' => true, 'message' => 'Registration successful!'];
    } catch (PDOException $e) {
        return ['success' => false, 'error' => 'Registration failed: ' . $e->getMessage()];
    }
}

// Login user
function loginUser($username, $password) {
    $pdo = getDBConnection();
    
    $stmt = $pdo->prepare('SELECT id, username, password_hash FROM users WHERE username = ?');
    $stmt->execute([$username]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$user) {
        return ['success' => false, 'error' => 'Invalid username or password'];
    }
    
    if (!password_verify($password, $user['password_hash'])) {
        return ['success' => false, 'error' => 'Invalid username or password'];
    }
    
    // Set session
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['username'] = $user['username'];
    
    return ['success' => true, 'message' => 'Login successful!'];
}

// Logout user
function logoutUser() {
    session_destroy();
    session_start();
}

// Require authentication
function requireAuth() {
    if (!isLoggedIn()) {
        header('Location: login.php');
        exit;
    }
}
?>