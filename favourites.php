<?php include 'header.php'; ?>
<div class="favourites-container">
    <h2>Your Favourites</h2>
    <div id="favouritesGrid" class="main-grid"></div>
</div>
<script>
document.addEventListener('DOMContentLoaded', () => {
    const userId = window.CURRENT_USER_ID;
    const favs = JSON.parse(localStorage.getItem(`user_${userId}_favourites`) || '[]');
    const grid = document.getElementById('favouritesGrid');

    if (favs.length === 0) {
        grid.innerHTML = '<p>You have no favourites yet.</p>';
        return;
    }

    favs.forEach(show => {
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
