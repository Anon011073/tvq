<?php
// api/tmdb.php
header('Content-Type: application/json');

$api_key = '0186591f1a581e28945625c27f33d024';
$base_url = 'https://api.themoviedb.org/3';

$endpoint = $_GET['endpoint'] ?? '';

if (!$endpoint) {
    echo json_encode(['error' => 'No endpoint provided']);
    exit;
}

// Manually parse the full query string from the REQUEST_URI to avoid PHP's dot-to-underscore conversion
$queryString = '';
$parts = explode('endpoint=', $_SERVER['REQUEST_URI']);
if (count($parts) > 1) {
    $queryString = urldecode($parts[1]);
}

// Determine if we need to add the API key
$connector = (strpos($queryString, '?') !== false) ? '&' : '?';
$url = $base_url . $queryString . $connector . 'api_key=' . $api_key;

// If the endpoint starts with /search or /discover, it might have multiple params
// We've already captured them in $queryString

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
$response = curl_exec($ch);

if (curl_errno($ch)) {
    echo json_encode(['error' => curl_error($ch)]);
} else {
    echo $response;
}
curl_close($ch);
?>
