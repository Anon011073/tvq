/**
 * js/watch.js - Watch View Logic
 */

function startWatch(id, type) {
    const container = document.getElementById('watchView');
    container.innerHTML = `
        <button class="btn btn-secondary" onclick="showView('${type === 'tv' ? 'showDetails' : 'movieDetails'}', {id: ${id}})">⬅️ Back to Details</button>
        <div class="watch-container">
            <h2>Now Watching</h2>
            <div class="mock-player">
                <p>🎬 [PREMIUM PLAYER MOCK]</p>
                <p>Streaming Content ID: ${id} (${type})</p>
                <div class="player-controls">
                     [ Play | Pause | Stop ]
                </div>
            </div>
        </div>
    `;
}
