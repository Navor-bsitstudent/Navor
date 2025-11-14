// Duck Gallery - Interactive JavaScript with Full Social Features and Theme Toggle

// API Configuration
const API_PROXY = 'api.php';
const PROFILE_API = 'profile_api.php';
let currentDuckType = 'jpg';
let currentDuckUrl = '';
let currentDuckName = '';

// State Management
const state = {
    modals: {},
    draggedModal: null,
    offset: { x: 0, y: 0 },
    galleryData: null,
    favorites: [],
    ratings: {},
    comments: []
};

// Initialize on DOM Load
document.addEventListener('DOMContentLoaded', () => {
    initializeTheme(); // Initialize theme first
    initializeModals();
    initializeNavigation();
    initializeRandomDuck();
    initializeGallery();
    initializeHTTPDucks();
    initializeProfileDropdown();
    initializeProfileForm();
    initializeSocialFeatures();
    initializeThemeToggle();
    loadUserFavorites();
});

// ========== THEME FUNCTIONS ==========

function initializeTheme() {
    // Check for saved theme preference or default to light mode
    const currentTheme = localStorage.getItem('theme') || 'light';
    
    // Apply the theme immediately on page load
    if (currentTheme === 'dark') {
        document.body.classList.add('dark-mode');
    }
}

function initializeThemeToggle() {
    const themeToggle = document.getElementById('themeToggle');
    const themeIcon = document.getElementById('themeIcon');
    const themeText = document.getElementById('themeText');
    
    if (!themeToggle || !themeIcon || !themeText) {
        console.warn('Theme toggle elements not found');
        return;
    }
    
    // Set initial icon and text based on current theme
    const currentTheme = localStorage.getItem('theme') || 'light';
    
    if (currentTheme === 'dark') {
        themeIcon.textContent = '☀️';
        themeText.textContent = 'Light Mode';
    } else {
        themeIcon.textContent = '🌙';
        themeText.textContent = 'Dark Mode';
    }
    
    // Toggle theme on button click
    themeToggle.addEventListener('click', (e) => {
        e.stopPropagation(); // Prevent dropdown from closing
        
        document.body.classList.toggle('dark-mode');
        
        // Update icon and text
        if (document.body.classList.contains('dark-mode')) {
            themeIcon.textContent = '☀️';
            themeText.textContent = 'Light Mode';
            localStorage.setItem('theme', 'dark');
            showNotification('Dark mode enabled 🌙', 'info');
        } else {
            themeIcon.textContent = '🌙';
            themeText.textContent = 'Dark Mode';
            localStorage.setItem('theme', 'light');
            showNotification('Light mode enabled ☀️', 'info');
        }
    });
}

// Profile Dropdown
function initializeProfileDropdown() {
    const profileBtn = document.getElementById('profileBtn');
    const dropdownMenu = document.getElementById('dropdownMenu');
    
    if (!profileBtn || !dropdownMenu) return;
    
    profileBtn.addEventListener('click', (e) => {
        e.stopPropagation();
        profileBtn.classList.toggle('active');
        dropdownMenu.classList.toggle('active');
    });
    
    // Close dropdown when clicking outside
    document.addEventListener('click', (e) => {
        if (!profileBtn.contains(e.target) && !dropdownMenu.contains(e.target)) {
            profileBtn.classList.remove('active');
            dropdownMenu.classList.remove('active');
        }
    });
    
    // Handle dropdown item clicks
    const dropdownItems = dropdownMenu.querySelectorAll('.dropdown-item[data-modal]');
    dropdownItems.forEach(item => {
        item.addEventListener('click', () => {
            const modalName = item.getAttribute('data-modal');
            openModal(modalName + '-modal');
            profileBtn.classList.remove('active');
            dropdownMenu.classList.remove('active');
            
            // Load data when opening specific modals
            if (modalName === 'favorites') loadFavorites();
            if (modalName === 'my-ratings') loadRatings();
            if (modalName === 'my-comments') loadComments();
        });
    });
}

// Profile Form
function initializeProfileForm() {
    const form = document.getElementById('profile-form');
    const messageDiv = document.getElementById('profile-message');
    
    if (!form) return;
    
    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        
        const formData = new FormData(form);
        formData.append('action', 'update_profile');
        
        try {
            const response = await fetch(PROFILE_API, {
                method: 'POST',
                body: formData
            });
            
            const data = await response.json();
            
            if (data.success) {
                showMessage(messageDiv, data.message, 'success');
                // Clear password fields
                document.getElementById('current-password').value = '';
                document.getElementById('new-password').value = '';
                document.getElementById('confirm-password').value = '';
            } else {
                showMessage(messageDiv, data.error, 'error');
            }
        } catch (error) {
            showMessage(messageDiv, 'Failed to update profile', 'error');
        }
    });
}

function showMessage(element, message, type) {
    element.textContent = message;
    element.className = type;
    element.style.display = 'block';
    
    setTimeout(() => {
        element.style.display = 'none';
    }, 5000);
}

// Social Features
function initializeSocialFeatures() {
    // Favorite button
    const favoriteBtn = document.getElementById('favorite-duck-btn');
    if (favoriteBtn) {
        favoriteBtn.addEventListener('click', toggleFavorite);
    }
    
    // Comment submit button
    const submitCommentBtn = document.getElementById('submit-comment-btn');
    if (submitCommentBtn) {
        submitCommentBtn.addEventListener('click', async () => {
            const commentTextarea = document.getElementById('duck-comment');
            if (commentTextarea) {
                const comment = commentTextarea.value;
                const success = await addComment(comment);
                if (success) {
                    commentTextarea.value = ''; // Clear the textarea
                }
            }
        });
    }
    
    // Star rating
    const stars = document.querySelectorAll('.star');
    stars.forEach(star => {
        star.addEventListener('click', () => {
            const rating = parseInt(star.getAttribute('data-rating'));
            rateDuck(rating);
        });
        
        star.addEventListener('mouseenter', () => {
            const rating = parseInt(star.getAttribute('data-rating'));
            highlightStars(rating);
        });
    });
    
    const starRating = document.getElementById('duck-star-rating');
    if (starRating) {
        starRating.addEventListener('mouseleave', () => {
            // Show current rating or clear
            const currentRating = state.ratings[currentDuckUrl] || 0;
            highlightStars(currentRating);
        });
    }
}

// Load user's favorites and ratings on startup
async function loadUserFavorites() {
    try {
        const response = await fetch(`${PROFILE_API}?action=get_favorites`);
        const data = await response.json();
        
        if (data.success) {
            state.favorites = data.favorites.map(fav => fav.duck_url);
        }
    } catch (error) {
        console.error('Error loading favorites:', error);
    }
    
    // Also load ratings
    try {
        const response = await fetch(`${PROFILE_API}?action=get_ratings`);
        const data = await response.json();
        
        if (data.success) {
            data.ratings.forEach(rating => {
                state.ratings[rating.duck_url] = rating.rating;
            });
        }
    } catch (error) {
        console.error('Error loading ratings:', error);
    }
}

// Check if current duck is favorited
function checkFavoriteStatus() {
    const favoriteBtn = document.getElementById('favorite-duck-btn');
    if (!favoriteBtn || !currentDuckUrl) return;
    
    const isFavorite = state.favorites.includes(currentDuckUrl);
    
    if (isFavorite) {
        favoriteBtn.classList.add('active');
        favoriteBtn.innerHTML = '💖 Unfavorite';
    } else {
        favoriteBtn.classList.remove('active');
        favoriteBtn.innerHTML = '❤️ Favorite';
    }
}

async function toggleFavorite() {
    if (!currentDuckUrl) {
        showNotification('Please load a duck first!', 'error');
        return;
    }
    
    const favoriteBtn = document.getElementById('favorite-duck-btn');
    const isFavorite = state.favorites.includes(currentDuckUrl);
    
    const formData = new FormData();
    formData.append('action', isFavorite ? 'remove_favorite' : 'add_favorite');
    formData.append('duck_url', currentDuckUrl);
    formData.append('duck_name', currentDuckName);
    
    try {
        const response = await fetch(PROFILE_API, {
            method: 'POST',
            body: formData
        });
        
        const data = await response.json();
        
        if (data.success) {
            if (isFavorite) {
                // Remove from favorites
                state.favorites = state.favorites.filter(url => url !== currentDuckUrl);
                favoriteBtn.classList.remove('active');
                favoriteBtn.innerHTML = '❤️ Favorite';
                showNotification('Removed from favorites!', 'info');
            } else {
                // Add to favorites
                state.favorites.push(currentDuckUrl);
                favoriteBtn.classList.add('active');
                favoriteBtn.innerHTML = '💖 Unfavorite';
                showNotification('Added to favorites! Check Profile → My Favorites', 'success');
            }
        } else {
            showNotification(data.error || 'Failed to update favorite', 'error');
        }
    } catch (error) {
        console.error('Error toggling favorite:', error);
        showNotification('Failed to update favorite', 'error');
    }
}

async function rateDuck(rating) {
    if (!currentDuckUrl) {
        showNotification('Please load a duck first!', 'error');
        return;
    }
    
    const formData = new FormData();
    formData.append('action', 'add_rating');
    formData.append('duck_url', currentDuckUrl);
    formData.append('duck_name', currentDuckName);
    formData.append('rating', rating);
    
    try {
        const response = await fetch(PROFILE_API, {
            method: 'POST',
            body: formData
        });
        
        const data = await response.json();
        
        if (data.success) {
            highlightStars(rating);
            state.ratings[currentDuckUrl] = rating;
            const info = document.getElementById('duck-info');
            if (info) {
                info.textContent = `You rated this duck ${rating} star${rating > 1 ? 's' : ''}!`;
            }
            showNotification(`Rated ${rating} star${rating > 1 ? 's' : ''}!`, 'success');
        } else {
            showNotification(data.error || 'Failed to rate', 'error');
        }
    } catch (error) {
        console.error('Error rating duck:', error);
        showNotification('Failed to rate duck', 'error');
    }
}

function highlightStars(rating) {
    const stars = document.querySelectorAll('.star');
    stars.forEach((star, index) => {
        if (index < rating) {
            star.classList.add('active');
        } else {
            star.classList.remove('active');
        }
    });
}

// Add comment functionality
async function addComment(comment) {
    if (!currentDuckUrl) {
        showNotification('Please load a duck first!', 'error');
        return false;
    }
    
    if (!comment || comment.trim() === '') {
        showNotification('Please enter a comment', 'error');
        return false;
    }
    
    const formData = new FormData();
    formData.append('action', 'add_comment');
    formData.append('duck_url', currentDuckUrl);
    formData.append('duck_name', currentDuckName);
    formData.append('comment', comment.trim());
    
    try {
        const response = await fetch(PROFILE_API, {
            method: 'POST',
            body: formData
        });
        
        const data = await response.json();
        
        if (data.success) {
            showNotification('Comment added! Check Profile → My Comments', 'success');
            return true;
        } else {
            showNotification(data.error || 'Failed to add comment', 'error');
            return false;
        }
    } catch (error) {
        console.error('Error adding comment:', error);
        showNotification('Failed to add comment', 'error');
        return false;
    }
}

async function loadFavorites() {
    const grid = document.getElementById('favorites-grid');
    if (!grid) return;
    
    grid.innerHTML = '<div class="loading-spinner">Loading favorites...</div>';
    
    try {
        const response = await fetch(`${PROFILE_API}?action=get_favorites`);
        const data = await response.json();
        
        if (data.success && data.favorites.length > 0) {
            grid.innerHTML = '';
            data.favorites.forEach((fav, index) => {
                const item = document.createElement('div');
                item.className = 'gallery-item';
                item.style.animationDelay = `${index * 0.05}s`;
                
                const img = document.createElement('img');
                img.src = fav.duck_url;
                img.alt = fav.duck_name || 'Favorite Duck';
                img.loading = 'lazy';
                
                const removeBtn = document.createElement('button');
                removeBtn.className = 'remove-favorite';
                removeBtn.textContent = '✖';
                removeBtn.onclick = (e) => {
                    e.stopPropagation();
                    removeFavorite(fav.duck_url);
                };
                
                item.appendChild(img);
                item.appendChild(removeBtn);
                
                item.addEventListener('click', () => {
                    window.open(img.src, '_blank');
                });
                
                grid.appendChild(item);
            });
        } else {
            grid.innerHTML = `
                <div class="empty-state">
                    <div class="empty-state-icon">💔</div>
                    <div class="empty-state-text">No favorites yet</div>
                    <div class="empty-state-subtext">Click the heart button on ducks you love!</div>
                </div>
            `;
        }
    } catch (error) {
        console.error('Error loading favorites:', error);
        grid.innerHTML = '<p style="text-align: center; color: var(--accent-orange);">Failed to load favorites</p>';
    }
}

async function removeFavorite(duckUrl) {
    const formData = new FormData();
    formData.append('action', 'remove_favorite');
    formData.append('duck_url', duckUrl);
    
    try {
        const response = await fetch(PROFILE_API, {
            method: 'POST',
            body: formData
        });
        
        const data = await response.json();
        
        if (data.success) {
            // Update local state
            state.favorites = state.favorites.filter(url => url !== duckUrl);
            loadFavorites(); // Reload the grid
            showNotification('Removed from favorites', 'info');
        }
    } catch (error) {
        console.error('Error removing favorite:', error);
        showNotification('Failed to remove favorite', 'error');
    }
}

async function loadRatings() {
    const list = document.getElementById('ratings-list');
    if (!list) return;
    
    list.innerHTML = '<div class="loading-spinner">Loading ratings...</div>';
    
    try {
        const response = await fetch(`${PROFILE_API}?action=get_ratings`);
        const data = await response.json();
        
        if (data.success && data.ratings.length > 0) {
            list.innerHTML = '';
            data.ratings.forEach(rating => {
                const item = document.createElement('div');
                item.className = 'rating-item';
                
                const img = document.createElement('img');
                img.src = rating.duck_url;
                img.alt = rating.duck_name || 'Rated Duck';
                img.onclick = () => window.open(rating.duck_url, '_blank');
                img.style.cursor = 'pointer';
                
                const info = document.createElement('div');
                info.className = 'rating-info';
                
                const stars = '⭐'.repeat(rating.rating);
                const date = new Date(rating.created_at).toLocaleDateString('en-US', {
                    year: 'numeric',
                    month: 'short',
                    day: 'numeric'
                });
                
                info.innerHTML = `
                    <div class="rating-stars">${stars} (${rating.rating}/5)</div>
                    <div class="rating-name">${rating.duck_name || 'Duck Image'}</div>
                    <div class="rating-date">${date}</div>
                `;
                
                item.appendChild(img);
                item.appendChild(info);
                list.appendChild(item);
            });
        } else {
            list.innerHTML = `
                <div class="empty-state">
                    <div class="empty-state-icon">⭐</div>
                    <div class="empty-state-text">No ratings yet</div>
                    <div class="empty-state-subtext">Rate some ducks to see them here!</div>
                </div>
            `;
        }
    } catch (error) {
        console.error('Error loading ratings:', error);
        list.innerHTML = '<p style="text-align: center; color: var(--accent-orange);">Failed to load ratings</p>';
    }
}

async function loadComments() {
    const list = document.getElementById('comments-list');
    if (!list) return;
    
    list.innerHTML = '<div class="loading-spinner">Loading comments...</div>';
    
    try {
        const response = await fetch(`${PROFILE_API}?action=get_comments`);
        const data = await response.json();
        
        if (data.success && data.comments.length > 0) {
            list.innerHTML = '';
            data.comments.forEach(comment => {
                const item = document.createElement('div');
                item.className = 'comment-item';
                
                const date = new Date(comment.created_at).toLocaleDateString('en-US', {
                    year: 'numeric',
                    month: 'short',
                    day: 'numeric',
                    hour: '2-digit',
                    minute: '2-digit'
                });
                
                item.innerHTML = `
                    <div class="comment-header">
                        <span class="comment-duck-name">${comment.duck_name || 'Duck'}</span>
                        <span class="comment-date">${date}</span>
                    </div>
                    <div class="comment-text">${escapeHtml(comment.comment)}</div>
                `;
                
                // Make it clickable to view the duck
                item.style.cursor = 'pointer';
                item.onclick = () => window.open(comment.duck_url, '_blank');
                
                list.appendChild(item);
            });
        } else {
            list.innerHTML = `
                <div class="empty-state">
                    <div class="empty-state-icon">💬</div>
                    <div class="empty-state-text">No comments yet</div>
                    <div class="empty-state-subtext">Share your thoughts about ducks!</div>
                </div>
            `;
        }
    } catch (error) {
        console.error('Error loading comments:', error);
        list.innerHTML = '<p style="text-align: center; color: var(--accent-orange);">Failed to load comments</p>';
    }
}

// Utility function to escape HTML
function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

// Notification system
function showNotification(message, type = 'info') {
    const notification = document.createElement('div');
    notification.className = `notification notification-${type}`;
    notification.textContent = message;
    
    // Add styles dynamically
    notification.style.cssText = `
        position: fixed;
        top: 80px;
        right: 20px;
        padding: 1rem 1.5rem;
        border-radius: 12px;
        font-weight: 600;
        z-index: 10000;
        animation: slideInRight 0.3s ease-out, slideOutRight 0.3s ease-in 2.7s;
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    `;
    
    if (type === 'success') {
        notification.style.background = '#4caf50';
        notification.style.color = 'white';
    } else if (type === 'error') {
        notification.style.background = '#f44336';
        notification.style.color = 'white';
    } else {
        notification.style.background = '#2196f3';
        notification.style.color = 'white';
    }
    
    document.body.appendChild(notification);
    
    setTimeout(() => {
        notification.remove();
    }, 3000);
}

// Add notification animations to stylesheet
if (!document.getElementById('notification-styles')) {
    const style = document.createElement('style');
    style.id = 'notification-styles';
    style.textContent = `
        @keyframes slideInRight {
            from {
                transform: translateX(400px);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }
        @keyframes slideOutRight {
            from {
                transform: translateX(0);
                opacity: 1;
            }
            to {
                transform: translateX(400px);
                opacity: 0;
            }
        }
    `;
    document.head.appendChild(style);
}

// Modal Management
function initializeModals() {
    const modals = document.querySelectorAll('.modal');
    
    modals.forEach(modal => {
        positionModalCenter(modal);
        makeModalDraggable(modal);
        state.modals[modal.id] = modal;
    });
    
    document.querySelectorAll('.close-btn').forEach(btn => {
        btn.addEventListener('click', (e) => {
            const modalId = btn.getAttribute('data-close');
            closeModal(modalId);
        });
    });
}

function positionModalCenter(modal) {
    const rect = modal.getBoundingClientRect();
    const x = (window.innerWidth - 400) / 2;
    const y = Math.max(50, (window.innerHeight - rect.height) / 3);
    
    modal.style.left = x + 'px';
    modal.style.top = y + 'px';
}

function makeModalDraggable(modal) {
    const header = modal.querySelector('.modal-header');
    let isDragging = false;
    let currentX, currentY, initialX, initialY;
    
    header.addEventListener('mousedown', dragStart);
    document.addEventListener('mousemove', drag);
    document.addEventListener('mouseup', dragEnd);
    
    function dragStart(e) {
        if (e.target.classList.contains('close-btn') || e.target.closest('.close-btn')) {
            return;
        }
        
        isDragging = true;
        modal.style.zIndex = getHighestZIndex() + 1;
        
        initialX = e.clientX - (parseFloat(modal.style.left) || 0);
        initialY = e.clientY - (parseFloat(modal.style.top) || 0);
        
        header.style.cursor = 'grabbing';
    }
    
    function drag(e) {
        if (!isDragging) return;
        
        e.preventDefault();
        currentX = e.clientX - initialX;
        currentY = e.clientY - initialY;
        
        modal.style.left = currentX + 'px';
        modal.style.top = currentY + 'px';
    }
    
    function dragEnd() {
        if (!isDragging) return;
        isDragging = false;
        header.style.cursor = 'move';
    }
}

function getHighestZIndex() {
    const modals = document.querySelectorAll('.modal');
    let highest = 1000;
    
    modals.forEach(modal => {
        const z = parseInt(window.getComputedStyle(modal).zIndex) || 1000;
        if (z > highest) highest = z;
    });
    
    return highest;
}

function openModal(modalId) {
    const modal = state.modals[modalId];
    if (modal) {
        modal.classList.add('active');
        modal.style.zIndex = getHighestZIndex() + 1;
    }
}

function closeModal(modalId) {
    const modal = state.modals[modalId];
    if (modal) {
        modal.classList.remove('active');
    }
}

// Navigation
function initializeNavigation() {
    const navCards = document.querySelectorAll('.nav-card');
    
    navCards.forEach(card => {
        card.addEventListener('click', () => {
            const modalName = card.getAttribute('data-modal');
            openModal(modalName + '-modal');
        });
    });
}

// Random Duck Feature
function initializeRandomDuck() {
    const newDuckBtn = document.getElementById('new-duck-btn');
    const toggleTypeBtn = document.getElementById('toggle-type-btn');
    
    if (newDuckBtn) {
        newDuckBtn.addEventListener('click', loadRandomDuck);
    }
    
    if (toggleTypeBtn) {
        toggleTypeBtn.addEventListener('click', () => {
            currentDuckType = currentDuckType === 'jpg' ? 'gif' : 'jpg';
            toggleTypeBtn.textContent = currentDuckType === 'jpg' ? 'Switch to GIF' : 'Switch to JPG';
            loadRandomDuck();
        });
    }
    
    loadRandomDuck();
}

async function loadRandomDuck() {
    const img = document.getElementById('random-duck-img');
    const info = document.getElementById('duck-info');
    const spinner = document.querySelector('#random-duck-modal .loading-spinner');
    
    if (!img) return;
    
    img.classList.remove('loaded');
    if (spinner) spinner.style.display = 'block';
    
    try {
        const response = await fetch(`${API_PROXY}?action=random&type=${currentDuckType}`);
        
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        
        const data = await response.json();
        
        const tempImg = new Image();
        tempImg.onload = () => {
            img.src = data.url;
            img.classList.add('loaded');
            if (spinner) spinner.style.display = 'none';
            if (info) info.textContent = data.message || 'Enjoy this random duck!';
            
            // Store current duck info
            currentDuckUrl = data.url;
            currentDuckName = data.url.split('/').pop();
            
            // Check favorite status
            checkFavoriteStatus();
            
            // Load and display current rating for this duck
            const currentRating = state.ratings[currentDuckUrl] || 0;
            highlightStars(currentRating);
            
            if (currentRating > 0 && info) {
                info.textContent = `You rated this duck ${currentRating} star${currentRating > 1 ? 's' : ''}! ${data.message || ''}`;
            }
            
            // Clear comment box for new duck
            const commentBox = document.getElementById('duck-comment');
            if (commentBox) commentBox.value = '';
        };
        tempImg.onerror = () => {
            if (info) info.textContent = 'Image failed to load. Try again!';
            if (spinner) spinner.style.display = 'none';
        };
        tempImg.src = data.url;
        
    } catch (error) {
        console.error('Error loading duck:', error);
        if (info) info.textContent = 'Oops! Could not load duck. Try again!';
        if (spinner) spinner.style.display = 'none';
    }
}

// Gallery Feature
async function initializeGallery() {
    const filterBtns = document.querySelectorAll('.filter-btn');
    
    filterBtns.forEach(btn => {
        btn.addEventListener('click', () => {
            filterBtns.forEach(b => b.classList.remove('active'));
            btn.classList.add('active');
            
            const type = btn.getAttribute('data-type');
            loadGallery(type);
        });
    });
    
    try {
        const response = await fetch(`${API_PROXY}?action=list`);
        
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        
        state.galleryData = await response.json();
        loadGallery('jpg');
        loadGIFsGallery();
        
    } catch (error) {
        console.error('Error loading gallery list:', error);
        
        const galleryGrid = document.getElementById('gallery-grid');
        const gifsGrid = document.getElementById('gifs-grid');
        
        if (galleryGrid) {
            galleryGrid.innerHTML = '<p style="text-align: center; padding: 2rem; color: var(--accent-orange);">Oops! Could not load gallery. Please try refreshing the page.</p>';
        }
        
        if (gifsGrid) {
            gifsGrid.innerHTML = '<p style="text-align: center; padding: 2rem; color: var(--accent-orange);">Oops! Could not load GIFs. Please try refreshing the page.</p>';
        }
    }
}

function loadGallery(type) {
    const grid = document.getElementById('gallery-grid');
    if (!grid) return;
    
    if (!state.galleryData) {
        grid.innerHTML = '<p style="text-align: center; padding: 2rem; color: var(--accent-orange);">Gallery data not available. Please refresh the page.</p>';
        return;
    }
    
    const items = type === 'jpg' ? state.galleryData.images : state.galleryData.gifs;
    
    grid.innerHTML = '';
    
    const displayItems = items.slice(0, 20);
    
    displayItems.forEach((filename, index) => {
        const item = document.createElement('div');
        item.className = 'gallery-item';
        item.style.animationDelay = `${index * 0.05}s`;
        
        const img = document.createElement('img');
        img.src = `https://random-d.uk/api/${filename}`;
        img.alt = `Duck ${filename}`;
        img.loading = 'lazy';
        
        item.appendChild(img);
        
        item.addEventListener('click', () => {
            window.open(img.src, '_blank');
        });
        
        grid.appendChild(item);
    });
}

function loadGIFsGallery() {
    const grid = document.getElementById('gifs-grid');
    if (!grid || !state.galleryData) return;
    
    const gifs = state.galleryData.gifs;
    
    grid.innerHTML = '';
    
    const displayGifs = gifs.slice(0, 12);
    
    displayGifs.forEach((filename, index) => {
        const item = document.createElement('div');
        item.className = 'gallery-item';
        item.style.animationDelay = `${index * 0.05}s`;
        
        const img = document.createElement('img');
        img.src = `https://random-d.uk/api/${filename}`;
        img.alt = `Duck GIF ${filename}`;
        img.loading = 'lazy';
        
        item.appendChild(img);
        
        item.addEventListener('click', () => {
            window.open(img.src, '_blank');
        });
        
        grid.appendChild(item);
    });
}

// HTTP Status Ducks
function initializeHTTPDucks() {
    const httpBtns = document.querySelectorAll('.http-btn');
    const httpImg = document.getElementById('http-duck-img');
    
    httpBtns.forEach(btn => {
        btn.addEventListener('click', () => {
            const code = btn.getAttribute('data-code');
            loadHTTPDuck(code);
        });
    });
}

function loadHTTPDuck(code) {
    const httpImg = document.getElementById('http-duck-img');
    if (!httpImg) return;
    
    httpImg.classList.remove('loaded');
    
    const url = `https://random-d.uk/api/http/${code}`;
    
    const tempImg = new Image();
    tempImg.onload = () => {
        httpImg.src = url;
        httpImg.classList.add('loaded');
        
        // Store for potential favoriting
        currentDuckUrl = url;
        currentDuckName = `HTTP ${code} Duck`;
    };
    tempImg.src = url;
}

// Easter egg: Konami code
let konamiCode = [];
const konamiPattern = ['ArrowUp', 'ArrowUp', 'ArrowDown', 'ArrowDown', 'ArrowLeft', 'ArrowRight', 'ArrowLeft', 'ArrowRight', 'b', 'a'];

document.addEventListener('keydown', (e) => {
    konamiCode.push(e.key);
    konamiCode = konamiCode.slice(-10);
    
    if (JSON.stringify(konamiCode) === JSON.stringify(konamiPattern)) {
        createDuckRain();
        konamiCode = [];
    }
});

function createDuckRain() {
    for (let i = 0; i < 20; i++) {
        setTimeout(() => {
            const duck = document.createElement('div');
            duck.textContent = '🦆';
            duck.style.position = 'fixed';
            duck.style.fontSize = '3rem';
            duck.style.left = Math.random() * window.innerWidth + 'px';
            duck.style.top = '-50px';
            duck.style.zIndex = '9999';
            duck.style.pointerEvents = 'none';
            duck.style.animation = 'duckFall 3s linear forwards';
            
            document.body.appendChild(duck);
            
            setTimeout(() => duck.remove(), 3000);
        }, i * 100);
    }
}

// Add duck fall animation
const style = document.createElement('style');
style.textContent = `
    @keyframes duckFall {
        to {
            transform: translateY(${window.innerHeight + 100}px) rotate(360deg);
            opacity: 0;
        }
    }
`;
document.head.appendChild(style);