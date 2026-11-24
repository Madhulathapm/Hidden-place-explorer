/**
 * Hidden Places Explorer - Main Frontend Interactions
 */

document.addEventListener('DOMContentLoaded', function () {
    // 1. Mobile Menu Toggle
    const mobileToggle = document.querySelector('.mobile-nav-toggle');
    const navMenu = document.querySelector('.nav-menu');
    if (mobileToggle && navMenu) {
        mobileToggle.addEventListener('click', function () {
            navMenu.classList.toggle('open');
            const icon = mobileToggle.querySelector('i');
            if (icon) {
                icon.classList.toggle('fa-bars');
                icon.classList.toggle('fa-times');
            }
        });
    }

    // 2. User Dropdown Toggle on Mobile/Touch
    const userDropdown = document.querySelector('.user-dropdown');
    if (userDropdown) {
        const btn = userDropdown.querySelector('.user-dropdown-btn');
        if (btn) {
            btn.addEventListener('click', function (e) {
                if (window.innerWidth <= 768) {
                    e.preventDefault();
                    userDropdown.classList.toggle('show');
                }
            });
        }
    }

    // 3. AJAX Favorites Toggle
    document.addEventListener('click', function (e) {
        const favBtn = e.target.closest('.fav-toggle-btn');
        if (!favBtn) return;

        e.preventDefault();
        e.stopPropagation();

        const destId = favBtn.getAttribute('data-id');
        const csrfToken = favBtn.getAttribute('data-csrf') || document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

        if (!destId) return;

        // Visual feedback immediately
        favBtn.style.transform = 'scale(0.85)';
        setTimeout(() => { favBtn.style.transform = ''; }, 150);

        const formData = new FormData();
        formData.append('destination_id', destId);
        if (csrfToken) {
            formData.append('csrf_token', csrfToken);
        }

        // Determine base url from attribute or window
        const baseUrl = document.body.getAttribute('data-base-url') || '';
        const endpoint = baseUrl + '/ajax/toggle_favorite.php';

        fetch(endpoint, {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'unauthorized') {
                showToast(data.message || 'Please log in to save favorites', 'fa-lock');
                setTimeout(() => {
                    window.location.href = baseUrl + '/login.php?redirect=' + encodeURIComponent(window.location.pathname + window.location.search);
                }, 1200);
                return;
            }

            if (data.status === 'success') {
                if (data.action === 'added') {
                    favBtn.classList.add('is-active');
                    const icon = favBtn.querySelector('i');
                    if (icon) {
                        icon.className = 'fas fa-heart';
                    }
                    showToast(data.message || 'Saved to your favorites!', 'fa-heart text-danger');
                } else {
                    favBtn.classList.remove('is-active');
                    const icon = favBtn.querySelector('i');
                    if (icon) {
                        icon.className = 'far fa-heart';
                    }
                    showToast(data.message || 'Removed from favorites', 'fa-heart-broken');
                }

                // If on favorites page, optionally remove the card
                if (document.body.classList.contains('page-favorites') && data.action === 'removed') {
                    const card = favBtn.closest('.gem-card');
                    if (card) {
                        card.style.opacity = '0';
                        card.style.transform = 'scale(0.9)';
                        setTimeout(() => {
                            card.remove();
                            // Check if grid empty
                            const grid = document.querySelector('.destinations-grid');
                            if (grid && grid.children.length === 0) {
                                location.reload();
                            }
                        }, 300);
                    }
                }
            } else {
                showToast(data.message || 'An error occurred', 'fa-exclamation-circle');
            }
        })
        .catch(err => {
            console.error('Favorites toggle error:', err);
            showToast('Unable to update favorite status.', 'fa-exclamation-triangle');
        });
    });

    // 4. Interactive Review Star Rating Hover/Select Feedback
    const starPicker = document.querySelector('.star-picker');
    if (starPicker) {
        const ratingInputs = starPicker.querySelectorAll('input[type="radio"]');
        const ratingText = document.getElementById('selected-rating-text');
        
        ratingInputs.forEach(input => {
            input.addEventListener('change', function () {
                const labels = {
                    '5': '5 Stars - Truly Extraordinary Experience',
                    '4': '4 Stars - Wonderful Hidden Gem',
                    '3': '3 Stars - Good & Worth the Journey',
                    '2': '2 Stars - Average / Difficult Access',
                    '1': '1 Star - Disappointing Experience'
                };
                if (ratingText) {
                    ratingText.textContent = labels[this.value] || `${this.value} Stars`;
                }
            });
        });
    }

    // 5. Auto dismiss flash alerts after 6 seconds
    const flashAlerts = document.querySelectorAll('.flash-alert');
    flashAlerts.forEach(alert => {
        setTimeout(() => {
            alert.style.transition = 'opacity 0.4s ease, transform 0.4s ease';
            alert.style.opacity = '0';
            alert.style.transform = 'translateY(-10px)';
            setTimeout(() => alert.remove(), 400);
        }, 6000);
    });
});

/**
 * Global Toast Notification Helper
 */
function showToast(message, iconClass = 'fa-info-circle') {
    let container = document.querySelector('.toast-container');
    if (!container) {
        container = document.createElement('div');
        container.className = 'toast-container';
        document.body.appendChild(container);
    }

    const toast = document.createElement('div');
    toast.className = 'toast-msg';
    toast.innerHTML = `<i class="fas ${iconClass}"></i> <span>${message}</span>`;
    container.appendChild(toast);

    requestAnimationFrame(() => {
        toast.classList.add('show');
    });

    setTimeout(() => {
        toast.classList.remove('show');
        setTimeout(() => toast.remove(), 350);
    }, 3500);
}
