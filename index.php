<?php include 'header.php'; ?>

<section id="searchSection" style="display: none;">
    <h2>Search Results</h2>
    <div id="searchResults" class="horizontal-scroll"></div>
</section>

<section id="mainContent">
    <div class="top-row">
        <h2 id="gridTitle">Popular Shows</h2>
        <div class="search-box">
            <input type="text" id="searchInput" placeholder="Search for a show..." />
        </div>
    </div>

    <div class="layout-container">
        <!-- Filters Sidebar -->
        <aside class="filters-sidebar">
            <div class="filter-group">
                <h3>Sort By</h3>
                <select id="sortBy">
                    <option value="popularity.desc" selected>Popularity</option>
                    <option value="first_air_date.desc">Release Date</option>
                    <option value="vote_average.desc">Rating</option>
                </select>
            </div>

            <div class="filter-group">
                <h3>Released since</h3>
                <input type="range" id="maxAgeYears" min="1920" max="2020" step="10" value="1920">
                <span id="maxAgeYearsValue">1920</span>
            </div>

            <div class="filter-group">
                <h3>Max show age (days)</h3>
                <input type="range" id="maxShowAgeDays" min="0" max="90" value="0">
                <span id="maxShowAgeDaysValue">0</span>
            </div>

            <div class="filter-group">
                <h3>Options</h3>
                <label>
                    <input type="checkbox" id="englishOnly" checked> English Only
                </label>
            </div>

            <div class="filter-group">
                <h3>Country</h3>
                <label><input type="checkbox" class="country-opt" value="US" checked> 🇺🇸 USA</label>
                <label><input type="checkbox" class="country-opt" value="GB"> 🇬🇧 UK</label>
                <label><input type="checkbox" class="country-opt" value="CA"> 🇨🇦 Canada</label>
                <label><input type="checkbox" class="country-opt" value="SE"> 🇸🇪 Sweden</label>
                <label><input type="checkbox" class="country-opt" value="NO"> 🇳🇴 Norway</label>
                <label><input type="checkbox" class="country-opt" value="FI"> 🇫🇮 Finland</label>
                <label><input type="checkbox" class="country-opt" value="IS"> 🇮🇸 Iceland</label>
                <label><input type="checkbox" class="country-opt" value="DK"> 🇩🇰 Denmark</label>
                <label><input type="checkbox" class="country-opt" value="DE"> 🇩🇪 Germany</label>
            </div>

            <div class="filter-group">
                <h3>Genre</h3>
                <div id="genreList">
                    <div class="genre-item active" data-id="">All</div>
                    <div class="genre-item" data-id="10759">Action & Adventure</div>
                    <div class="genre-item" data-id="16">Animation</div>
                    <div class="genre-item" data-id="35">Comedy</div>
                    <div class="genre-item" data-id="80">Crime</div>
                    <div class="genre-item" data-id="99">Documentary</div>
                    <div class="genre-item" data-id="18">Drama</div>
                    <div class="genre-item" data-id="10751">Family</div>
                    <div class="genre-item" data-id="10762">Kids</div>
                    <div class="genre-item" data-id="9648">Mystery</div>
                    <div class="genre-item" data-id="10763">News</div>
                    <div class="genre-item" data-id="10764">Reality</div>
                    <div class="genre-item" data-id="10765">Sci-Fi & Fantasy</div>
                    <div class="genre-item" data-id="10766">Soap</div>
                    <div class="genre-item" data-id="10767">Talk</div>
                    <div class="genre-item" data-id="10768">War & Politics</div>
                    <div class="genre-item" data-id="37">Western</div>
                </div>
            </div>
        </aside>

        <!-- Main Show Grid -->
        <div class="grid-content">
            <div id="mainGrid" class="main-grid">
                <!-- Data from JS -->
            </div>
            <div id="pagination" class="pagination">
                <button id="prevPage">Prev</button>
                <span id="pageInfo">Page 1 of ?</span>
                <div id="pageNumbers"></div>
                <button id="nextPage">Next</button>
            </div>
        </div>
    </div>
</section>

<?php include 'footer.php'; ?>
