<?php
/**
 * Plugin Name: TVQ Tracker
 * Description: A TV show and movie discovery and tracking plugin.
 * Version: 1.0.0
 * Author: TVQ Team
 */

if (!defined('ABSPATH')) exit;

class TVQ_Tracker {
    public function __construct() {
        add_shortcode('tvq_tracker', array($this, 'render_tracker'));
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_init', array($this, 'register_settings'));
        add_action('wp_ajax_tvq_tmdb_proxy', array($this, 'tmdb_proxy'));
        add_action('wp_ajax_nopriv_tvq_tmdb_proxy', array($this, 'tmdb_proxy'));
        add_action('wp_ajax_tvq_save_user_data', array($this, 'save_user_data'));
        add_action('wp_ajax_tvq_get_user_data', array($this, 'get_user_data'));
    }

    public function save_user_data() {
        if (!is_user_logged_in()) {
            wp_send_json_error('Unauthorized', 401);
        }

        $key = isset($_POST['key']) ? sanitize_text_field($_POST['key']) : '';
        $data = isset($_POST['data']) ? $_POST['data'] : '';

        if (!in_array($key, array('tvq_favs', 'tvq_watchlist', 'tvq_watch_progress'))) {
            wp_send_json_error('Invalid key', 400);
        }

        update_user_meta(get_current_user_id(), $key, $data);
        wp_send_json_success();
    }

    public function get_user_data() {
        if (!is_user_logged_in()) {
            wp_send_json_error('Unauthorized', 401);
        }

        $key = isset($_GET['key']) ? sanitize_text_field($_GET['key']) : '';
        if (!in_array($key, array('tvq_favs', 'tvq_watchlist', 'tvq_watch_progress'))) {
            wp_send_json_error('Invalid key', 400);
        }

        $data = get_user_meta(get_current_user_id(), $key, true);
        wp_send_json_success($data ? $data : array());
    }

    public function tmdb_proxy() {
        $api_key = get_option('tvq_tmdb_api_key');
        if (!$api_key) {
            wp_send_json_error('API Key not configured', 400);
        }

        $endpoint = isset($_GET['endpoint']) ? sanitize_text_field($_GET['endpoint']) : '';
        $url = 'https://api.themoviedb.org/3' . $endpoint;

        $params = $_GET;
        unset($params['action'], $params['endpoint']);
        $params['api_key'] = $api_key;

        $url = add_query_arg($params, $url);

        $response = wp_remote_get($url);

        if (is_wp_error($response)) {
            wp_send_json_error('API Request Failed: ' . $response->get_error_message(), 500);
        }

        $body = wp_remote_retrieve_body($response);
        wp_send_json(json_decode($body));
    }

    public function add_admin_menu() {
        add_menu_page(
            'TVQ Tracker Settings',
            'TVQ Tracker',
            'manage_options',
            'tvq-tracker',
            array($this, 'settings_page'),
            'dashicons-video-alt3'
        );
    }

    public function register_settings() {
        register_setting('tvq_settings_group', 'tvq_tmdb_api_key');
    }

    public function settings_page() {
        ?>
        <div class="wrap">
            <h1>TVQ Tracker Settings</h1>

            <div class="notice notice-info">
                <p><strong>How to use:</strong> To display the TV and Movie tracker on any page or post, simply add the shortcode: <code>[tvq_tracker]</code></p>
            </div>

            <form method="post" action="options.php">
                <?php
                settings_fields('tvq_settings_group');
                do_settings_sections('tvq_settings_group');
                ?>
                <table class="form-table">
                    <tr valign="top">
                        <th scope="row">TMDB API Key</th>
                        <td>
                            <input type="text" name="tvq_tmdb_api_key" value="<?php echo esc_attr(get_option('tvq_tmdb_api_key')); ?>" class="regular-text" />
                            <p class="description">Enter your TMDB API v3 key. You can get one for free at <a href="https://www.themoviedb.org/settings/api" target="_blank">The Movie Database (TMDB)</a>.</p>
                        </td>
                    </tr>
                </table>
                <?php submit_button(); ?>
            </form>

            <hr>

            <h2>Plugin Documentation</h2>
            <p>This plugin allows you to browse TV shows and movies, track your watchlist, and manage your favourites.</p>
            <ul>
                <li><strong>Discovery:</strong> Use the sidebar to filter by Genre, Country, and Sort Order.</li>
                <li><strong>Search:</strong> Use the search bar at the top to find specific titles.</li>
                <li><strong>Watchlist & Favourites:</strong> Logged-in users can save items to their profile. These are synced to the WordPress database.</li>
                <li><strong>Trailers:</strong> Movie and TV series trailers are automatically embedded on the details pages.</li>
                <li><strong>Premium Features:</strong> The "Watch Now" functionality is reserved for users with the TVQ Watch Premium plugin installed.</li>
            </ul>
        </div>
        <?php
    }

    public function render_tracker($atts) {
        $this->enqueue_scripts();

        ob_start();
        ?>
        <div id="tvq-tracker-app" class="tvq-tracker-container">
            <nav class="tvq-nav">
                <div class="nav-links">
                    <a href="#" data-view="home">🏠 Home</a>
                    <a href="#" data-view="calendar">📅 Calendar</a>
                    <a href="#" data-view="favourites">⭐ Favourites</a>
                    <a href="#" data-view="watchlist">📋 Watchlist</a>
                    <a href="#" data-view="movies">🎬 Movies</a>
                    <a href="#" data-view="profile">👤 Profile</a>
                    <button id="themeToggle" class="theme-toggle">🌙 Toggle Theme</button>
                </div>
            </nav>

            <div class="main-layout">
                <aside class="sidebar" id="tvq-sidebar">
                    <div class="sidebar-section">
                        <h3>Sort By</h3>
                        <select id="sortBy" class="sidebar-select">
                            <option value="popularity.desc">Popularity</option>
                            <option value="first_air_date.desc">Latest Aired</option>
                        </select>
                    </div>

                    <div class="sidebar-section">
                        <h3>Options</h3>
                        <div>
                            <input type="checkbox" id="englishOnly" checked>
                            <label for="englishOnly">Only English Language</label>
                        </div>
                    </div>

                    <div class="sidebar-section">
                        <h3>Countries</h3>
                        <div class="country-filters">
                            <div><input type="checkbox" class="country-opt" id="countryUS" value="US"><label for="countryUS">🇺🇸 USA</label></div>
                            <div><input type="checkbox" class="country-opt" id="countryUK" value="GB"><label for="countryUK">🇬🇧 UK</label></div>
                            <div><input type="checkbox" class="country-opt" id="countryCA" value="CA"><label for="countryCA">🇨🇦 Canada</label></div>
                        </div>
                    </div>

                    <div class="sidebar-section">
                        <h3>Genres</h3>
                        <div class="genre-list" id="genreFilters">
                            <div class="genre-item active" data-id="">All Genres</div>
                            <div class="genre-item" data-id="10759">Action</div>
                            <div class="genre-item" data-id="16">Animation</div>
                            <div class="genre-item" data-id="35">Comedy</div>
                            <div class="genre-item" data-id="80">Crime</div>
                            <div class="genre-item" data-id="18">Drama</div>
                            <div class="genre-item" data-id="10765">Sci-Fi</div>
                        </div>
                    </div>
                </aside>

                <main class="content-area">
                    <header class="content-header">
                        <input type="text" id="searchInput" placeholder="Search..." />
                    </header>

                    <div id="view-container">
                        <!-- Views will be injected here -->
                        <section id="searchSection" style="display: none;">
                            <h2>🔍 Search Results</h2>
                            <div id="searchResults" class="show-grid"></div>
                        </section>

                        <section id="mainContent">
                            <h2 id="gridTitle">Popular Shows</h2>
                            <div id="mainGrid" class="main-grid"></div>
                            <div class="pagination">
                                <button id="prevPage" class="btn" disabled>Previous</button>
                                <div class="page-numbers" id="pageNumbers"></div>
                                <button id="nextPage" class="btn">Next</button>
                                <span id="pageInfo">Page 1</span>
                            </div>
                        </section>

                        <section id="showDetails" style="display: none;"></section>
                        <section id="movieDetails" style="display: none;"></section>
                        <section id="calendarView" style="display: none;"></section>
                        <section id="favouritesView" style="display: none;"></section>
                        <section id="watchlistView" style="display: none;"></section>
                        <section id="profileView" style="display: none;">
                             <h3>🧩 Backup & Restore</h3>
                             <div class="backup-btns">
                                <button onclick="exportData()" class="btn btn-secondary">⬇️ Export</button>
                                <input type="file" id="importFile" accept=".json" />
                                <button onclick="importData()" class="btn btn-secondary">⬆️ Import</button>
                             </div>
                        </section>
                        <section id="watchView" style="display: none;"></section>
                    </div>
                </main>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }

    private function enqueue_scripts() {
        if ( ! function_exists( 'is_plugin_active' ) ) {
            require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
        }

        wp_enqueue_style('tvq-style', plugins_url('css/style.css', __FILE__));
        wp_enqueue_script('tvq-utils', plugins_url('js/utils.js', __FILE__), array(), '1.0.0', true);
        wp_enqueue_script('tvq-theme', plugins_url('js/theme.js', __FILE__), array('tvq-utils'), '1.0.0', true);
        wp_enqueue_script('tvq-main', plugins_url('js/main.js', __FILE__), array('tvq-theme'), '1.0.0', true);
        wp_enqueue_script('tvq-show', plugins_url('js/show.js', __FILE__), array('tvq-main'), '1.0.0', true);
        wp_enqueue_script('tvq-movie', plugins_url('js/movie.js', __FILE__), array('tvq-main'), '1.0.0', true);
        wp_enqueue_script('tvq-watch', plugins_url('js/watch.js', __FILE__), array('tvq-main'), '1.0.0', true);
        wp_enqueue_script('tvq-calendar', plugins_url('js/calendar.js', __FILE__), array('tvq-main'), '1.0.0', true);
        wp_enqueue_script('tvq-favourites', plugins_url('js/favourites.js', __FILE__), array('tvq-main'), '1.0.0', true);
        wp_enqueue_script('tvq-watchlist', plugins_url('js/watchlist.js', __FILE__), array('tvq-main'), '1.0.0', true);
        wp_enqueue_script('tvq-profile', plugins_url('js/profile.js', __FILE__), array('tvq-main'), '1.0.0', true);

        $is_premium_active = is_plugin_active('tvq-watch-premium/tvq-watch-premium.php');

        wp_localize_script('tvq-utils', 'tvq_params', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'user_id' => get_current_user_id(),
            'is_logged_in' => is_user_logged_in(),
            'is_premium' => $is_premium_active,
            'can_watch' => current_user_can('read') && $is_premium_active
        ));
    }
}

new TVQ_Tracker();
