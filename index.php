<?php require_once 'auth_check.php'; ?><!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>TV Tracker - Home</title>
 
  <link rel="stylesheet" href="css/style.css?v=2">
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
      <a href="index.php" class="active">🏠 Home</a>
      <a href="calendar.php">📅 Calendar</a>
      <a href="favourites.php">⭐ Favourites</a>
      <a href="watchlist.php">📋 Watchlist</a>
      <a href="movies.php">🎬 Movies</a>
      <a href="profile.php">👤 Profile</a>
      <a href="logout.php">🚪 Logout</a>
      <button id="themeToggle" class="theme-toggle">🌙 Toggle Theme</button>
    </div>
  </nav>

  <div class="main-layout">
    <aside class="sidebar">
      <div class="sidebar-section">
      <h3>Sort By</h3>
      <select id="sortBy" class="sidebar-select">
        <option value="popularity.desc">Popularity</option>
        <option value="first_air_date.desc">Latest Aired</option>
      </select>
    </div>

    <div class="sidebar-section">
      <h3>Options</h3>
      <div style="margin-bottom: 10px;">
        <input type="checkbox" id="englishOnly" checked>
        <label for="englishOnly" style="cursor: pointer; font-size: 0.9rem; color: #a0a0a0;">Only English Language</label>
      </div>
    </div>

    <div class="sidebar-section">
      <h3>Countries</h3>
      <div class="country-filters" style="display: flex; flex-direction: column; gap: 8px;">
        <div style="display: flex; align-items: center; gap: 8px;">
          <input type="checkbox" class="country-opt" id="countryUS" value="US">
          <label for="countryUS" style="cursor: pointer; font-size: 0.9rem; color: #a0a0a0;">🇺🇸 USA</label>
        </div>
        <div style="display: flex; align-items: center; gap: 8px;">
          <input type="checkbox" class="country-opt" id="countryUK" value="GB">
          <label for="countryUK" style="cursor: pointer; font-size: 0.9rem; color: #a0a0a0;">🇬🇧 UK</label>
        </div>
        <div style="display: flex; align-items: center; gap: 8px;">
          <input type="checkbox" class="country-opt" id="countryCA" value="CA">
          <label for="countryCA" style="cursor: pointer; font-size: 0.9rem; color: #a0a0a0;">🇨🇦 Canada</label>
        </div>
      </div>
    </div>

    <div class="sidebar-section">
      <h3>Genres</h3>
      <div class="genre-list" id="genreFilters">
        <div class="genre-item active" data-id="">All Genres</div>
        <div class="genre-item" data-id="10759">Action</div>
        <div class="genre-item" data-id="10759">Adventure</div>
        <div class="genre-item" data-id="16">Animation</div>
        <div class="genre-item" data-id="35">Comedy</div>
        <div class="genre-item" data-id="80">Crime</div>
        <div class="genre-item" data-id="99">Documentary</div>
        <div class="genre-item" data-id="18">Drama</div>
        <div class="genre-item" data-id="10751">Family</div>
        <div class="genre-item" data-id="10765">Fantasy</div>
        <div class="genre-item" data-id="36">History</div>
        <div class="genre-item" data-id="27">Horror</div>
        <div class="genre-item" data-id="10402">Music</div>
        <div class="genre-item" data-id="9648">Mystery</div>
        <div class="genre-item" data-id="10749">Romance</div>
        <div class="genre-item" data-id="10765">Sci-Fi</div>
        <div class="genre-item" data-id="10770">TV Movie</div>
        <div class="genre-item" data-id="53">Thriller</div>
        <div class="genre-item" data-id="10768">War</div>
        <div class="genre-item" data-id="37">Western</div>
      </div>
    </div>
  </aside>

  <main class="content-area">
    <header class="content-header">
      <input type="text" id="searchInput" placeholder="Search shows..." />
    </header>

    <section id="searchSection" style="display: none;">
      <h2>🔍 Search Results</h2>
      <div id="searchResults" class="show-grid"></div>
    </section>

    <section id="mainContent">
      <h2 id="gridTitle">Popular Shows</h2>
      <div id="mainGrid" class="main-grid"></div>
      <div class="pagination">
        <button id="prevPage" class="btn" disabled>Previous</button>
        <div class="page-numbers" id="pageNumbers"></div>
        <button id="nextPage" class="btn">Next</button>
        <span id="pageInfo">Page 1</span>
      </div>
    </section>

    <section class="backup-restore">
      <h3>🧩 Backup & Restore</h3>
      <div class="backup-btns">
        <button onclick="exportData()" class="btn btn-secondary">⬇️ Export</button>
        <input type="file" id="importFile" accept=".json" />
        <button onclick="importData()" class="btn btn-secondary">⬆️ Import</button>
      </div>
    </section>
  </main>
</div>
  <script src="js/utils.js"></script>
  <script src="js/theme.js"></script>
  <script src="js/main.js"></script>
<footer class="site-footer">
  <p>© 2025 TV Tracker — Built with ❤️ for your watchlist.</p>
  <p>
    <a href="calendar.php">📅 Episode Calendar</a> |
    <a href="index.php">🏠 Home</a> |
    <a href="movies.php">🎬 Movies</a>
  </p>
</footer>
<a href="#top" class="back-to-top">Back to Top</a>
</body>
</html>
