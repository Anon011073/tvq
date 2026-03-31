/**
 * js/movie.js - Movie Details Logic
 */

function fetchMovieDetails(id) {
    const container = document.getElementById('movieDetails');
    container.innerHTML = '<div class="loading">Loading details...</div>';

    tmdbFetch(`/movie/${id}`)
        .then(movie => {
            renderMovieDetails(movie);
            loadTrailers(id, 'movie', 'movieTrailer');
            loadCast(id, 'movie', 'movieCast');
            loadReviews(id, 'movie', 'movieReviews');
            loadRecommendations(id, 'movie', 'movieRecommendations');
        })
        .catch(err => {
            container.innerHTML = '<div class="error">Error loading movie details.</div>';
            console.error(err);
        });
}

function renderMovieDetails(movie) {
    const container = document.getElementById('movieDetails');
    const poster = movie.poster_path ? `https://image.tmdb.org/t/p/w300${movie.poster_path}` : 'https://placehold.co/300x450?text=No+Image';

    container.innerHTML = `
        <button class="btn btn-secondary back-btn" onclick="showView('mainContent')">⬅️ Back</button>
        <section class="hero">
            <img src="${poster}" alt="${movie.title}" class="poster">
            <div class="hero-text">
                <h1>${movie.title}</h1>
                <p class="overview">${movie.overview}</p>
                <div class="meta-info">
                    <span>📅 Release Date: ${movie.release_date}</span>
                    <span>⭐ Rating: ${movie.vote_average}</span>
                    <span>🕒 Runtime: ${movie.runtime} min</span>
                </div>
                <div class="btn-group">
                    <button id="movieFavBtn" class="btn btn-secondary" onclick="toggleMediaStorage(${movie.id}, '${movie.title.replace(/'/g, "\\'")}', 'movie', 'favs', '${movie.poster_path}')">❤️ Favourite</button>
                    <button id="movieWatchlistBtn" class="btn btn-secondary" onclick="toggleMediaStorage(${movie.id}, '${movie.title.replace(/'/g, "\\'")}', 'movie', 'watchlist', '${movie.poster_path}')">📋 Watchlist</button>
                    ${tvq_settings.can_watch ?
                        `<button class="btn btn-primary btn-watch" onclick="showView('watchView', {id: ${movie.id}, type: 'movie'})">▶️ Watch Now</button>` :
                        `<div class="premium-upsell">
                            <span class="premium-notice">Watch requires Premium Plugin</span>
                            <a href="${tvq_settings.buy_url}" target="_blank" class="btn btn-small btn-premium">🛒 Get Premium</a>
                        </div>`
                    }
                </div>
            </div>
        </section>

        <section id="movieTrailer" class="details-section"><h3>📽️ Trailer</h3><p class="loading">Loading trailer...</p></section>
        <section id="movieCast" class="details-section"></section>
        <section id="movieReviews" class="details-section"></section>
        <section id="movieRecommendations" class="details-section"></section>
    `;

    updateButtonStates(movie.id, 'favs', 'movieFavBtn');
    updateButtonStates(movie.id, 'watchlist', 'movieWatchlistBtn');
}
