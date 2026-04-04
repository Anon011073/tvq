<?php
// api/tmdb.php
header('Content-Type: application/json');

require_once __DIR__ . '/db.php';

// Fetch API key from DB
$api_key = '0186591f1a581e28945625c27f33d024'; // Default fallback
try {
    $stmt = $pdo->prepare("SELECT value FROM settings WHERE setting_key = 'tmdb_api_key'");
    $stmt->execute();
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($row) $api_key = $row['value'];
} catch (Exception $e) {}

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
