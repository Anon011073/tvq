<?php require_once 'auth_check.php'; ?><!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Watchlist</title>
  <link rel="stylesheet" href="css/style.css" />
  <script>
    window.CURRENT_USER_ID = <?php echo json_encode($_SESSION['user_id']); ?>;
  </script>
</head>
<body>
  <nav class="top-nav">
    <div class="nav-brand">
      <h1>📺 TV Tracker</h1>
    </div>
    <div class="nav-links">
      <a href="index.php">🏠 Home</a>
      <a href="calendar.php">📅 Calendar</a>
      <a href="favourites.php">⭐ Favourites</a>
      <a href="watchlist.php" class="active">📋 Watchlist</a>
      <a href="movies.php">🎬 Movies</a>
      <a href="profile.php">👤 Profile</a>
      <a href="logout.php">🚪 Logout</a>
      <button id="themeToggle" class="theme-toggle">🌙 Toggle Theme</button>
    </div>
  </nav>

  <main class="content-area">
    <h1>📋 Unwatched Episodes</h1>
    <div id="watchList"></div>
  </main>

  <script src="js/utils.js"></script>
  <script src="js/theme.js"></script>
  <script>
    const favs = JSON.parse(localStorage.getItem(getUserKey('favs')) || '[]');
    const progress = JSON.parse(localStorage.getItem(getUserKey('watchProgress')) || '{}');
    const container = document.getElementById('watchList');

    if (favs.length === 0) {
      container.innerHTML = '<p>No favourites added.</p>';
    }

    favs.forEach(fav => {
      fetch(`api/tmdb.php?endpoint=/tv/${fav.id}`)
        .then(res => res.json())
        .then(show => {
          const seasons = [];
          for (let s = 1; s <= show.number_of_seasons; s++) {
            seasons.push(
              fetch(`api/tmdb.php?endpoint=/tv/${fav.id}/season/${s}`).then(r => r.json())
            );
          }

          Promise.all(seasons).then(results => {
            let unwatched = [];

            results.forEach(season => {
              const seasonNum = season.season_number;
              season.episodes.forEach((ep, idx) => {
                const watched = progress[show.id]?.[seasonNum]?.[idx] || false;
                if (!watched) {
                  unwatched.push(`S${seasonNum}E${ep.episode_number} - ${ep.name}`);
                }
              });
            });

            if (unwatched.length > 0) {
const div = document.createElement('div');
const bodyId = `body-${show.id}`;

div.innerHTML = `
  <h3 onclick="toggleWatchlist('${bodyId}')">▶ ${show.name} (click to expand)</h3>
  <div class="watchlist-body" id="${bodyId}">
    <ul>${unwatched.map(e => `<li>${e}</li>`).join('')}</ul>
    <a href="show.php?id=${show.id}">▶ Go to show</a>
  </div>
`;
container.appendChild(div);

            }
          });
        });
    });
  </script>
  <script>
  function toggleWatchlist(id) {
    const el = document.getElementById(id);
    el.style.display = el.style.display === 'block' ? 'none' : 'block';
  }
</script>
<script src="js/theme.js"></script>
<a href="#" class="back-to-top" title="Back to Top">⬆️ Top</a>
</body>
</html>
