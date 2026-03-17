<?php
// File: api/tmdb.php
header('Content-Type: application/json');

$apiKey = 'b6b677eb7d4ec17f700e3d4dfc31d005';
$endpoint = $_GET['endpoint'] ?? '';
$params = $_GET;
unset($params['endpoint']);

$baseUrl = 'https://api.themoviedb.org/3' . $endpoint;
$params['api_key'] = $apiKey;

$fullUrl = $baseUrl . '?' . http_build_query($params);

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $fullUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpCode !== 200) {
    http_response_code($httpCode);
    echo json_encode(['error' => 'Failed to fetch from TMDB', 'url' => $fullUrl]);
} else {
    echo $response;
}
