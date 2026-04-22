/**
 * js/watch.js - Watch View Logic (Integrated with Premium Plugin)
 */

async function startWatch(id, type, s = 1, e = 1) {
    const container = document.getElementById('watchView');
    container.innerHTML = `
        <button class="btn btn-secondary" style="margin-bottom: 20px;" onclick="showView('${type === 'tv' ? 'showDetails' : 'movieDetails'}', {id: ${id}})">⬅️ Back to Details</button>
        <div class="watch-container">
            <h2>Now Watching ${type === 'tv' ? `(Season ${s} Episode ${e})` : ''}</h2>
            <div id="player-loading" class="loading">Loading secure player...</div>
            <div id="player-frame" class="video-container" style="display:none;"></div>
        </div>
        ${type === 'tv' ? `
            <div id="watch-episodes" class="details-section" style="margin-top: 40px;">
                <h3>📂 Select Episode</h3>
                <div id="watch-episodes-list" class="episodes-container"></div>
            </div>
        ` : ''}
    `;

    // Fetch secure player URL from Premium Plugin via AJAX
    const url = new URL(window.tvq_settings.ajax_url, window.location.href);
    url.searchParams.append('action', 'tvq_get_player');
    url.searchParams.append('nonce', window.tvq_settings.nonce);
    url.searchParams.append('id', id);
    url.searchParams.append('type', type);
    if (type === 'tv') {
        url.searchParams.append('s', s);
        url.searchParams.append('e', e);
    }

    try {
        const response = await fetch(url);
        const result = await response.json();

        if (result.success && result.data.url) {
            document.getElementById('player-loading').style.display = 'none';
            const frame = document.getElementById('player-frame');
            frame.style.display = 'block';
            frame.innerHTML = `<iframe src="${result.data.url}" frameborder="0" allowfullscreen></iframe>`;

            // Log watch progress
            markAsWatched(id, type, s, e);

            // Load episode list if TV
            if (type === 'tv') {
                tmdbFetch(`/tv/${id}`).then(async show => {
                    const epList = document.getElementById('watch-episodes-list');
                    if (!epList) return;
                    epList.innerHTML = '<p class="loading">Loading episodes...</p>';

                    const seasonPromises = [];
                    for (let i = 1; i <= show.number_of_seasons; i++) {
                        seasonPromises.push(tmdbFetch(`/tv/${id}/season/${i}`));
                    }

                    const allSeasons = await Promise.all(seasonPromises);
                    allSeasons.sort((a, b) => a.season_number - b.season_number);

                    epList.innerHTML = '';
                    allSeasons.forEach(data => {
                        const i = data.season_number;
                        const seasonId = `watch-season-${id}-${i}`;
                        const div = document.createElement('div');
                        div.className = 'season-block';
                        div.innerHTML = `
                            <h4 onclick="toggleSeason('${seasonId}')" class="season-title" style="background: #34495e; color: #fff; padding: 10px; border-radius: 6px; margin-bottom: 5px; border-left: 5px solid #ff5e57;">Season ${i}</h4>
                            <div id="${seasonId}" class="season-body" style="${i == s ? 'display:block;' : 'display:none;'}">
                                ${data.episodes.map(ep => `
                                    <div class="episode-row ${ep.episode_number == e && i == s ? 'active' : ''}"
                                         style="${ep.episode_number == e && i == s ? 'background: rgba(255,94,87,0.1); border-left: 3px solid #ff5e57;' : ''}"
                                         onclick="showView('watchView', {id: ${id}, type: 'tv', s: ${i}, e: ${ep.episode_number}})">
                                        <span>S${i}E${ep.episode_number}: ${ep.name}</span>
                                    </div>
                                `).join('')}
                            </div>
                        `;
                        epList.appendChild(div);
                    });
                });
            }
        } else {
            document.getElementById('player-loading').innerHTML = '<div class="error">Failed to load player. Ensure Premium Plugin is active.</div>';
        }
    } catch (err) {
        console.error('Player error:', err);
        document.getElementById('player-loading').innerHTML = '<div class="error">An error occurred while loading the player.</div>';
    }
}

async function markAsWatched(id, type, s = 1, e = 1) {
    let progress = await getUserData('watch_progress');
    const index = progress.findIndex(item => item.id == id);
    if (index > -1) {
        progress[index].s = s;
        progress[index].e = e;
        progress[index].date = new Date().toISOString();
    } else {
        progress.push({ id, type, s, e, date: new Date().toISOString() });
    }
    await saveUserData('watch_progress', progress);
}
