<?php
/**
 * Plugin Name: TVQ Watch Premium
 * Description: Unlocks the "Watch Now" feature for the TVQ Tracker plugin with SuperEmbed Player integration.
 * Version: 1.0.1
 * Author: TVQ Team
 */

if (!defined('ABSPATH')) exit;

class TVQ_Watch_Premium {
    public function __construct() {
        add_action('wp_ajax_tvq_get_player', array($this, 'get_player_url'));
    }

    public function get_player_url() {
        check_ajax_referer('tvq_nonce', 'nonce');

        $tmdb_id = isset($_GET['id']) ? sanitize_text_field($_GET['id']) : '';
        $type = isset($_GET['type']) ? sanitize_text_field($_GET['type']) : 'tv';

        // SuperEmbed Player Logic
        $url = "https://multiembed.mov/directstream.php?video_id=$tmdb_id&tmdb=1";

        wp_send_json_success(array('url' => $url));
    }
}

new TVQ_Watch_Premium();
