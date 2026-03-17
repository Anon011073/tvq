<?php require_once 'auth_check.php'; ?><!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>TV Tracker - Movies</title>
  <link rel="stylesheet" href="css/style.css" />
  <script>
    window.CURRENT_USER_ID = <?php echo json_encode($_SESSION['user_id']); ?>;
  </script>
</head>
<body id="top">
  <nav class="top-nav">
    <div class="nav-brand">
      <h1>📺 TV Tracker</h1>
    </div>
    <div class="nav-links">
      <a href="index.php">🏠 Home</a>
      <a href="calendar.php">📅 Calendar</a>
      <a href="favourites.php">⭐ Favourites</a>
      <a href="watchlist.php">📋 Watchlist</a>
      <a href="movies.php" class="active">🎬 Movies</a>
      <a href="profile.php">👤 Profile</a>
      <a href="logout.php">🚪 Logout</a>
      <button id="themeToggle" class="theme-toggle">🌙 Toggle Theme</button>
    </div>
  </nav>

  <main class="content-area">
    <header class="content-header">
      <input type="text" id="searchInput" placeholder="Search movies..." />
    </header>
   
<section id="searchSection" style="display: none;">
  <h2>🔍 Search Results</h2>
  <div id="searchResults" class="show-grid"></div>
</section>

<section>
  <h2>📈 Trending Today</h2>
  <div id="trending" class="show-grid"></div>
</section>
    <section>
      <h2>🔥 Popular Movies</h2>
      <div id="popular" class="show-grid"></div>
    </section>
    <section>
      <h2>🏆 Top Rated Movies</h2>
      <div id="topRated" class="show-grid"></div>
    </section>
  </main>
  <script src="js/utils.js"></script>
  <script src="js/theme.js"></script>
  <script>
    // Specific JS for Movies page if needed or just use main.js logic adapted
    document.addEventListener('DOMContentLoaded', () => {
        loadMovieData('/movie/popular', 'popular');
        loadMovieData('/movie/top_rated', 'topRated');
        loadMovieData('/trending/movie/day', 'trending');

        const searchInput = document.getElementById('searchInput');
        if (searchInput) {
          searchInput.addEventListener('input', () => {
            clearTimeout(window.searchTimeout);
            window.searchTimeout = setTimeout(searchMoviesOnly, 400);
          });
        }
    });

    function loadMovieData(endpoint, containerId) {
      fetch(`api/tmdb.php?endpoint=${endpoint}`)
        .then(res => res.json())
        .then(data => renderMovies(data.results, containerId))
        .catch(err => console.error('Error loading movies:', err));
    }

    function searchMoviesOnly() {
      const query = document.getElementById('searchInput').value.trim();
      const searchSection = document.getElementById('searchSection');

      if (!query) {
        searchSection.style.display = 'none';
        return;
      }

      fetch(`api/tmdb.php?endpoint=/search/movie&query=${encodeURIComponent(query)}`)
        .then(res => res.json())
        .then(data => {
            searchSection.style.display = 'block';
            renderMovies(data.results || [], 'searchResults');
        })
        .catch(err => console.error('Search error:', err));
    }

    function renderMovies(movies, containerId) {
      const container = document.getElementById(containerId);
      if (!container) return;
      container.innerHTML = '';

      movies.slice(0, 20).forEach(movie => {
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
  </script>
  <script src="js/main.js"></script>
<footer class="site-footer">
  <p>© 2025 TV Tracker — Built with ❤️ for your watchlist.</p>
  <p>
    <a href="calendar.php">📅 Episode Calendar</a> |
    <a href="index.php">🏠 Home</a>
  </p>
</footer>
<a href="#top" class="back-to-top">Back to Top</a>
</body>
</html>
