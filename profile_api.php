<?php
// Don't start session here - auth.php already does it
require_once 'auth.php';

header('Content-Type: application/json');

// Check if user is logged in
if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'error' => 'Not authenticated']);
    exit;
}

$action = $_POST['action'] ?? $_GET['action'] ?? '';
$userId = $_SESSION['user_id'];

// Get database connection
try {
    $pdo = getDBConnection();
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => 'Database connection failed']);
    exit;
}

// Route to appropriate function
switch ($action) {
    case 'update_profile':
        updateProfile($pdo, $userId);
        break;
    case 'add_favorite':
        addFavorite($pdo, $userId);
        break;
    case 'remove_favorite':
        removeFavorite($pdo, $userId);
        break;
    case 'get_favorites':
        getFavorites($pdo, $userId);
        break;
    case 'add_rating':
        addRating($pdo, $userId);
        break;
    case 'get_ratings':
        getRatings($pdo, $userId);
        break;
    case 'add_comment':
        addComment($pdo, $userId);
        break;
    case 'get_comments':
        getComments($pdo, $userId);
        break;
    default:
        echo json_encode(['success' => false, 'error' => 'Invalid action']);
        break;
}

// ========== PROFILE FUNCTIONS ==========

function updateProfile($pdo, $userId) {
    $email = trim($_POST['email'] ?? '');
    $currentPassword = $_POST['current_password'] ?? '';
    $newPassword = $_POST['new_password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';
    
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['success' => false, 'error' => 'Invalid email address']);
        return;
    }
    
    try {
        $stmt = $pdo->prepare('SELECT id FROM users WHERE email = ? AND id != ?');
        $stmt->execute([$email, $userId]);
        if ($stmt->fetch()) {
            echo json_encode(['success' => false, 'error' => 'Email already in use']);
            return;
        }
        
        $stmt = $pdo->prepare('UPDATE users SET email = ? WHERE id = ?');
        $stmt->execute([$email, $userId]);
        
        if (!empty($newPassword)) {
            if (empty($currentPassword)) {
                echo json_encode(['success' => false, 'error' => 'Current password required']);
                return;
            }
            
            if (strlen($newPassword) < 6) {
                echo json_encode(['success' => false, 'error' => 'New password must be at least 6 characters']);
                return;
            }
            
            if ($newPassword !== $confirmPassword) {
                echo json_encode(['success' => false, 'error' => 'Passwords do not match']);
                return;
            }
            
            $stmt = $pdo->prepare('SELECT password_hash FROM users WHERE id = ?');
            $stmt->execute([$userId]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!password_verify($currentPassword, $user['password_hash'])) {
                echo json_encode(['success' => false, 'error' => 'Current password is incorrect']);
                return;
            }
            
            $newHash = password_hash($newPassword, PASSWORD_BCRYPT);
            $stmt = $pdo->prepare('UPDATE users SET password_hash = ? WHERE id = ?');
            $stmt->execute([$newHash, $userId]);
        }
        
        echo json_encode(['success' => true, 'message' => 'Profile updated successfully!']);
        
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'error' => 'Update failed']);
    }
}

// ========== FAVORITES FUNCTIONS ==========

function addFavorite($pdo, $userId) {
    $duckUrl = $_POST['duck_url'] ?? '';
    $duckName = $_POST['duck_name'] ?? 'Duck Image';
    
    if (empty($duckUrl)) {
        echo json_encode(['success' => false, 'error' => 'Duck URL required']);
        return;
    }
    
    try {
        // Check if already exists
        $stmt = $pdo->prepare('SELECT id FROM favorites WHERE user_id = ? AND duck_url = ?');
        $stmt->execute([$userId, $duckUrl]);
        
        if ($stmt->fetch()) {
            echo json_encode(['success' => false, 'error' => 'Already in favorites']);
            return;
        }
        
        // Insert new favorite
        $stmt = $pdo->prepare('INSERT INTO favorites (user_id, duck_url, duck_name) VALUES (?, ?, ?)');
        $stmt->execute([$userId, $duckUrl, $duckName]);
        
        echo json_encode(['success' => true, 'message' => 'Added to favorites!']);
        
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'error' => 'Failed to add favorite']);
    }
}

function removeFavorite($pdo, $userId) {
    $duckUrl = $_POST['duck_url'] ?? '';
    
    if (empty($duckUrl)) {
        echo json_encode(['success' => false, 'error' => 'Duck URL required']);
        return;
    }
    
    try {
        $stmt = $pdo->prepare('DELETE FROM favorites WHERE user_id = ? AND duck_url = ?');
        $stmt->execute([$userId, $duckUrl]);
        
        echo json_encode(['success' => true, 'message' => 'Removed from favorites']);
        
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'error' => 'Failed to remove']);
    }
}

function getFavorites($pdo, $userId) {
    try {
        $stmt = $pdo->prepare('SELECT duck_url, duck_name, created_at FROM favorites WHERE user_id = ? ORDER BY created_at DESC');
        $stmt->execute([$userId]);
        $favorites = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode(['success' => true, 'favorites' => $favorites]);
        
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'error' => 'Failed to load favorites']);
    }
}

// ========== RATINGS FUNCTIONS ==========

function addRating($pdo, $userId) {
    $duckUrl = $_POST['duck_url'] ?? '';
    $duckName = $_POST['duck_name'] ?? 'Duck Image';
    $rating = intval($_POST['rating'] ?? 0);
    
    if (empty($duckUrl)) {
        echo json_encode(['success' => false, 'error' => 'Duck URL required']);
        return;
    }
    
    if ($rating < 1 || $rating > 5) {
        echo json_encode(['success' => false, 'error' => 'Rating must be 1-5']);
        return;
    }
    
    try {
        // Check if rating already exists
        $stmt = $pdo->prepare('SELECT id FROM ratings WHERE user_id = ? AND duck_url = ?');
        $stmt->execute([$userId, $duckUrl]);
        $existing = $stmt->fetch();
        
        if ($existing) {
            // Update existing rating
            $stmt = $pdo->prepare('UPDATE ratings SET rating = ?, duck_name = ?, created_at = CURRENT_TIMESTAMP WHERE user_id = ? AND duck_url = ?');
            $stmt->execute([$rating, $duckName, $userId, $duckUrl]);
        } else {
            // Insert new rating
            $stmt = $pdo->prepare('INSERT INTO ratings (user_id, duck_url, duck_name, rating) VALUES (?, ?, ?, ?)');
            $stmt->execute([$userId, $duckUrl, $duckName, $rating]);
        }
        
        echo json_encode(['success' => true, 'message' => 'Rating saved!']);
        
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'error' => 'Failed to save rating']);
    }
}

function getRatings($pdo, $userId) {
    try {
        $stmt = $pdo->prepare('SELECT duck_url, duck_name, rating, created_at FROM ratings WHERE user_id = ? ORDER BY created_at DESC');
        $stmt->execute([$userId]);
        $ratings = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode(['success' => true, 'ratings' => $ratings]);
        
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'error' => 'Failed to load ratings']);
    }
}

// ========== COMMENTS FUNCTIONS ==========

function addComment($pdo, $userId) {
    $duckUrl = $_POST['duck_url'] ?? '';
    $duckName = $_POST['duck_name'] ?? 'Duck Image';
    $comment = trim($_POST['comment'] ?? '');
    
    if (empty($duckUrl)) {
        echo json_encode(['success' => false, 'error' => 'Duck URL required']);
        return;
    }
    
    if (empty($comment)) {
        echo json_encode(['success' => false, 'error' => 'Comment cannot be empty']);
        return;
    }
    
    try {
        $stmt = $pdo->prepare('INSERT INTO comments (user_id, duck_url, duck_name, comment) VALUES (?, ?, ?, ?)');
        $stmt->execute([$userId, $duckUrl, $duckName, $comment]);
        
        echo json_encode(['success' => true, 'message' => 'Comment added!']);
        
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'error' => 'Failed to add comment']);
    }
}

function getComments($pdo, $userId) {
    try {
        $stmt = $pdo->prepare('SELECT duck_url, duck_name, comment, created_at FROM comments WHERE user_id = ? ORDER BY created_at DESC');
        $stmt->execute([$userId]);
        $comments = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode(['success' => true, 'comments' => $comments]);
        
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'error' => 'Failed to load comments']);
    }
}
?>