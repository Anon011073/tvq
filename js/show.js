// File: js/show.js
const params = new URLSearchParams(window.location.search);
const showId = params.get('id');

document.addEventListener('DOMContentLoaded', () => {
  fetchShowDetails(showId);
});

function fetchShowDetails(id) {
  fetch(`api/tmdb.php?endpoint=/tv/${id}`)
    .then(res => res.json())
    .then(show => {
      renderShowDetails(show);
      loadCast(id);
      loadReviews(id);
      loadRecommendations(id);
    });
}

function renderShowDetails(show) {
  const container = document.getElementById('showDetails');
  const caughtUpDate = getCaughtUp(show.id) || 'Not set';

  container.innerHTML = `
    <section class="hero">
      <img src="https://image.tmdb.org/t/p/w300${show.poster_path}" alt="${show.name}" class="poster" />
      <div class="hero-text">
        <h1>${show.name}</h1>
        <p>${show.overview}</p>
        <p><strong>Status:</strong> ${show.status}</p>
        <p><strong>Seasons:</strong> ${show.number_of_seasons}</p>

        <div class="btn-group">
          <button id="favBtn" class="btn">❤️ Loading...</button>
          <button id="caughtUpBtn" class="btn btn-secondary">✅ Mark as Caught Up</button>
          <button id="resetBtn" class="btn btn-danger">🗑️ Reset Progress</button>
          <a class="btn btn-primary" href="watch.php?id=${show.id}" target="_blank">▶️ Watch Now</a>
        </div>
        <p><em>Last watched air date: ${caughtUpDate}</em></p>
      </div>
    </section>

    <section id="cast"></section>
    <section id="reviews"></section>
    <section id="recommendations"></section>
    <section id="episodes"></section>
  `;

  const favBtn = document.getElementById('favBtn');
  const isFav = (JSON.parse(localStorage.getItem(getUserKey('favs')) || '[]')).some(f => f.id === show.id);
  favBtn.textContent = isFav ? '❌ Remove from Favourites' : '❤️ Add to Favourites';
  favBtn.addEventListener('click', () => toggleFavourite(show.id, show.name));
  document.getElementById('caughtUpBtn').addEventListener('click', () => markCaughtUp(show.id));
  document.getElementById('resetBtn').addEventListener('click', () => resetProgress(show.id));

  for (let season = 1; season <= show.number_of_seasons; season++) {
    fetch(`api/tmdb.php?endpoint=/tv/${show.id}/season/${season}`)
      .then(res => res.json())
      .then(seasonData => renderEpisodes(show.id, season, seasonData.episodes));
  }
}


function loadCast(id) {
  fetch(`api/tmdb.php?endpoint=/tv/${id}/credits`)
    .then(res => res.json())
    .then(data => {
      const castDiv = document.getElementById('cast');
      castDiv.innerHTML = '<h3>🎭 Cast</h3>' +
        '<div class="cast-list">' +
        data.cast.slice(0, 6).map(c =>
          `<div class="cast-card">${c.name}<br><small>as ${c.character}</small></div>`
        ).join('') + '</div>';
    });
}

function loadReviews(id) {
  fetch(`api/tmdb.php?endpoint=/tv/${id}/reviews`)
    .then(res => res.json())
    .then(data => {
      const reviewDiv = document.getElementById('reviews');
      if (data.results.length > 0) {
        reviewDiv.innerHTML = '<h3>📝 Reviews</h3>' +
          data.results.slice(0, 3).map(r =>
            `<div class="review">
              <strong>${r.author}</strong><p>${r.content.substring(0, 200)}...</p>
            </div>`
          ).join('');
      }
    });
}

function loadRecommendations(id) {
  fetch(`api/tmdb.php?endpoint=/tv/${id}/recommendations`)
    .then(res => res.json())
    .then(data => {
      const recDiv = document.getElementById('recommendations');
      if (data.results.length > 0) {
        recDiv.innerHTML = '<h3>🔁 Recommendations</h3>' +
          '<div class="recommendation-grid">' +
          data.results.slice(0, 6).map(show => `
            <div class="rec-card" onclick="window.location.href='show.php?id=${show.id}'">
              <img src="https://image.tmdb.org/t/p/w154${show.poster_path}" alt="${show.name}" />
              <h4>${show.name}</h4>
            </div>
          `).join('') + '</div>';
      }
    });
}

function renderEpisodes(showId, seasonNumber, episodes) {
  const container = document.getElementById('episodes');
  const progress = getWatchProgress(showId);
  const seasonId = `season-${showId}-${seasonNumber}`;

  let html = `
    <div class="season-block">
      <h3 onclick="toggleSeason('${seasonId}')">📂 Season ${seasonNumber} (click to expand)</h3>
      <div id="${seasonId}" class="season-body" style="display:none;">
  `;

  episodes.forEach((ep, idx) => {
    const watched = progress[seasonNumber]?.[idx] ?? false;
    html += `
      <div class="episode-row">
        <input type="checkbox" id="ep-${seasonNumber}-${idx}" ${watched ? 'checked' : ''}
          onchange="markEpisode(${showId}, ${seasonNumber}, ${idx}, this.checked)">
        <label for="ep-${seasonNumber}-${idx}">S${seasonNumber}E${ep.episode_number}: ${ep.name}</label>
      </div>
    `;
  });

  html += `</div></div>`;
  container.innerHTML += html;
}

function toggleFavourite(id, name) {
  let favs = JSON.parse(localStorage.getItem(getUserKey('favs')) || '[]');
  const isFav = favs.some(s => s.id === id);
  const btn = document.getElementById('favBtn');

  if (isFav) {
    favs = favs.filter(s => s.id !== id);
    alert(`Removed ${name} from favourites`);
    if (btn) btn.textContent = '❤️ Add to Favourites';
  } else {
    favs.push({ id, name });
    alert(`Added ${name} to favourites`);
    if (btn) btn.textContent = '❌ Remove from Favourites';
  }

  localStorage.setItem(getUserKey('favs'), JSON.stringify(favs));
}

function markCaughtUp(showId) {
  fetch(`api/tmdb.php?endpoint=/tv/${showId}`)
    .then(res => res.json())
    .then(show => {
      const latestDate = show.last_episode_to_air?.air_date;
      if (!latestDate) return alert('Could not determine last air date.');

      const totalSeasons = show.number_of_seasons;
      const promises = [];

      for (let s = 1; s <= totalSeasons; s++) {
        promises.push(fetch(`api/tmdb.php?endpoint=/tv/${showId}/season/${s}`).then(r => r.json()));
      }

      Promise.all(promises).then(seasons => {
        const all = JSON.parse(localStorage.getItem(getUserKey('watchProgress')) || '{}');
        all[showId] = all[showId] || {};

        seasons.forEach(season => {
          const seasonNum = season.season_number;
          all[showId][seasonNum] = season.episodes.map(() => true);
        });

        localStorage.setItem(getUserKey('watchProgress'), JSON.stringify(all));

        const caughtUp = JSON.parse(localStorage.getItem(getUserKey('caughtUp')) || '{}');
        caughtUp[showId] = latestDate;
        localStorage.setItem(getUserKey('caughtUp'), JSON.stringify(caughtUp));

        alert(`Marked all episodes watched and caught up to ${latestDate}`);
        location.reload();
      });
    });
}

function resetProgress(showId) {
  const all = JSON.parse(localStorage.getItem(getUserKey('watchProgress')) || '{}');
  delete all[showId];
  localStorage.setItem(getUserKey('watchProgress'), JSON.stringify(all));

  const caughtUp = JSON.parse(localStorage.getItem(getUserKey('caughtUp')) || '{}');
  delete caughtUp[showId];
  localStorage.setItem(getUserKey('caughtUp'), JSON.stringify(caughtUp));

  alert('Watch progress has been reset for this show.');
  location.reload();
}

function getCaughtUp(showId) {
  const all = JSON.parse(localStorage.getItem(getUserKey('caughtUp')) || '{}');
  return all[showId];
}

function getWatchProgress(showId) {
  const all = JSON.parse(localStorage.getItem(getUserKey('watchProgress')) || '{}');
  return all[showId] || {};
}

function markEpisode(showId, season, index, checked) {
  const all = JSON.parse(localStorage.getItem(getUserKey('watchProgress')) || '{}');
  all[showId] = all[showId] || {};
  all[showId][season] = all[showId][season] || [];
  all[showId][season][index] = checked;
  localStorage.setItem(getUserKey('watchProgress'), JSON.stringify(all));
}

function toggleSeason(id) {
  const el = document.getElementById(id);
  el.style.display = el.style.display === 'block' ? 'none' : 'block';
}
