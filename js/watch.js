/**
 * js/watch.js - Watch View Logic (Integrated with Premium Plugin)
 */

async function startWatch(id, type, s = 1, e = 1) {
    const container = document.getElementById('watchView');
    container.innerHTML = `
        <button class="btn btn-secondary" onclick="showView('${type === 'tv' ? 'showDetails' : 'movieDetails'}', {id: ${id}})">⬅️ Back to Details</button>
        <div class="watch-container">
            <h2>Now Watching ${type === 'tv' ? `(Season ${s} Episode ${e})` : ''}</h2>
            <div id="player-loading" class="loading">Loading secure player...</div>
            <div id="player-frame" class="video-container" style="display:none;"></div>
        </div>
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
            markAsWatched(id, type);
        } else {
            document.getElementById('player-loading').innerHTML = '<div class="error">Failed to load player. Ensure Premium Plugin is active.</div>';
        }
    } catch (err) {
        console.error('Player error:', err);
        document.getElementById('player-loading').innerHTML = '<div class="error">An error occurred while loading the player.</div>';
    }
}

async function markAsWatched(id, type) {
    let progress = await getUserData('watch_progress');
    const exists = progress.some(item => item.id === id);
    if (!exists) {
        progress.push({ id, type, date: new Date().toISOString(), status: 'watched' });
        await saveUserData('watch_progress', progress);
    }
}
