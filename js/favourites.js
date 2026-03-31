/**
 * js/favourites.js - Favourites View Logic
 */

async function renderFavourites() {
    const container = document.getElementById('favouritesView');
    container.innerHTML = '<h2>⭐ Your Favourites</h2><div class="show-grid" id="favsGrid"></div>';

    const favs = await getUserData('favs');
    const grid = document.getElementById('favsGrid');

    if (favs.length === 0) {
        grid.innerHTML = '<p class="empty-msg">You haven\'t added any favourites yet.</p>';
        return;
    }

    // Fetch details for each fav to show posters
    for (const item of favs) {
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
                        <button class="btn btn-small btn-secondary" onclick="event.stopPropagation(); toggleMediaStorage(${item.id}, '${title.replace(/'/g, "\\'")}', '${item.type}', 'favs').then(renderFavourites)">Remove</button>
                    </div>
                </div>
            `;
            card.onclick = () => showView(item.type === 'tv' ? 'showDetails' : 'movieDetails', { id: item.id });
            grid.appendChild(card);
        });
    }
}
