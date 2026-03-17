document.addEventListener('DOMContentLoaded', () => {
  loadCalendar();
});

function loadCalendar() {
  const favs = JSON.parse(localStorage.getItem(getUserKey('favs')) || '[]');
  const container = document.getElementById('calendarContainer');
  container.innerHTML = '<p>📡 Loading upcoming episodes...</p>';

  const promises = favs.map(show =>
    fetch(`api/tmdb.php?endpoint=/tv/${show.id}`)
      .then(res => res.json())
      .then(data => {

        const today = new Date();
        const threeDaysAgo = new Date();
        threeDaysAgo.setDate(today.getDate() - 3);

        const results = [];

        // check last aired episode
        if (data.last_episode_to_air) {
          const last = data.last_episode_to_air;
          const airDate = new Date(last.air_date);

          if (airDate >= threeDaysAgo && airDate <= today) {
            results.push({
              name: show.name,
              episode: `S${last.season_number}E${last.episode_number} - ${last.name}`,
              air_date: last.air_date,
              showId: show.id
            });
          }
        }

        // next episode
        if (data.next_episode_to_air) {
          const next = data.next_episode_to_air;

          results.push({
            name: show.name,
            episode: `S${next.season_number}E${next.episode_number} - ${next.name}`,
            air_date: next.air_date,
            showId: show.id
          });
        }

        return results;
      })
  );

  Promise.all(promises).then(episodes => {
    const filtered = episodes.flat().filter(e => e);
    renderCalendar(filtered);
  });
}

function renderCalendar(episodes) {
  const container = document.getElementById('calendarContainer');
  if (!episodes.length) {
    container.innerHTML = '<p>No upcoming episodes found for your tracked shows.</p>';
    return;
  }

  const eventsByDate = {};
  episodes.forEach(ep => {
    if (!eventsByDate[ep.air_date]) eventsByDate[ep.air_date] = [];
    eventsByDate[ep.air_date].push(ep);
  });

  const today = new Date();
  const year = today.getFullYear();
  const month = today.getMonth();

  const firstDay = new Date(year, month, 1);
  const lastDay = new Date(year, month + 1, 0);
  const numDays = lastDay.getDate();
  const startDay = (firstDay.getDay() + 6) % 7;

  const monthName = firstDay.toLocaleString('default', { month: 'long' });

  let html = `<h2>${monthName} ${year}</h2>`;
  html += '<div class="calendar-grid-view">';

  const weekdays = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];
  weekdays.forEach(day => {
    html += `<div class="calendar-header">${day}</div>`;
  });

  for (let i = 0; i < startDay; i++) {
    html += `<div class="calendar-day empty"></div>`;
  }

  for (let d = 1; d <= numDays; d++) {
    const dateStr = `${year}-${String(month + 1).padStart(2, '0')}-${String(d).padStart(2, '0')}`;
    const eps = eventsByDate[dateStr] || [];

    let inner = `<strong>${d}</strong>`;
    eps.forEach(ep => {
      inner += `
        <div class="ep-tile">
          <a href="show.php?id=${ep.showId}">
            ${ep.name}<br><small>${ep.episode}</small>
          </a>
        </div>
      `;
    });

    html += `<div class="calendar-day ${eps.length ? 'has-ep' : ''}">${inner}</div>`;
  }

  const totalCells = startDay + numDays;
  const remaining = totalCells % 7 === 0 ? 0 : 7 - (totalCells % 7);
  for (let i = 0; i < remaining; i++) {
    html += `<div class="calendar-day empty"></div>`;
  }

  html += '</div>';
  container.innerHTML = html;
}