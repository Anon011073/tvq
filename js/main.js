/**
 * js/main.js - Central Router and Discovery Logic
 */

let currentPage = 1;
let currentGenre = '';
let currentSort = 'popularity.desc';
let currentMediaType = 'tv'; // 'tv' or 'movie'

// --- View Router ---

function showView(viewId, params = {}) {
    const sections = ['searchSection', 'mainContent', 'showDetails', 'movieDetails', 'calendarView', 'favouritesView', 'watchlistView', 'watchView', 'trendingSection'];
    sections.forEach(s => {
        const el = document.getElementById(s);
        if (el) el.style.display = 'none';
    });

    const target = document.getElementById(viewId);
    if (target) target.style.display = 'block';

    const filters = document.getElementById('tvq-filters');
    if (filters) {
        filters.style.display = (viewId === 'mainContent' || viewId === 'searchSection') ? 'flex' : 'none';
    }

    // Handle view-specific initialization
    if (viewId === 'mainContent') {
        if (window.tvq_settings.trending_enabled) {
            loadTrending();
        } else {
            const trend = document.getElementById('trendingSection');
            if (trend) trend.style.display = 'none';
        }
        loadMainGrid(currentPage, false);
    } else if (viewId === 'showDetails' && params.id) {
        if (typeof fetchShowDetails === 'function') fetchShowDetails(params.id);
    } else if (viewId === 'movieDetails' && params.id) {
        if (typeof fetchMovieDetails === 'function') fetchMovieDetails(params.id);
    } else if (viewId === 'calendarView') {
        if (typeof renderCalendar === 'function') renderCalendar();
    } else if (viewId === 'favouritesView') {
        if (typeof renderFavourites === 'function') renderFavourites();
    } else if (viewId === 'watchlistView') {
        if (typeof renderWatchlist === 'function') renderWatchlist();
    } else if (viewId === 'watchView' && params.id) {
        if (typeof startWatch === 'function') startWatch(params.id, params.type);
    }
}

// --- Discovery Logic ---

function renderGrid(items, containerId) {
    const container = document.getElementById(containerId);
    if (!container) return;
    container.innerHTML = '';

    items.forEach(item => {
        const div = document.createElement('div');
        div.className = 'show-card'; // Consistent class name
        const title = item.name || item.title;
        const date = item.first_air_date || item.release_date || '';
        const poster = item.poster_path ? `https://image.tmdb.org/t/p/w200${item.poster_path}` : 'https://placehold.co/200x300?text=No+Image';

        div.innerHTML = `
            <div class="card-inner">
                <img src="${poster}" alt="${title}" loading="lazy" />
                <div class="card-info">
                    <h3>${title}</h3>
                    <p>⭐ ${item.vote_average || 'N/A'}</p>
                    <small>${date.split('-')[0]}</small>
                </div>
            </div>
        `;
        div.addEventListener('click', () => {
            if (currentMediaType === 'tv') {
                showView('showDetails', { id: item.id });
            } else {
                showView('movieDetails', { id: item.id });
            }
        });
        container.appendChild(div);
    });
}

function loadMainGrid(page = 1, shouldScroll = true) {
    currentPage = page;
    const endpoint = currentMediaType === 'tv' ? '/discover/tv' : '/discover/movie';

    let params = {
        page: page,
        sort_by: currentSort
    };

    if (currentSort === 'vote_average.desc') {
        params['vote_count.gte'] = 200;
    }

    const englishOnly = document.getElementById('englishOnly');
    if (englishOnly && englishOnly.checked) {
        params['with_original_language'] = 'en';
    } else {
        params['with_original_language'] = tvq_settings.default_lang || '';
    }

    if (currentGenre) {
        params['with_genres'] = currentGenre;
    }

    const countrySelect = document.getElementById('countrySelect');
    if (countrySelect && countrySelect.value) {
        params['with_origin_country'] = countrySelect.value;
    } else if (window.tvq_settings.default_country) {
        params['with_origin_country'] = window.tvq_settings.default_country;
    }

    tmdbFetch(endpoint, params)
        .then(data => {
            renderGrid(data.results || [], 'mainGrid');
            updatePagination(data.page, data.total_pages);
            if (shouldScroll) {
                document.getElementById('mainContent').scrollIntoView({ behavior: 'smooth' });
            }
        })
        .catch(err => console.error('Error loading main grid:', err));
}

function loadTrending() {
    const trendSection = document.getElementById('trendingSection');
    const trendGrid = document.getElementById('trendingGrid');
    const trendTitle = document.getElementById('trendingTitle');

    if (!trendSection || !trendGrid) return;

    let endpoint = '';
    const type = window.tvq_settings.trending_type || 'trending';
    const media = currentMediaType; // 'tv' or 'movie'

    if (type === 'trending') {
        endpoint = `/trending/${media}/day`;
        trendTitle.textContent = `Trending ${media === 'tv' ? 'Shows' : 'Movies'}`;
    } else if (type === 'top_rated') {
        endpoint = `/${media}/top_rated`;
        trendTitle.textContent = `Top Rated ${media === 'tv' ? 'Shows' : 'Movies'}`;
    } else {
        endpoint = media === 'tv' ? '/tv/on_the_air' : '/movie/now_playing';
        trendTitle.textContent = media === 'tv' ? 'On The Air' : 'Now Playing';
    }

    tmdbFetch(endpoint).then(data => {
        trendSection.style.display = 'block';
        renderGrid(data.results || [], 'trendingGrid');
    }).catch(err => console.error('Trending error:', err));
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

function searchMedia() {
    const query = document.getElementById('searchInput').value.trim();
    const searchSection = document.getElementById('searchSection');
    const mainContent = document.getElementById('mainContent');

    if (!query) {
        searchSection.style.display = 'none';
        mainContent.style.display = 'block';
        return;
    }

    const endpoint = currentMediaType === 'tv' ? '/search/tv' : '/search/movie';

    tmdbFetch(endpoint, { query })
        .then(data => {
            searchSection.style.display = 'block';
            mainContent.style.display = 'none';
            renderGrid(data.results || [], 'searchResults');
        })
        .catch(err => console.error('Search error:', err));
}

// --- Initialization ---

document.addEventListener('DOMContentLoaded', () => {
    // Apply grid column setting
    if (window.tvq_settings.grid_cols) {
        document.documentElement.style.setProperty('--tvq-grid-cols', window.tvq_settings.grid_cols);
    }

    // Apply Admin Defaults
    if (window.tvq_settings.default_sort) {
        currentSort = window.tvq_settings.default_sort;
        const sortEl = document.getElementById('sortBy');
        if (sortEl) sortEl.value = currentSort;
    }

    const engEl = document.getElementById('englishOnly');
    if (engEl) engEl.checked = window.tvq_settings.english_only;

    const countryEl = document.getElementById('countrySelect');
    if (countryEl) countryEl.value = window.tvq_settings.default_country || '';
    // Navigation Links
    document.querySelectorAll('.tvq-nav a[data-view]').forEach(link => {
        link.addEventListener('click', (e) => {
            e.preventDefault();
            const view = e.target.closest('a').dataset.view;

            if (view === 'movies') {
                currentMediaType = 'movie';
                currentGenre = '';
                document.getElementById('gridTitle').textContent = 'Popular Movies';
                loadMainGrid(1, true);
                showView('mainContent');
            } else if (view === 'home') {
                currentMediaType = 'tv';
                currentGenre = '';
                document.getElementById('gridTitle').textContent = 'Popular Shows';
                loadMainGrid(1, true);
                showView('mainContent');
            } else if (view === 'profile') {
                window.location.href = window.tvq_settings.profile_url;
            } else {
                showView(view + 'View');
            }
        });
    });

    // Filters
    const sortBy = document.getElementById('sortBy');
    if (sortBy) {
        sortBy.addEventListener('change', (e) => {
            currentSort = e.target.value;
            loadMainGrid(1, false);
        });
    }

    const englishOnly = document.getElementById('englishOnly');
    if (englishOnly) {
        englishOnly.addEventListener('change', () => loadMainGrid(1, false));
    }

    const countrySelect = document.getElementById('countrySelect');
    if (countrySelect) {
        countrySelect.addEventListener('change', () => loadMainGrid(1, false));
    }

    const genreSelect = document.getElementById('genreSelect');
    if (genreSelect) {
        genreSelect.addEventListener('change', (e) => {
            currentGenre = e.target.value;
            loadMainGrid(1, false);

            const gridTitle = document.getElementById('gridTitle');
            if (gridTitle) {
                const typeLabel = currentMediaType === 'tv' ? 'Shows' : 'Movies';
                const genreLabel = e.target.options[e.target.selectedIndex].text;
                gridTitle.textContent = currentGenre ? `${genreLabel} ${typeLabel}` : `Popular ${typeLabel}`;
            }
        });
    }

    // Pagination
    const prevBtn = document.getElementById('prevPage');
    const nextBtn = document.getElementById('nextPage');
    if (prevBtn) prevBtn.addEventListener('click', () => { if (currentPage > 1) loadMainGrid(currentPage - 1); });
    if (nextBtn) nextBtn.addEventListener('click', () => loadMainGrid(currentPage + 1));

    // Search
    const searchInput = document.getElementById('searchInput');
    if (searchInput) {
        searchInput.addEventListener('input', () => {
            clearTimeout(window.searchTimeout);
            window.searchTimeout = setTimeout(searchMedia, 400);
        });
    }

    // Initial Load
    if (tvq_settings.default_view === 'movies') {
        currentMediaType = 'movie';
        document.getElementById('gridTitle').textContent = 'Popular Movies';
    }
    showView('mainContent');
});
