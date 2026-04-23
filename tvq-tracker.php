<?php
/**
 * Plugin Name: TVQ Tracker
 * Description: A TV show and movie discovery and tracking plugin.
 * Version: 1.1.0
 * Author: TVQ Team
 */

if (!defined('ABSPATH')) exit;

class TVQ_Tracker {
    public function __construct() {
        register_activation_hook(__FILE__, array($this, 'activate'));
        add_shortcode('tvq_tracker', array($this, 'render_tracker'));
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_menu', array($this, 'add_profile_page'));
        add_action('admin_init', array($this, 'register_settings'));
        add_action('wp_ajax_tvq_tmdb_proxy', array($this, 'tmdb_proxy'));
        add_action('wp_ajax_nopriv_tvq_tmdb_proxy', array($this, 'tmdb_proxy'));
        add_action('wp_ajax_tvq_save_user_data', array($this, 'save_user_data'));
        add_action('wp_ajax_tvq_get_user_data', array($this, 'get_user_data'));
        add_action('wp_ajax_tvq_redirect', array($this, 'handle_profile_redirect'));
        add_action('show_user_profile', array($this, 'user_profile_fields'));
        add_action('edit_user_profile', array($this, 'user_profile_fields'));
        add_action('personal_options_update', array($this, 'save_user_profile_fields'));
        add_action('edit_user_profile_update', array($this, 'save_user_profile_fields'));
        add_action('admin_notices', array($this, 'display_upcoming_notifications'));
        add_action('wp_dashboard_setup', array($this, 'add_dashboard_widgets'));
    }

    public function activate() {
        add_role('tvq_user', 'TVQ User', array(
            'read' => true,
            'tvq_tracker_access' => true,
        ));

        $admin = get_role('administrator');
        if ($admin) {
            $admin->add_cap('tvq_tracker_access');
            $admin->add_cap('tvq_can_watch');
        }
    }

    public function register_settings() {
        register_setting('tvq_settings_group', 'tvq_tmdb_api_key');
        register_setting('tvq_settings_group', 'tvq_global_theme');
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
        register_setting('tvq_settings_group', 'tvq_admin_news');
        register_setting('tvq_settings_group', 'tvq_custom_css');
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
                        <th scope="row">Global Theme Color</th>
                        <td>
                            <select name="tvq_global_theme">
                                <option value="dark" <?php selected(get_option('tvq_global_theme'), 'dark'); ?>>Dark Mode</option>
                                <option value="light" <?php selected(get_option('tvq_global_theme'), 'light'); ?>>Light Mode</option>
                            </select>
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
                            <p class="description">ISO 639-1 code (e.g., 'en').</p>
                        </td>
                    </tr>
                    <tr valign="top">
                        <th scope="row">Default Country</th>
                        <td>
                            <input type="text" name="tvq_default_country" value="<?php echo esc_attr(get_option('tvq_default_country', '')); ?>" class="small-text" />
                            <p class="description">ISO 3166-1 alpha-2 (e.g., 'US'). Leave blank for All.</p>
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
                        </td>
                    </tr>
                    <tr valign="top">
                        <th scope="row">Grid Columns</th>
                        <td>
                            <input type="number" name="tvq_grid_columns" value="<?php echo esc_attr(get_option('tvq_grid_columns', 6)); ?>" min="1" max="10" />
                        </td>
                    </tr>
                    <tr valign="top">
                        <th scope="row">Premium Buy URL</th>
                        <td>
                            <input type="text" name="tvq_premium_buy_url" value="<?php echo esc_attr(get_option('tvq_premium_buy_url', 'http://zeaks.org')); ?>" class="regular-text" />
                        </td>
                    </tr>
                    <tr valign="top">
                        <th scope="row">Admin News / Updates</th>
                        <td>
                            <textarea name="tvq_admin_news" rows="3" cols="50" class="large-text" placeholder="Share updates with your users..."><?php echo esc_textarea(get_option('tvq_admin_news')); ?></textarea>
                            <p class="description">This message will appear on the user's TVQ Dashboard.</p>
                        </td>
                    </tr>
                    <tr valign="top">
                        <th scope="row">Custom CSS</th>
                        <td>
                            <textarea name="tvq_custom_css" rows="5" cols="50" class="large-text"><?php echo esc_textarea(get_option('tvq_custom_css')); ?></textarea>
                        </td>
                    </tr>
                </table>
                <?php submit_button(); ?>
            </form>
        </div>
        <?php
    }

    public function add_profile_page() {
        add_submenu_page('profile.php', 'My TVQ Tracker', 'My TVQ Tracker', 'read', 'my-tvq-tracker', array($this, 'my_tvq_page'));
    }

    public function my_tvq_page() {
        $user_id = get_current_user_id();
        $favs = get_user_meta($user_id, 'tvq_favs', true);
        if (is_string($favs)) $favs = json_decode($favs, true);
        if (!is_array($favs)) $favs = array();

        $watchlist = get_user_meta($user_id, 'tvq_watchlist', true);
        if (is_string($watchlist)) $watchlist = json_decode($watchlist, true);
        if (!is_array($watchlist)) $watchlist = array();

        $this->enqueue_scripts();
        ?>
        <div class="wrap tvq-tracker-container <?php echo esc_attr(get_option('tvq_global_theme', 'dark')); ?>-mode">
            <h1>📺 My TVQ Tracker Dashboard</h1>

            <div class="tvq-status-bar" style="background: #fdfdfd; padding: 15px; border-radius: 8px; border: 1px solid #ddd; margin-bottom: 25px; display: flex; gap: 30px;">
                <span><strong>TV Shows Tracked:</strong> <?php echo count(array_filter($watchlist, function($i){return isset($i['type']) && $i['type'] === 'tv';})); ?></span>
                <span><strong>Movies in Watchlist:</strong> <?php echo count(array_filter($watchlist, function($i){return isset($i['type']) && $i['type'] === 'movie';})); ?></span>
                <span><strong>Total Favourites:</strong> <?php echo count($favs); ?></span>
            </div>

            <div class="tvq-profile-data" style="max-width: 1200px;">
                <!-- Admin News Section -->
                <?php $news = get_option('tvq_admin_news'); if(!empty($news)): ?>
                <section id="tvq-admin-news" style="margin-bottom: 25px; background: #e7f3ff; padding: 20px; border-radius: 12px; border: 1px solid #2196f3;">
                    <h3>📢 Latest Updates</h3>
                    <div class="news-content"><?php echo wpautop(esc_html($news)); ?></div>
                </section>
                <?php endif; ?>

                <!-- Live Upcoming Section -->
                <section id="upcoming-notifications" style="margin-bottom: 40px; background: #fff9f9; padding: 20px; border-radius: 12px; border: 1px solid #ff5e57;">
                    <h3>📅 Upcoming Episodes (Next 7 Days)</h3>
                    <div id="profileUpcomingGrid" class="tvq-profile-grid">
                        <p class="loading">Checking your watchlist...</p>
                    </div>
                </section>

                <div class="tvq-profile-columns" style="display: grid; grid-template-columns: 1fr 1fr; gap: 30px;">
                    <section>
                        <h3>⭐ Favourite TV Series</h3>
                        <div class="tvq-profile-grid mini">
                            <?php foreach($favs as $item):
                                if(!is_array($item)) continue;
                                if(isset($item['type']) && $item['type'] === 'tv'):
                                    $id = isset($item['id']) ? $item['id'] : '';
                                    $name = isset($item['name']) ? $item['name'] : (isset($item['title']) ? $item['title'] : 'Unknown');
                                    $poster_path = (isset($item['poster_path']) && $item['poster_path'] !== 'null' && !empty($item['poster_path'])) ? $item['poster_path'] : '';
                                    $poster = !empty($poster_path) ? 'https://image.tmdb.org/t/p/w200' . $poster_path : 'https://placehold.co/200x300?text=No+Image';
                            ?>
                                <div class="tvq-profile-card" onclick="location.href='<?php echo admin_url('admin-ajax.php?action=tvq_redirect&id='.esc_attr($id).'&type=tv'); ?>'">
                                    <img src="<?php echo esc_url($poster); ?>" onerror="this.src='https://placehold.co/200x300?text=No+Image'" alt="<?php echo esc_attr($name); ?>" />
                                    <span><?php echo esc_html($name); ?></span>
                                </div>
                            <?php endif; endforeach; ?>
                        </div>
                    </section>
                    <section>
                        <h3>⭐ Favourite Movies</h3>
                        <div class="tvq-profile-grid mini">
                            <?php foreach($favs as $item):
                                if(!is_array($item)) continue;
                                if(isset($item['type']) && $item['type'] === 'movie'):
                                    $id = isset($item['id']) ? $item['id'] : '';
                                    $name = isset($item['name']) ? $item['name'] : (isset($item['title']) ? $item['title'] : 'Unknown');
                                    $poster_path = isset($item['poster_path']) ? $item['poster_path'] : '';
                                    $poster = !empty($poster_path) ? 'https://image.tmdb.org/t/p/w200' . $poster_path : 'https://placehold.co/200x300?text=No+Image';
                            ?>
                                <div class="tvq-profile-card" onclick="location.href='<?php echo admin_url('admin-ajax.php?action=tvq_redirect&id='.esc_attr($id).'&type=movie'); ?>'">
                                    <img src="<?php echo esc_url($poster); ?>" onerror="this.src='https://placehold.co/200x300?text=No+Image'" alt="<?php echo esc_attr($name); ?>" />
                                    <span><?php echo esc_html($name); ?></span>
                                </div>
                            <?php endif; endforeach; ?>
                        </div>
                    </section>
                </div>

                <section style="margin-top: 30px;">
                    <h3>📋 My Watchlist</h3>
                    <div class="tvq-profile-grid">
                        <?php foreach($watchlist as $item):
                            if(!is_array($item)) continue;
                            $id = isset($item['id']) ? $item['id'] : '';
                            $type = isset($item['type']) ? $item['type'] : 'tv';
                            $name = isset($item['name']) ? $item['name'] : (isset($item['title']) ? $item['title'] : 'Unknown');
                            $poster_path = (isset($item['poster_path']) && $item['poster_path'] !== 'null' && !empty($item['poster_path'])) ? $item['poster_path'] : '';
                            $poster = !empty($poster_path) ? 'https://image.tmdb.org/t/p/w200' . $poster_path : 'https://placehold.co/200x300?text=No+Image';
                        ?>
                            <div class="tvq-profile-card" onclick="location.href='<?php echo admin_url('admin-ajax.php?action=tvq_redirect&id='.esc_attr($id).'&type='.esc_attr($type)); ?>'">
                                <img src="<?php echo esc_url($poster); ?>" onerror="this.src='https://placehold.co/200x300?text=No+Image'" alt="<?php echo esc_attr($name); ?>" />
                                <span><?php echo esc_html($name); ?></span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </section>
            </div>
        </div>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                if (typeof tmdbFetch === 'function') {
                    async function loadProfileUpcoming() {
                        const grid = document.getElementById('profileUpcomingGrid');
                        if (!grid) return;

                        const watchlist = await getUserData('watchlist');
                        const shows = watchlist.filter(item => item.type === 'tv');

                        if (shows.length === 0) {
                            grid.innerHTML = '<p>Add TV shows to your watchlist to see upcoming episodes.</p>';
                            return;
                        }

                        grid.innerHTML = '<p class="loading">Loading schedule...</p>';

                        const today = new Date();
                        today.setHours(0,0,0,0);
                        const next7 = new Date();
                        next7.setDate(today.getDate() + 7);

                        let found = 0;
                        const promises = shows.map(show =>
                            tmdbFetch(`/tv/${show.id}`)
                                .then(details => {
                                    if (details.next_episode_to_air) {
                                        const ep = details.next_episode_to_air;
                                        const airDate = new Date(ep.air_date);
                                        if (airDate >= today && airDate <= next7) {
                                            found++;
                                            return {
                                                name: details.name,
                                                id: details.id,
                                                poster: details.poster_path,
                                                air_date: ep.air_date,
                                                ep_num: `S${ep.season_number}E${ep.episode_number}`
                                            };
                                        }
                                    }
                                    return null;
                                })
                                .catch(() => null)
                        );

                        const results = (await Promise.all(promises)).filter(r => r !== null);

                        if (results.length === 0) {
                            grid.innerHTML = '<p>No episodes airing in the next 7 days.</p>';
                        } else {
                            grid.innerHTML = '';
                            results.sort((a,b) => new Date(a.air_date) - new Date(b.air_date));
                            results.forEach(res => {
                                const card = document.createElement('div');
                                card.className = 'tvq-profile-card';
                                card.onclick = () => { window.location.href = `<?php echo admin_url('admin-ajax.php?action=tvq_redirect&id='); ?>${res.id}&type=tv`; };
                                card.innerHTML = `
                                    <img src="https://image.tmdb.org/t/p/w200${res.poster}" onerror="this.src='https://placehold.co/200x300?text=No+Image'" />
                                    <strong>${res.name}</strong>
                                    <small>${res.ep_num}</small>
                                    <small style="color: #ff5e57; font-weight: bold;">${res.air_date}</small>
                                `;
                                grid.appendChild(card);
                            });
                        }
                    }
                    loadProfileUpcoming();
                }
            });
        </script>
        <style>
            .tvq-profile-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(140px, 1fr)); gap: 20px; }
            .tvq-profile-grid.mini { grid-template-columns: repeat(auto-fill, minmax(110px, 1fr)); }
            .tvq-profile-card { text-align: center; cursor: pointer; background: #fff; padding: 10px; border-radius: 8px; border: 1px solid #eee; display: flex; flex-direction: column; transition: transform 0.2s; }
            .tvq-profile-card:hover { transform: translateY(-3px); border-color: #ff5e57; }
            .tvq-profile-card img { width: 100%; aspect-ratio: 2/3; object-fit: cover; border-radius: 6px; margin-bottom: 8px; }
            .tvq-profile-card span { font-size: 13px; line-height: 1.2; height: 32px; overflow: hidden; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; }
            .tvq-profile-data h3 { border-bottom: 2px solid #ff5e57; padding-bottom: 8px; margin-top: 30px; }
            @media (max-width: 800px) { .tvq-profile-columns { grid-template-columns: 1fr !important; } }
        </style>
        <?php
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
                        <input type="checkbox" name="tvq_can_watch" value="1" <?php checked($can_watch); ?>> Allow "Watch Now" feature.
                    </td>
                </tr>
                <?php endif; ?>
                <tr>
                    <th>My Tracker Data</th>
                    <td><a href="<?php echo admin_url('profile.php?page=my-tvq-tracker'); ?>" class="button">View Dashboard</a></td>
                </tr>
            </table>
        </div>
        <?php
    }

    public function save_user_profile_fields($user_id) {
        if (current_user_can('manage_options')) {
            $user = get_userdata($user_id);
            if (isset($_POST['tvq_can_watch'])) $user->add_cap('tvq_can_watch');
            else $user->remove_cap('tvq_can_watch');
        }
    }

    public function handle_profile_redirect() {
        $id = $_GET['id']; $type = $_GET['type'];
        global $wpdb; $post_id = $wpdb->get_var("SELECT post_id FROM $wpdb->postmeta WHERE meta_key = '_tvq_has_shortcode' LIMIT 1");
        $url = $post_id ? get_permalink($post_id) : home_url('/');
        wp_redirect($url . '#view=' . ($type === 'tv' ? 'show' : 'movie') . '&id=' . $id);
        exit;
    }

    public function tmdb_proxy() {
        check_ajax_referer('tvq_nonce', 'nonce');
        $api_key = get_option('tvq_tmdb_api_key');
        if (!$api_key) wp_send_json_error('API Key missing', 400);
        $endpoint = sanitize_text_field($_GET['endpoint']);
        $params = $_GET; unset($params['action'], $params['endpoint'], $params['nonce']);
        $params['api_key'] = $api_key;
        $url = add_query_arg($params, 'https://api.themoviedb.org/3' . $endpoint);
        $response = wp_remote_get($url);
        if (is_wp_error($response)) wp_send_json_error($response->get_error_message(), 500);
        wp_send_json(json_decode(wp_remote_retrieve_body($response)));
    }

    public function display_upcoming_notifications() {
        $screen = get_current_screen();
        if ($screen->id === 'dashboard') {
            ?>
            <div class="notice notice-info is-dismissible">
                <p><strong>📺 TVQ Tracker:</strong> Check your <a href="<?php echo admin_url('profile.php?page=my-tvq-tracker'); ?>">TVQ Dashboard</a> for new episodes!</p>
            </div>
            <?php
        }
    }

    public function add_dashboard_widgets() {
        wp_add_dashboard_widget(
            'tvq_news_widget',
            '📺 TVQ Tracker Updates',
            array($this, 'news_widget_display')
        );
    }

    public function news_widget_display() {
        $news = get_option('tvq_admin_news');
        if (!empty($news)) {
            echo wpautop(esc_html($news));
        } else {
            echo '<p>No new updates at this time. Check back later!</p>';
        }
        echo '<hr><p><a href="' . admin_url('profile.php?page=my-tvq-tracker') . '" class="button">Go to TVQ Dashboard</a></p>';
    }

    public function save_user_data() {
        check_ajax_referer('tvq_nonce', 'nonce');
        if (is_user_logged_in()) update_user_meta(get_current_user_id(), sanitize_text_field($_POST['key']), $_POST['data']);
        wp_send_json_success();
    }

    public function get_user_data() {
        check_ajax_referer('tvq_nonce', 'nonce');
        $data = get_user_meta(get_current_user_id(), sanitize_text_field($_GET['key']), true);
        wp_send_json_success($data ? $data : array());
    }

    public function render_tracker($atts) {
        $this->enqueue_scripts();
        if (is_singular()) update_post_meta(get_the_ID(), '_tvq_has_shortcode', '1');
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
                    <a href="#" data-view="help">❓ Help</a>
                </div>
            </nav>

            <div class="main-layout no-sidebar">
                <main class="content-area">
                    <header class="content-header">
                        <div class="search-bar">
                            <input type="text" id="searchInput" placeholder="Search for shows or movies..." />
                        </div>
                        <div class="filter-bar" id="tvq-filters">
                            <div class="filter-group"><label>Sort By</label>
                                <select id="sortBy" class="filter-select">
                                    <option value="popularity.desc">Popularity</option>
                                    <option value="first_air_date.desc">Latest Aired</option>
                                    <option value="vote_average.desc">Highest Rated</option>
                                </select>
                            </div>
                            <div class="filter-group"><label>Genre</label>
                                <select id="genreSelect" class="filter-select">
                                    <option value="">All Genres</option>
                                    <option value="10759">Action</option><option value="16">Animation</option><option value="35">Comedy</option><option value="80">Crime</option><option value="99">Documentary</option><option value="18">Drama</option><option value="10751">Family</option><option value="10765">Sci-Fi</option><option value="10766">Soap</option><option value="10767">Talk</option>
                                </select>
                            </div>
                            <div class="filter-group"><label>Countries</label>
                                <div class="dropdown-multi" id="countryDropdown">
                                    <div class="dropdown-multi-label" onclick="toggleMultiDropdown('countryDropdown')">All Countries</div>
                                    <div class="dropdown-multi-content" id="countryFilter">
                                        <label class="all-opt"><input type="checkbox" value="" checked> All Countries</label>
                                        <hr style="margin: 5px 0; border: 0; border-top: 1px solid #444;">
                                        <label><input type="checkbox" value="US"> 🇺🇸 USA</label>
                                        <label><input type="checkbox" value="GB"> 🇬🇧 UK</label>
                                        <label><input type="checkbox" value="CA"> 🇨🇦 Canada</label>
                                        <label><input type="checkbox" value="AU"> 🇦🇺 Australia</label>
                                        <label><input type="checkbox" value="KR"> 🇰🇷 South Korea</label>
                                        <label><input type="checkbox" value="JP"> 🇯🇵 Japan</label>
                                        <label><input type="checkbox" value="DE"> 🇩🇪 Germany</label>
                                        <label><input type="checkbox" value="FR"> 🇫🇷 France</label>
                                        <label><input type="checkbox" value="ES"> 🇪🇸 Spain</label>
                                        <label><input type="checkbox" value="MX"> 🇲🇽 Mexico</label>
                                    </div>
                                </div>
                            </div>
                            <div class="filter-group checkbox-group">
                                <input type="checkbox" id="englishOnly"> <label for="englishOnly">English Only</label>
                            </div>
                        </div>
                    </header>
                    <div id="view-container">
                        <section id="trendingSection" style="display: none;">
                            <h2 id="trendingTitle">Trending Now</h2>
                            <div id="trendingGrid" class="horizontal-scroll"></div>
                        </section>
                        <section id="searchSection" style="display: none;"><h2>🔍 Search Results</h2><div id="searchResults" class="show-grid"></div></section>
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
                        <section id="helpView" style="display: none;">
                            <h2>❓ TVQ Tracker Help & Usage</h2>
                            <div class="help-content" style="background: rgba(255,255,255,0.05); padding: 25px; border-radius: 12px; line-height: 1.6;">
                                <h3>🚀 Getting Started</h3>
                                <p>Welcome to TVQ Tracker! Use the navigation bar at the top to switch between TV Shows and Movies. You can search for specific titles using the search bar.</p>

                                <h3>⭐ Favourites & Watchlist</h3>
                                <p>Click on any show or movie to see its details. You can add items to your <strong>Favourites</strong> or your <strong>Watchlist</strong> by clicking the buttons on the details page. These are saved to your account and can be accessed from the navigation menu or your profile dashboard.</p>

                                <h3>📊 Tracking Your Progress</h3>
                                <p>On any TV show page, you'll see a <strong>My Episode Progress</strong> section. Enter the Season and Episode number of the last episode you watched and click "Save Progress". The "Watch Now" button will then automatically suggest the next episode for you.</p>

                                <h3>▶️ Watching Content</h3>
                                <p>If you have the premium addon active and permission from the site administrator, you will see a <strong>Watch Now</strong> button. This will open a secure player. You can also select specific episodes to watch directly from the list on the details page or from the player page itself.</p>

                                <h3>📅 Using the Calendar</h3>
                                <p>The <strong>Calendar</strong> view shows you which shows from your watchlist have new episodes airing in the next 7 days. This helps you stay up to date with your favorite series.</p>

                                <h3>🌓 Theme Settings</h3>
                                <p>You can toggle between Dark and Light mode from your <strong>My TVQ Tracker</strong> dashboard located in your WordPress Profile menu.</p>
                            </div>
                        </section>
                    </div>
                </main>
            </div>
        </div>
        <style><?php echo get_option('tvq_custom_css'); ?></style>
        <?php
        return ob_get_clean();
    }

    private function enqueue_scripts() {
        if (!function_exists('is_plugin_active')) require_once(ABSPATH . 'wp-admin/includes/plugin.php');
        wp_enqueue_style('tvq-style', plugins_url('css/style.css', __FILE__));
        wp_enqueue_script('tvq-utils', plugins_url('js/utils.js', __FILE__), array(), '1.1.0', true);
        wp_enqueue_script('tvq-theme', plugins_url('js/theme.js', __FILE__), array('tvq-utils'), '1.1.0', true);
        wp_enqueue_script('tvq-main', plugins_url('js/main.js', __FILE__), array('tvq-theme'), '1.1.0', true);
        wp_enqueue_script('tvq-show', plugins_url('js/show.js', __FILE__), array('tvq-main'), '1.1.0', true);
        wp_enqueue_script('tvq-movie', plugins_url('js/movie.js', __FILE__), array('tvq-main'), '1.1.0', true);
        wp_enqueue_script('tvq-watch', plugins_url('js/watch.js', __FILE__), array('tvq-main'), '1.1.0', true);
        wp_enqueue_script('tvq-calendar', plugins_url('js/calendar.js', __FILE__), array('tvq-main'), '1.1.0', true);
        wp_enqueue_script('tvq-favourites', plugins_url('js/favourites.js', __FILE__), array('tvq-main'), '1.1.0', true);
        wp_enqueue_script('tvq-watchlist', plugins_url('js/watchlist.js', __FILE__), array('tvq-main'), '1.1.0', true);

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
            'trending_type' => get_option('tvq_trending_type', 'trending'),
            'global_theme' => get_option('tvq_global_theme', 'dark')
        ));
    }
}
new TVQ_Tracker();
