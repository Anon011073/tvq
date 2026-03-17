// File: js/movie.js

const params = new URLSearchParams(window.location.search);
const movieId = params.get('id');

if (movieId) fetchMovieDetails(movieId);

function fetchMovieDetails(id) {
  fetch(`api/tmdb.php?endpoint=/movie/${id}`)
    .then(res => res.json())
    .then(renderMovieDetails);
}

function renderMovieDetails(movie) {
  const container = document.getElementById('movieDetails');
  const poster = movie.poster_path ? `https://image.tmdb.org/t/p/w300${movie.poster_path}` : '';

  container.innerHTML = `
    <section class="hero">
      <img src="${poster}" alt="${movie.title}" class="poster">
      <div class="hero-text">
        <h1>${movie.title}</h1>
        <p>${movie.overview}</p>
        <p><strong>Release Date:</strong> ${movie.release_date}</p>
        <p><strong>Rating:</strong> ⭐ ${movie.vote_average}</p>
        <div class="btn-group">
          <button class="btn btn-primary" onclick="watchMovie(${movie.id}, '${movie.title.replace(/'/g, "\'")}')">▶️ Watch Now</button>
        </div>
      </div>
    </section>
  `;
}

async function watchMovie(tmdbId, title) {
  const res = await fetch(`api/tmdb.php?endpoint=/movie/${tmdbId}/external_ids`);
  const data = await res.json();
  const imdbId = data.imdb_id;
  if (!imdbId) return alert('No IMDb ID found.');

  const watched = JSON.parse(localStorage.getItem(getUserKey('watchedMovies')) || '[]');
  if (!watched.find(m => m.id === tmdbId)) {
    watched.push({ id: tmdbId, title });
    localStorage.setItem(getUserKey('watchedMovies'), JSON.stringify(watched));
  }

  window.location.href = `watch.php?id=${tmdbId}&type=movie`;
}

