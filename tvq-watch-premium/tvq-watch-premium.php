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
        $season = isset($_GET['s']) ? sanitize_text_field($_GET['s']) : '1';
        $episode = isset($_GET['e']) ? sanitize_text_field($_GET['e']) : '1';

        // SuperEmbed Player Logic
        // For movies: video_id=$id&tmdb=1
        // For TV: video_id=$id&tmdb=1&s=$season&e=$episode
        $url = "https://multiembed.mov/directstream.php?video_id=$tmdb_id&tmdb=1";
        if ($type === 'tv') {
            $url .= "&s=$season&e=$episode";
        }

        wp_send_json_success(array('url' => $url));
    }
}

new TVQ_Watch_Premium();
