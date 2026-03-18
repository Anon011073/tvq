/**
 * js/main.js - Homepage Logic
 */

let currentPage = 1;
let currentGenre = '';
let currentSort = 'popularity.desc'; // Default = Popularity

// Restored + Updated renderShows function (horizontal sections)
function renderShows(shows, containerId) {
  const container = document.getElementById(containerId);
  if (!container) return;
  container.innerHTML = '';

  shows.slice(0, 24).forEach(show => {
    const div = document.createElement('div');
    div.className = 'card';
    div.innerHTML = `
      <img src="https://image.tmdb.org/t/p/w200${show.poster_path}" alt="${show.name}" />
      <h3>${show.name}</h3>
      <p>⭐ ${show.vote_average}</p>
    `;
    div.addEventListener('click', () => {
      window.location.href = `show.php?id=${show.id}`;
    });
    container.appendChild(div);
  });

  if (typeof addScrollArrows === 'function') {
    addScrollArrows(container);
  }
}

// Unified Grid Rendering
function renderGrid(shows, containerId) {
  const container = document.getElementById(containerId);
  if (!container) return;
  container.innerHTML = '';

  shows.forEach(show => {
    const div = document.createElement('div');
    div.className = 'card';
    div.innerHTML = `
      <img src="https://image.tmdb.org/t/p/w200${show.poster_path}" alt="${show.name}" onerror="this.src='https://placehold.co/200x300?text=No+Image'"/>
      <h3>${show.name}</h3>
      <p>⭐ ${show.vote_average}</p>
    `;
    div.addEventListener('click', () => {
      window.location.href = `show.php?id=${show.id}`;
    });
    container.appendChild(div);
  });
}

function loadMainGrid(page = 1, shouldScroll = true) {
  currentPage = page;

  let endpoint = `/discover/tv&page=${page}&sort_by=${currentSort}`;

  // If sorting by rating, require votes
  if (currentSort === 'vote_average.desc') {
    endpoint += `&vote_count.gte=200`;
  }

  // Language filter
  const englishOnly = document.getElementById('englishOnly');
  if (englishOnly && englishOnly.checked) {
      endpoint += '&with_original_language=en';
  }

  if (currentGenre) {
    endpoint += `&with_genres=${currentGenre}`;
  }

  // Country filter
  const countryOpts = document.querySelectorAll('.country-opt:checked');
  if (countryOpts.length > 0) {
      const countries = Array.from(countryOpts).map(opt => opt.value).join('|');
      endpoint += `&with_origin_country=${countries}`;
  }

  // Filters
  const maxAgeYears = document.getElementById('maxAgeYears');
  const maxShowAgeDays = document.getElementById('maxShowAgeDays');
  const today = new Date();

  if (maxAgeYears) {
      const year = parseInt(maxAgeYears.value);
      // Filter for shows released since the start of the selected decade
      const dateStr = `${year}-01-01`;
      endpoint += `&first_air_date.gte=${dateStr}`;
  }

  if (maxShowAgeDays && parseInt(maxShowAgeDays.value) > 0) {
      const days = parseInt(maxShowAgeDays.value);
      const date = new Date();
      date.setDate(today.getDate() - days);
      const dateStr = date.toISOString().split('T')[0];
      endpoint += `&air_date.gte=${dateStr}`;
  }

  fetch(`api/tmdb.php?endpoint=${endpoint}`)
    .then(res => res.json())
    .then(data => {
      renderGrid(data.results || [], 'mainGrid');
      updatePagination(data.page, data.total_pages);

      if (shouldScroll) {
          document.getElementById('mainContent').scrollIntoView({ behavior: 'smooth' });
      }
    })
    .catch(err => console.error('Error loading main grid:', err));
}

function updatePagination(current, total) {
    const info = document.getElementById('pageInfo');
    if (info) info.textContent = `Page ${current} of ${total}`;

    const prev = document.getElementById('prevPage');
    const next = document.getElementById('nextPage');
    if (prev) prev.disabled = current <= 1;
    if (next) next.disabled = current >= total;

    const numbers = document.getElementById('pageNumbers');
    if (!numbers) return;
    numbers.innerHTML = '';

    let start = Math.max(1, current - 2);
    let end = Math.min(total, current + 2);

    for (let i = start; i <= end; i++) {
        const btn = document.createElement('button');
        btn.className = `page-num ${i === current ? 'active' : ''}`;
        btn.textContent = i;
        btn.onclick = () => loadMainGrid(i);
        numbers.appendChild(btn);
    }
}

function searchShows() {
  const query = document.getElementById('searchInput').value.trim();
  const searchSection = document.getElementById('searchSection');
  const mainContent = document.getElementById('mainContent');

  if (!query) {
    searchSection.style.display = 'none';
    mainContent.style.display = 'block';
    return;
  }

  fetch(`api/tmdb.php?endpoint=/search/tv&query=${encodeURIComponent(query)}`)
    .then(res => res.json())
    .then(data => {
      searchSection.style.display = 'block';
      mainContent.style.display = 'none';
      renderShows(data.results || [], 'searchResults');
    })
    .catch(err => console.error('Search error:', err));
}

function renderMovies(movies, containerId) {
  const container = document.getElementById(containerId);
  if (!container) return;
  container.innerHTML = '';

  movies.slice(0, 12).forEach(movie => {
    const div = document.createElement('div');
    div.className = 'card';
    div.innerHTML = `
      <img src="https://image.tmdb.org/t/p/w200${movie.poster_path}" alt="${movie.title}" />
      <h3>${movie.title}</h3>
      <p>📅 ${movie.release_date || 'Unknown'}</p>
    `;
    div.addEventListener('click', () => {
      window.location.href = `movie.php?id=${movie.id}`;
    });
    container.appendChild(div);
  });

  if (typeof addScrollArrows === 'function') {
    addScrollArrows(container);
  }
}

document.addEventListener('DOMContentLoaded', () => {
  const mainGrid = document.getElementById('mainGrid');
  const sortBy = document.getElementById('sortBy');

  if (sortBy) {
    currentSort = sortBy.value; // Read dropdown value on first load

    sortBy.addEventListener('change', (e) => {
      currentSort = e.target.value;
      loadMainGrid(1, false);
    });
  }

  if (mainGrid) {
    loadMainGrid(1, false);
  }

  const englishOnly = document.getElementById('englishOnly');
  if (englishOnly) {
      englishOnly.addEventListener('change', () => {
          loadMainGrid(1, false);
      });
  }

  const countryOpts = document.querySelectorAll('.country-opt');
  countryOpts.forEach(opt => {
      opt.addEventListener('change', () => {
          loadMainGrid(1, false);
      });
  });

  // Sliders
  const maxAgeYears = document.getElementById('maxAgeYears');
  const maxAgeYearsValue = document.getElementById('maxAgeYearsValue');
  if (maxAgeYears) {
      maxAgeYears.addEventListener('input', (e) => {
          if (maxAgeYearsValue) maxAgeYearsValue.textContent = e.target.value;
      });
      maxAgeYears.addEventListener('change', () => {
          loadMainGrid(1, false);
      });
  }


  const maxShowAgeDays = document.getElementById('maxShowAgeDays');
  const maxShowAgeDaysValue = document.getElementById('maxShowAgeDaysValue');
  if (maxShowAgeDays) {
      maxShowAgeDays.addEventListener('input', (e) => {
          if (maxShowAgeDaysValue) maxShowAgeDaysValue.textContent = e.target.value;
      });
      maxShowAgeDays.addEventListener('change', () => {
          loadMainGrid(1, false);
      });
  }

  const genreItems = document.querySelectorAll('.genre-item');
  genreItems.forEach(item => {
    item.addEventListener('click', () => {
      genreItems.forEach(i => i.classList.remove('active'));
      item.classList.add('active');
      currentGenre = item.dataset.id;
      loadMainGrid(1, false);

      const gridTitle = document.getElementById('gridTitle');
      if (gridTitle) {
          gridTitle.textContent = currentGenre ? `${item.textContent} Shows` : 'Popular Shows';
      }
    });
  });

  const prevBtn = document.getElementById('prevPage');
  const nextBtn = document.getElementById('nextPage');

  if (prevBtn && mainGrid) {
    prevBtn.addEventListener('click', () => {
      if (currentPage > 1) loadMainGrid(currentPage - 1);
    });
  }

  if (nextBtn && mainGrid) {
    nextBtn.addEventListener('click', () => {
      loadMainGrid(currentPage + 1);
    });
  }

  const searchInput = document.getElementById('searchInput');
  if (searchInput) {
    searchInput.addEventListener('input', () => {
      clearTimeout(window.searchTimeout);
      window.searchTimeout = setTimeout(searchShows, 400);
    });
  }
});
/**
 * Backup & Restore Logic
 */

function exportData() {
    const userId = window.CURRENT_USER_ID || 'guest';
    const data = {};
    const prefix = `user_${userId}_`;

    for (let i = 0; i < localStorage.length; i++) {
        const key = localStorage.key(i);
        if (key.startsWith(prefix)) {
            data[key] = localStorage.getItem(key);
        }
    }

    if (Object.keys(data).length === 0) {
        alert("No data found to export.");
        return;
    }

    const blob = new Blob([JSON.stringify(data, null, 2)], { type: 'application/json' });
    const url = URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = `tv_tracker_backup_${userId}_${new Date().toISOString().split('T')[0]}.json`;
    a.click();
    URL.revokeObjectURL(url);
}

function importData() {
    const fileInput = document.getElementById('importFile');
    if (!fileInput || !fileInput.files.length) {
        alert("Please select a backup file first.");
        return;
    }

    const file = fileInput.files[0];
    const reader = new FileReader();

    reader.onload = (e) => {
        try {
            const data = JSON.parse(e.target.result);
            const userId = window.CURRENT_USER_ID || 'guest';
            const prefix = `user_${userId}_`;

            // Basic validation: check if keys match current user or are at least somewhat valid
            let count = 0;
            for (const key in data) {
                // If we want to allow cross-user import, we could strip the old prefix and add new
                // For now, let's just import them as is if they match the user prefix
                if (key.startsWith(prefix)) {
                    localStorage.setItem(key, data[key]);
                    count++;
                } else if (key.startsWith('user_')) {
                    // Remap to current user
                    const actualKey = key.split('_').slice(2).join('_');
                    localStorage.setItem(prefix + actualKey, data[key]);
                    count++;
                }
            }

            if (count > 0) {
                alert(`Successfully imported ${count} items. Page will now reload.`);
                location.reload();
            } else {
                alert("No valid data found in the file.");
            }
        } catch (err) {
            console.error("Import error:", err);
            alert("Failed to parse the backup file. Ensure it is a valid JSON.");
        }
    };

    reader.readAsText(file);
}
