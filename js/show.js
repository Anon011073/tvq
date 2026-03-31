/**
 * js/show.js - TV Show Details Logic
 */

function fetchShowDetails(id) {
    const container = document.getElementById('showDetails');
    container.innerHTML = '<div class="loading">Loading details...</div>';

    tmdbFetch(`/tv/${id}`)
        .then(show => {
            renderShowDetails(show);
            loadTrailers(id, 'tv');
            loadCast(id, 'tv');
            loadReviews(id, 'tv');
            loadRecommendations(id, 'tv');
        })
        .catch(err => {
            container.innerHTML = '<div class="error">Error loading show details.</div>';
            console.error(err);
        });
}

function renderShowDetails(show) {
    const container = document.getElementById('showDetails');
    const poster = show.poster_path ? `https://image.tmdb.org/t/p/w300${show.poster_path}` : 'https://placehold.co/300x450?text=No+Image';

    container.innerHTML = `
        <button class="btn btn-secondary back-btn" onclick="showView('mainContent')">⬅️ Back</button>
        <section class="hero">
            <img src="${poster}" alt="${show.name}" class="poster">
            <div class="hero-text">
                <h1>${show.name}</h1>
                <p class="overview">${show.overview}</p>
                <div class="meta-info">
                    <span>📅 First Air: ${show.first_air_date}</span>
                    <span>⭐ Rating: ${show.vote_average}</span>
                    <span>📺 Seasons: ${show.number_of_seasons}</span>
                </div>
                <div class="btn-group">
                    <button id="favBtn" class="btn btn-secondary" onclick="toggleMediaStorage(${show.id}, '${show.name.replace(/'/g, "\\'")}', 'tv', 'favs', '${show.poster_path}')">❤️ Favourite</button>
                    <button id="watchlistBtn" class="btn btn-secondary" onclick="toggleMediaStorage(${show.id}, '${show.name.replace(/'/g, "\\'")}', 'tv', 'watchlist', '${show.poster_path}')">📋 Watchlist</button>
                    ${tvq_settings.can_watch ?
                        `<button class="btn btn-primary btn-watch" onclick="showView('watchView', {id: ${show.id}, type: 'tv'})">▶️ Watch Now</button>` :
                        `<div class="premium-upsell">
                            <span class="premium-notice">Watch requires Premium Plugin</span>
                            <a href="${tvq_settings.buy_url}" target="_blank" class="btn btn-small btn-premium">🛒 Get Premium</a>
                        </div>`
                    }
                </div>
            </div>
        </section>

        <section id="trailer" class="details-section"><h3>📽️ Trailer</h3><p class="loading">Loading trailer...</p></section>

        <section id="cast" class="details-section"></section>
        <section id="reviews" class="details-section"></section>
        <section id="recommendations" class="details-section"></section>

        <section class="details-section">
            <h3>📂 Episodes</h3>
            <div id="episodes" class="episodes-container"></div>
        </section>
    `;

    // Load episodes
    for (let season = 1; season <= show.number_of_seasons; season++) {
        tmdbFetch(`/tv/${show.id}/season/${season}`)
            .then(seasonData => renderEpisodes(show.id, season, seasonData.episodes));
    }

    updateButtonStates(show.id, 'favs', 'favBtn');
    updateButtonStates(show.id, 'watchlist', 'watchlistBtn');
}

function renderEpisodes(showId, seasonNumber, episodes) {
    const container = document.getElementById('episodes');
    if (!container) return;

    const seasonId = `season-${showId}-${seasonNumber}`;
    const div = document.createElement('div');
    div.className = 'season-block';

    div.innerHTML = `
        <h4 onclick="toggleSeason('${seasonId}')" class="season-title">Season ${seasonNumber} <small>(expand)</small></h4>
        <div id="${seasonId}" class="season-body" style="display:none;">
            ${episodes.map(ep => `
                <div class="episode-row">
                    <span>S${seasonNumber}E${ep.episode_number}: ${ep.name}</span>
                </div>
            `).join('')}
        </div>
    `;
    container.appendChild(div);
}

function toggleSeason(id) {
    const el = document.getElementById(id);
    if (el) el.style.display = el.style.display === 'none' ? 'block' : 'none';
}
