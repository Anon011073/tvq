<?php require_once 'auth_check.php'; ?><!-- File: favourites.php -->
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Favourites</title>
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
      <a href="favourites.php" class="active">⭐ Favourites</a>
      <a href="watchlist.php">📋 Watchlist</a>
      <a href="movies.php">🎬 Movies</a>
      <a href="profile.php">👤 Profile</a>
      <a href="logout.php">🚪 Logout</a>
      <button id="themeToggle" class="theme-toggle">🌙 Toggle Theme</button>
    </div>
  </nav>

  <main class="content-area">
    <h1>❤️ Your Favourites</h1>
    <div id="favouritesList" class="main-grid"></div>
  </main>

  <script src="js/utils.js"></script>
  <script src="js/theme.js"></script>
  <script>
    const favs = JSON.parse(localStorage.getItem(getUserKey('favs')) || '[]');
    const container = document.getElementById('favouritesList');

    if (favs.length === 0) {
      container.innerHTML = '<p>No favourites yet!</p>';
    } else {
      favs.forEach(fav => {
        const div = document.createElement('div');
        div.innerHTML = `<h3>${fav.name}</h3>
          <button onclick="removeFav(${fav.id})">Remove</button>
          <button onclick="location.href='show.php?id=${fav.id}'">Details</button>
        `;
        container.appendChild(div);
      });
    }

    function removeFav(id) {
      let updated = favs.filter(f => f.id !== id);
      localStorage.setItem(getUserKey('favs'), JSON.stringify(updated));
      location.reload();
    }
  </script>
<footer class="site-footer">
  <p>© 2025 TV Tracker — Built with ❤️ for your watchlist.</p>
  <p>
    <a href="calendar.php">📅 Episode Calendar</a> |
    <a href="index.php">🏠 Back to Home</a>
  </p>
</footer>
<a href="#" class="back-to-top" title="Back to Top">⬆️ Top</a>
</body>
</html>
