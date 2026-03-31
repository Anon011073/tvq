/**
 * js/watchlist.js - Watchlist View Logic
 */

async function renderWatchlist() {
    const container = document.getElementById('watchlistView');
    container.innerHTML = '<h2>📋 Your Watchlist</h2><div class="show-grid" id="watchlistGrid"></div>';

    const watchlist = await getUserData('watchlist');
    const grid = document.getElementById('watchlistGrid');

    if (watchlist.length === 0) {
        grid.innerHTML = '<p class="empty-msg">Your watchlist is empty.</p>';
        return;
    }

    // Sort by most recently added
    watchlist.sort((a, b) => new Date(b.date) - new Date(a.date));

    for (const item of watchlist) {
        const endpoint = item.type === 'tv' ? `/tv/${item.id}` : `/movie/${item.id}`;
        tmdbFetch(endpoint).then(details => {
            const card = document.createElement('div');
            card.className = 'show-card';
            const title = details.name || details.title;
            const poster = details.poster_path ? `https://image.tmdb.org/t/p/w200${details.poster_path}` : 'https://placehold.co/200x300?text=No+Image';

            card.innerHTML = `
                <div class="card-inner">
                    <img src="${poster}" alt="${title}" />
                    <div class="card-info">
                        <h3>${title}</h3>
                        <p>${item.type === 'tv' ? '📺 TV' : '🎬 Movie'}</p>
                        <button class="btn btn-small btn-secondary" onclick="event.stopPropagation(); toggleMediaStorage(${item.id}, '${title.replace(/'/g, "\\'")}', '${item.type}', 'watchlist').then(renderWatchlist)">Remove</button>
                    </div>
                </div>
            `;
            card.onclick = () => showView(item.type === 'tv' ? 'showDetails' : 'movieDetails', { id: item.id });
            grid.appendChild(card);
        });
    }
}
