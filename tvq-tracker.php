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
        register_activation_hook(__FILE__, array($this, 'activate'));
        add_shortcode('tvq_tracker', array($this, 'render_tracker'));
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_init', array($this, 'register_settings'));
        add_action('wp_ajax_tvq_tmdb_proxy', array($this, 'tmdb_proxy'));
        add_action('wp_ajax_nopriv_tvq_tmdb_proxy', array($this, 'tmdb_proxy'));
        add_action('wp_ajax_tvq_save_user_data', array($this, 'save_user_data'));
        add_action('wp_ajax_tvq_get_user_data', array($this, 'get_user_data'));
        add_action('show_user_profile', array($this, 'user_profile_fields'));
        add_action('edit_user_profile', array($this, 'user_profile_fields'));
        add_action('admin_menu', array($this, 'add_profile_page'));
        add_action('personal_options_update', array($this, 'save_user_profile_fields'));
        add_action('edit_user_profile_update', array($this, 'save_user_profile_fields'));
    }

    public function save_user_data() {
        check_ajax_referer('tvq_nonce', 'nonce');
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
        check_ajax_referer('tvq_nonce', 'nonce');
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

    public function activate() {
        add_role('tvq_user', 'TVQ User', array(
            'read' => true,
            'tvq_tracker_access' => true,
        ));

        // Ensure Administrator has the capability
        $admin = get_role('administrator');
        if ($admin) {
            $admin->add_cap('tvq_tracker_access');
            $admin->add_cap('tvq_can_watch');
        }
    }

    public function user_profile_fields($user) {
        $can_watch = user_can($user->ID, 'tvq_can_watch');
        ?>
        <hr>
        <div class="tvq-profile-section">
            <h2>TVQ Tracker Profile</h2>
            <table class="form-table">
                <?php if (current_user_can('manage_options')): ?>
                <tr>
                    <th>Premium Watch Access</th>
                    <td>
                        <label for="tvq_can_watch">
                            <input type="checkbox" name="tvq_can_watch" id="tvq_can_watch" value="1" <?php checked($can_watch); ?>>
                            Allow this user to use the "Watch Now" premium feature.
                        </label>
                    </td>
                </tr>
                <?php endif; ?>
                <tr>
                    <th>My Tracker Data</th>
                    <td>
                        <a href="<?php echo admin_url('profile.php?page=my-tvq-tracker'); ?>" class="button button-secondary">View My TVQ Tracker Dashboard</a>
                        <p class="description">View your favorited movies and tracked TV series.</p>
                    </td>
                </tr>
            </table>
        </div>
        <style>
            .tvq-profile-section { margin-top: 30px; border-top: 2px solid #222; padding-top: 20px; }
            .tvq-profile-section h2 { background: #222; color: #fff; padding: 10px 15px; border-radius: 4px; display: inline-block; }
            .tvq-mini-grid { display: flex; flex-wrap: wrap; gap: 10px; margin-bottom: 20px; }
            .tvq-mini-item { background: #fff; padding: 6px 12px; border-radius: 20px; border: 1px solid #ff5e57; font-size: 13px; color: #333; box-shadow: 0 2px 4px rgba(0,0,0,0.05); }
            .tvq-profile-data h3 { margin-top: 30px; border-bottom: 2px solid #ff5e57; padding-bottom: 5px; color: #222; text-transform: uppercase; letter-spacing: 1px; }
            .tvq-row h4 { margin-bottom: 8px; color: #ff5e57; font-size: 15px; font-weight: bold; }
        </style>
        <?php
    }

    public function save_user_profile_fields($user_id) {
        if (!current_user_can('manage_options')) {
            return false;
        }

        $user = get_userdata($user_id);
        if (isset($_POST['tvq_can_watch']) && $_POST['tvq_can_watch'] == '1') {
            $user->add_cap('tvq_can_watch');
        } else {
            $user->remove_cap('tvq_can_watch');
        }
    }

    public function tmdb_proxy() {
        check_ajax_referer('tvq_nonce', 'nonce');
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

    public function add_profile_page() {
        add_submenu_page(
            'profile.php',
            'My TVQ Tracker',
            'My TVQ Tracker',
            'read',
            'my-tvq-tracker',
            array($this, 'my_tvq_page')
        );
    }

    public function my_tvq_page() {
        $user_id = get_current_user_id();
        $favs = get_user_meta($user_id, 'tvq_favs', true);
        if (is_string($favs)) $favs = json_decode($favs, true);
        if (!is_array($favs)) $favs = array();

        $watchlist = get_user_meta($user_id, 'tvq_watchlist', true);
        if (is_string($watchlist)) $watchlist = json_decode($watchlist, true);
        if (!is_array($watchlist)) $watchlist = array();

        ?>
        <div class="wrap tvq-tracker-container light-mode">
            <h1>📺 My TVQ Tracker Data</h1>

            <div class="tvq-profile-data" style="max-width: 1000px;">
                <section>
                    <h3>⭐ Favourites: TV Series</h3>
                    <div class="tvq-profile-grid">
                        <?php foreach($favs as $item): if(isset($item['type']) && $item['type'] === 'tv'): ?>
                            <div class="tvq-profile-card">
                                <img src="https://image.tmdb.org/t/p/w200<?php echo $item['poster_path']; ?>" onerror="this.src='https://placehold.co/100x150?text=No+Image'" />
                                <span><?php echo esc_html($item['name']); ?></span>
                            </div>
                        <?php endif; endforeach; ?>
                    </div>
                </section>

                <section>
                    <h3>⭐ Favourites: Movies</h3>
                    <div class="tvq-profile-grid">
                        <?php foreach($favs as $item): if(isset($item['type']) && $item['type'] === 'movie'): ?>
                            <div class="tvq-profile-card">
                                <img src="https://image.tmdb.org/t/p/w200<?php echo $item['poster_path']; ?>" onerror="this.src='https://placehold.co/100x150?text=No+Image'" />
                                <span><?php echo esc_html($item['name']); ?></span>
                            </div>
                        <?php endif; endforeach; ?>
                    </div>
                </section>

                <section>
                    <h3>📋 My Watchlist</h3>
                    <div class="tvq-profile-grid">
                        <?php foreach($watchlist as $item): ?>
                            <div class="tvq-profile-card">
                                <img src="https://image.tmdb.org/t/p/w200<?php echo isset($item['poster_path']) ? $item['poster_path'] : ''; ?>" onerror="this.src='https://placehold.co/100x150?text=No+Image'" />
                                <span><?php echo esc_html($item['name']); ?> (<?php echo strtoupper($item['type']); ?>)</span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </section>
            </div>
        </div>
        <style>
            .tvq-profile-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(120px, 1fr)); gap: 15px; margin-bottom: 30px; }
            .tvq-profile-card { text-align: center; font-size: 12px; }
            .tvq-profile-card img { width: 100%; border-radius: 4px; margin-bottom: 5px; border: 1px solid #ddd; }
            .tvq-profile-data h3 { margin-top: 20px; border-bottom: 2px solid #ff5e57; padding-bottom: 5px; color: #222; }
        </style>
        <?php
    }

    public function register_settings() {
        register_setting('tvq_settings_group', 'tvq_tmdb_api_key');
        register_setting('tvq_settings_group', 'tvq_default_view');
        register_setting('tvq_settings_group', 'tvq_default_language');
        register_setting('tvq_settings_group', 'tvq_default_country');
        register_setting('tvq_settings_group', 'tvq_default_sort');
        register_setting('tvq_settings_group', 'tvq_english_only_default');
        register_setting('tvq_settings_group', 'tvq_enable_trending');
        register_setting('tvq_settings_group', 'tvq_trending_type');
        register_setting('tvq_settings_group', 'tvq_min_role_watch');
        register_setting('tvq_settings_group', 'tvq_grid_columns');
        register_setting('tvq_settings_group', 'tvq_premium_buy_url');
        register_setting('tvq_settings_group', 'tvq_custom_css');
    }

    public function settings_page() {
        $is_premium = is_plugin_active('tvq-watch-premium/tvq-watch-premium.php');
        ?>
        <div class="wrap">
            <h1>TVQ Tracker Settings</h1>

            <div class="notice <?php echo $is_premium ? 'notice-success' : 'notice-warning'; ?>">
                <p>
                    <strong>Premium Status:</strong>
                    <?php if ($is_premium): ?>
                        ✅ Active - "Watch Now" feature is enabled.
                    <?php else: ?>
                        ❌ Inactive - "Watch Now" feature is disabled. <a href="<?php echo esc_url(get_option('tvq_premium_buy_url', 'http://zeaks.org')); ?>" target="_blank" class="button button-primary" style="margin-left: 10px;">Upgrade to Premium</a>
                    <?php endif; ?>
                </p>
            </div>

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
                    <tr valign="top">
                        <th scope="row">Default View</th>
                        <td>
                            <select name="tvq_default_view">
                                <option value="home" <?php selected(get_option('tvq_default_view'), 'home'); ?>>TV Shows</option>
                                <option value="movies" <?php selected(get_option('tvq_default_view'), 'movies'); ?>>Movies</option>
                            </select>
                        </td>
                    </tr>
                    <tr valign="top">
                        <th scope="row">Default Language</th>
                        <td>
                            <input type="text" name="tvq_default_language" value="<?php echo esc_attr(get_option('tvq_default_language', 'en')); ?>" class="small-text" />
                            <p class="description">ISO 639-1 code (e.g., 'en', 'es', 'fr').</p>
                        </td>
                    </tr>
                    <tr valign="top">
                        <th scope="row">Default Country</th>
                        <td>
                            <input type="text" name="tvq_default_country" value="<?php echo esc_attr(get_option('tvq_default_country', '')); ?>" class="small-text" />
                            <p class="description">ISO 3166-1 alpha-2 (e.g., 'US', 'GB'). Leave blank for All.</p>
                        </td>
                    </tr>
                    <tr valign="top">
                        <th scope="row">Default Sort</th>
                        <td>
                            <select name="tvq_default_sort">
                                <option value="popularity.desc" <?php selected(get_option('tvq_default_sort'), 'popularity.desc'); ?>>Popularity</option>
                                <option value="first_air_date.desc" <?php selected(get_option('tvq_default_sort'), 'first_air_date.desc'); ?>>Latest Aired</option>
                                <option value="vote_average.desc" <?php selected(get_option('tvq_default_sort'), 'vote_average.desc'); ?>>Highest Rated</option>
                            </select>
                        </td>
                    </tr>
                    <tr valign="top">
                        <th scope="row">Default English Only</th>
                        <td>
                            <input type="checkbox" name="tvq_english_only_default" value="1" <?php checked(get_option('tvq_english_only_default'), '1'); ?> />
                        </td>
                    </tr>
                    <tr valign="top">
                        <th scope="row">Enable Trending Section</th>
                        <td>
                            <input type="checkbox" name="tvq_enable_trending" value="1" <?php checked(get_option('tvq_enable_trending'), '1'); ?> />
                            <p class="description">Show a special horizontal section above the grid.</p>
                        </td>
                    </tr>
                    <tr valign="top">
                        <th scope="row">Trending Type</th>
                        <td>
                            <select name="tvq_trending_type">
                                <option value="trending" <?php selected(get_option('tvq_trending_type'), 'trending'); ?>>Trending (Day)</option>
                                <option value="top_rated" <?php selected(get_option('tvq_trending_type'), 'top_rated'); ?>>Top Rated</option>
                                <option value="on_the_air" <?php selected(get_option('tvq_trending_type'), 'on_the_air'); ?>>On The Air / Now Playing</option>
                            </select>
                        </td>
                    </tr>
                    <tr valign="top">
                        <th scope="row">Minimum Role to Watch</th>
                        <td>
                            <select name="tvq_min_role_watch">
                                <option value="read" <?php selected(get_option('tvq_min_role_watch'), 'read'); ?>>Subscriber</option>
                                <option value="edit_posts" <?php selected(get_option('tvq_min_role_watch'), 'edit_posts'); ?>>Contributor</option>
                                <option value="publish_posts" <?php selected(get_option('tvq_min_role_watch'), 'publish_posts'); ?>>Author</option>
                                <option value="manage_options" <?php selected(get_option('tvq_min_role_watch'), 'manage_options'); ?>>Administrator</option>
                            </select>
                            <p class="description">User must have this capability to see the "Watch Now" button (Premium required).</p>
                        </td>
                    </tr>
                    <tr valign="top">
                        <th scope="row">Grid Columns</th>
                        <td>
                            <input type="number" name="tvq_grid_columns" value="<?php echo esc_attr(get_option('tvq_grid_columns', 6)); ?>" min="1" max="10" />
                            <p class="description">How many columns of posters to display in the grid (Desktop view).</p>
                        </td>
                    </tr>
                    <tr valign="top">
                        <th scope="row">Premium Buy URL</th>
                        <td>
                            <input type="text" name="tvq_premium_buy_url" value="<?php echo esc_attr(get_option('tvq_premium_buy_url', 'http://zeaks.org')); ?>" class="regular-text" />
                        </td>
                    </tr>
                    <tr valign="top">
                        <th scope="row">Custom CSS</th>
                        <td>
                            <textarea name="tvq_custom_css" rows="5" cols="50" class="large-text"><?php echo esc_textarea(get_option('tvq_custom_css')); ?></textarea>
                            <p class="description">Override plugin styles here.</p>
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
                    <a href="#" data-view="home">📺 TV Series</a>
                    <a href="#" data-view="movies">🎬 Movies</a>
                    <a href="#" data-view="calendar">📅 Calendar</a>
                    <a href="#" data-view="favourites">⭐ Favourites</a>
                    <a href="#" data-view="watchlist">📋 Watchlist</a>
                    <a href="#" data-view="profile">👤 Profile</a>
                    <button id="themeToggle" class="theme-toggle">🌙 Toggle Theme</button>
                </div>
            </nav>

            <div class="main-layout no-sidebar">
                <main class="content-area">
                    <header class="content-header">
                        <div class="search-bar">
                            <input type="text" id="searchInput" placeholder="Search for shows or movies..." />
                        </div>

                        <div class="filter-bar" id="tvq-filters">
                            <div class="filter-group">
                                <label>Sort By</label>
                                <select id="sortBy" class="filter-select">
                                    <option value="popularity.desc">Popularity</option>
                                    <option value="first_air_date.desc">Latest Aired</option>
                                    <option value="vote_average.desc">Highest Rated</option>
                                </select>
                            </div>

                            <div class="filter-group">
                                <label>Genre</label>
                                <select id="genreSelect" class="filter-select">
                                    <option value="">All Genres</option>
                                    <option value="10759">Action</option>
                                    <option value="16">Animation</option>
                                    <option value="35">Comedy</option>
                                    <option value="80">Crime</option>
                                    <option value="99">Documentary</option>
                                    <option value="18">Drama</option>
                                    <option value="10751">Family</option>
                                    <option value="10765">Sci-Fi</option>
                                    <option value="10766">Soap</option>
                                    <option value="10767">Talk</option>
                                </select>
                            </div>

                            <div class="filter-group">
                                <label>Country</label>
                                <select id="countrySelect" class="filter-select">
                                    <option value="">All Countries</option>
                                    <option value="US">🇺🇸 USA</option>
                                    <option value="GB">🇬🇧 UK</option>
                                    <option value="CA">🇨🇦 Canada</option>
                                    <option value="AU">🇦🇺 Australia</option>
                                    <option value="KR">🇰🇷 South Korea</option>
                                    <option value="JP">🇯🇵 Japan</option>
                                </select>
                            </div>

                            <div class="filter-group checkbox-group">
                                <input type="checkbox" id="englishOnly">
                                <label for="englishOnly">English Only</label>
                            </div>
                        </div>
                    </header>

                    <div id="view-container">
                        <section id="trendingSection" style="display: none;">
                            <h2 id="trendingTitle">Trending Now</h2>
                            <div id="trendingGrid" class="horizontal-scroll"></div>
                        </section>

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
                        <section id="watchView" style="display: none;"></section>
                    </div>
                </main>
            </div>
        </div>
        <style>
            <?php echo get_option('tvq_custom_css'); ?>
        </style>
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

        $is_premium_active = is_plugin_active('tvq-watch-premium/tvq-watch-premium.php');

        wp_localize_script('tvq-utils', 'tvq_params', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('tvq_nonce'),
            'user_id' => get_current_user_id(),
            'is_logged_in' => is_user_logged_in(),
            'is_premium' => $is_premium_active,
            'buy_url' => get_option('tvq_premium_buy_url', 'http://zeaks.org'),
            'grid_cols' => get_option('tvq_grid_columns', 6),
            'can_watch' => current_user_can('tvq_can_watch') && $is_premium_active,
            'profile_url' => admin_url('profile.php'),
            'default_view' => get_option('tvq_default_view', 'home'),
            'default_lang' => get_option('tvq_default_language', 'en'),
            'default_country' => get_option('tvq_default_country', ''),
            'default_sort' => get_option('tvq_default_sort', 'popularity.desc'),
            'english_only' => get_option('tvq_english_only_default') == '1',
            'trending_enabled' => get_option('tvq_enable_trending') == '1',
            'trending_type' => get_option('tvq_trending_type', 'trending')
        ));
    }
}

new TVQ_Tracker();
