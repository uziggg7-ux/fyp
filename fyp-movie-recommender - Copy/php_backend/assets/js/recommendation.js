document.addEventListener('DOMContentLoaded', function() {
    const movieGrid = document.getElementById('movieGrid');
    const toastContainer = document.getElementById('toastContainer');

    // Simple mock set to track favorited movies locally (to prevent duplicates)
    // In production, this would be fetched from the database on page load.
    let localFavorites = new Set();

    // --- 1. Event Listener for 'Add to Favorites' Buttons ---
    if (movieGrid) {
        movieGrid.addEventListener('click', function(e) {
            const favoriteButton = e.target.closest('.favorite-btn');
            if (favoriteButton) {
                const movieId = favoriteButton.getAttribute('data-movie-id');
                const movieTitle = favoriteButton.getAttribute('data-movie-title');
                const moviePoster = favoriteButton.getAttribute('data-movie-poster');

                if (localFavorites.has(movieId)) {
                    showToast('Movie Already Saved', `${movieTitle} is already in your favorites!`, 'warning');
                    return;
                }

                addFavorite(movieId, movieTitle, moviePoster, favoriteButton);
            }
        });

        movieGrid.addEventListener('click', function(e) {
            const feedbackBtn = e.target.closest('.feedback-btn');
            if (feedbackBtn) {
                const movieId = feedbackBtn.getAttribute('data-id');
                const type = feedbackBtn.getAttribute('data-type');
                sendFeedback(movieId, type, feedbackBtn);
            }
        });

        async function sendFeedback(movieId, type, button) {
            try {
                const response = await fetch('api/feedback_api.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ movie_id: movieId, type: type })
                });
                const result = await response.json();
                if (result.success) {
                    showToast('Feedback Received', `You ${type}d this movie!`, 'success');
                    // Toggle active state
                    const card = button.closest('.movie-card');
                    card.querySelectorAll('.feedback-btn').forEach(b => b.classList.remove('active'));
                    button.classList.add('active');
                }
            } catch (error) {
                showToast('Error', 'Failed to save feedback.', 'danger');
            }
        }

        // Initial staggered fade-in animation
        document.querySelectorAll('.movie-card-col').forEach((card, index) => {
            card.style.animationDelay = `${0.05 * index}s`;
            card.style.opacity = '1';
        });

        // Neural Vibe Stats Animation
        document.querySelectorAll('.vibe-stat-item').forEach((stat, index) => {
            stat.style.opacity = '0';
            stat.style.transform = 'translateX(-10px)';
            stat.style.transition = 'all 0.6s ease';
            
            setTimeout(() => {
                stat.style.opacity = '1';
                stat.style.transform = 'translateX(0)';
            }, 500 + (index * 200));
        });
    }

    // --- 2. Handle Add to Favorites (AJAX/Fetch) ---
    async function addFavorite(movieId, movieTitle, moviePoster, button) {
        const originalText = button.innerHTML;

        // Show loading state with High-Tech "Syncing" feel
        button.disabled = true;
        button.classList.add('syncing');
        button.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span> SYNCING_NEURAL_DATA...';

        try {
            const formData = new URLSearchParams();
            formData.append('movie_id', movieId);
            formData.append('title', movieTitle);
            formData.append('poster', moviePoster);

            const response = await fetch('api/save_favorite.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: formData
            });

            const result = await response.json();

            if (response.ok && (result.status === 'success' || result.status === 'info')) {
                // SUCCESS: Update UI state
                localFavorites.add(movieId);
                button.classList.add('added');
                button.innerHTML = '<i class="bi bi-heart-fill me-1"></i> SAVED';

                showToast(result.status === 'success' ? 'Movie Saved!' : 'Already in Favorites', result.message, result.status === 'success' ? 'success' : 'info');
            } else {
                throw new Error(result.message || 'Server response failed.');
            }

        } catch (error) {
            console.error('Error adding favorite:', error);

            // FAILURE: Revert button state and show error
            button.innerHTML = originalText;
            button.disabled = false;
            showToast('Error', `Failed to add ${movieTitle} to favorites: ${error.message}`, 'danger');
        }
    }

    // --- 3. Bootstrap Toast Notification Handler ---
    function showToast(title, message, type = 'primary') {
        const toastId = `toast-${Date.now()}`;
        const toastHtml = `
            <div id="${toastId}" class="toast toast-tech align-items-center text-white border-0" role="alert" aria-live="assertive" aria-atomic="true" data-bs-delay="4000">
                <div class="toast-header toast-header-tech">
                    <strong class="me-auto">${title}</strong>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="toast" aria-label="Close"></button>
                </div>
                <div class="d-flex">
                    <div class="toast-body">
                        ${message}
                    </div>
                </div>
            </div>
        `;

        toastContainer.insertAdjacentHTML('beforeend', toastHtml);

        const toastEl = document.getElementById(toastId);
        if (typeof bootstrap !== 'undefined' && bootstrap.Toast) {
            const toast = new bootstrap.Toast(toastEl);
            toast.show();
        } else {
             console.warn("Bootstrap JS Toast component not found.");
        }
    }
});