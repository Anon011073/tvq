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
                    <button id="favBtn" class="btn btn-secondary" onclick="toggleMediaStorage(${show.id}, '${show.name.replace(/'/g, "\\'").replace(/"/g, "&quot;")}', 'tv', 'favs', '${show.poster_path}')">❤️ Favourite</button>
                    <button id="watchlistBtn" class="btn btn-secondary" onclick="toggleMediaStorage(${show.id}, '${show.name.replace(/'/g, "\\'").replace(/"/g, "&quot;")}', 'tv', 'watchlist', '${show.poster_path}')">📋 Watchlist</button>
                    ${tvq_settings.can_watch ?
                        `<button id="mainWatchBtn" class="btn btn-primary btn-watch" onclick="watchNextEpisode(${show.id})">▶️ Watch Now</button>` :
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

        <section class="details-section progress-tracker" style="background: rgba(255,94,87,0.1); padding: 20px; border-radius: 12px; margin-bottom: 30px;">
            <h3>📊 My Episode Progress</h3>
            <p>Enter the last season and episode you've watched:</p>
            <div style="display: flex; gap: 15px; align-items: center;">
                <label>Season:</label> <input type="number" id="progressSeason" min="1" style="width: 60px; padding: 5px;">
                <label>Episode:</label> <input type="number" id="progressEpisode" min="1" style="width: 60px; padding: 5px;">
                <button class="btn btn-primary" onclick="saveShowProgress(${show.id})">Save Progress</button>
            </div>
            <div id="progressMessage" style="margin-top: 10px; font-weight: bold; color: #ff5e57;"></div>
        </section>

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
    loadShowProgress(show.id);
}

async function loadShowProgress(id) {
    const progress = await getUserData('watch_progress');
    const showProgress = progress.find(p => p.id == id);
    if (showProgress) {
        document.getElementById('progressSeason').value = showProgress.s || 1;
        document.getElementById('progressEpisode').value = showProgress.e || 1;
        document.getElementById('progressMessage').innerText = `Last Watched: Season ${showProgress.s}, Episode ${showProgress.e}`;
    }
}

async function saveShowProgress(id) {
    const s = parseInt(document.getElementById('progressSeason').value);
    const e = parseInt(document.getElementById('progressEpisode').value);
    if (!s || !e) return;

    let progress = await getUserData('watch_progress');
    const index = progress.findIndex(p => p.id == id);
    if (index > -1) {
        progress[index].s = s;
        progress[index].e = e;
        progress[index].date = new Date().toISOString();
    } else {
        progress.push({ id, type: 'tv', s, e, date: new Date().toISOString() });
    }

    await saveUserData('watch_progress', progress);
    document.getElementById('progressMessage').innerText = `Progress saved! Last Watched: Season ${s}, Episode ${e}`;

    // Update main watch button to reflect progress
    const watchBtn = document.getElementById('mainWatchBtn');
    if (watchBtn) watchBtn.onclick = () => watchNextEpisode(id);
}

async function watchNextEpisode(id) {
    const progress = await getUserData('watch_progress');
    const showProgress = progress.find(p => p.id == id);

    let s = 1, e = 1;
    if (showProgress) {
        s = showProgress.s || 1;
        e = (showProgress.e || 0) + 1;
    }

    showView('watchView', { id, type: 'tv', s, e });
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
                <div class="episode-row" onclick="showView('watchView', {id: ${showId}, type: 'tv', s: ${seasonNumber}, e: ${ep.episode_number}})">
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
