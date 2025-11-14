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
    $password = $_POST['password'] ?? '';
    
    if (empty($username) || empty($password)) {
        $error = 'Please fill in all fields';
    } else {
        $result = loginUser($username, $password);
        if ($result['success']) {
            header('Location: index.php');
            exit;
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
    <title>Login - Duck Gallery</title>
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
    </style>
</head>
<body>
    <div class="background-pattern"></div>
    
    <div class="auth-container">
        <div class="auth-header">
            <div class="duck-emoji">🦆</div>
            <h1>Welcome Back!</h1>
            <p>Login to access Duck Gallery</p>
        </div>
        
        <?php if ($error): ?>
            <div class="error-message"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="success-message"><?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>
        
        <form method="POST" action="login.php">
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" required autofocus>
            </div>
            
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required>
            </div>
            
            <button type="submit" class="btn-submit">Login</button>
        </form>
        
        <div class="auth-footer">
            Don't have an account? <a href="signup.php">Sign up here</a>
        </div>
    </div>
</body>
</html>
