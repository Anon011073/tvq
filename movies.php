<?php include 'header.php'; ?>
<div class="movies-container">
    <h2>Popular Movies</h2>
    <div id="moviesGrid" class="main-grid"></div>
</div>
<script>
fetch('api/tmdb.php?endpoint=/movie/popular')
    .then(res => res.json())
    .then(data => {
        const grid = document.getElementById('moviesGrid');
        data.results.forEach(movie => {
            const div = document.createElement('div');
            div.className = 'card';
            div.innerHTML = `
                <img src="https://image.tmdb.org/t/p/w200${movie.poster_path}" alt="${movie.title}" />
                <h3>${movie.title}</h3>
                <p>⭐ ${movie.vote_average}</p>
            `;
            grid.appendChild(div);
        });
    });
</script>
<?php include 'footer.php'; ?>
