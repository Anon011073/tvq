<?php require_once 'auth_check.php'; ?><!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>TV Tracker - Profile</title>
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
      <a href="movies.php">🎬 Movies</a>
      <a href="profile.php" class="active">👤 Profile</a>
      <a href="logout.php">🚪 Logout</a>
      <button id="themeToggle" class="theme-toggle">🌙 Toggle Theme</button>
    </div>
  </nav>

  <main class="content-area">
    <h1>👤 Your Profile</h1>
    <section id="trackedSection">
      <h2>🎯 Shows I Watch</h2>
      <div id="trackedShowsGrid" class="main-grid"></div>
      
      <div class="pagination" id="profilePagination">
        <button id="prevProfilePage" class="btn">Previous</button>
        <div class="page-numbers" id="profilePageNumbers"></div>
        <button id="nextProfilePage" class="btn">Next</button>
        <span id="profilePageInfo"></span>
      </div>
    </section>
  </main>

  <script src="js/utils.js"></script>
  <script src="js/theme.js"></script>
  <script>
    let profilePage = 1;
    const itemsPerPage = 18; // 3 rows of 6 cards roughly

    document.addEventListener('DOMContentLoaded', () => {
        loadProfileTrackedShows(1);
        
        document.getElementById('prevProfilePage').onclick = () => {
            if (profilePage > 1) loadProfileTrackedShows(profilePage - 1);
        };
        document.getElementById('nextProfilePage').onclick = () => {
            loadProfileTrackedShows(profilePage + 1);
        };
    });

    async function loadProfileTrackedShows(page) {
        profilePage = page;
        const tracked = JSON.parse(localStorage.getItem(getUserKey('favs')) || '[]');
        const container = document.getElementById('trackedShowsGrid');
        
        if (tracked.length === 0) {
            container.innerHTML = '<p>You are not tracking any shows yet. Go to Home or Search to add some!</p>';
            document.getElementById('profilePagination').style.display = 'none';
            return;
        }

        const totalPages = Math.ceil(tracked.length / itemsPerPage);
        const start = (page - 1) * itemsPerPage;
        const end = start + itemsPerPage;
        const pageItems = tracked.slice(start, end);

        container.innerHTML = 'Loading tracked shows...';

        const promises = pageItems.map(show => 
            fetch(`api/tmdb.php?endpoint=/tv/${show.id}`).then(res => res.json())
        );

        const results = await Promise.all(promises);
        container.innerHTML = '';
        results.forEach(data => {
            if (data.id) {
                const div = document.createElement('div');
                div.className = 'card';
                div.innerHTML = `
                  <img src="https://image.tmdb.org/t/p/w200${data.poster_path}" alt="${data.name}" onerror="this.src='https://placehold.co/200x300?text=No+Image'"/>
                  <h3>${data.name}</h3>
                  <p>⭐ ${data.vote_average}</p>
                `;
                div.addEventListener('click', () => {
                  window.location.href = `show.php?id=${data.id}`;
                });
                container.appendChild(div);
            }
        });

        updateProfilePagination(page, totalPages);
    }

    function updateProfilePagination(current, total) {
        const info = document.getElementById('profilePageInfo');
        info.textContent = `Page ${current} of ${total}`;
        
        document.getElementById('prevProfilePage').disabled = current <= 1;
        document.getElementById('nextProfilePage').disabled = current >= total;

        const numbers = document.getElementById('profilePageNumbers');
        numbers.innerHTML = '';
        
        for (let i = 1; i <= total; i++) {
            const btn = document.createElement('button');
            btn.className = `page-num ${i === current ? 'active' : ''}`;
            btn.textContent = i;
            btn.onclick = () => loadProfileTrackedShows(i);
            numbers.appendChild(btn);
        }
    }
  </script>
<footer class="site-footer">
  <p>© 2025 TV Tracker — Built with ❤️ for your watchlist.</p>
</footer>
<a href="#top" class="back-to-top">Back to Top</a>
</body>
</html>
