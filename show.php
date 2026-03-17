<?php require_once 'auth_check.php'; ?><!-- File: show.php -->
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Show Details</title>
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

<main id="showDetails" class="content-area">
  <!-- Dynamic content will be injected by show.js -->
</main>

<footer class="site-footer">
  <p>© 2025 TV Tracker — Built with ❤️ for your watchlist.</p>
  <p>
    <a href="calendar.php">📅 Episode Calendar</a> |
    <a href="index.php">🏠 Back to Home</a>
  </p>
</footer>

<script src="js/utils.js"></script>
<script src="js/theme.js"></script>
<script src="js/show.js"></script>
<a href="#top" class="back-to-top">Back to Top</a>
</body>
</html>
