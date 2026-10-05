<?php
/*
if (session_status() === PHP_SESSION_NONE) {
session_set_cookie_params([
    'lifetime' => 0,
    'path' => '/',
    'domain' => '.tsunamiflow.club',
    'secure' => true,
    'httponly' => true,
    'samesite' => 'None'
]);

session_start();
} else {

}
*/
/*
//server listens on this port below
const PORT = process.env.PORT || 3000;

app.listen(PORT, "0.0.0.0", () => {
  console.log(`Server listening on ${PORT}`);
});
*/
$allowed_origins = [
    "https://tsunamiflow.club",
    "https://tsunamiflow.onrender.com",
    "https://world-l87q.onrender.com"
];

session_start();

header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Origin, Content-Type, Accept, Authorization, X-Requested-With");

if (isset($_SERVER['HTTP_ORIGIN']) && in_array($_SERVER['HTTP_ORIGIN'], $allowed_origins, true)) {
    header("Access-Control-Allow-Origin: " . $_SERVER['HTTP_ORIGIN']);
    header("Vary: Origin");
}

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    //http_response_code(200); // shows 200
    http_response_code(204); // No Content shown but passes.
    exit;
}

error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

//require_once __DIR__ . "/../routes/store.php";
require_once __DIR__ . "/Class/Websocket.php";

$TycadomeBackend = new BasicServer("testing");

switch ($_SERVER['REQUEST_METHOD']) {
    case "fetchRadioSongs":
        $TycadomeBackend::class::RadioPlaylist($_SERVER['REQUEST_METHOD'], "accessKey", "secretKey", "endpoint", "array");
        break;

    default:

        break;

}

/*
//Printful
$myProductsFr = $_SESSION['PrintfulItems'] ?? BasicPrintfulRequest();
if (!isset($myProductsFr['result']) || !is_array($myProductsFr['result'])) {
    $myProductsFr['result'] = [];
}
$showSuccess = true;
*/
?>