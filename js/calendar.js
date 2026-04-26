/**
 * js/calendar.js - Upcoming Episodes View
 */

async function renderCalendar() {
    const container = document.getElementById('calendarView');
    container.innerHTML = '<h2>📅 TV Episode Calendar</h2><p class="loading">Fetching schedule for your watchlist...</p>';

    const watchlist = await getUserData('watchlist');
    const shows = watchlist.filter(item => item.type === 'tv');

    if (shows.length === 0) {
        container.innerHTML = '<h2>📅 TV Episode Calendar</h2><p class="empty-msg">Add TV shows to your watchlist to see your personalized calendar.</p>';
        return;
    }

    // Prepare Calendar Grid
    const today = new Date();
    today.setHours(0,0,0,0);

    const days = [];
    const dayNames = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];

    container.innerHTML = `
        <h2>📅 7-Day TV Calendar</h2>
        <div class="calendar-grid-view">
            ${Array.from({length: 7}).map((_, i) => {
                const d = new Date(today);
                d.setDate(today.getDate() + i);
                const dateStr = d.toISOString().split('T')[0];
                days.push(dateStr);
                return `
                    <div class="calendar-column">
                        <div class="calendar-header">${dayNames[d.getDay()]}<br><small>${d.getDate()}</small></div>
                        <div id="day-${dateStr}" class="calendar-day"></div>
                    </div>
                `;
            }).join('')}
        </div>
    `;

    // Fetch and Populate
    shows.forEach(show => {
        tmdbFetch(`/tv/${show.id}`).then(details => {
            if (details.next_episode_to_air) {
                const ep = details.next_episode_to_air;
                const airDate = ep.air_date;
                const dayEl = document.getElementById(`day-${airDate}`);
                if (dayEl) {
                    dayEl.classList.add('has-ep');
                    const item = document.createElement('div');
                    item.className = 'cal-item';
                    item.style.cursor = 'pointer';
                    item.style.background = '#ff5e57';
                    item.style.color = '#fff';
                    item.style.padding = '5px';
                    item.style.borderRadius = '4px';
                    item.style.marginBottom = '5px';
                    item.style.fontSize = '0.75rem';
                    item.innerHTML = `<strong>${details.name}</strong><br>S${ep.season_number}E${ep.episode_number}`;
                    item.onclick = () => showView('showDetails', {id: details.id});
                    dayEl.appendChild(item);
                }
            }
        });
    });
}
