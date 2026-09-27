<?php
require_once __DIR__ . "/../Tycadome/Function/functions.php";
require_once __DIR__ . "/../Tycadome/Arrays/tycadomeArrays.php";
require_once __DIR__ . "/../Tycadome/Variables/tycadomeVariables.php";
require_once __DIR__ . "/vendor/autoload.php";

if ($requestType !== 'fetchRadioSongs') {
    http_response_code(400);
    respond(["error" => "Invalid Request Type"]);
    exit;
} else {
// --- Your category array (kept same shape) ---

// --- Initialize S3 (Cloudflare R2) client once, using env vars for safety ---


if (!$accessKey || !$secretKey || !$r2Endpoint) {
    // If credentials are missing, respond with an error instead of silently failing
    http_response_code(500);
    respond([
        "error" => "Missing R2 credentials or endpoint. Set R2_ACCESS_KEY, R2_SECRET_KEY and R2_ENDPOINT environment variables."
    ]);
    exit;
}

// 12 a.m Rizz IceBreakers Flirting GetHerDone Shot
addSongsToArray("Music/Rizz/IceBreakers/", $sentToJsArray, 0, 0);
addSongsToArray("Music/Rizz/Flirting/", $sentToJsArray, 0, 1);
addSongsToArray("Music/Rizz/GetHerDone/", $sentToJsArray, 0, 2);
addSongsToArray("Music/Rizz/Shots/", $sentToJsArray, 0, 3);

// 1 a.m.
addSongsToArray("Music/Dance/Twerking/", $sentToJsArray, 1, 0);
addSongsToArray("Music/Dance/LineDance/", $sentToJsArray, 1, 1);
addSongsToArray("Music/Dance/PopDance/", $sentToJsArray, 1, 2);
addSongsToArray("Music/Dance/Battle/", $sentToJsArray, 1, 3);

// 2 a.m
addSongsToArray("Music/Afterparty/", $sentToJsArray, 2, null);

// 3 a.m Sex
addSongsToArray("Music/Sex/Foreplay/", $sentToJsArray, 3, 0);
addSongsToArray("Music/Sex/sex/", $sentToJsArray, 3, 1);
addSongsToArray("Music/Sex/Cuddle/", $sentToJsArray, 3, 2);

// 4 a.m Love 
addSongsToArray("Music/Love/Memories/", $sentToJsArray, 4, 0);
addSongsToArray("Music/Love/love/", $sentToJsArray, 4, 1);
addSongsToArray("Music/Love/Intimacy/", $sentToJsArray, 4, 2);

// 5 a.m Family
addSongsToArray("Music/Family/Lifestyle/", $sentToJsArray, 5, 0);
addSongsToArray("Music/Family/Values/", $sentToJsArray, 5, 1);
addSongsToArray("Music/Family/Kids/", $sentToJsArray, 5, 2);

// 6 a.m Inspiration
addSongsToArray("Music/Inspiration/Motivation/", $sentToJsArray, 6, 0);
addSongsToArray("Music/Inspiration/Meditation/", $sentToJsArray, 6, 1);
addSongsToArray("Music/Inspiration/Something/", $sentToJsArray, 6, 2);

// 7 a.m History 
addSongsToArray("Music/History/DH/", $sentToJsArray, 7, 0);
addSongsToArray("Music/History/BAH/", $sentToJsArray, 7, 1);
addSongsToArray("Music/History/HFnineteen/", $sentToJsArray, 7, 2);

// 8 a.m Politics 
addSongsToArray("Music/Politics/Neutral/", $sentToJsArray, 8, 0);
addSongsToArray("Music/Politics/Democracy/", $sentToJsArray, 8, 1);
addSongsToArray("Music/Politics/Republican/", $sentToJsArray, 8, 2);
addSongsToArray("Music/Politics/Socialism/", $sentToJsArray, 8, 3);
addSongsToArray("Music/Politics/Bureaucracy/", $sentToJsArray, 8, 4);
addSongsToArray("Music/Politics/Aristocratic/", $sentToJsArray, 8, 5);

// 9 a.m. Gaming 
addSongsToArray("Music/Gaming/Fighters/", $sentToJsArray, 9, 0);
addSongsToArray("Music/Gaming/Shooters/", $sentToJsArray, 9, 1);
addSongsToArray("Music/Gaming/Instrumentals/", $sentToJsArray, 9, 2);

// 10 a.m. Comedy
addSongsToArray("Music/Comedy/", $sentToJsArray, 10, null);

// 12 a.m Literature
addSongsToArray("Music/Literature/Poems/", $sentToJsArray, 12, 0);
addSongsToArray("Music/Literature/SS/", $sentToJsArray, 12, 1);
addSongsToArray("Music/Literature/Instrumentals/", $sentToJsArray, 12, 2);
addSongsToArray("Music/Literature/Books/", $sentToJsArray, 12, 3);

// 1 p.m Sports 
addSongsToArray("Music/Sports/Reviews/", $sentToJsArray, 13, 0);
addSongsToArray("Music/Sports/Fans/", $sentToJsArray, 13, 1);
addSongsToArray("Music/Sports/Updates/", $sentToJsArray, 13, 2);
addSongsToArray("Music/Sports/Disses/", $sentToJsArray, 13, 3);

// 2 p.m Tech
addSongsToArray("Music/Tech/News/", $sentToJsArray, 14, 0);
addSongsToArray("Music/Tech/Music/", $sentToJsArray, 14, 1);
addSongsToArray("Music/Tech/History/", $sentToJsArray, 14, 2);
addSongsToArray("Music/Tech/Instrumentals/", $sentToJsArray, 14, 3);

// 3 p.m Science 
addSongsToArray("Music/Science/Biology/", $sentToJsArray, 15, 0);
addSongsToArray("Music/Science/Chemistry/", $sentToJsArray, 15, 1);
addSongsToArray("Music/Science/Physics/", $sentToJsArray, 15, 2);
addSongsToArray("Music/Science/Environmental/", $sentToJsArray, 15, 3);

// 4 p.m Real Estate EP, Seller, Mortgage, Buyer
addSongsToArray("Music/RealEstate/EP/", $sentToJsArray, 16, 0);
addSongsToArray("Music/RealEstate/Seller/", $sentToJsArray, 16, 1);
addSongsToArray("Music/RealEstate/Mortgage/", $sentToJsArray, 16, 2);
addSongsToArray("Music/RealEstate/Buyer/", $sentToJsArray, 16, 3);

// 5 p.m DJ Shuba (Basically New Music)
addSongsToArray("Music/DJshuba/", $sentToJsArray, 17, null);

// 6 p.m Film
addSongsToArray("Music/Film/NM/", $sentToJsArray, 18, 0);
addSongsToArray("Music/Film/SHM/", $sentToJsArray, 18, 1);
addSongsToArray("Music/Film/MH/", $sentToJsArray, 18, 2);
addSongsToArray("Music/Film/VM/", $sentToJsArray, 18, 3);
addSongsToArray("Music/Film/LS/", $sentToJsArray, 18, 4);
addSongsToArray("Music/Film/CB/", $sentToJsArray, 18, 5);

// 7 p.m Fashion PD, LD, FH, SM 
addSongsToArray("Music/Fashion/PD/", $sentToJsArray, 19, 0);
addSongsToArray("Music/Fashion/LD/", $sentToJsArray, 19, 1);
addSongsToArray("Music/Fashion/FH/", $sentToJsArray, 19, 2);
addSongsToArray("Music/Fashion/SM/", $sentToJsArray, 19, 3);

// 8 p.m Business 
addSongsToArray("Music/Business/FE/", $sentToJsArray, 20, 0);
addSongsToArray("Music/Business/TOB/", $sentToJsArray, 20, 1);
addSongsToArray("Music/Business/Insurance/", $sentToJsArray, 20, 2);
addSongsToArray("Music/Business/TE/", $sentToJsArray, 20, 3);

// Hustlin
addSongsToArray("Music/Hustlin/", $sentToJsArray, 21, null);

// Pregame
addSongsToArray("Music/Pregame/", $sentToJsArray, 22, null);

// Outside
addSongsToArray("Music/Outside/", $sentToJsArray, 23, null);


// --- Finally output JSON ---
$sentToJsArray[11] = array_values(array_unique($sentToJsArray[11]));
$TsunamiFlowRadio = json_encode($sentToJsArray, JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_IGNORE | JSON_UNESCAPED_SLASHES);

/* -------------------------
   RESPONSE
   ------------------------- */
echo respond($sentToJsArray);
exit;
}