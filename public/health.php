<?
<?php
// health.php - lightweight readiness check.
// Keep this free of DB calls and heavy includes so it responds as soon as the server is up.

header('Content-Type: application/json');
header('Cache-Control: no-store');

// Allow your frontend to call this cross-origin. Replace * with your real frontend origin.
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, HEAD, OPTIONS');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

http_response_code(200);
echo json_encode([
    'status' => 'ok',
    'time'   => gmdate('c'),
]);

?>