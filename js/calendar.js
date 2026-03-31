/**
 * js/calendar.js - Upcoming Episodes View
 */

async function renderCalendar() {
    const container = document.getElementById('calendarView');
    container.innerHTML = '<h2>📅 Upcoming Episodes</h2><p class="loading">Fetching calendar from your watchlist...</p>';

    const watchlist = await getUserData('watchlist');
    const shows = watchlist.filter(item => item.type === 'tv');

    if (shows.length === 0) {
        container.innerHTML = '<h2>📅 Upcoming Episodes</h2><p class="empty-msg">Add TV shows to your watchlist to see upcoming episodes here.</p>';
        return;
    }

    container.innerHTML = '<h2>📅 Upcoming Episodes</h2><div class="calendar-grid" id="calGrid"></div>';
    const grid = document.getElementById('calGrid');

    const today = new Date();
    const next7Days = new Date();
    next7Days.setDate(today.getDate() + 7);

    for (const show of shows) {
        tmdbFetch(`/tv/${show.id}`).then(details => {
            if (details.next_episode_to_air) {
                const ep = details.next_episode_to_air;
                const airDate = new Date(ep.air_date);

                if (airDate >= today && airDate <= next7Days) {
                    const card = document.createElement('div');
                    card.className = 'calendar-card';
                    card.innerHTML = `
                        <div class="cal-header">
                            <strong>${details.name}</strong>
                            <span class="cal-date">${ep.air_date}</span>
                        </div>
                        <div class="cal-body">
                            <p>S${ep.season_number}E${ep.episode_number}: ${ep.name}</p>
                            <small>${ep.overview ? ep.overview.substring(0, 100) + '...' : 'No overview available.'}</small>
                        </div>
                    `;
                    card.onclick = () => showView('showDetails', { id: show.id });
                    grid.appendChild(card);
                }
            }
        });
    }
}
