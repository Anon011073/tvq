/**
 * Shared Utility Functions
 */

// Global to store WordPress settings
window.tvq_settings = window.tvq_settings || window.tvq_params || {};

// Helper to get user-specific localStorage key
function getUserKey(key) {
  const userId = window.tvq_settings.user_id || 'guest';
  return `user_${userId}_${key}`;
}

// Wrapper for TMDB API calls using WordPress AJAX
async function tmdbFetch(endpoint, params = {}) {
  const baseUrl = window.tvq_settings.ajax_url || 'admin-ajax.php';
  const url = new URL(baseUrl, window.location.href);
  url.searchParams.append('action', 'tvq_tmdb_proxy');
  url.searchParams.append('nonce', window.tvq_settings.nonce);
  url.searchParams.append('endpoint', endpoint);
  for (const [key, value] of Object.entries(params)) {
    url.searchParams.append(key, value);
  }
  const response = await fetch(url);
  return response.json();
}

// persistence helpers
async function saveUserData(key, data) {
    tvq_cache[key] = data; // Update cache immediately

    if (!window.tvq_settings.is_logged_in) {
        localStorage.setItem(getUserKey(key), JSON.stringify(data));
        return;
    }

    const formData = new FormData();
    formData.append('action', 'tvq_save_user_data');
    formData.append('nonce', window.tvq_settings.nonce);
    formData.append('key', `tvq_${key}`);
    formData.append('data', JSON.stringify(data));

    await fetch(window.tvq_settings.ajax_url, {
        method: 'POST',
        body: formData
    });
}

const tvq_cache = {};
async function getUserData(key) {
    if (tvq_cache[key]) return tvq_cache[key];

    if (!window.tvq_settings.is_logged_in) {
        const localData = JSON.parse(localStorage.getItem(getUserKey(key)) || '[]');
        tvq_cache[key] = localData;
        return localData;
    }

    const url = new URL(window.tvq_settings.ajax_url, window.location.href);
    url.searchParams.append('action', 'tvq_get_user_data');
    url.searchParams.append('nonce', window.tvq_settings.nonce);
    url.searchParams.append('key', `tvq_${key}`);

    const response = await fetch(url);
    const result = await response.json();

    if (result.success) {
        const parsed = typeof result.data === 'string' ? JSON.parse(result.data) : result.data;
        tvq_cache[key] = parsed;
        return parsed;
    }
    return [];
}

// Media detail helpers
function loadTrailers(id, type, targetId = 'trailer') {
    const endpoint = type === 'tv' ? `/tv/${id}/videos` : `/movie/${id}/videos`;
    tmdbFetch(endpoint)
        .then(data => {
            const trailer = data.results.find(v => (v.type === 'Trailer' || v.type === 'Teaser') && v.site === 'YouTube');
            const trailerDiv = document.getElementById(targetId);
            if (trailerDiv) {
                if (trailer) {
                    trailerDiv.innerHTML = `
                        <h3>📽️ Trailer</h3>
                        <div class="video-container">
                            <iframe src="https://www.youtube.com/embed/${trailer.key}" frameborder="0" allowfullscreen></iframe>
                        </div>
                    `;
                } else {
                    trailerDiv.innerHTML = ''; // Hide if no trailer found
                }
            }
        });
}

function loadCast(id, type, targetId = 'cast') {
    const endpoint = type === 'tv' ? `/tv/${id}/credits` : `/movie/${id}/credits`;
    tmdbFetch(endpoint)
        .then(data => {
            const castDiv = document.getElementById(targetId);
            if (castDiv && data.cast) {
                castDiv.innerHTML = `
                    <h3>🎭 Cast</h3>
                    <div class="cast-grid">
                        ${data.cast.slice(0, 6).map(c => `
                            <div class="cast-card">
                                <strong>${c.name}</strong>
                                <span>${c.character}</span>
                            </div>
                        `).join('')}
                    </div>
                `;
            }
        });
}

function loadReviews(id, type, targetId = 'reviews') {
    const endpoint = type === 'tv' ? `/tv/${id}/reviews` : `/movie/${id}/reviews`;
    tmdbFetch(endpoint).then(data => {
        const reviewDiv = document.getElementById(targetId);
        if (reviewDiv && data.results && data.results.length > 0) {
            reviewDiv.innerHTML = `<h3>📝 Reviews</h3>` + data.results.slice(0, 2).map(r => `
                <div class="review-item">
                    <strong>${r.author}</strong>
                    <p>${r.content.substring(0, 300)}...</p>
                </div>
            `).join('');
        }
    });
}

// Storage helpers
async function toggleMediaStorage(id, name, type, key, posterPath = '') {
    let data = await getUserData(key);
    const exists = data.some(item => item.id === id);

    if (exists) {
        data = data.filter(item => item.id !== id);
        // Removed blocking alert for performance
    } else {
        data.push({
            id,
            name,
            type,
            poster_path: posterPath,
            date: new Date().toISOString()
        });
        // Removed blocking alert for performance
    }

    // Refresh button states immediately for better UI response
    if (document.getElementById('favBtn') && key === 'favs') updateButtonStates(id, 'favs', 'favBtn', !exists);
    if (document.getElementById('watchlistBtn') && key === 'watchlist') updateButtonStates(id, 'watchlist', 'watchlistBtn', !exists);
    if (document.getElementById('movieFavBtn') && key === 'favs') updateButtonStates(id, 'favs', 'movieFavBtn', !exists);
    if (document.getElementById('movieWatchlistBtn') && key === 'watchlist') updateButtonStates(id, 'watchlist', 'movieWatchlistBtn', !exists);

    await saveUserData(key, data);
}

async function updateButtonStates(id, storageKey, btnId, isForcedValue = null) {
    const btn = document.getElementById(btnId);
    if (!btn) return;

    let exists;
    if (isForcedValue !== null) {
        exists = isForcedValue;
    } else {
        const data = await getUserData(storageKey);
        exists = data.some(item => item.id === id);
    }

    if (storageKey === 'favs') {
        btn.innerHTML = exists ? '❤️ Unfavourite' : '❤️ Favourite';
        btn.classList.toggle('btn-danger', exists);
        btn.classList.toggle('btn-secondary', !exists);
    } else if (storageKey === 'watchlist') {
        btn.innerHTML = exists ? '📋 Remove Watchlist' : '📋 Watchlist';
        btn.classList.toggle('btn-primary', exists);
        btn.classList.toggle('btn-secondary', !exists);
    }
}

function loadRecommendations(id, type, targetId = 'recommendations') {
    const endpoint = type === 'tv' ? `/tv/${id}/recommendations` : `/movie/${id}/recommendations`;
    tmdbFetch(endpoint).then(data => {
        const recDiv = document.getElementById(targetId);
        if (recDiv && data.results && data.results.length > 0) {
            recDiv.innerHTML = `<h3>🔁 Recommendations</h3><div class="rec-grid"></div>`;
            const grid = recDiv.querySelector('.rec-grid');
            data.results.slice(0, 6).forEach(item => {
                const card = document.createElement('div');
                card.className = 'rec-card';
                card.innerHTML = `<img src="https://image.tmdb.org/t/p/w154${item.poster_path}" /><h4>${item.name || item.title}</h4>`;
                card.onclick = () => showView(type === 'tv' ? 'showDetails' : 'movieDetails', {id: item.id});
                grid.appendChild(card);
            });
        }
    });
}
