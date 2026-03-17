<?php require_once 'auth_check.php'; ?><!-- File: watch.php -->
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Watch</title>
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
    <div id="videoPlayer">
      <h2>Now Playing: <span id="movieTitle">Loading...</span></h2>
      <iframe id="iframe" scrolling="no" src="" allowfullscreen></iframe>
      <p style="color: rgb(79, 221, 221); font-weight: 500;">If there's an error or buffering, refresh or try a different source.</p>
    </div>

    <div id="episodeList"></div>
  </main>

  <script src="js/utils.js"></script>
  <script src="js/theme.js"></script>
  <script src="js/watch.js"></script>
</body>
</html>
