<?php
require_once 'auth.php';

// Redirect if already logged in
if (isLoggedIn()) {
    header('Location: index.php');
    exit;
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';
    
    if (empty($username) || empty($email) || empty($password) || empty($confirmPassword)) {
        $error = 'Please fill in all fields';
    } elseif ($password !== $confirmPassword) {
        $error = 'Passwords do not match';
    } else {
        $result = registerUser($username, $email, $password);
        if ($result['success']) {
            $success = $result['message'] . ' Please login.';
        } else {
            $error = $result['error'];
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up - Duck Gallery</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        .auth-container {
            max-width: 450px;
            margin: 4rem auto;
            padding: 3rem;
            background: white;
            border: 4px solid var(--primary-orange);
            border-radius: 25px;
            box-shadow: 0 20px 60px var(--shadow-hover);
            animation: modalPopIn 0.6s cubic-bezier(0.68, -0.55, 0.265, 1.55);
        }
        
        .auth-header {
            text-align: center;
            margin-bottom: 2rem;
        }
        
        .auth-header h1 {
            color: var(--primary-orange);
            font-size: 2.5rem;
            margin-bottom: 0.5rem;
        }
        
        .auth-header .duck-emoji {
            font-size: 3rem;
            animation: wave 2s ease-in-out infinite;
        }
        
        .form-group {
            margin-bottom: 1.5rem;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 600;
            color: var(--text-dark);
        }
        
        .form-group input {
            width: 100%;
            padding: 0.9rem;
            border: 2px solid var(--primary-yellow);
            border-radius: 12px;
            font-size: 1rem;
            transition: all 0.3s ease;
        }
        
        .form-group input:focus {
            outline: none;
            border-color: var(--accent-orange);
            box-shadow: 0 0 0 3px rgba(255, 107, 53, 0.1);
        }
        
        .error-message {
            background: #ffe6e6;
            color: #d32f2f;
            padding: 1rem;
            border-radius: 12px;
            margin-bottom: 1.5rem;
            border: 2px solid #ffcccc;
        }
        
        .success-message {
            background: #e6ffe6;
            color: #2f8d2f;
            padding: 1rem;
            border-radius: 12px;
            margin-bottom: 1.5rem;
            border: 2px solid #ccffcc;
        }
        
        .btn-submit {
            width: 100%;
            padding: 1rem;
            background: linear-gradient(135deg, var(--primary-orange), var(--accent-orange));
            color: white;
            border: none;
            border-radius: 12px;
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .btn-submit:hover {
            transform: translateY(-3px);
            box-shadow: 0 5px 20px var(--shadow-hover);
        }
        
        .auth-footer {
            text-align: center;
            margin-top: 1.5rem;
            color: var(--text-dark);
        }
        
        .auth-footer a {
            color: var(--accent-blue);
            text-decoration: none;
            font-weight: 600;
        }
        
        .auth-footer a:hover {
            color: var(--accent-orange);
            text-decoration: underline;
        }
        
        .password-requirements {
            font-size: 0.85rem;
            color: #666;
            margin-top: 0.3rem;
        }
    </style>
</head>
<body>
    <div class="background-pattern"></div>
    
    <div class="auth-container">
        <div class="auth-header">
            <div class="duck-emoji">🦆</div>
            <h1>Join Duck Gallery!</h1>
            <p>Create your account to explore</p>
        </div>
        
        <?php if ($error): ?>
            <div class="error-message"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="success-message">
                <?php echo htmlspecialchars($success); ?>
                <a href="login.php">Login now</a>
            </div>
        <?php endif; ?>
        
        <form method="POST" action="signup.php">
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" required autofocus minlength="3" maxlength="50">
                <div class="password-requirements">3-50 characters</div>
            </div>
            
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" required>
            </div>
            
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required minlength="6">
                <div class="password-requirements">At least 6 characters</div>
            </div>
            
            <div class="form-group">
                <label for="confirm_password">Confirm Password</label>
                <input type="password" id="confirm_password" name="confirm_password" required minlength="6">
            </div>
            
            <button type="submit" class="btn-submit">Sign Up</button>
        </form>
        
        <div class="auth-footer">
            Already have an account? <a href="login.php">Login here</a>
        </div>
    </div>
</body>
</html>
