<?php
////////////////////// SUPEREMBED PLAYER SCRIPT //////////////////////////////////////////
//////////////////////////////////////////////////////////////////////////////////////////
////////////////////////// PLAYER SETTINGS ///////////////////////////////////////////////

// Player font - paste font name from Google fonts, replace spaces with +
$player_font = "Poppins";

// Player colors - paste color code in HEX format without # eg. 123456
$player_bg_color = "000000";        // Background color
$player_font_color = "ffffff";      // Font color
$player_primary_color = "34cfeb";   // Primary color for loader and buttons
$player_secondary_color = "6900e0"; // Secondary color for hovers and elements

// Player loader - choose a loading animation from 1 to 10
$player_loader = 1;

// Preferred server for quality >= 720p
// Options: vidlox = 7, fembed = 11, mixdrop = 12, upstream = 17, etc.
// Set to 0 for no preference
$preferred_server = 0;

// Source list style:
// 1 = full-page overlay
// 2 = dropdown menu
$player_sources_toggle_type = 2;

//////////////////////////////////////////////////////////////////////////////////////////
//////////////////////////////////////////////////////////////////////////////////////////

if (isset($_GET['video_id'])) {
    $video_id = $_GET['video_id'];
    $is_tmdb = $_GET['tmdb'] ?? 0;
    $season = $_GET['season'] ?? ($_GET['s'] ?? 0);
    $episode = $_GET['episode'] ?? ($_GET['e'] ?? 0);

    $request_url = "https://getsuperembed.link/?video_id=$video_id&tmdb=$is_tmdb&season=$season&episode=$episode&player_font=$player_font&player_bg_color=$player_bg_color&player_font_color=$player_font_color&player_primary_color=$player_primary_color&player_secondary_color=$player_secondary_color&player_loader=$player_loader&preferred_server=$preferred_server&player_sources_toggle_type=$player_sources_toggle_type";

    if (function_exists('curl_version')) {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $request_url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($curl, CURLOPT_TIMEOUT, 7);
        curl_setopt($curl, CURLOPT_HEADER, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
        $player_url = curl_exec($curl);
        curl_close($curl);
    } else {
        $player_url = file_get_contents($request_url);
    }

    if (!empty($player_url)) {
        if (strpos($player_url, "https://") !== false) {
            echo "<iframe src=\"$player_url\" width=\"100%\" height=\"100%\" style=\"border:none;\" allowfullscreen></iframe>";
        } else {
            echo "<span style='color:red'>$player_url</span>";
        }
    } else {
        echo "<span style='color:red'>Request server didn't respond</span>";
    }
} else {
    echo "<span style='color:red'>Missing video_id</span>";
}
?>
// TV Tracker Player
