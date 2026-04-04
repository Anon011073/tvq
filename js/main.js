/**
 * js/main.js - Homepage Logic
 */

let currentPage = 1;
let currentGenre = '';
let currentSort = 'popularity.desc'; // Default = Popularity

// Unified Grid Rendering
function renderGrid(shows, containerId) {
  const container = document.getElementById(containerId);
  if (!container) return;
  container.innerHTML = '';

  if (!shows || shows.length === 0) {
      container.innerHTML = '<p style="padding: 20px;">No shows found matching your criteria.</p>';
      return;
  }

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

  let params = new URLSearchParams();
  params.append('page', page);
  params.append('sort_by', currentSort);

  // If sorting by rating, require votes
  if (currentSort === 'vote_average.desc') {
    params.append('vote_count.gte', '200');
  }

  // Language filter
  const englishOnly = document.getElementById('englishOnly');
  if (englishOnly && englishOnly.checked) {
      params.append('with_original_language', 'en');
  }

  if (currentGenre) {
    params.append('with_genres', currentGenre);
  }

  // Country filter
  const countryOpts = document.querySelectorAll('.country-opt:checked');
  if (countryOpts.length > 0) {
      const countries = Array.from(countryOpts).map(opt => opt.value).join('|');
      params.append('with_origin_country', countries);
  }

  // Filters
  const maxAgeYears = document.getElementById('maxAgeYears');
  const maxShowAgeDays = document.getElementById('maxShowAgeDays');
  const today = new Date();

  if (maxAgeYears) {
      const year = parseInt(maxAgeYears.value);
      // Filter for shows released since the start of the selected decade
      const dateStr = `${year}-01-01`;
      params.append('first_air_date.gte', dateStr);
  }

  if (maxShowAgeDays && parseInt(maxShowAgeDays.value) > 0) {
      const days = parseInt(maxShowAgeDays.value);
      const date = new Date();
      date.setDate(today.getDate() - days);
      const dateStr = date.toISOString().split('T')[0];
      params.append('air_date.gte', dateStr);
  }

  const endpoint = `/discover/tv?${params.toString()}`;

  fetch(`api/tmdb.php?endpoint=${encodeURIComponent(endpoint)}`)
    .then(res => res.json())
    .then(data => {
      renderGrid(data.results || [], 'mainGrid');
      updatePagination(data.page || 1, data.total_pages || 1);

      if (shouldScroll) {
          const mainContent = document.getElementById('mainContent');
          if (mainContent) mainContent.scrollIntoView({ behavior: 'smooth' });
      }
    })
    .catch(err => {
        console.error('Error loading main grid:', err);
        const grid = document.getElementById('mainGrid');
        if (grid) grid.innerHTML = '<p>Error loading data. Please check your API key in the profile settings.</p>';
    });
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
    if (searchSection) searchSection.style.display = 'none';
    if (mainContent) mainContent.style.display = 'block';
    return;
  }

  fetch(`api/tmdb.php?endpoint=${encodeURIComponent('/search/tv?query=' + encodeURIComponent(query))}`)
    .then(res => res.json())
    .then(data => {
      if (searchSection) searchSection.style.display = 'block';
      if (mainContent) mainContent.style.display = 'none';
      renderGrid(data.results || [], 'searchResults');
    })
    .catch(err => console.error('Search error:', err));
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

            let count = 0;
            for (const key in data) {
                if (key.startsWith(prefix)) {
                    localStorage.setItem(key, data[key]);
                    count++;
                } else if (key.startsWith('user_')) {
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
