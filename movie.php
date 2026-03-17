<?php require_once 'auth_check.php'; ?><!-- File: movie.php -->
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Movie Details</title>
  <link rel="stylesheet" href="css/style.css">
  <script>
    window.CURRENT_USER_ID = <?php echo json_encode($_SESSION['user_id']); ?>;
  </script>
  <script src="js/utils.js" defer></script>
  <script src="js/movie.js" defer></script>
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
      <a href="movies.php">🎬 Movies</a>
      <a href="profile.php">👤 Profile</a>
      <a href="logout.php">🚪 Logout</a>
      <button id="themeToggle" class="theme-toggle">🌙 Toggle Theme</button>
    </div>
  </nav>

  <main id="movieDetails" class="content-area">
    <!-- Movie details will load here -->
  </main>

  <footer class="site-footer">
    <p>© 2025 TV Tracker — Movie Mode</p>
    <p><a href="#top">⬆ Back to Top</a></p>
  </footer>
</body>
</html>
