<?php
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

if (in_array($_SERVER['HTTP_ORIGIN'] , $allowed_origins)) {
    header("Access-Control-Allow-Origin: " . $_SERVER['HTTP_ORIGIN'] );
}

header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Origin, Content-Type, Accept, Authorization, X-Requested-With");

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    //http_response_code(200); // shows 200
    http_response_code(204); // No Content shown but passes.
    exit;
}

// ============================
// ERROR REPORTING (DEV)
// ============================
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

// ============================
// SESSION
// ============================
session_start();

//require_once __DIR__ . "/Function/functions.php";
//require_once __DIR__ . "../vendor/autoload.php";
require_once __DIR__ . "/../src/Printful.php";
require_once __DIR__ . "/../src/RadioPlaylist.php";

//Printful
$myProductsFr = $_SESSION['PrintfulItems'] ?? BasicPrintfulRequest();
if (!isset($myProductsFr['result']) || !is_array($myProductsFr['result'])) {
    $myProductsFr['result'] = [];
}
$showSuccess = true;