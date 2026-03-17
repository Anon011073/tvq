<?php require_once 'auth_check.php'; ?><!-- File: schedule.php -->
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Upcoming Episodes</title>
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
      <a href="watchlist.php">📋 Watchlist</a>
      <a href="movies.php">🎬 Movies</a>
      <a href="profile.php">👤 Profile</a>
      <a href="logout.php">🚪 Logout</a>
      <button id="themeToggle" class="theme-toggle">🌙 Toggle Theme</button>
    </div>
  </nav>

  <main class="content-area">
    <h1>🗓️ Upcoming Episodes You're Not Caught Up On</h1>
  <div id="scheduleList"></div>

  </main>
  <script src="js/utils.js"></script>
  <script>
    const favs = JSON.parse(localStorage.getItem(getUserKey('favs')) || '[]');
    const caughtUp = JSON.parse(localStorage.getItem(getUserKey('caughtUp')) || '{}');
    const container = document.getElementById('scheduleList');

    favs.forEach(fav => {
      fetch(`api/tmdb.php?endpoint=/tv/${fav.id}`)
        .then(res => res.json())
        .then(show => {
          if (show.status === 'Returning Series' && show.next_episode_to_air) {
            const next = show.next_episode_to_air;
            const nextAir = new Date(next.air_date);
            const lastSeen = new Date(caughtUp[fav.id] || '1900-01-01');

            if (nextAir > lastSeen) {
              const div = document.createElement('div');
              div.innerHTML = `
                <h3>${show.name}</h3>
                <p>Next: S${next.season_number}E${next.episode_number} - ${next.name}</p>
                <p>Airs on: ${next.air_date}</p>
                <a href="show.php?id=${show.id}">📄 View Show</a>
              `;
              container.appendChild(div);
            }
          }
        });
    });
  </script>
  <script src="js/theme.js"></script>

</body>
</html>
