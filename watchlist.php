<?php include 'header.php'; ?>
<div class="watchlist-container">
    <h2>Your Watchlist</h2>
    <div id="watchlistGrid" class="main-grid"></div>
</div>
<script>
document.addEventListener('DOMContentLoaded', () => {
    const userId = window.CURRENT_USER_ID;
    const watchlist = JSON.parse(localStorage.getItem(`user_${userId}_watchlist`) || '[]');
    const grid = document.getElementById('watchlistGrid');

    if (watchlist.length === 0) {
        grid.innerHTML = '<p>Your watchlist is empty.</p>';
        return;
    }

    watchlist.forEach(show => {
        const div = document.createElement('div');
        div.className = 'card';
        div.innerHTML = `
            <img src="https://image.tmdb.org/t/p/w200${show.poster_path}" alt="${show.name}" />
            <h3>${show.name}</h3>
        `;
        div.onclick = () => window.location.href = `show.php?id=${show.id}`;
        grid.appendChild(div);
    });
});
</script>
<?php include 'footer.php'; ?>
