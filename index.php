<?php
require_once 'auth.php';
requireAuth();
$user = getCurrentUser();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Duck Gallery - Random Ducks Await!</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="profile-dropdown.css">
    <link rel="stylesheet" href="dark-mode.css">
</head>
<body>
    <div class="background-pattern"></div>
    
    <!-- Modern Header with Profile -->
    <header class="top-header">
        <div class="header-container">
            <div class="logo-section">
                <span class="logo-icon">🦆</span>
                <span class="logo-text">Duck Gallery</span>
            </div>
            
            <!-- Profile Dropdown -->
            <div class="profile-dropdown">
                <button class="profile-btn" id="profileBtn">
                    <div class="profile-avatar">
                        <span><?php echo strtoupper(substr($user['username'], 0, 1)); ?></span>
                    </div>
                    <div class="profile-info">
                        <span class="profile-name"><?php echo htmlspecialchars($user['username']); ?></span>
                        <span class="profile-email"><?php echo htmlspecialchars($user['email']); ?></span>
                    </div>
                    <span class="dropdown-arrow">▼</span>
                </button>
                
                <div class="dropdown-menu" id="dropdownMenu">
                    <div class="dropdown-header">
                        <div class="dropdown-avatar-large">
                            <?php echo strtoupper(substr($user['username'], 0, 1)); ?>
                        </div>
                        <div class="dropdown-user-info">
                            <div class="dropdown-username"><?php echo htmlspecialchars($user['username']); ?></div>
                            <div class="dropdown-email"><?php echo htmlspecialchars($user['email']); ?></div>
                        </div>
                    </div>
                    
                    <div class="dropdown-divider"></div>
                    
                    <button class="dropdown-item" data-modal="profile">
                        <span class="dropdown-item-icon">⚙️</span>
                        <span>Edit Profile</span>
                    </button>
                    
                    <button class="dropdown-item" data-modal="favorites">
                        <span class="dropdown-item-icon">❤️</span>
                        <span>My Favorites</span>
                    </button>
                    
                    <button class="dropdown-item" data-modal="my-ratings">
                        <span class="dropdown-item-icon">⭐</span>
                        <span>My Ratings</span>
                    </button>
                    
                    <button class="dropdown-item" data-modal="my-comments">
                        <span class="dropdown-item-icon">💬</span>
                        <span>My Comments</span>
                    </button>
                    
                    <div class="dropdown-divider"></div>
                    
                    <button class="dropdown-item theme-toggle" id="themeToggle">
                        <span class="dropdown-item-icon" id="themeIcon">🌙</span>
                        <span id="themeText">Dark Mode</span>
                    </button>
                    
                    <div class="dropdown-divider"></div>
                    
                    <a href="logout.php" class="dropdown-item logout-item">
                        <span class="dropdown-item-icon">🚪</span>
                        <span>Logout</span>
                    </a>
                </div>
            </div>
        </div>
    </header>
    
    <!-- Main Header -->
    <div class="main-header">
        <div class="header-content">
            <h1 class="site-title">
                <span class="title-wave">🦆</span>
                <span class="title-text">Duck Gallery</span>
                <span class="title-wave">🦆</span>
            </h1>
            <p class="subtitle">Your daily dose of random ducks!</p>
        </div>
    </div>

    <!-- Navigation Grid -->
    <nav class="nav-grid">
        <button class="nav-card" data-modal="random-duck">
            <div class="nav-icon">🎲</div>
            <span class="nav-label">Random Duck</span>
        </button>
        
        <button class="nav-card" data-modal="gallery">
            <div class="nav-icon">🖼️</div>
            <span class="nav-label">Gallery</span>
        </button>
        
        <button class="nav-card" data-modal="gifs">
            <div class="nav-icon">🎬</div>
            <span class="nav-label">Duck GIFs</span>
        </button>
        
        <button class="nav-card" data-modal="http-codes">
            <div class="nav-icon">🔢</div>
            <span class="nav-label">HTTP Ducks</span>
        </button>
        
        <button class="nav-card" data-modal="about">
            <div class="nav-icon">ℹ️</div>
            <span class="nav-label">About</span>
        </button>
    </nav>

    <!-- All Modals -->
    <!-- Random Duck Modal -->
    <div class="modal" id="random-duck-modal">
        <div class="modal-header">
            <h2>🎲 Random Duck</h2>
            <button class="close-btn" data-close="random-duck-modal">[x]</button>
        </div>
        <div class="modal-content">
            <div class="duck-display">
                <img id="random-duck-img" src="" alt="Random Duck" class="duck-image">
                <div class="loading-spinner">Loading duck...</div>
            </div>
            <div class="duck-controls">
                <button class="btn btn-primary" id="new-duck-btn">Get New Duck!</button>
                <button class="btn btn-secondary" id="toggle-type-btn">Switch to GIF</button>
                <button class="btn btn-favorite" id="favorite-duck-btn">❤️ Favorite</button>
            </div>
            <div class="duck-rating">
                <span>Rate this duck:</span>
                <div class="star-rating" id="duck-star-rating">
                    <span class="star" data-rating="1">⭐</span>
                    <span class="star" data-rating="2">⭐</span>
                    <span class="star" data-rating="3">⭐</span>
                    <span class="star" data-rating="4">⭐</span>
                    <span class="star" data-rating="5">⭐</span>
                </div>
            </div>
            <div class="duck-comment-section">
                <label for="duck-comment">Add a comment:</label>
                <textarea id="duck-comment" placeholder="Share your thoughts about this duck..." rows="3"></textarea>
                <button class="btn btn-primary" id="submit-comment-btn">💬 Submit Comment</button>
            </div>
            <p class="duck-info" id="duck-info"></p>
        </div>
    </div>

    <!-- Gallery Modal -->
    <div class="modal" id="gallery-modal">
        <div class="modal-header">
            <h2>🖼️ Duck Gallery</h2>
            <button class="close-btn" data-close="gallery-modal">[x]</button>
        </div>
        <div class="modal-content">
            <div class="gallery-filters">
                <button class="filter-btn active" data-type="jpg">JPG Ducks</button>
                <button class="filter-btn" data-type="gif">GIF Ducks</button>
            </div>
            <div class="gallery-grid" id="gallery-grid">
                <div class="loading-spinner">Loading gallery...</div>
            </div>
        </div>
    </div>

    <!-- GIFs Modal -->
    <div class="modal" id="gifs-modal">
        <div class="modal-header">
            <h2>🎬 Duck GIFs</h2>
            <button class="close-btn" data-close="gifs-modal">[x]</button>
        </div>
        <div class="modal-content">
            <div class="gallery-grid" id="gifs-grid">
                <div class="loading-spinner">Loading GIFs...</div>
            </div>
        </div>
    </div>

    <!-- HTTP Codes Modal -->
    <div class="modal" id="http-codes-modal">
        <div class="modal-header">
            <h2>🔢 HTTP Status Ducks</h2>
            <button class="close-btn" data-close="http-codes-modal">[x]</button>
        </div>
        <div class="modal-content">
            <p class="modal-description">Ducks representing HTTP status codes!</p>
            <div class="http-grid">
                <button class="http-btn" data-code="200">200 OK</button>
                <button class="http-btn" data-code="400">400 Bad Request</button>
                <button class="http-btn" data-code="404">404 Not Found</button>
                <button class="http-btn" data-code="500">500 Server Error</button>
                <button class="http-btn" data-code="418">418 I'm a teapot</button>
            </div>
            <div class="http-duck-display">
                <img id="http-duck-img" src="" alt="HTTP Status Duck" class="duck-image">
            </div>
        </div>
    </div>

    <!-- About Modal -->
    <div class="modal" id="about-modal">
        <div class="modal-header">
            <h2>ℹ️ About Duck Gallery</h2>
            <button class="close-btn" data-close="about-modal">[x]</button>
        </div>
        <div class="modal-content">
            <div class="about-content">
                <p>Welcome to the <strong>Duck Gallery</strong>! 🦆</p>
                <p>This highly animated website showcases random duck images powered by the <a href="https://random-d.uk/api" target="_blank">random-d.uk API</a>.</p>
                
                <h3>Features:</h3>
                <ul>
                    <li>🎲 Random duck images (JPG & GIF)</li>
                    <li>🖼️ Browse the complete duck gallery</li>
                    <li>🎬 Animated duck GIFs</li>
                    <li>🔢 HTTP status code ducks</li>
                    <li>✨ Draggable modal windows</li>
                    <li>💫 Smooth animations throughout</li>
                    <li>❤️ Favorite ducks system</li>
                    <li>⭐ Rate and comment on ducks</li>
                    <li>🌙 Dark/Light mode toggle</li>
                </ul>
                
                <h3>Tech Stack:</h3>
                <ul>
                    <li>PHP for server-side rendering</li>
                    <li>JavaScript for interactivity</li>
                    <li>CSS3 for animations</li>
                </ul>
                
                <p class="api-credit">Powered by <a href="https://random-d.uk" target="_blank">random-d.uk</a></p>
            </div>
        </div>
    </div>

    <!-- Profile Edit Modal -->
    <div class="modal" id="profile-modal">
        <div class="modal-header">
            <h2>⚙️ Edit Profile</h2>
            <button class="close-btn" data-close="profile-modal">[x]</button>
        </div>
        <div class="modal-content">
            <div id="profile-message"></div>
            <form id="profile-form" class="profile-form">
                <div class="form-group">
                    <label for="profile-username">Username</label>
                    <input type="text" id="profile-username" value="<?php echo htmlspecialchars($user['username']); ?>" disabled>
                    <small>Username cannot be changed</small>
                </div>
                
                <div class="form-group">
                    <label for="profile-email">Email</label>
                    <input type="email" id="profile-email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="current-password">Current Password</label>
                    <input type="password" id="current-password" name="current_password" placeholder="Enter to change password">
                </div>
                
                <div class="form-group">
                    <label for="new-password">New Password</label>
                    <input type="password" id="new-password" name="new_password" placeholder="Leave blank to keep current">
                </div>
                
                <div class="form-group">
                    <label for="confirm-password">Confirm New Password</label>
                    <input type="password" id="confirm-password" name="confirm_password" placeholder="Confirm new password">
                </div>
                
                <button type="submit" class="btn btn-primary">Update Profile</button>
            </form>
        </div>
    </div>

    <!-- Favorites Modal -->
    <div class="modal" id="favorites-modal">
        <div class="modal-header">
            <h2>❤️ My Favorites</h2>
            <button class="close-btn" data-close="favorites-modal">[x]</button>
        </div>
        <div class="modal-content">
            <div class="gallery-grid" id="favorites-grid">
                <div class="loading-spinner">Loading favorites...</div>
            </div>
        </div>
    </div>

    <!-- My Ratings Modal -->
    <div class="modal" id="my-ratings-modal">
        <div class="modal-header">
            <h2>⭐ My Ratings</h2>
            <button class="close-btn" data-close="my-ratings-modal">[x]</button>
        </div>
        <div class="modal-content">
            <div id="ratings-list" class="ratings-list">
                <div class="loading-spinner">Loading ratings...</div>
            </div>
        </div>
    </div>

    <!-- My Comments Modal -->
    <div class="modal" id="my-comments-modal">
        <div class="modal-header">
            <h2>💬 My Comments</h2>
            <button class="close-btn" data-close="my-comments-modal">[x]</button>
        </div>
        <div class="modal-content">
            <div id="comments-list" class="comments-list">
                <div class="loading-spinner">Loading comments...</div>
            </div>
        </div>
    </div>

    <script src="script.js"></script>
</body>
</html>